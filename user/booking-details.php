<?php
/**
 * Booking Details & Invoice Receipt
 */

$page_title = "Order Invoice";
$path_prefix = '../';
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/header.php';

$userId = (int)$_SESSION['user_id'];
$rentalId = (int)($_GET['id'] ?? 0);

if (!$rentalId) {
    header("Location: bookings.php");
    exit();
}

// Fetch Booking Details
$stmt = $pdo->prepare("
    SELECT r.*, d.dress_name, d.image, d.size, d.color, d.description, c.category_name,
           v.business_name, v.full_name as vendor_owner, v.phone as vendor_phone,
           p.payment_id, p.payment_method, p.payment_status, p.transaction_id, p.payment_date
    FROM rentals r
    JOIN dresses d ON r.dress_id = d.dress_id
    LEFT JOIN categories c ON d.category_id = c.category_id
    LEFT JOIN vendors v ON d.vendor_id = v.vendor_id
    LEFT JOIN payments p ON r.rental_id = p.rental_id
    WHERE r.rental_id = :rental_id AND r.user_id = :user_id
    LIMIT 1
");
$stmt->execute([':rental_id' => $rentalId, ':user_id' => $userId]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Booking record not found.');
    header("Location: bookings.php");
    exit();
}

$thumb = get_dress_image_url($order['image'], $order['dress_name'], '../');
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/user_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <!-- Action Navigation -->
            <div class="page-header-row">
                <div>
                    <a href="bookings.php" class="text-magenta" style="font-size: 0.88rem; font-weight: 600;">&larr; Back to Bookings</a>
                    <h1 class="page-title-text" style="margin-top: 6px;">Order Invoice #ORD-<?php echo str_pad($order['rental_id'], 5, '0', STR_PAD_LEFT); ?></h1>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="button" class="btn btn-subtle" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> Print Invoice
                    </button>
                    <a href="dresses.php" class="btn btn-magenta">
                        <i class="fa-solid fa-plus"></i> Rent Another Dress
                    </a>
                </div>
            </div>

            <!-- Invoice Card -->
            <div class="dashboard-card" style="padding: 40px; max-width: 880px; margin: 0 auto 40px;">
                <!-- Invoice Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 28px; border-bottom: 1px solid rgba(15, 23, 42, 0.08); margin-bottom: 28px;">
                    <div>
                        <div class="brand-logo" style="margin-bottom: 8px;">
                            <div class="logo-symbol">
                                <i class="fa-solid fa-vest-patches"></i>
                            </div>
                            <span class="logo-main" style="font-size: 1.4rem;">CLOUD CLOSET</span>
                        </div>
                        <p class="text-muted" style="font-size: 0.85rem;">Luxury Editorial Clothing Rental Platform</p>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; display: block;">Booking Status</span>
                        <div style="margin: 4px 0 8px;"><?php echo get_status_badge($order['rental_status']); ?></div>
                        <span class="text-muted" style="font-size: 0.82rem;">Placed: <?php echo date('M d, Y - g:i A', strtotime($order['request_date'])); ?></span>
                    </div>
                </div>

                <!-- Customer & Vendor 2-Col Info -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                    <div>
                        <h4 style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 10px;">Billed & Delivered To:</h4>
                        <div class="font-weight-bold" style="font-size: 1.05rem; color: var(--text-primary);"><?php echo htmlspecialchars($current_user['full_name']); ?></div>
                        <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; margin-top: 4px;">
                            <?php echo htmlspecialchars($current_user['email']); ?><br>
                            Phone: <?php echo htmlspecialchars($current_user['phone']); ?><br>
                            Address: <?php echo htmlspecialchars($current_user['address']); ?>
                        </p>
                    </div>
                    <div>
                        <h4 style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 10px;">Curating Boutique:</h4>
                        <div class="font-weight-bold" style="font-size: 1.05rem; color: var(--text-primary);"><?php echo htmlspecialchars($order['business_name'] ?? 'Cloud Closet Main Atelier'); ?></div>
                        <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; margin-top: 4px;">
                            Contact: <?php echo htmlspecialchars($order['vendor_owner'] ?? 'Support Team'); ?><br>
                            Support: <?php echo htmlspecialchars($order['vendor_phone'] ?? '1-800-CLOUD'); ?>
                        </p>
                    </div>
                </div>

                <!-- Item Breakdown Table -->
                <div class="table-responsive" style="margin-bottom: 30px;">
                    <table class="modern-table" style="background: transparent;">
                        <thead>
                            <tr>
                                <th>Item Details</th>
                                <th>Rental Period</th>
                                <th>Duration</th>
                                <th style="text-align: right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="table-user-cell">
                                        <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Dress" class="table-item-thumb" style="width: 60px; height: 72px;">
                                        <div>
                                            <span class="font-weight-bold" style="font-size: 1rem; color: var(--text-primary); display: block;"><?php echo htmlspecialchars($order['dress_name']); ?></span>
                                            <span class="text-muted" style="font-size: 0.82rem;">
                                                <?php echo htmlspecialchars($order['category_name'] ?? 'Couture'); ?> &bull; Size <?php echo htmlspecialchars($order['size'] ?? 'M'); ?> &bull; Color: <?php echo htmlspecialchars($order['color'] ?? 'Standard'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 600; font-size: 0.9rem;">
                                        <?php echo format_date($order['rental_start_date']); ?> to <?php echo format_date($order['rental_end_date']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="font-weight-bold"><?php echo $order['total_days']; ?> Days</span>
                                </td>
                                <td style="text-align: right;">
                                    <span class="font-weight-bold" style="font-size: 1.05rem; color: var(--text-primary);">
                                        <?php echo format_currency($order['rental_amount']); ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Financial Ledger Breakdown -->
                <div style="display: flex; justify-content: flex-end; margin-bottom: 30px;">
                    <div style="width: 320px; background: #f8fafc; border-radius: var(--radius-md); padding: 20px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.92rem; margin-bottom: 8px;">
                            <span class="text-muted">Rental Subtotal:</span>
                            <span class="font-weight-bold"><?php echo format_currency($order['rental_amount']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.92rem; margin-bottom: 12px;">
                            <span class="text-muted">Refundable Deposit:</span>
                            <span class="font-weight-bold"><?php echo format_currency($order['security_deposit']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 800; padding-top: 12px; border-top: 2px solid rgba(15, 23, 42, 0.08); color: var(--color-magenta);">
                            <span>Grand Total:</span>
                            <span><?php echo format_currency($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Details Card -->
                <div style="background: var(--color-pink-soft); border-radius: var(--radius-md); padding: 20px; border: 1px solid rgba(225, 29, 72, 0.12);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--color-magenta);">Payment Information</span>
                            <div style="font-weight: 700; font-size: 1rem; color: var(--text-primary); margin-top: 4px;">
                                Method: <?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($order['payment_method'] ?? 'COD'))); ?>
                            </div>
                            <span class="text-muted" style="font-size: 0.82rem;">Txn ID: <?php echo htmlspecialchars($order['transaction_id'] ?? 'Pending Assignment'); ?></span>
                        </div>
                        <div>
                            <?php echo get_status_badge($order['payment_status'] ?? 'pending'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
