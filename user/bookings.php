<?php
/**
 * Customer Bookings & Rentals Management
 */

$page_title = "My Bookings";
$path_prefix = '../';
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/header.php';

$userId = (int)$_SESSION['user_id'];
$statusFilter = trim($_GET['status'] ?? 'all');

// Handle Cancel Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
    $rentalId = (int)($_POST['rental_id'] ?? 0);
    if ($rentalId) {
        try {
            // Check if rental belongs to user and is pending or approved
            $checkStmt = $pdo->prepare("SELECT * FROM rentals WHERE rental_id = :rental_id AND user_id = :user_id LIMIT 1");
            $checkStmt->execute([':rental_id' => $rentalId, ':user_id' => $userId]);
            $rental = $checkStmt->fetch();

            if ($rental && in_array($rental['rental_status'], ['pending', 'approved'])) {
                $cancelStmt = $pdo->prepare("UPDATE rentals SET rental_status = 'cancelled' WHERE rental_id = :rental_id");
                $cancelStmt->execute([':rental_id' => $rentalId]);

                // Update payment if exists to refunded
                $pdo->prepare("UPDATE payments SET payment_status = 'refunded' WHERE rental_id = :rental_id")->execute([':rental_id' => $rentalId]);

                set_flash('success', "Booking #ORD-" . str_pad($rentalId, 5, '0', STR_PAD_LEFT) . " has been cancelled.");
                header("Location: bookings.php");
                exit();
            } else {
                set_flash('error', "This booking cannot be cancelled in its current state.");
            }
        } catch (Exception $e) {
            set_flash('error', "Error cancelling booking: " . $e->getMessage());
        }
    }
}

// Build Query
$sql = "
    SELECT r.*, d.dress_name, d.image, d.size, d.color, c.category_name, p.payment_method, p.payment_status, p.transaction_id
    FROM rentals r
    JOIN dresses d ON r.dress_id = d.dress_id
    LEFT JOIN categories c ON d.category_id = c.category_id
    LEFT JOIN payments p ON r.rental_id = p.rental_id
    WHERE r.user_id = :user_id
";
$params = [':user_id' => $userId];

if ($statusFilter !== 'all') {
    $sql .= " AND r.rental_status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY r.request_date DESC";

$bookings = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/user_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">My Rental Bookings</h1>
                    <p class="page-subtitle-text">Track your active couture rentals, delivery dates, and past order invoices.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="download-report.php" class="btn btn-subtle">
                        <i class="fa-solid fa-download"></i> Export History
                    </a>
                    <a href="dresses.php" class="btn btn-magenta">
                        <i class="fa-solid fa-plus"></i> New Rental
                    </a>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="dashboard-card" style="padding: 14px 20px; margin-bottom: 24px;">
                <div class="chart-toggle-group" style="background: transparent; flex-wrap: wrap; gap: 8px;">
                    <a href="bookings.php?status=all" class="chart-toggle-btn <?php echo ($statusFilter === 'all') ? 'active' : ''; ?>">All Bookings</a>
                    <a href="bookings.php?status=active" class="chart-toggle-btn <?php echo ($statusFilter === 'active') ? 'active' : ''; ?>">Active Rentals</a>
                    <a href="bookings.php?status=approved" class="chart-toggle-btn <?php echo ($statusFilter === 'approved') ? 'active' : ''; ?>">Upcoming / Confirmed</a>
                    <a href="bookings.php?status=pending" class="chart-toggle-btn <?php echo ($statusFilter === 'pending') ? 'active' : ''; ?>">Pending Approval</a>
                    <a href="bookings.php?status=returned" class="chart-toggle-btn <?php echo ($statusFilter === 'returned') ? 'active' : ''; ?>">Returned History</a>
                    <a href="bookings.php?status=cancelled" class="chart-toggle-btn <?php echo ($statusFilter === 'cancelled') ? 'active' : ''; ?>">Cancelled</a>
                </div>
            </div>

            <!-- Bookings Table Card -->
            <div class="dashboard-card">
                <?php if (!empty($bookings)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Order Ref</th>
                                    <th>Gown Details</th>
                                    <th>Rental Period</th>
                                    <th>Total Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): 
                                    $thumb = !empty($booking['image']) && file_exists(__DIR__ . '/../' . $booking['image']) ? '../' . $booking['image'] : '../assets/images/hero_closet_banner.jpg';
                                ?>
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold">#ORD-<?php echo str_pad($booking['rental_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                            <span class="text-muted" style="display: block; font-size: 0.72rem;"><?php echo date('M d, Y', strtotime($booking['request_date'])); ?></span>
                                        </td>
                                        <td>
                                            <div class="table-user-cell">
                                                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Dress" class="table-item-thumb">
                                                <div>
                                                    <a href="dress-details.php?id=<?php echo $booking['dress_id']; ?>" class="font-weight-bold text-primary" style="display: block;">
                                                        <?php echo htmlspecialchars($booking['dress_name']); ?>
                                                    </a>
                                                    <span class="text-muted" style="font-size: 0.78rem;">
                                                        <?php echo htmlspecialchars($booking['category_name'] ?? 'Dress'); ?> &bull; Size <?php echo htmlspecialchars($booking['size'] ?? 'M'); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.88rem; font-weight: 600;">
                                                <?php echo format_date($booking['rental_start_date'], 'M d'); ?> - <?php echo format_date($booking['rental_end_date'], 'M d, Y'); ?>
                                            </span>
                                            <span class="text-muted" style="display: block; font-size: 0.75rem;"><?php echo $booking['total_days']; ?> Rental Days</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta" style="font-size: 1.05rem;">
                                                <?php echo format_currency($booking['total_amount']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase;">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $booking['payment_method'] ?? 'COD')); ?>
                                            </span>
                                            <span style="display: block; font-size: 0.72rem; color: var(--text-muted);">
                                                <?php echo ucfirst($booking['payment_status'] ?? 'pending'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo get_status_badge($booking['rental_status']); ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; gap: 8px; align-items: center;">
                                                <a href="booking-details.php?id=<?php echo $booking['rental_id']; ?>" class="btn btn-sm btn-subtle" title="View Order Receipt">
                                                    Invoice
                                                </a>
                                                <?php if (in_array($booking['rental_status'], ['pending', 'approved'])): ?>
                                                    <form action="bookings.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');" style="margin: 0;">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="action" value="cancel_booking">
                                                        <input type="hidden" name="rental_id" value="<?php echo $booking['rental_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-subtle text-danger" title="Cancel Booking">
                                                            <i class="fa-solid fa-ban"></i> Cancel
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 50px 20px;">
                        <i class="fa-solid fa-calendar-xmark text-muted" style="font-size: 3rem; margin-bottom: 14px;"></i>
                        <h3 style="color: var(--text-primary); margin-bottom: 6px;">No Bookings in this Category</h3>
                        <p class="text-muted" style="margin-bottom: 20px;">No rental orders currently match the selected status.</p>
                        <a href="dresses.php" class="btn btn-magenta">Browse Dress Collection</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
