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

            <!-- Dress Catalog Table Card -->
            <div class="dashboard-card">
                <?php if (!empty($dresses)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Outfit</th>
                                    <th>Category</th>
                                    <th>Size & Color</th>
                                    <th>Rental Price</th>
                                    <th>Deposit</th>
                                    <th>Availability</th>
                                    <th>Rentals</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dresses as $dress): 
                                    $thumb = get_dress_image_url($dress['image'], $dress['dress_name'], '../');
                                ?>
                                    <tr>
                                        <td>
                                            <div class="table-user-cell">
                                                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Dress" class="table-item-thumb">
                                                <div>
                                                    <span class="font-weight-bold" style="color: var(--text-primary); display: block; font-size: 0.95rem;">
                                                        <?php echo htmlspecialchars($dress['dress_name']); ?>
                                                    </span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">Added <?php echo date('M d, Y', strtotime($dress['created_at'])); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.88rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($dress['category_name'] ?? 'Unassigned'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">Size <?php echo htmlspecialchars($dress['size'] ?? 'M'); ?></span>
                                            <span class="text-muted" style="display: block; font-size: 0.78rem;"><?php echo htmlspecialchars($dress['color'] ?? 'Standard'); ?></span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta" style="font-size: 1.05rem;">
                                                <?php echo format_currency($dress['rental_price']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.88rem; color: var(--text-muted);">
                                                <?php echo format_currency($dress['security_deposit']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo get_status_badge($dress['availability']); ?>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?php echo $dress['total_rentals_count']; ?></span> <span class="text-muted" style="font-size: 0.78rem;">times</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; gap: 8px;">
                                                <a href="edit-dress.php?id=<?php echo $dress['dress_id']; ?>" class="btn btn-sm btn-subtle" title="Edit Dress">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </a>
                                                <a href="delete-dress.php?id=<?php echo $dress['dress_id']; ?>" class="btn btn-sm btn-subtle text-danger" onclick="return confirm('Are you sure you want to delete this dress listing?');" title="Delete Dress">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
