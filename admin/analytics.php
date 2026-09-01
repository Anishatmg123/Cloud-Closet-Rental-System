<?php
/**
 * Admin Analytics & Deep Dive Insights
 */

$page_title = "Platform Analytics";
$path_prefix = '../';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/header.php';

// 1. Category Distribution
$catData = $pdo->query("
    SELECT c.category_name, COUNT(d.dress_id) as dress_count
    FROM categories c
    LEFT JOIN dresses d ON c.category_id = d.category_id
    GROUP BY c.category_id, c.category_name
    HAVING dress_count > 0
")->fetchAll();

$catLabels = array_column($catData, 'category_name');
$catCounts = array_map('intval', array_column($catData, 'dress_count'));

// 2. Booking Status Distribution
$statusData = $pdo->query("
    SELECT rental_status, COUNT(*) as status_count
    FROM rentals
    GROUP BY rental_status
")->fetchAll();

$statusLabels = array_map('ucfirst', array_column($statusData, 'rental_status'));
$statusCounts = array_map('intval', array_column($statusData, 'status_count'));

// 3. Most Popular Dresses
$popularDresses = $pdo->query("
    SELECT d.dress_name, d.image, c.category_name, d.rental_price, COUNT(r.rental_id) as rental_count
    FROM dresses d
    LEFT JOIN categories c ON d.category_id = c.category_id
    LEFT JOIN rentals r ON d.dress_id = r.dress_id
    GROUP BY d.dress_id
    ORDER BY rental_count DESC, d.created_at DESC
    LIMIT 6
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
                    <h1 class="page-title-text">Platform Analytics & Insights</h1>
                    <p class="page-subtitle-text">Wardrobe category breakdown, booking distribution, and customer demand trends.</p>
                </div>
            </div>

            <!-- Two Pie / Doughnut Charts -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;" class="dashboard-columns-1-1">
                <!-- Chart 1: Category Distribution -->
                <div class="dashboard-card">
                    <h3 class="card-title-text" style="margin-bottom: 20px;">Wardrobe Category Distribution</h3>
                    <div style="height: 280px; position: relative;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Booking Status Distribution -->
                <div class="dashboard-card">
                    <h3 class="card-title-text" style="margin-bottom: 20px;">Rental Status Distribution</h3>
                    <div style="height: 280px; position: relative;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Popular Dresses Table -->
            <div class="dashboard-card">
                <h3 class="card-title-text" style="margin-bottom: 20px;">Most In-Demand Designer Silhouettes</h3>

                <?php if (!empty($popularDresses)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Gown Name</th>
                                    <th>Category</th>
                                    <th>Rental Fee</th>
                                    <th>Total Rentals</th>
                                    <th>Demand Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popularDresses as $d): 
                                    $thumb = !empty($d['image']) && file_exists(__DIR__ . '/../' . $d['image']) ? '../' . $d['image'] : '../assets/images/hero_closet_banner.jpg';
                                ?>
                                    <tr>
                                        <td>
                                            <div class="table-user-cell">
                                                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Dress" class="table-item-thumb">
                                                <span class="font-weight-bold" style="color: var(--text-primary);"><?php echo htmlspecialchars($d['dress_name']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($d['category_name'] ?? 'Dress'); ?></td>
                                        <td><span class="font-weight-bold text-magenta"><?php echo format_currency($d['rental_price']); ?></span></td>
                                        <td><span class="font-weight-bold"><?php echo $d['rental_count']; ?></span> rentals</td>
                                        <td>
                                            <?php if ($d['rental_count'] > 1): ?>
                                                <span class="status-badge badge-success">High Demand</span>
                                            <?php else: ?>
                                                <span class="status-badge badge-info">Standard</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No dress data available.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Category Doughnut Chart
    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(!empty($catLabels) ? $catLabels : ['Evening Gowns', 'Cocktail', 'Bridal']); ?>,
                datasets: [{
                    data: <?php echo json_encode(!empty($catCounts) ? $catCounts : [4, 2, 2]); ?>,
                    backgroundColor: ['#e11d48', '#0284c7', '#4f46e5', '#16a34a', '#d97706', '#9333ea'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Plus Jakarta Sans', size: 11 }, usePointStyle: true } }
                }
            }
        });
    }

    // 2. Status Pie Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(!empty($statusLabels) ? $statusLabels : ['Returned', 'Active', 'Approved']); ?>,
                datasets: [{
                    data: <?php echo json_encode(!empty($statusCounts) ? $statusCounts : [1, 1, 1]); ?>,
                    backgroundColor: ['#0284c7', '#16a34a', '#d97706', '#dc2626', '#64748b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Plus Jakarta Sans', size: 11 }, usePointStyle: true } }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
