<?php
/**
 * Vendor Manage Dresses
 */

$page_title = "Manage Dresses";
$path_prefix = '../';
require_once __DIR__ . '/../includes/vendor_auth.php';
require_once __DIR__ . '/../includes/header.php';

$vendorId = (int)$_SESSION['vendor_id'];
$catFilter = (int)($_GET['category'] ?? 0);

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

// Fetch vendor dresses
$sql = "
    SELECT d.*, c.category_name,
           (SELECT COUNT(*) FROM rentals r WHERE r.dress_id = d.dress_id AND r.rental_status != 'cancelled') as total_rentals_count
    FROM dresses d
    LEFT JOIN categories c ON d.category_id = c.category_id
    WHERE d.vendor_id = :vendor_id
";
$params = [':vendor_id' => $vendorId];

if ($catFilter > 0) {
    $sql .= " AND d.category_id = :category_id";
    $params[':category_id'] = $catFilter;
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dresses = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">My Boutique Dresses</h1>
                    <p class="page-subtitle-text">Manage your active wardrobe collection, edit details, and monitor rental pricing.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="add-dress.php" class="btn btn-magenta">
                        <i class="fa-solid fa-plus"></i> Add New Dress
                    </a>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="dashboard-card" style="padding: 16px 20px; margin-bottom: 24px;">
                <form action="dresses.php" method="GET" style="display: flex; gap: 16px; align-items: center;">
                    <label class="form-label" style="margin-bottom: 0;">Filter Category:</label>
                    <select name="category" class="form-input no-icon" style="max-width: 260px;" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($catFilter === (int)$cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($catFilter > 0): ?>
                        <a href="dresses.php" class="btn btn-sm btn-subtle">Clear Filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Dress Catalog Cards -->
            <div>
                <?php if (!empty($dresses)): ?>
                    <div class="vendor-dress-grid">
                        <?php foreach ($dresses as $dress):
                            $thumb = get_dress_image_url($dress['image'], $dress['dress_name'], '../');
                        ?>
                            <article class="vendor-dress-card">
                                <a class="vendor-dress-image" href="edit-dress.php?id=<?php echo $dress['dress_id']; ?>">
                                    <img src="<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo htmlspecialchars($dress['dress_name']); ?>" loading="lazy">
                                </a>
                                <div class="vendor-dress-card-body">
                                    <div class="vendor-dress-card-meta">
                                        <span class="dress-card-category"><?php echo htmlspecialchars($dress['category_name'] ?? 'Unassigned'); ?></span>
                                        <?php echo get_status_badge($dress['availability']); ?>
                                    </div>
                                    <h2 class="vendor-dress-card-title"><?php echo htmlspecialchars($dress['dress_name']); ?></h2>
                                    <p class="vendor-dress-description">
                                        <?php echo htmlspecialchars($dress['description'] ?: 'No description provided.'); ?>
                                    </p>
                                    <div class="vendor-dress-attributes">
                                        <span>Size: <strong><?php echo htmlspecialchars($dress['size'] ?? 'M'); ?></strong></span>
                                        <span>Color: <strong><?php echo htmlspecialchars($dress['color'] ?? 'Standard'); ?></strong></span>
                                    </div>
                                    <div class="vendor-dress-prices">
                                        <div>
                                            <span class="vendor-dress-price-label">Rental (4 days)</span>
                                            <strong class="vendor-dress-rent-price"><?php echo format_currency($dress['rental_price']); ?></strong>
                                        </div>
                                        <div>
                                            <span class="vendor-dress-price-label">Refundable deposit</span>
                                            <strong><?php echo format_currency($dress['security_deposit']); ?></strong>
                                        </div>
                                    </div>
                                    <div class="vendor-dress-card-footer">
                                        <span class="text-muted"><?php echo (int)$dress['total_rentals_count']; ?> rentals</span>
                                        <div class="vendor-dress-actions">
                                            <a href="edit-dress.php?id=<?php echo $dress['dress_id']; ?>" class="btn btn-sm btn-subtle">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit details
                                            </a>
                                            <a href="delete-dress.php?id=<?php echo $dress['dress_id']; ?>" class="btn btn-sm btn-subtle text-danger" onclick="return confirm('Are you sure you want to delete this dress listing?');" aria-label="Delete <?php echo htmlspecialchars($dress['dress_name']); ?>">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 50px 20px;">
                        <i class="fa-solid fa-vest-patches text-muted" style="font-size: 3rem; margin-bottom: 14px;"></i>
                        <h3 style="color: var(--text-primary); margin-bottom: 6px;">No Dresses Found</h3>
                        <p class="text-muted" style="margin-bottom: 20px;">Start building your boutique wardrobe by adding your first designer piece.</p>
                        <a href="add-dress.php" class="btn btn-magenta">Add New Dress</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
