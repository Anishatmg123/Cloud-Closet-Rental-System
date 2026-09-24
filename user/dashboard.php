<?php
/**
 * Customer Dashboard
 */

$page_title = "Customer Dashboard";
$path_prefix = '../';
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/header.php';

$userId = (int)$_SESSION['user_id'];

// 1. Fetch Summary Stats for User
$activeRental = null;
$totalRentalsCount = 0;
$totalSpent = 0.0;
$totalSavingsEstimated = 0.0;

try {
    // Current / Active Rental
    $stmt = $pdo->prepare("
        SELECT r.*, d.dress_name, d.image, d.size, d.color, c.category_name
        FROM rentals r
        JOIN dresses d ON r.dress_id = d.dress_id
        LEFT JOIN categories c ON d.category_id = c.category_id
        WHERE r.user_id = :user_id AND r.rental_status = 'active'
        ORDER BY r.rental_end_date ASC
        LIMIT 1
    ");
    $stmt->execute([':user_id' => $userId]);
    $activeRental = $stmt->fetch();

    // Total rentals count & spend
    $stmt = $pdo->prepare("SELECT COUNT(*), COALESCE(SUM(rental_amount), 0) FROM rentals WHERE user_id = :user_id AND rental_status != 'cancelled'");
    $stmt->execute([':user_id' => $userId]);
    list($totalRentalsCount, $totalSpent) = $stmt->fetch(PDO::FETCH_NUM);

    // Estimate savings (Average luxury dress retail is ~7x rental price)
    $totalSavingsEstimated = $totalSpent * 6.5;

    // 2. Fetch Recent Activities / Notifications
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_type = 'user' AND user_id = :user_id ORDER BY created_at DESC LIMIT 4");
    $stmt->execute([':user_id' => $userId]);
    $recentActivities = $stmt->fetchAll();

    // 3. Fetch Curated Dresses
    $stmt = $pdo->query("
        SELECT d.*, c.category_name 
        FROM dresses d 
        LEFT JOIN categories c ON d.category_id = c.category_id 
        WHERE d.availability = 'available' 
        ORDER BY d.created_at DESC 
        LIMIT 4
    ");
    $curatedDresses = $stmt->fetchAll();

    // 4. Fetch Recent Rental History
    $stmt = $pdo->prepare("
        SELECT r.*, d.dress_name, d.image, d.size, p.payment_method, p.payment_status
        FROM rentals r
        JOIN dresses d ON r.dress_id = d.dress_id
        LEFT JOIN payments p ON r.rental_id = p.rental_id
        WHERE r.user_id = :user_id
        ORDER BY r.request_date DESC
        LIMIT 6
    ");
    $stmt->execute([':user_id' => $userId]);
    $recentRentals = $stmt->fetchAll();

} catch (Exception $e) {
    // Graceful error handle
}

$userFavs = get_user_favorites_ids($pdo, $userId);
?>

<div class="dashboard-layout">
    <!-- Left Navigation Sidebar -->
    <?php require_once __DIR__ . '/../includes/user_sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="dashboard-main-wrapper">
        <!-- Topbar -->
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <!-- Dashboard Content Area -->
        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <!-- Page Title & Greetings -->
            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Welcome back, <?php echo htmlspecialchars($current_user['full_name']); ?></h1>
                    <p class="page-subtitle-text">Explore new designer arrivals or manage your scheduled event outfits.</p>
                </div>
                <div>
                    <a href="dresses.php" class="btn btn-magenta">
                        <i class="fa-solid fa-plus"></i> Book a Dress
                    </a>
                </div>
            </div>

            <!-- Featured Collection Hero Banner: "THE VELVET NIGHT" -->
            <div class="editorial-hero-banner">
                <div>
                    <span class="banner-tagline"><i class="fa-solid fa-sparkles"></i> NEW COLLECTION</span>
                    <h2 class="banner-title">THE VELVET NIGHT</h2>
                    <p class="banner-subtitle">Sensual silhouettes, opulent fabrics, and midnight hues curated for this season's most prestigious galas.</p>
                </div>
                <div>
                    <a href="dresses.php?category=1" class="btn btn-lg btn-white">
                        EXPLORE COLLECTION <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Summary Stat Cards -->
            <div class="stats-grid-4">
                <!-- Current Active Rental -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">CURRENT RENTAL</span>
                        <div class="stat-icon-circle">
                            <i class="fa-solid fa-vest-patches"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">
                        <?php if ($activeRental): ?>
                            1 <span style="font-size: 1rem; font-weight: 500; color: var(--text-muted);">Active</span>
                        <?php else: ?>
                            0 <span style="font-size: 1rem; font-weight: 500; color: var(--text-muted);">None</span>
                        <?php endif; ?>
                    </div>
                    <div class="stat-card-trend">
                        <?php if ($activeRental): ?>
                            <span class="text-magenta font-weight-bold"><?php echo htmlspecialchars($activeRental['dress_name']); ?></span> (Due <?php echo format_date($activeRental['rental_end_date']); ?>)
                        <?php else: ?>
                            <span>Ready for your next event</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Total Savings -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL SAVINGS</span>
                        <div class="stat-icon-circle emerald">
                            <i class="fa-solid fa-piggy-bank"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">
                        <?php echo format_currency($totalSavingsEstimated); ?>
                    </div>
                    <div class="stat-card-trend">
                        <span class="trend-up"><i class="fa-solid fa-arrow-trend-up"></i> 85% Saved</span> vs retail buying
                    </div>
                </div>

                <!-- Total Bookings -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">TOTAL EXPERIENCES</span>
                        <div class="stat-icon-circle blue">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">
                        <?php echo $totalRentalsCount; ?>
                    </div>
                    <div class="stat-card-trend">
                        <span>Lifetime rentals completed</span>
                    </div>
                </div>

                <!-- Saved Favorites -->
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">SAVED WISHLIST</span>
                        <div class="stat-icon-circle indigo">
                            <i class="fa-regular fa-heart"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">
                        <?php echo count($userFavs); ?>
                    </div>
                    <div class="stat-card-trend">
                        <a href="wishlist.php" class="text-magenta font-weight-bold">View wishlist &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Two-Column Section: Curated For You & Activity Feed -->
            <div class="dashboard-columns-2-1">
                <!-- Curated For You Section -->
                <div class="dashboard-card">
                    <div class="card-header-flex">
                        <div>
                            <span class="section-pretitle">HANDPICKED FOR YOU</span>
                            <h3 class="card-title-text">Curated Outfits</h3>
                        </div>
                        <a href="dresses.php" class="btn btn-sm btn-outline-magenta">View Catalog &rarr;</a>
                    </div>

                    <?php if (!empty($curatedDresses)): ?>
                        <div class="dress-grid" style="grid-template-columns: repeat(2, 1fr); gap: 20px;">
                            <?php foreach ($curatedDresses as $dress): 
                                $isFav = in_array($dress['dress_id'], $userFavs);
                                $imgSrc = get_dress_image_url($dress['image'], $dress['dress_name'], '../');
                            ?>
                                <div class="dress-card" style="box-shadow: none; border: 1px solid rgba(15, 23, 42, 0.06);">
                                    <div class="dress-image-box" style="height: 260px;">
                                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($dress['dress_name']); ?>">
                                        <button type="button" class="dress-fav-btn <?php echo $isFav ? 'active' : ''; ?>" onclick="toggleFavorite(<?php echo $dress['dress_id']; ?>, this)">
                                            <i class="<?php echo $isFav ? 'fa-solid text-magenta' : 'fa-regular'; ?> fa-heart"></i>
                                        </button>
                                        <span class="dress-size-pill">Size <?php echo htmlspecialchars($dress['size'] ?? 'M'); ?></span>
                                    </div>
                                    <div class="dress-card-body" style="padding: 16px;">
                                        <span class="dress-card-category"><?php echo htmlspecialchars($dress['category_name'] ?? 'Couture'); ?></span>
                                        <h4 class="dress-card-title" style="font-size: 1.1rem; margin-bottom: 8px;"><?php echo htmlspecialchars($dress['dress_name']); ?></h4>
                                        <div class="dress-card-footer" style="padding-top: 10px;">
                                            <div class="dress-price-wrap">
                                                <span class="dress-rent-price" style="font-size: 1.2rem;"><?php echo format_currency($dress['rental_price']); ?></span>
                                                <span class="dress-rent-period">per period</span>
                                            </div>
                                            <a href="dress-details.php?id=<?php echo $dress['dress_id']; ?>" class="btn btn-sm btn-magenta">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No curated gowns available.</p>
                    <?php endif; ?>
                </div>

                <!-- Recent Activity Feed -->
                <div class="dashboard-card">
                    <div class="card-header-flex">
                        <h3 class="card-title-text">Recent Activity</h3>
                        <a href="notifications.php" class="text-magenta" style="font-size: 0.85rem; font-weight: 600;">All updates</a>
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
                                        <div class="activity-time"><?php echo date('M d, g:i A', strtotime($act['created_at'])); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center" style="padding: 30px 10px;">
                                <i class="fa-regular fa-bell-slash text-muted" style="font-size: 2rem; margin-bottom: 10px;"></i>
                                <p class="text-muted" style="font-size: 0.9rem;">No recent activities yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Rental History Table Section -->
            <div class="dashboard-card">
                <div class="card-header-flex">
                    <div>
                        <span class="section-pretitle">ORDER LEDGER</span>
                        <h3 class="card-title-text">Recent Rental History</h3>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <a href="download-report.php" class="btn btn-sm btn-subtle" id="download-report-btn">
                            <i class="fa-solid fa-download"></i> Download Report
                        </a>
                        <a href="bookings.php" class="btn btn-sm btn-outline-magenta">View All Bookings</a>
                    </div>
                </div>

                <?php if (!empty($recentRentals)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Item Details</th>
                                    <th>Rental Period</th>
                                    <th>Total Paid</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRentals as $rental): 
                                    $thumb = get_dress_image_url($rental['image'], $rental['dress_name'], '../');
                                ?>
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold">#ORD-<?php echo str_pad($rental['rental_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                        </td>
                                        <td>
                                            <div class="table-user-cell">
                                                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Dress" class="table-item-thumb">
                                                <div>
                                                    <span class="font-weight-bold" style="display: block; color: var(--text-primary);"><?php echo htmlspecialchars($rental['dress_name']); ?></span>
                                                    <span class="text-muted" style="font-size: 0.78rem;">Size <?php echo htmlspecialchars($rental['size'] ?? 'M'); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.88rem;">
                                                <?php echo format_date($rental['rental_start_date'], 'M d'); ?> - <?php echo format_date($rental['rental_end_date'], 'M d, Y'); ?>
                                            </span>
                                            <span class="text-muted" style="display: block; font-size: 0.75rem;"><?php echo $rental['total_days']; ?> Days</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta"><?php echo format_currency($rental['total_amount']); ?></span>
                                        </td>
                                        <td>
                                            <span style="text-transform: uppercase; font-size: 0.78rem; font-weight: 700;">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $rental['payment_method'] ?? 'COD')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo get_status_badge($rental['rental_status']); ?>
                                        </td>
                                        <td>
                                            <a href="booking-details.php?id=<?php echo $rental['rental_id']; ?>" class="btn btn-sm btn-subtle" title="View Order Receipt">
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 40px 20px; background: var(--bg-subtle); border-radius: var(--radius-md);">
                        <i class="fa-solid fa-bag-shopping" style="font-size: 2.5rem; color: var(--text-light); margin-bottom: 12px;"></i>
                        <h4 style="color: var(--text-primary); margin-bottom: 6px;">No Rental History Yet</h4>
                        <p class="text-muted" style="font-size: 0.92rem; margin-bottom: 20px;">Ready to shine at your upcoming party, wedding, or gala?</p>
                        <a href="dresses.php" class="btn btn-magenta">Browse Dress Collection</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
