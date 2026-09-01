<?php
/**
 * Vendor Bookings Management
 */

$page_title = "Manage Bookings";
$path_prefix = '../';
require_once __DIR__ . '/../includes/vendor_auth.php';
require_once __DIR__ . '/../includes/header.php';

$vendorId = (int)$_SESSION['vendor_id'];
$statusFilter = trim($_GET['status'] ?? 'all');

// Handle Status Transitions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $rentalId = (int)($_POST['rental_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? '');

    $allowedStatuses = ['approved', 'rejected', 'active', 'returned'];
    if ($rentalId && in_array($newStatus, $allowedStatuses)) {
        try {
            // Verify booking belongs to this vendor
            $checkStmt = $pdo->prepare("
                SELECT r.*, d.dress_name, d.dress_id, u.user_id, u.full_name as customer_name
                FROM rentals r 
                JOIN dresses d ON r.dress_id = d.dress_id 
                JOIN users u ON r.user_id = u.user_id
                WHERE r.rental_id = :rental_id AND d.vendor_id = :vendor_id
                LIMIT 1
            ");
            $checkStmt->execute([':rental_id' => $rentalId, ':vendor_id' => $vendorId]);
            $booking = $checkStmt->fetch();

            if ($booking) {
                $updateFields = "rental_status = :new_status";
                $extraParams = [':new_status' => $newStatus, ':rental_id' => $rentalId];

                if ($newStatus === 'approved') {
                    $updateFields .= ", approved_at = NOW()";
                    // Notify Customer
                    create_notification($pdo, 'user', $booking['user_id'], 'Booking Approved!', "Your rental request for {$booking['dress_name']} has been approved by the boutique.", 'success', 'user/bookings.php');
                } elseif ($newStatus === 'active') {
                    // Mark dress as rented
                    $pdo->prepare("UPDATE dresses SET availability = 'rented' WHERE dress_id = :dress_id")->execute([':dress_id' => $booking['dress_id']]);
                    create_notification($pdo, 'user', $booking['user_id'], 'Dress Dispatched / Active', "Your rented gown {$booking['dress_name']} is now active.", 'info', 'user/bookings.php');
                } elseif ($newStatus === 'returned') {
                    $updateFields .= ", returned_at = NOW()";
                    // Restore dress availability to available
                    $pdo->prepare("UPDATE dresses SET availability = 'available' WHERE dress_id = :dress_id")->execute([':dress_id' => $booking['dress_id']]);
                    create_notification($pdo, 'user', $booking['user_id'], 'Return Confirmed', "The boutique has confirmed the return of {$booking['dress_name']}. Thank you for renting with Cloud Closet!", 'success', 'user/bookings.php');
                } elseif ($newStatus === 'rejected') {
                    // Update payment to refunded
                    $pdo->prepare("UPDATE payments SET payment_status = 'refunded' WHERE rental_id = :rental_id")->execute([':rental_id' => $rentalId]);
                    create_notification($pdo, 'user', $booking['user_id'], 'Booking Request Declined', "Unfortunately your booking request for {$booking['dress_name']} could not be fulfilled.", 'error', 'user/bookings.php');
                }

                $stmt = $pdo->prepare("UPDATE rentals SET $updateFields WHERE rental_id = :rental_id");
                $stmt->execute($extraParams);

                set_flash('success', "Booking #ORD-" . str_pad($rentalId, 5, '0', STR_PAD_LEFT) . " status updated to " . ucfirst($newStatus) . ".");
                header("Location: bookings.php" . ($statusFilter !== 'all' ? "?status=$statusFilter" : ""));
                exit();
            }
        } catch (Exception $e) {
            set_flash('error', "Error updating booking status: " . $e->getMessage());
        }
    }
}

// Fetch bookings
$sql = "
    SELECT r.*, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address,
           d.dress_name, d.image, d.size, d.color, p.payment_method, p.payment_status
    FROM rentals r
    JOIN users u ON r.user_id = u.user_id
    JOIN dresses d ON r.dress_id = d.dress_id
    LEFT JOIN payments p ON r.rental_id = p.rental_id
    WHERE d.vendor_id = :vendor_id
";
$params = [':vendor_id' => $vendorId];

