<?php
/**
 * Customer Dress Catalog & Exploration
 */

$page_title = "Explore Dresses";
$path_prefix = '../';
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/header.php';

$userId = (int)$_SESSION['user_id'];

// Filter parameters
$search = trim($_GET['q'] ?? '');
$categoryFilter = (int)($_GET['category'] ?? 0);
$sizeFilter = trim($_GET['size'] ?? '');
$priceMax = (float)($_GET['price_max'] ?? 0);
$sortBy = trim($_GET['sort'] ?? 'newest');

// Fetch all categories for filter dropdown
$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
} catch (Exception $e) {}

// Build SQL Query
$sql = "
    SELECT d.*, c.category_name, v.business_name 
    FROM dresses d 
    LEFT JOIN categories c ON d.category_id = c.category_id 
    LEFT JOIN vendors v ON d.vendor_id = v.vendor_id 
    WHERE d.availability != 'unavailable'
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (d.dress_name LIKE :search OR d.description LIKE :search OR c.category_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($categoryFilter > 0) {
    $sql .= " AND d.category_id = :category_id";
    $params[':category_id'] = $categoryFilter;
}

if (!empty($sizeFilter)) {
    $sql .= " AND d.size = :size";
    $params[':size'] = $sizeFilter;
}

if ($priceMax > 0) {
    $sql .= " AND d.rental_price <= :price_max";
    $params[':price_max'] = $priceMax;
}

// Sorting
switch ($sortBy) {
    case 'price_asc':
        $sql .= " ORDER BY d.rental_price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY d.rental_price DESC";
        break;
    case 'popularity':
        $sql .= " ORDER BY (SELECT COUNT(*) FROM rentals r WHERE r.dress_id = d.dress_id) DESC, d.created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY d.created_at DESC";
        break;
}

$dresses = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dresses = $stmt->fetchAll();
} catch (Exception $e) {}

$userFavs = get_user_favorites_ids($pdo, $userId);
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/user_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <!-- Page Title Header -->
            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Curated Dress Catalog</h1>
                    <p class="page-subtitle-text">Browse luxury gowns, cocktail silhouettes, and designer sets for rent.</p>
                </div>
            </div>

            <!-- Filter Controls Bar -->
            <div class="dashboard-card" style="padding: 20px; margin-bottom: 28px;">
                <form action="dresses.php" method="GET" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
                    <!-- Search Input -->
                    <div style="flex: 2; min-width: 200px;">
                        <label class="form-label" style="font-size: 0.78rem;">Search Catalog</label>
                        <div class="form-control-wrap">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" 
                                   name="q" 
                                   class="form-input" 
                                   placeholder="Dress name, designer, keyword..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div style="flex: 1; min-width: 160px;">
                        <label class="form-label" style="font-size: 0.78rem;">Category</label>
                        <select name="category" class="form-input no-icon">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php echo ($categoryFilter === (int)$cat['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Size Filter -->
                    <div style="flex: 1; min-width: 120px;">
                        <label class="form-label" style="font-size: 0.78rem;">Size</label>
                        <select name="size" class="form-input no-icon">
                            <option value="">All Sizes</option>
                            <option value="XS" <?php echo ($sizeFilter === 'XS') ? 'selected' : ''; ?>>XS</option>
                            <option value="S" <?php echo ($sizeFilter === 'S') ? 'selected' : ''; ?>>S</option>
                            <option value="M" <?php echo ($sizeFilter === 'M') ? 'selected' : ''; ?>>M</option>
                            <option value="L" <?php echo ($sizeFilter === 'L') ? 'selected' : ''; ?>>L</option>
                            <option value="XL" <?php echo ($sizeFilter === 'XL') ? 'selected' : ''; ?>>XL</option>
                        </select>
                    </div>

                    <!-- Sort By -->
                    <div style="flex: 1; min-width: 160px;">
                        <label class="form-label" style="font-size: 0.78rem;">Sort By</label>
                        <select name="sort" class="form-input no-icon">
                            <option value="newest" <?php echo ($sortBy === 'newest') ? 'selected' : ''; ?>>Newest Arrivals</option>
                            <option value="popularity" <?php echo ($sortBy === 'popularity') ? 'selected' : ''; ?>>Most Popular</option>
                            <option value="price_asc" <?php echo ($sortBy === 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo ($sortBy === 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-magenta">Filter</button>
                        <a href="dresses.php" class="btn btn-subtle" title="Reset Filters">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Dress Catalog Grid -->
            <?php if (!empty($dresses)): ?>
                <div class="dress-grid">
                    <?php foreach ($dresses as $dress): 
                        $isFav = in_array($dress['dress_id'], $userFavs);
                        $imgSrc = !empty($dress['image']) && file_exists(__DIR__ . '/../' . $dress['image']) ? '../' . $dress['image'] : '../assets/images/hero_closet_banner.jpg';
                    ?>
                        <div class="dress-card">
                            <div class="dress-image-box">
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($dress['dress_name']); ?>" loading="lazy">
                                <button type="button" class="dress-fav-btn <?php echo $isFav ? 'active' : ''; ?>" onclick="toggleFavorite(<?php echo $dress['dress_id']; ?>, this)" aria-label="Add to wishlist">
                                    <i class="<?php echo $isFav ? 'fa-solid text-magenta' : 'fa-regular'; ?> fa-heart"></i>
                                </button>
                                <span class="dress-size-pill">Size <?php echo htmlspecialchars($dress['size'] ?? 'M'); ?></span>
                            </div>

                            <div class="dress-card-body">
                                <span class="dress-card-category"><?php echo htmlspecialchars($dress['category_name'] ?? 'Luxury'); ?></span>
                                <h3 class="dress-card-title"><?php echo htmlspecialchars($dress['dress_name']); ?></h3>
                                
                                <p class="text-muted" style="font-size: 0.82rem; line-height: 1.4; margin-bottom: 14px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?php echo htmlspecialchars($dress['description'] ?? ''); ?>
                                </p>

                                <div class="dress-card-footer">
                                    <div class="dress-price-wrap">
                                        <span class="dress-rent-price"><?php echo format_currency($dress['rental_price']); ?></span>
                                        <span class="dress-rent-period">per period</span>
                                    </div>
                                    <a href="dress-details.php?id=<?php echo $dress['dress_id']; ?>" class="btn btn-sm btn-magenta">
                                        Rent Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="dashboard-card text-center" style="padding: 60px 20px;">
                    <i class="fa-solid fa-magnifying-glass text-muted" style="font-size: 3rem; margin-bottom: 16px;"></i>
                    <h3 style="color: var(--text-primary); margin-bottom: 8px;">No Dresses Match Your Filters</h3>
                    <p class="text-muted" style="max-width: 440px; margin: 0 auto 24px;">Try searching with different keywords, removing size restrictions, or clearing filters.</p>
                    <a href="dresses.php" class="btn btn-magenta">View All Available Dresses</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
