<?php
/**
 * Administrator Central Dashboard
 */

$page_title = "Admin Dashboard";
$path_prefix = '../';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/header.php';

// 1. Calculate 5 Top Statistics via SQL
$totalRevenue = 0.0;
$totalBookings = 0;
$totalUsers = 0;
$totalVendors = 0;
$totalDresses = 0;

try {
    // Total Revenue (from paid/approved rentals)
    $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(rental_amount), 0) FROM rentals WHERE rental_status IN ('approved', 'active', 'returned')")->fetchColumn();

    // Total Bookings
    $totalBookings = (int)$pdo->query("SELECT COUNT(*) FROM rentals")->fetchColumn();

    // Total Users
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Total Vendors
    $totalVendors = (int)$pdo->query("SELECT COUNT(*) FROM vendors")->fetchColumn();

    // Total Dresses
    $totalDresses = (int)$pdo->query("SELECT COUNT(*) FROM dresses")->fetchColumn();

    // 2. Fetch Recent Activities
    $recentActivities = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 6")->fetchAll();

    // 3. Fetch Recent Transactions
    $stmt = $pdo->query("
        SELECT p.*, u.full_name as customer_name, d.dress_name
        FROM payments p
        JOIN rentals r ON p.rental_id = r.rental_id
        JOIN users u ON r.user_id = u.user_id
        JOIN dresses d ON r.dress_id = d.dress_id
        ORDER BY p.payment_date DESC
        LIMIT 6
    ");
    $recentTransactions = $stmt->fetchAll();

    // 4. Booking Statistics Data for Chart.js
    $chartData = $pdo->query("
        SELECT DATE_FORMAT(request_date, '%b') as m_name, COUNT(*) as b_count, COALESCE(SUM(rental_amount), 0) as r_amount
        FROM rentals
        GROUP BY DATE_FORMAT(request_date, '%Y-%m'), DATE_FORMAT(request_date, '%b')
        ORDER BY request_date ASC
        LIMIT 6
    ")->fetchAll();

} catch (Exception $e) {
    // Error handling
}

// Chart Arrays
$chartLabels = !empty($chartData) ? array_column($chartData, 'm_name') : [date('M')];
$chartCounts = !empty($chartData) ? array_map('intval', array_column($chartData, 'b_count')) : [$totalBookings];
$chartRevenues = !empty($chartData) ? array_map('floatval', array_column($chartData, 'r_amount')) : [$totalRevenue];
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <!-- Admin Header -->
            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Dashboard Overview</h1>
                    <p class="page-subtitle-text">Welcome back, Administrator. Platform operations & live metrics.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="reports.php" class="btn btn-subtle">
                        <i class="fa-solid fa-file-invoice"></i> Financial Reports
                    </a>
                    <a href="vendors.php" class="btn btn-magenta">
                        <i class="fa-solid fa-store"></i> Review Vendors
                    </a>
                </div>
            </div>

            <!-- Top 5 Metric Cards -->
            <div class="stats-grid-5">
                <!-- 1. Total Revenue -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL REVENUE</span>
                        <div class="stat-icon-circle emerald">
                            <i class="fa-solid fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo format_currency($totalRevenue); ?></div>
                    <div class="stat-card-trend">
                        <span class="trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Gross platform volume</span>
                    </div>
                </div>

                <!-- 2. Total Bookings -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL BOOKINGS</span>
                        <div class="stat-icon-circle blue">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $totalBookings; ?></div>
                    <div class="stat-card-trend">
                        <span>All rental transactions</span>
                    </div>
                </div>

                <!-- 3. Total Users -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL USERS</span>
                        <div class="stat-icon-circle">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-card-trend">
                        <span>Registered customers</span>
                    </div>
                </div>

                <!-- 4. Total Vendors -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL VENDORS</span>
                        <div class="stat-icon-circle amber">
                            <i class="fa-solid fa-store"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $totalVendors; ?></div>
                    <div class="stat-card-trend">
                        <a href="vendors.php" class="text-magenta font-weight-bold">Manage boutiques &rarr;</a>
                    </div>
                </div>

                <!-- 5. Total Dresses -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL DRESSES</span>
                        <div class="stat-icon-circle indigo">
                            <i class="fa-solid fa-vest-patches"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $totalDresses; ?></div>
                    <div class="stat-card-trend">
                        <span>Catalog wardrobe pieces</span>
                    </div>
                </div>
            </div>

            <!-- Booking Activity Chart -->
            <div class="dashboard-card">
                <div class="card-header-flex">
                    <div>
                        <span class="section-pretitle">SYSTEM ANALYTICS</span>
                        <h3 class="card-title-text">Booking Activity & Revenue Trend</h3>
                    </div>
                    <div class="chart-toggle-group">
                        <button type="button" class="chart-toggle-btn active" onclick="switchChartMode('monthly')">Monthly</button>
                        <button type="button" class="chart-toggle-btn" onclick="switchChartMode('weekly')">Weekly</button>
                    </div>
                </div>

                <div class="chart-container-box">
                    <canvas id="adminBookingChart"></canvas>
                </div>
            </div>

            <!-- Two-Column: Activities Stream & Recent Transactions -->
            <div class="dashboard-columns-1-1">
                <!-- Recent System Activities -->
                <div class="dashboard-card">
                    <div class="card-header-flex">
                        <h3 class="card-title-text">Recent System Activities</h3>
                    </div>

                    <div class="activity-stream">
                        <?php if (!empty($recentActivities)): ?>
                            <?php foreach ($recentActivities as $act): ?>
                                <div class="activity-item">
                                    <div class="activity-icon-bubble <?php echo htmlspecialchars($act['type'] ?? 'info'); ?>">
                                        <i class="fa-solid fa-bell"></i>
                                    </div>
                                    <div class="activity-text-box">
                                        <div class="activity-title"><?php echo htmlspecialchars($act['title']); ?></div>
                                        <div class="activity-subtitle"><?php echo htmlspecialchars($act['message']); ?></div>
                                        <div class="activity-time"><?php echo date('M d, Y - g:i A', strtotime($act['created_at'])); ?> &bull; Target: <?php echo ucfirst($act['user_type']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No system events logged.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Transactions Table -->
                <div class="dashboard-card">
                    <div class="card-header-flex">
                        <h3 class="card-title-text">Recent Transactions</h3>
                        <a href="transactions.php" class="btn btn-sm btn-subtle">View Ledger &rarr;</a>
                    </div>

                    <?php if (!empty($recentTransactions)): ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Txn ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $tx): ?>
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold" style="font-family: monospace; font-size: 0.82rem;">
                                                    <?php echo htmlspecialchars($tx['transaction_id'] ?? ('TXN-' . $tx['payment_id'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold"><?php echo htmlspecialchars($tx['customer_name']); ?></span>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-magenta"><?php echo format_currency($tx['amount']); ?></span>
                                            </td>
                                            <td>
                                                <span style="font-size: 0.8rem; color: var(--text-muted);">
                                                    <?php echo date('M d, Y', strtotime($tx['payment_date'] ?? 'now')); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo get_status_badge($tx['payment_status']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No payment transactions recorded.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js Script for Admin Activity -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('adminBookingChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [
                {
                    type: 'line',
                    label: 'Revenue ($)',
                    data: <?php echo json_encode($chartRevenues); ?>,
                    borderColor: '#e11d48',
                    backgroundColor: 'rgba(225, 29, 72, 0.08)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.35,
                    yAxisID: 'y'
                },
                {
                    type: 'bar',
                    label: 'Bookings Volume',
                    data: <?php echo json_encode($chartCounts); ?>,
                    backgroundColor: '#4f46e5',
                    borderRadius: 6,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { family: 'Plus Jakarta Sans', size: 12 }, usePointStyle: true }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: { color: 'rgba(15, 23, 42, 0.04)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});

function switchChartMode(mode) {
    showToast(`Displaying ${mode} system statistics`, 'info');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
