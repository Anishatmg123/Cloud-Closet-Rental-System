<?php
/**
 * Vendor Business Reports & Analytics
 */

$path_prefix = '../';
require_once __DIR__ . '/../includes/vendor_auth.php';

$vendorId = (int)$_SESSION['vendor_id'];

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Vendor_Report_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');

    fputcsv($output, ['Order ID', 'Customer Name', 'Dress Name', 'Category', 'Rental Start', 'Rental Return', 'Duration (Days)', 'Rental Fee ($)', 'Deposit ($)', 'Total ($)', 'Status', 'Date Placed']);

    $stmt = $pdo->prepare("
        SELECT r.rental_id, u.full_name as customer_name, d.dress_name, c.category_name,
               r.rental_start_date, r.rental_end_date, r.total_days, r.rental_amount,
               r.security_deposit, r.total_amount, r.rental_status, r.request_date
        FROM rentals r
        JOIN users u ON r.user_id = u.user_id
        JOIN dresses d ON r.dress_id = d.dress_id
        LEFT JOIN categories c ON d.category_id = c.category_id
        WHERE d.vendor_id = :vendor_id
        ORDER BY r.request_date DESC
    ");
    $stmt->execute([':vendor_id' => $vendorId]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            '#ORD-' . str_pad($row['rental_id'], 5, '0', STR_PAD_LEFT),
            $row['customer_name'],
            $row['dress_name'],
            $row['category_name'] ?? 'Dress',
            $row['rental_start_date'],
            $row['rental_end_date'],
            $row['total_days'],
            number_format($row['rental_amount'], 2),
            number_format($row['security_deposit'], 2),
            number_format($row['total_amount'], 2),
            ucfirst($row['rental_status']),
            $row['request_date']
        ]);
    }
    fclose($output);
    exit();
}

$page_title = "Business Reports";
require_once __DIR__ . '/../includes/header.php';

// Fetch Performance Aggregates
$totalEarnings = 0.0;
$totalBookings = 0;
$topDresses = [];

try {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(r.rental_amount), 0), COUNT(*)
        FROM rentals r
        JOIN dresses d ON r.dress_id = d.dress_id
        WHERE d.vendor_id = :vendor_id AND r.rental_status IN ('approved', 'active', 'returned')
    ");
    $stmt->execute([':vendor_id' => $vendorId]);
    list($totalEarnings, $totalBookings) = $stmt->fetch(PDO::FETCH_NUM);

    // Most rented dresses
    $stmt = $pdo->prepare("
        SELECT d.dress_name, d.image, d.rental_price, COUNT(r.rental_id) as rental_count, SUM(r.rental_amount) as dress_revenue
        FROM dresses d
        LEFT JOIN rentals r ON d.dress_id = r.dress_id AND r.rental_status IN ('approved', 'active', 'returned')
        WHERE d.vendor_id = :vendor_id
        GROUP BY d.dress_id
        ORDER BY rental_count DESC, dress_revenue DESC
        LIMIT 5
    ");
    $stmt->execute([':vendor_id' => $vendorId]);
    $topDresses = $stmt->fetchAll();

} catch (Exception $e) {}
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Business Reports & Statements</h1>
                    <p class="page-subtitle-text">Analyze your store revenue, popular pieces, and download spreadsheet records.</p>
                </div>
                <div>
                    <a href="reports.php?export=csv" class="btn btn-magenta">
                        <i class="fa-solid fa-file-csv"></i> Download CSV Statement
                    </a>
                </div>
            </div>

            <!-- 3 Stat Widgets -->
            <div class="stats-grid-4" style="grid-template-columns: repeat(3, 1fr);">
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL EARNINGS</span>
                        <div class="stat-icon-circle emerald"><i class="fa-solid fa-dollar-sign"></i></div>
                    </div>
                    <div class="stat-card-value"><?php echo format_currency($totalEarnings); ?></div>
                    <div class="stat-card-trend"><span class="trend-up">Lifetime rental revenue</span></div>
                </div>

                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">CONFIRMED RENTALS</span>
                        <div class="stat-icon-circle blue"><i class="fa-solid fa-calendar-check"></i></div>
                    </div>
                    <div class="stat-card-value"><?php echo $totalBookings; ?></div>
                    <div class="stat-card-trend"><span>Total fulfilled orders</span></div>
                </div>

                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">AVG EARNING / ORDER</span>
                        <div class="stat-icon-circle indigo"><i class="fa-solid fa-chart-simple"></i></div>
                    </div>
                    <div class="stat-card-value">
                        <?php echo format_currency($totalBookings > 0 ? ($totalEarnings / $totalBookings) : 0); ?>
                    </div>
                    <div class="stat-card-trend"><span>Per client rental</span></div>
                </div>
            </div>

            <!-- Top Performing Gowns -->
            <div class="dashboard-card">
                <h3 class="card-title-text" style="margin-bottom: 20px;">Top Performing Dresses</h3>
                <?php if (!empty($topDresses)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Outfit</th>
                                    <th>Rental Fee</th>
                                    <th>Times Booked</th>
                                    <th>Total Revenue Generated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topDresses as $d): 
                                    $thumb = get_dress_image_url($d['image'], $d['dress_name'], '../');
                                ?>
                                    <tr>
                                        <td>
                                            <div class="table-user-cell">
                                                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Dress" class="table-item-thumb">
                                                <span class="font-weight-bold"><?php echo htmlspecialchars($d['dress_name']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo format_currency($d['rental_price']); ?></td>
                                        <td><span class="font-weight-bold"><?php echo $d['rental_count'] ?? 0; ?></span> bookings</td>
                                        <td><span class="font-weight-bold text-magenta"><?php echo format_currency($d['dress_revenue'] ?? 0); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No rental data recorded yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
