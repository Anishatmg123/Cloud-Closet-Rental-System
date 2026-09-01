<?php
/**
 * Vendor Manage Returns
 */

$page_title = "Manage Returns";
$path_prefix = '../';
require_once __DIR__ . '/../includes/vendor_auth.php';
require_once __DIR__ . '/../includes/header.php';

$vendorId = (int)$_SESSION['vendor_id'];

// Process Return Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_return') {
    $rentalId = (int)($_POST['rental_id'] ?? 0);
    $dressId = (int)($_POST['dress_id'] ?? 0);

    if ($rentalId && $dressId) {
        try {
            $pdo->prepare("UPDATE rentals SET rental_status = 'returned', returned_at = NOW() WHERE rental_id = :rental_id")->execute([':rental_id' => $rentalId]);
            $pdo->prepare("UPDATE dresses SET availability = 'available' WHERE dress_id = :dress_id AND vendor_id = :vendor_id")->execute([':dress_id' => $dressId, ':vendor_id' => $vendorId]);

            // Notify User
            $uId = $pdo->query("SELECT user_id FROM rentals WHERE rental_id = $rentalId")->fetchColumn();
            if ($uId) {
                create_notification($pdo, 'user', $uId, 'Dress Return Processed', 'Your returned outfit was verified and security deposit released.', 'success', 'user/bookings.php');
            }

            set_flash('success', "Return processed successfully. Garment restored to available inventory.");
            header("Location: returns.php");
            exit();
        } catch (Exception $e) {
            set_flash('error', "Error processing return: " . $e->getMessage());
        }
    }
}

// Fetch Active and Returned rentals
$activeOutStmt = $pdo->prepare("
    SELECT r.*, u.full_name as customer_name, u.phone as customer_phone, d.dress_name, d.dress_id, d.image, d.size
    FROM rentals r
    JOIN users u ON r.user_id = u.user_id
    JOIN dresses d ON r.dress_id = d.dress_id
    WHERE d.vendor_id = :vendor_id AND r.rental_status = 'active'
    ORDER BY r.rental_end_date ASC
");
$activeOutStmt->execute([':vendor_id' => $vendorId]);
$activeOut = $activeOutStmt->fetchAll();

$returnedStmt = $pdo->prepare("
    SELECT r.*, u.full_name as customer_name, d.dress_name, d.image
    FROM rentals r
    JOIN users u ON r.user_id = u.user_id
    JOIN dresses d ON r.dress_id = d.dress_id
    WHERE d.vendor_id = :vendor_id AND r.rental_status = 'returned'
    ORDER BY r.returned_at DESC
    LIMIT 10
");
$returnedStmt->execute([':vendor_id' => $vendorId]);
$returnedHistory = $returnedStmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Manage Returns & Check-In</h1>
                    <p class="page-subtitle-text">Verify returned garments, process dry-cleaning check-in, and release deposits.</p>
                </div>
            </div>

            <!-- Active Rentals Awaiting Return -->
            <div class="dashboard-card" style="margin-bottom: 30px;">
                <div class="card-header-flex">
                    <h3 class="card-title-text">Garments Out with Clients (<?php echo count($activeOut); ?>)</h3>
                </div>

                <?php if (!empty($activeOut)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer Details</th>
                                    <th>Outfit</th>
                                    <th>Expected Return Date</th>
                                    <th>Deposit</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeOut as $out): 
                                    $thumb = !empty($out['image']) && file_exists(__DIR__ . '/../' . $out['image']) ? '../' . $out['image'] : '../assets/images/hero_closet_banner.jpg';
                                ?>
                                    <tr>
                                        <td><span class="font-weight-bold">#ORD-<?php echo str_pad($out['rental_id'], 5, '0', STR_PAD_LEFT); ?></span></td>
                                        <td>
                                            <span class="font-weight-bold" style="display: block; color: var(--text-primary);"><?php echo htmlspecialchars($out['customer_name']); ?></span>
                                            <span class="text-muted" style="font-size: 0.75rem;">Phone: <?php echo htmlspecialchars($out['customer_phone']); ?></span>
                                        </td>
                                        <td>
                                            <div class="table-user-cell">
                                                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Dress" class="table-item-thumb">
                                                <div>
                                                    <span class="font-weight-bold"><?php echo htmlspecialchars($out['dress_name']); ?></span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">Size <?php echo htmlspecialchars($out['size'] ?? 'M'); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta"><?php echo format_date($out['rental_end_date']); ?></span>
                                        </td>
                                        <td><?php echo format_currency($out['security_deposit']); ?></td>
                                        <td style="text-align: right;">
                                            <form action="returns.php" method="POST" onsubmit="return confirm('Confirm garment has been returned in good condition?');">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="process_return">
                                                <input type="hidden" name="rental_id" value="<?php echo $out['rental_id']; ?>">
                                                <input type="hidden" name="dress_id" value="<?php echo $out['dress_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-magenta">
                                                    <i class="fa-solid fa-check"></i> Process Return
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="padding: 20px 0;">No outfits currently out on active rental.</p>
                <?php endif; ?>
            </div>

            <!-- Returned History -->
            <div class="dashboard-card">
                <h3 class="card-title-text" style="margin-bottom: 20px;">Recent Return History</h3>
                <?php if (!empty($returnedHistory)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Outfit</th>
                                    <th>Returned Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($returnedHistory as $ret): ?>
                                    <tr>
                                        <td>#ORD-<?php echo str_pad($ret['rental_id'], 5, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($ret['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($ret['dress_name']); ?></td>
                                        <td><?php echo date('M d, Y - g:i A', strtotime($ret['returned_at'] ?? 'now')); ?></td>
                                        <td><span class="status-badge badge-success">Returned & Sanitized</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No return history recorded.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
