<?php
/**
 * Admin Category Management
 */

$page_title = "Manage Categories";
$path_prefix = '../';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/header.php';

$errors = [];

// Handle Add / Edit / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $name = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $errors['category_name'] = "Category name is required.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (category_name, description) VALUES (:name, :desc)");
                $stmt->execute([':name' => $name, ':desc' => $description]);
                set_flash('success', "Category '{$name}' created successfully.");
                header("Location: categories.php");
                exit();
            } catch (Exception $e) {
                $errors['general'] = "Category name must be unique.";
            }
        }
    } elseif ($action === 'edit_category') {
        $catId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($catId && !empty($name)) {
            try {
                $stmt = $pdo->prepare("UPDATE categories SET category_name = :name, description = :desc WHERE category_id = :id");
                $stmt->execute([':name' => $name, ':desc' => $description, ':id' => $catId]);
                set_flash('success', "Category updated successfully.");
                header("Location: categories.php");
                exit();
            } catch (Exception $e) {
                $errors['general'] = "Error updating category.";
            }
        }
    } elseif ($action === 'delete_category') {
        $catId = (int)($_POST['category_id'] ?? 0);
        if ($catId) {
            // Check if active dresses are using this category
            $count = (int)$pdo->query("SELECT COUNT(*) FROM dresses WHERE category_id = $catId")->fetchColumn();
            if ($count > 0) {
                set_flash('error', "Cannot delete category: {$count} dresses are actively assigned to it. Please reassign those dresses first.");
            } else {
                $pdo->prepare("DELETE FROM categories WHERE category_id = :id")->execute([':id' => $catId]);
                set_flash('success', "Category deleted successfully.");
            }
            header("Location: categories.php");
            exit();
        }
    }
}

// Fetch categories with dress counts
$categories = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM dresses d WHERE d.category_id = c.category_id) as dress_count
    FROM categories c
    ORDER BY c.category_name ASC
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
                    <h1 class="page-title-text">Couture Categories</h1>
                    <p class="page-subtitle-text">Manage clothing taxonomy, silhouettes, and wardrobe collections.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;" class="dashboard-columns-2-1">
                <!-- Add Category Form -->
                <div>
                    <div class="dashboard-card">
                        <h3 class="card-title-text" style="margin-bottom: 20px;">Add New Category</h3>

                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-error">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <div><?php echo htmlspecialchars($errors['general']); ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="categories.php" method="POST" novalidate>
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="add_category">

                            <div class="form-group">
                                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="category_name" 
                                       class="form-input no-icon <?php echo isset($errors['category_name']) ? 'is-invalid' : ''; ?>" 
                                       placeholder="e.g. Resort & Vacation" 
                                       required>
                                <?php if (isset($errors['category_name'])): ?>
                                    <div class="field-error-text"><?php echo htmlspecialchars($errors['category_name']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="3" class="form-input no-icon" placeholder="Describe the style, event type, and collection purpose..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-magenta btn-block">
                                <i class="fa-solid fa-plus"></i> Save Category
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Categories List -->
                <div>
                    <div class="dashboard-card">
                        <h3 class="card-title-text" style="margin-bottom: 20px;">Active Categories (<?php echo count($categories); ?>)</h3>

                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Description</th>
                                        <th>Catalog Pieces</th>
                                        <th style="text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold" style="color: var(--text-primary); font-size: 0.95rem;"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-muted" style="font-size: 0.82rem; line-height: 1.4; display: block; max-width: 280px;">
                                                    <?php echo htmlspecialchars($cat['description'] ?? 'No description provided.'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-magenta"><?php echo $cat['dress_count']; ?></span> dresses
                                            </td>
                                            <td style="text-align: right;">
                                                <form action="categories.php" method="POST" onsubmit="return confirm('Delete this category?');" style="margin: 0; display: inline-block;">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="action" value="delete_category">
                                                    <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-subtle text-danger" title="Delete Category">
                                                        <i class="fa-solid fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
