<?php
/**
 * Admin Platform Reports & CSV Exporter
 */

$path_prefix = '../';
require_once __DIR__ . '/../includes/admin_auth.php';

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Platform_Financial_Report_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');

    fputcsv($output, ['Order ID', 'Customer Name', 'Customer Email', 'Boutique Store', 'Dress Name', 'Category', 'Rental Start', 'Rental Return', 'Duration (Days)', 'Rental Fee ($)', 'Deposit ($)', 'Total Paid ($)', 'Payment Method', 'Payment Status', 'Rental Status', 'Date']);

    $stmt = $pdo->query("
        SELECT r.rental_id, u.full_name as customer_name, u.email as customer_email,
               v.business_name, d.dress_name, c.category_name,
               r.rental_start_date, r.rental_end_date, r.total_days, r.rental_amount,
               r.security_deposit, r.total_amount, p.payment_method, p.payment_status,
               r.rental_status, r.request_date
        FROM rentals r
        JOIN users u ON r.user_id = u.user_id
        JOIN dresses d ON r.dress_id = d.dress_id
        LEFT JOIN categories c ON d.category_id = c.category_id
        LEFT JOIN vendors v ON d.vendor_id = v.vendor_id
        LEFT JOIN payments p ON r.rental_id = p.rental_id
        ORDER BY r.request_date DESC
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            '#ORD-' . str_pad($row['rental_id'], 5, '0', STR_PAD_LEFT),
            $row['customer_name'],
            $row['customer_email'],
            $row['business_name'] ?? 'Main Atelier',
            $row['dress_name'],
            $row['category_name'] ?? 'Dress',
            $row['rental_start_date'],
            $row['rental_end_date'],
            $row['total_days'],
            number_format($row['rental_amount'], 2),
            number_format($row['security_deposit'], 2),
            number_format($row['total_amount'], 2),
            strtoupper(str_replace('_', ' ', $row['payment_method'] ?? 'COD')),
            ucfirst($row['payment_status'] ?? 'pending'),
            ucfirst($row['rental_status']),
            $row['request_date']
        ]);
    }
    fclose($output);
    exit();
}

$page_title = "Platform Reports";
require_once __DIR__ . '/../includes/header.php';

// Fetch Financial Summaries
$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(rental_amount), 0) FROM rentals WHERE rental_status IN ('approved', 'active', 'returned')")->fetchColumn();
$totalRentals = (int)$pdo->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalVendors = (int)$pdo->query("SELECT COUNT(*) FROM vendors WHERE status = 'approved'")->fetchColumn();

// Vendor Performance Breakdown
$vendorSummary = $pdo->query("
    SELECT v.vendor_id, v.business_name, v.full_name,
           (SELECT COUNT(*) FROM dresses d WHERE d.vendor_id = v.vendor_id) as total_dresses,
           (SELECT COUNT(*) FROM rentals r JOIN dresses d ON r.dress_id = d.dress_id WHERE d.vendor_id = v.vendor_id) as total_rentals,
           (SELECT COALESCE(SUM(r.rental_amount), 0) FROM rentals r JOIN dresses d ON r.dress_id = d.dress_id WHERE d.vendor_id = v.vendor_id AND r.rental_status IN ('approved', 'active', 'returned')) as total_earnings
    FROM vendors v
    ORDER BY total_earnings DESC
")->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Platform Financial Reports</h1>
                    <p class="page-subtitle-text">System-wide rental volume, boutique earnings, and full ledger export.</p>
                </div>
                <div>
                    <a href="reports.php?export=csv" class="btn btn-magenta">
                        <i class="fa-solid fa-file-csv"></i> Download Master CSV Report
                    </a>
                </div>
            </div>

            <!-- 4 Metric Cards -->
            <div class="stats-grid-4">
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL PLATFORM VOLUME</span>
                        <div class="stat-icon-circle emerald"><i class="fa-solid fa-dollar-sign"></i></div>
                    </div>
                    <div class="stat-card-value"><?php echo format_currency($totalRevenue); ?></div>
                    <div class="stat-card-trend"><span class="trend-up">Gross settled rentals</span></div>
                </div>

                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL RENTAL ORDERS</span>
                        <div class="stat-icon-circle blue"><i class="fa-solid fa-calendar-check"></i></div>
                    </div>
                    <div class="stat-card-value"><?php echo $totalRentals; ?></div>
                    <div class="stat-card-trend"><span>Lifetime orders</span></div>
                </div>

                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">ACTIVE VENDORS</span>
                        <div class="stat-icon-circle amber"><i class="fa-solid fa-store"></i></div>
                    </div>
                    <div class="stat-card-value"><?php echo $totalVendors; ?></div>
                    <div class="stat-card-trend"><span>Verified boutique partners</span></div>
                </div>

                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">AVERAGE ORDER VALUE</span>
                        <div class="stat-icon-circle indigo"><i class="fa-solid fa-chart-line"></i></div>
                    </div>
                    <div class="stat-card-value">
                        <?php echo format_currency($totalRentals > 0 ? ($totalRevenue / $totalRentals) : 0); ?>
                    </div>
                    <div class="stat-card-trend"><span>Per customer rental</span></div>
                </div>
            </div>

            <!-- Boutique Partner Breakdown Table -->
            <div class="dashboard-card">
                <h3 class="card-title-text" style="margin-bottom: 20px;">Boutique Partner Financial Breakdown</h3>

                <?php if (!empty($vendorSummary)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Boutique Store</th>
                                    <th>Contact Owner</th>
                                    <th>Wardrobe Inventory</th>
                                    <th>Total Rentals Fulfilled</th>
                                    <th>Gross Earnings ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vendorSummary as $vs): ?>
                                    <tr>
                                        <td>
                                            <div class="table-user-cell">
                                                <div class="user-avatar-circle vendor-avatar" style="width: 36px; height: 36px;">
                                                    <i class="fa-solid fa-shop"></i>
                                                </div>
                                                <span class="font-weight-bold" style="color: var(--text-primary);"><?php echo htmlspecialchars($vs['business_name'] ?? 'Boutique'); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($vs['full_name']); ?></td>
                                        <td><span class="font-weight-bold"><?php echo $vs['total_dresses']; ?></span> pieces</td>
                                        <td><span class="font-weight-bold"><?php echo $vs['total_rentals']; ?></span> bookings</td>
                                        <td>
                                            <span class="font-weight-bold text-magenta" style="font-size: 1.05rem;">
                                                <?php echo format_currency($vs['total_earnings']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No vendor records available.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