if ($statusFilter !== 'all') {
    $sql .= " AND r.rental_status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY r.request_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Client Bookings</h1>
                    <p class="page-subtitle-text">Approve incoming rental requests, dispatch outfits, and track returns.</p>
                </div>
            </div>

            <!-- Status Filter Tabs -->
            <div class="dashboard-card" style="padding: 14px 20px; margin-bottom: 24px;">
                <div class="chart-toggle-group" style="background: transparent; flex-wrap: wrap; gap: 8px;">
                    <a href="bookings.php?status=all" class="chart-toggle-btn <?php echo ($statusFilter === 'all') ? 'active' : ''; ?>">All Requests</a>
                    <a href="bookings.php?status=pending" class="chart-toggle-btn <?php echo ($statusFilter === 'pending') ? 'active' : ''; ?>">Pending Review</a>
                    <a href="bookings.php?status=approved" class="chart-toggle-btn <?php echo ($statusFilter === 'approved') ? 'active' : ''; ?>">Approved / Ready</a>
                    <a href="bookings.php?status=active" class="chart-toggle-btn <?php echo ($statusFilter === 'active') ? 'active' : ''; ?>">Active / Out with Client</a>
                    <a href="bookings.php?status=returned" class="chart-toggle-btn <?php echo ($statusFilter === 'returned') ? 'active' : ''; ?>">Returned Archive</a>
                </div>
            </div>

            <!-- Bookings List -->
            <div class="dashboard-card">
                <?php if (!empty($bookings)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer Info</th>
                                    <th>Dress</th>
                                    <th>Dates</th>
                                    <th>Total Fee</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Manage Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): 
                                    $thumb = !empty($b['image']) && file_exists(__DIR__ . '/../' . $b['image']) ? '../' . $b['image'] : '../assets/images/hero_closet_banner.jpg';
                                ?>
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold">#ORD-<?php echo str_pad($b['rental_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                            <span class="text-muted" style="display: block; font-size: 0.72rem;"><?php echo date('M d, Y', strtotime($b['request_date'])); ?></span>
                                        </td>
                                        <td>
                                            <div class="table-user-cell">
                                                <div class="user-avatar-circle" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    <?php echo strtoupper(substr($b['customer_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <span class="font-weight-bold" style="color: var(--text-primary); display: block;"><?php echo htmlspecialchars($b['customer_name']); ?></span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">Phone: <?php echo htmlspecialchars($b['customer_phone']); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?php echo htmlspecialchars($b['dress_name']); ?></span>
                                            <span class="text-muted" style="display: block; font-size: 0.75rem;">Size <?php echo htmlspecialchars($b['size'] ?? 'M'); ?></span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; font-weight: 600;">
                                                <?php echo format_date($b['rental_start_date'], 'M d'); ?> - <?php echo format_date($b['rental_end_date'], 'M d, Y'); ?>
                                            </span>
                                            <span class="text-muted" style="display: block; font-size: 0.72rem;"><?php echo $b['total_days']; ?> Days</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta"><?php echo format_currency($b['total_amount']); ?></span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $b['payment_method'] ?? 'COD')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo get_status_badge($b['rental_status']); ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <form action="bookings.php" method="POST" style="margin: 0; display: inline-flex; gap: 6px;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="rental_id" value="<?php echo $b['rental_id']; ?>">

                                                <?php if ($b['rental_status'] === 'pending'): ?>
                                                    <button type="submit" name="new_status" value="approved" class="btn btn-sm btn-magenta" title="Accept Booking">
                                                        <i class="fa-solid fa-check"></i> Accept
                                                    </button>
                                                    <button type="submit" name="new_status" value="rejected" class="btn btn-sm btn-subtle text-danger" title="Decline Booking" onclick="return confirm('Decline this booking?');">
                                                        <i class="fa-solid fa-xmark"></i> Decline
                                                    </button>
                                                <?php elseif ($b['rental_status'] === 'approved'): ?>
                                                    <button type="submit" name="new_status" value="active" class="btn btn-sm btn-subtle" title="Mark as Dispatched / Out with client">
                                                        <i class="fa-solid fa-truck"></i> Dispatch
                                                    </button>
                                                <?php elseif ($b['rental_status'] === 'active'): ?>
                                                    <button type="submit" name="new_status" value="returned" class="btn btn-sm btn-subtle text-success" title="Confirm Garment Returned">
                                                        <i class="fa-solid fa-rotate-left"></i> Mark Returned
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted" style="font-size: 0.8rem;">Archived</span>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 40px 20px;">
                        <p class="text-muted">No booking records found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
