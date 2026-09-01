<?php
/**
 * Vendor Inventory Management
 */

$page_title = "Manage Inventory";
$path_prefix = '../';
require_once __DIR__ . '/../includes/vendor_auth.php';
require_once __DIR__ . '/../includes/header.php';

$vendorId = (int)$_SESSION['vendor_id'];

// Quick Toggle Availability Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_availability') {
    $dressId = (int)($_POST['dress_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? 'available');

    if ($dressId && in_array($newStatus, ['available', 'unavailable', 'rented'])) {
        $stmt = $pdo->prepare("UPDATE dresses SET availability = :status WHERE dress_id = :dress_id AND vendor_id = :vendor_id");
        $stmt->execute([':status' => $newStatus, ':dress_id' => $dressId, ':vendor_id' => $vendorId]);
        set_flash('success', "Dress inventory status updated to " . ucfirst($newStatus) . ".");
        header("Location: inventory.php");
        exit();
    }
}

// Fetch Inventory items
$stmt = $pdo->prepare("
    SELECT d.*, c.category_name,
           (SELECT COUNT(*) FROM rentals r WHERE r.dress_id = d.dress_id AND r.rental_status = 'active') as active_rentals,
           (SELECT COUNT(*) FROM rentals r WHERE r.dress_id = d.dress_id AND r.rental_status = 'pending') as pending_rentals
    FROM dresses d
    LEFT JOIN categories c ON d.category_id = c.category_id
    WHERE d.vendor_id = :vendor_id
    ORDER BY d.dress_name ASC
");
$stmt->execute([':vendor_id' => $vendorId]);
$inventory = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Inventory & Stock Control</h1>
                    <p class="page-subtitle-text">Quickly toggle wardrobe availability and monitor in-transit items.</p>
                </div>
                <div>
                    <a href="add-dress.php" class="btn btn-magenta">
                        <i class="fa-solid fa-plus"></i> Add Item
                    </a>
                </div>
            </div>

            <div class="dashboard-card">
                <?php if (!empty($inventory)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Item Details</th>
                                    <th>Category</th>
                                    <th>Size & Color</th>
                                    <th>Rental Price</th>
                                    <th>Active Renters</th>
                                    <th>Stock Status</th>
                                    <th style="text-align: right;">Quick Status Toggle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventory as $item): 
                                    $thumb = !empty($item['image']) && file_exists(__DIR__ . '/../' . $item['image']) ? '../' . $item['image'] : '../assets/images/hero_closet_banner.jpg';
                                ?>
                                    <tr>
                                        <td>
                                            <div class="table-user-cell">
                                                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Dress" class="table-item-thumb">
                                                <div>
                                                    <span class="font-weight-bold" style="color: var(--text-primary); display: block; font-size: 0.95rem;">
                                                        <?php echo htmlspecialchars($item['dress_name']); ?>
                                                    </span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">ID #DRS-<?php echo $item['dress_id']; ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.88rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($item['category_name'] ?? 'Dress'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">Size <?php echo htmlspecialchars($item['size'] ?? 'M'); ?></span>
                                            <span class="text-muted" style="display: block; font-size: 0.75rem;"><?php echo htmlspecialchars($item['color'] ?? 'Standard'); ?></span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta"><?php echo format_currency($item['rental_price']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($item['active_rentals'] > 0): ?>
                                                <span class="status-badge badge-warning"><?php echo $item['active_rentals']; ?> Out on Rent</span>
                                            <?php else: ?>
                                                <span class="text-muted" style="font-size: 0.85rem;">In Store</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo get_status_badge($item['availability']); ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <form action="inventory.php" method="POST" style="margin: 0; display: inline-flex; gap: 6px;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="toggle_availability">
                                                <input type="hidden" name="dress_id" value="<?php echo $item['dress_id']; ?>">

                                                <?php if ($item['availability'] === 'available'): ?>
                                                    <button type="submit" name="new_status" value="unavailable" class="btn btn-sm btn-subtle text-danger" title="Mark Unavailable">
                                                        <i class="fa-solid fa-ban"></i> Set Unavailable
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="new_status" value="available" class="btn btn-sm btn-subtle text-success" title="Mark Available">
                                                        <i class="fa-solid fa-check"></i> Set Available
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 40px 20px;">
                        <p class="text-muted">No inventory items to display.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
