<?php
/**
 * Vendor Boutique Dashboard
 */

$page_title = "Vendor Overview";
$path_prefix = '../';
require_once __DIR__ . '/../includes/vendor_auth.php';
require_once __DIR__ . '/../includes/header.php';

$vendorId = (int)$_SESSION['vendor_id'];

// 1. Calculate 5 Top Statistics via SQL
$totalDresses = 0;
$availableDresses = 0;
$pendingBookings = 0;
$activeRentals = 0;
$totalRevenue = 0.0;

try {
    // Total & Available Dresses
    $stmt = $pdo->prepare("SELECT COUNT(*), SUM(CASE WHEN availability = 'available' THEN 1 ELSE 0 END) FROM dresses WHERE vendor_id = :vendor_id");
    $stmt->execute([':vendor_id' => $vendorId]);
    list($totalDresses, $availableDresses) = $stmt->fetch(PDO::FETCH_NUM);
    $totalDresses = (int)$totalDresses;
    $availableDresses = (int)$availableDresses;

    // Pending Bookings
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM rentals r 
        JOIN dresses d ON r.dress_id = d.dress_id 
        WHERE d.vendor_id = :vendor_id AND r.rental_status = 'pending'
    ");
    $stmt->execute([':vendor_id' => $vendorId]);
    $pendingBookings = (int)$stmt->fetchColumn();

    // Active Rentals
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM rentals r 
        JOIN dresses d ON r.dress_id = d.dress_id 
        WHERE d.vendor_id = :vendor_id AND r.rental_status = 'active'
    ");
    $stmt->execute([':vendor_id' => $vendorId]);
    $activeRentals = (int)$stmt->fetchColumn();

    // Total Revenue (From approved/active/returned rentals)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(r.rental_amount), 0)
        FROM rentals r 
        JOIN dresses d ON r.dress_id = d.dress_id 
        WHERE d.vendor_id = :vendor_id AND r.rental_status IN ('approved', 'active', 'returned')
    ");
    $stmt->execute([':vendor_id' => $vendorId]);
    $totalRevenue = (float)$stmt->fetchColumn();

    // 2. Fetch Recent Customers & Bookings
    $stmt = $pdo->prepare("
        SELECT r.*, u.full_name as customer_name, u.email as customer_email, d.dress_name, d.image, d.size
        FROM rentals r
        JOIN users u ON r.user_id = u.user_id
        JOIN dresses d ON r.dress_id = d.dress_id
        WHERE d.vendor_id = :vendor_id
        ORDER BY r.request_date DESC
        LIMIT 6
    ");
    $stmt->execute([':vendor_id' => $vendorId]);
    $recentCustomers = $stmt->fetchAll();

    // 3. Fetch Inventory Status (top items)
    $stmt = $pdo->prepare("
        SELECT d.*, c.category_name,
               (SELECT COUNT(*) FROM rentals r WHERE r.dress_id = d.dress_id AND r.rental_status IN ('approved', 'active')) as current_active_rentals
        FROM dresses d
        LEFT JOIN categories c ON d.category_id = c.category_id
        WHERE d.vendor_id = :vendor_id
        ORDER BY d.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([':vendor_id' => $vendorId]);
    $inventoryItems = $stmt->fetchAll();

    // 4. Monthly Revenue Analytics for Chart.js
    $monthlyData = [];
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(r.request_date, '%b') as month_name, 
               COALESCE(SUM(r.rental_amount), 0) as monthly_revenue,
               COUNT(*) as monthly_bookings
        FROM rentals r
        JOIN dresses d ON r.dress_id = d.dress_id
        WHERE d.vendor_id = :vendor_id AND r.rental_status IN ('approved', 'active', 'returned')
        GROUP BY DATE_FORMAT(r.request_date, '%Y-%m'), DATE_FORMAT(r.request_date, '%b')
        ORDER BY r.request_date ASC
        LIMIT 6
    ");
    $stmt->execute([':vendor_id' => $vendorId]);
    $monthlyData = $stmt->fetchAll();

} catch (Exception $e) {
    // Error handling
}

// Prepare Chart.js arrays
$chartLabels = !empty($monthlyData) ? array_column($monthlyData, 'month_name') : [date('M')];
$chartRevenue = !empty($monthlyData) ? array_map('floatval', array_column($monthlyData, 'monthly_revenue')) : [$totalRevenue];
$chartBookings = !empty($monthlyData) ? array_map('intval', array_column($monthlyData, 'monthly_bookings')) : [$activeRentals];
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <!-- Page Header -->
            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Vendor Overview</h1>
                    <p class="page-subtitle-text">Welcome back, here's what today's stats look like for <?php echo htmlspecialchars($_SESSION['business_name'] ?? 'your boutique'); ?>.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="add-dress.php" class="btn btn-magenta">
                        <i class="fa-solid fa-plus"></i> Add New Dress
                    </a>
                </div>
            </div>

            <!-- Top 5 Statistic Cards -->
            <div class="stats-grid-5">
                <!-- 1. Total Dresses -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL DRESSES</span>
                        <div class="stat-icon-circle">
                            <i class="fa-solid fa-vest-patches"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $totalDresses; ?></div>
                    <div class="stat-card-trend">
                        <span>Wardrobe listings</span>
                    </div>
                </div>

                <!-- 2. Available -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">AVAILABLE</span>
                        <div class="stat-icon-circle emerald">
                            <i class="fa-solid fa-check"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $availableDresses; ?></div>
                    <div class="stat-card-trend">
                        <span class="trend-up">Ready for booking</span>
                    </div>
                </div>

                <!-- 3. Pending Requests -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">PENDING</span>
                        <div class="stat-icon-circle amber">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $pendingBookings; ?></div>
                    <div class="stat-card-trend">
                        <span class="trend-down">Requires action</span>
                    </div>
                </div>

                <!-- 4. Active Rentals -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">ACTIVE RENTALS</span>
                        <div class="stat-icon-circle blue">
                            <i class="fa-solid fa-person-walking-luggage"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $activeRentals; ?></div>
                    <div class="stat-card-trend">
                        <span>Currently with clients</span>
                    </div>
                </div>

                <!-- 5. Revenue -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">REVENUE</span>
                        <div class="stat-icon-circle indigo">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo format_currency($totalRevenue); ?></div>
                    <div class="stat-card-trend">
                        <span class="trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Settled earnings</span>
                    </div>
                </div>
            </div>

            <!-- Action Needed Alerts (Dynamic) -->
            <?php if ($pendingBookings > 0 || $activeRentals > 0): ?>
                <div style="margin-bottom: 28px;">
                    <div class="action-cards-grid">
                        <?php if ($pendingBookings > 0): ?>
                            <div class="action-alert-card warning">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fa-solid fa-bell text-warning" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <div class="font-weight-bold" style="font-size: 0.95rem;">Check Pending Bookings</div>
                                        <span class="text-muted" style="font-size: 0.82rem;">You have <?php echo $pendingBookings; ?> pending rental request(s) awaiting approval.</span>
                                    </div>
                                </div>
                                <a href="bookings.php?status=pending" class="btn btn-sm btn-magenta">Review Requests</a>
                            </div>
                        <?php endif; ?>

                        <?php if ($activeRentals > 0): ?>
                            <div class="action-alert-card">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fa-solid fa-shirt text-magenta" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <div class="font-weight-bold" style="font-size: 0.95rem;">Returns & Sanitation</div>
                                        <span class="text-muted" style="font-size: 0.82rem;"><?php echo $activeRentals; ?> dress(es) currently out on active rental.</span>
                                    </div>
                                </div>
                                <a href="returns.php" class="btn btn-sm btn-subtle">Check Returns</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Growth Analytics Chart Section -->
            <div class="dashboard-card">
                <div class="card-header-flex">
                    <div>
                        <span class="section-pretitle">PERFORMANCE METRICS</span>
                        <h3 class="card-title-text">Growth & Revenue Analytics</h3>
                    </div>
                    <div class="chart-toggle-group">
                        <button type="button" class="chart-toggle-btn active" onclick="switchChartMode('monthly')">Monthly</button>
                        <button type="button" class="chart-toggle-btn" onclick="switchChartMode('weekly')">Weekly</button>
                    </div>
                </div>

                <div class="chart-container-box">
                    <canvas id="vendorGrowthChart"></canvas>
                </div>
            </div>

            <!-- Two Columns: Recent Customers & Inventory Status -->
            <div class="dashboard-columns-2-1">
                <!-- Recent Customers Table -->
                <div class="dashboard-card">
                    <div class="card-header-flex">
                        <h3 class="card-title-text">Recent Customers</h3>
                        <a href="bookings.php" class="btn btn-sm btn-subtle">View All &rarr;</a>
                    </div>

                    <?php if (!empty($recentCustomers)): ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Rented Item</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentCustomers as $cust): 
                                        $initial = strtoupper(substr($cust['customer_name'], 0, 1));
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="table-user-cell">
                                                    <div class="user-avatar-circle" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                                        <?php echo $initial; ?>
                                                    </div>
                                                    <div>
                                                        <span class="font-weight-bold" style="display: block; color: var(--text-primary);"><?php echo htmlspecialchars($cust['customer_name']); ?></span>
                                                        <span class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($cust['customer_email']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold"><?php echo htmlspecialchars($cust['dress_name']); ?></span>
                                                <span class="text-muted" style="display: block; font-size: 0.75rem;">Size <?php echo htmlspecialchars($cust['size'] ?? 'M'); ?></span>
                                            </td>
                                            <td>
                                                <span style="font-size: 0.85rem;">
                                                    <?php echo format_date($cust['rental_start_date'], 'M d'); ?> - <?php echo format_date($cust['rental_end_date'], 'M d'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo get_status_badge($cust['rental_status']); ?>
                                            </td>
                                            <td>
                                                <a href="bookings.php?id=<?php echo $cust['rental_id']; ?>" class="btn btn-sm btn-subtle">
                                                    Manage
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center" style="padding: 30px;">
                            <p class="text-muted">No rental bookings recorded yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Inventory Status Widget -->
                <div class="dashboard-card">
                    <div class="card-header-flex">
                        <h3 class="card-title-text">Inventory Status</h3>
                        <a href="inventory.php" class="text-magenta" style="font-size: 0.85rem; font-weight: 600;">Manage</a>
                    </div>

                    <?php if (!empty($inventoryItems)): ?>
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <?php foreach ($inventoryItems as $item): 
                                $thumb = get_dress_image_url($item['image'], $item['dress_name'], '../');
                                $stockStatus = ($item['availability'] === 'available') ? 'Available' : 'Unavailable';
                                $statusBadge = ($item['availability'] === 'available') ? 'badge-success' : 'badge-danger';
                            ?>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid rgba(15, 23, 42, 0.04);">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Dress" style="width: 44px; height: 52px; border-radius: 8px; object-fit: cover;">
                                        <div>
                                            <span class="font-weight-bold" style="font-size: 0.92rem; color: var(--text-primary); display: block;"><?php echo htmlspecialchars($item['dress_name']); ?></span>
                                            <span class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($item['category_name'] ?? 'Dress'); ?> &bull; <?php echo format_currency($item['rental_price']); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="status-badge <?php echo $statusBadge; ?>"><?php echo $stockStatus; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center" style="padding: 30px;">
                            <p class="text-muted">No items in your boutique inventory.</p>
                            <a href="add-dress.php" class="btn btn-sm btn-magenta mt-2">Add Your First Dress</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js Script for Growth Analytics -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('vendorGrowthChart');
    if (!ctx) return;

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [
                {
                    label: 'Revenue ($)',
                    data: <?php echo json_encode($chartRevenue); ?>,
                    borderColor: '#e11d48',
                    backgroundColor: 'rgba(225, 29, 72, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#e11d48',
                    pointRadius: 5
                },
                {
                    label: 'Bookings Volume',
                    data: <?php echo json_encode($chartBookings); ?>,
                    borderColor: '#0284c7',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    pointBackgroundColor: '#0284c7',
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: { family: 'Plus Jakarta Sans', size: 12 },
                        usePointStyle: true
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(15, 23, 42, 0.04)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});

function switchChartMode(mode) {
    showToast(`Displaying ${mode} analytics breakdown`, 'info');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
