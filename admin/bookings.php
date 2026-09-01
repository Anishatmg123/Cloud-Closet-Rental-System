<?php
/**
 * Admin View All Bookings
 */

$page_title = "All Bookings";
$path_prefix = '../';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/header.php';

// Filter params
$statusFilter = trim($_GET['status'] ?? 'all');
$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT r.*, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone,
           d.dress_name, d.image, d.size, v.business_name,
           p.payment_method, p.payment_status, p.transaction_id
    FROM rentals r
    JOIN users u ON r.user_id = u.user_id
    JOIN dresses d ON r.dress_id = d.dress_id
    LEFT JOIN vendors v ON d.vendor_id = v.vendor_id
    LEFT JOIN payments p ON r.rental_id = p.rental_id
    WHERE 1=1
";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND r.rental_status = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($search)) {
    $sql .= " AND (u.full_name LIKE :search OR d.dress_name LIKE :search OR v.business_name LIKE :search OR p.transaction_id LIKE :search)";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY r.request_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allBookings = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Platform Rental Bookings</h1>
                    <p class="page-subtitle-text">Monitor all active and historical rental transactions across boutiques and customers.</p>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="dashboard-card" style="padding: 14px 20px; margin-bottom: 24px;">
                <div class="chart-toggle-group" style="background: transparent; flex-wrap: wrap; gap: 8px;">
                    <a href="bookings.php?status=all" class="chart-toggle-btn <?php echo ($statusFilter === 'all') ? 'active' : ''; ?>">All Bookings</a>
                    <a href="bookings.php?status=active" class="chart-toggle-btn <?php echo ($statusFilter === 'active') ? 'active' : ''; ?>">Active / Out</a>
                    <a href="bookings.php?status=approved" class="chart-toggle-btn <?php echo ($statusFilter === 'approved') ? 'active' : ''; ?>">Confirmed</a>
                    <a href="bookings.php?status=pending" class="chart-toggle-btn <?php echo ($statusFilter === 'pending') ? 'active' : ''; ?>">Pending</a>
                    <a href="bookings.php?status=returned" class="chart-toggle-btn <?php echo ($statusFilter === 'returned') ? 'active' : ''; ?>">Returned</a>
                    <a href="bookings.php?status=cancelled" class="chart-toggle-btn <?php echo ($statusFilter === 'cancelled') ? 'active' : ''; ?>">Cancelled</a>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="dashboard-card">
                <?php if (!empty($allBookings)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Order Ref</th>
                                    <th>Customer</th>
                                    <th>Boutique</th>
                                    <th>Dress Item</th>
                                    <th>Rental Period</th>
                                    <th>Total ($)</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allBookings as $b): 
                                    $thumb = !empty($b['image']) && file_exists(__DIR__ . '/../' . $b['image']) ? '../' . $b['image'] : '../assets/images/hero_closet_banner.jpg';
                                ?>
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold">#ORD-<?php echo str_pad($b['rental_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                            <span class="text-muted" style="display: block; font-size: 0.72rem;"><?php echo date('M d, Y', strtotime($b['request_date'])); ?></span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold" style="color: var(--text-primary); display: block;"><?php echo htmlspecialchars($b['customer_name']); ?></span>
                                            <span class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($b['customer_email']); ?></span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($b['business_name'] ?? 'Main Atelier'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-user-cell">
                                                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Dress" class="table-item-thumb">
                                                <div>
                                                    <span class="font-weight-bold" style="display: block;"><?php echo htmlspecialchars($b['dress_name']); ?></span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">Size <?php echo htmlspecialchars($b['size'] ?? 'M'); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; font-weight: 600;">
                                                <?php echo format_date($b['rental_start_date'], 'M d'); ?> - <?php echo format_date($b['rental_end_date'], 'M d'); ?>
                                            </span>
                                            <span class="text-muted" style="display: block; font-size: 0.72rem;"><?php echo $b['total_days']; ?> Days</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta"><?php echo format_currency($b['total_amount']); ?></span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase;">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $b['payment_method'] ?? 'COD')); ?>
                                            </span>
                                            <span style="display: block; font-size: 0.7rem; color: var(--text-muted);">
                                                <?php echo ucfirst($b['payment_status'] ?? 'pending'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo get_status_badge($b['rental_status']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="text-align: center; padding: 40px 0;">No booking records found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
