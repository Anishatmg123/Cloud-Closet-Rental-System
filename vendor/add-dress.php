<?php
/**
 * Vendor Add Dress Listing
 */

$page_title = "Add New Dress";
$path_prefix = '../';
require_once __DIR__ . '/../includes/vendor_auth.php';
require_once __DIR__ . '/../includes/header.php';

$vendorId = (int)$_SESSION['vendor_id'];

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

$errors = [];
$dressName = '';
$categoryId = 0;
$size = 'M';
$color = '';
$rentalPrice = '';
$securityDeposit = '20.00';
$description = '';
$availability = 'available';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dressName = trim($_POST['dress_name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $size = trim($_POST['size'] ?? 'M');
    $color = trim($_POST['color'] ?? '');
    $rentalPrice = (float)($_POST['rental_price'] ?? 0);
    $securityDeposit = (float)($_POST['security_deposit'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $availability = trim($_POST['availability'] ?? 'available');

    // Validation
    if (empty($dressName)) {
        $errors['dress_name'] = "Dress name is required.";
    }
    if (!$categoryId) {
        $errors['category_id'] = "Please select a category.";
    }
    if ($rentalPrice <= 0) {
        $errors['rental_price'] = "Please enter a valid rental price.";
    }

    // Image Upload Handling
    $imagePath = 'assets/images/hero_closet_banner.jpg'; // default fallback
    if (isset($_FILES['dress_image']) && $_FILES['dress_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['dress_image']['tmp_name'];
        $fileName = $_FILES['dress_image']['name'];
        $fileSize = $_FILES['dress_image']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileExt, $allowedExts)) {
            $errors['image'] = "Only JPG, PNG, and WebP images are permitted.";
        } elseif ($fileSize > 5 * 1024 * 1024) {
            $errors['image'] = "Image size must not exceed 5MB.";
        } else {
            // Verify image contents
            $check = @getimagesize($fileTmp);
            if ($check === false) {
                $errors['image'] = "Uploaded file is not a valid image.";
            } else {
                $uploadDir = __DIR__ . '/../assets/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newFileName = 'dress_' . $vendorId . '_' . uniqid() . '.' . $fileExt;
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmp, $destPath)) {
                    $imagePath = 'assets/images/' . $newFileName;
                } else {
                    $errors['image'] = "Failed to save uploaded image.";
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO dresses (vendor_id, category_id, dress_name, description, size, color, rental_price, security_deposit, image, availability)
                VALUES (:vendor_id, :category_id, :dress_name, :description, :size, :color, :rental_price, :security_deposit, :image, :availability)
            ");
            $stmt->execute([
                ':vendor_id' => $vendorId,
                ':category_id' => $categoryId,
                ':dress_name' => $dressName,
                ':description' => $description,
                ':size' => $size,
                ':color' => $color,
                ':rental_price' => $rentalPrice,
                ':security_deposit' => $securityDeposit,
                ':image' => $imagePath,
                ':availability' => $availability
            ]);

            set_flash('success', "Dress '{$dressName}' has been added to your wardrobe catalog!");
            header("Location: dresses.php");
            exit();
        } catch (PDOException $e) {
            $errors['general'] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <a href="dresses.php" class="text-magenta" style="font-size: 0.88rem; font-weight: 600;">&larr; Back to Dresses</a>
                    <h1 class="page-title-text" style="margin-top: 4px;">Add New Dress Listing</h1>
                </div>
            </div>

            <div class="dashboard-card" style="max-width: 860px;">
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                        <div class="alert-content"><?php echo htmlspecialchars($errors['general']); ?></div>
                    </div>
                <?php endif; ?>

                <form action="add-dress.php" method="POST" enctype="multipart/form-data" novalidate>
                    <?php echo csrf_field(); ?>

                    <div class="form-group">
                        <label class="form-label">Dress Name / Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="dress_name" 
                               class="form-input no-icon <?php echo isset($errors['dress_name']) ? 'is-invalid' : ''; ?>" 
                               placeholder="e.g. Midnight Velvet Ball Gown" 
                               value="<?php echo htmlspecialchars($dressName); ?>" 
                               required>
                        <?php if (isset($errors['dress_name'])): ?>
                            <div class="field-error-text"><?php echo htmlspecialchars($errors['dress_name']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-input no-icon <?php echo isset($errors['category_id']) ? 'is-invalid' : ''; ?>" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" <?php echo ($categoryId === (int)$cat['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                                <div class="field-error-text"><?php echo htmlspecialchars($errors['category_id']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Size <span class="text-danger">*</span></label>
                            <select name="size" class="form-input no-icon">
                                <option value="XS" <?php echo ($size === 'XS') ? 'selected' : ''; ?>>XS - Extra Small</option>
                                <option value="S" <?php echo ($size === 'S') ? 'selected' : ''; ?>>S - Small</option>
                                <option value="M" <?php echo ($size === 'M') ? 'selected' : ''; ?>>M - Medium</option>
                                <option value="L" <?php echo ($size === 'L') ? 'selected' : ''; ?>>L - Large</option>
                                <option value="XL" <?php echo ($size === 'XL') ? 'selected' : ''; ?>>XL - Extra Large</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label class="form-label">Primary Color</label>
                            <input type="text" 
                                   name="color" 
                                   class="form-input no-icon" 
                                   placeholder="e.g. Emerald Green, Blush Pink, Black" 
                                   value="<?php echo htmlspecialchars($color); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Initial Availability</label>
                            <select name="availability" class="form-input no-icon">
                                <option value="available" <?php echo ($availability === 'available') ? 'selected' : ''; ?>>Available for Rent</option>
                                <option value="unavailable" <?php echo ($availability === 'unavailable') ? 'selected' : ''; ?>>Unavailable / Under Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label class="form-label">Rental Price ($ per 4-day period) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   step="0.01" 
                                   name="rental_price" 
                                   class="form-input no-icon <?php echo isset($errors['rental_price']) ? 'is-invalid' : ''; ?>" 
                                   placeholder="e.g. 45.00" 
                                   value="<?php echo htmlspecialchars($rentalPrice); ?>" 
                                   required>
                            <?php if (isset($errors['rental_price'])): ?>
                                <div class="field-error-text"><?php echo htmlspecialchars($errors['rental_price']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Refundable Security Deposit ($)</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="security_deposit" 
                                   class="form-input no-icon" 
                                   placeholder="e.g. 20.00" 
                                   value="<?php echo htmlspecialchars($securityDeposit); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">High-Resolution Gown Image</label>
                        <input type="file" 
                               name="dress_image" 
                               class="form-input no-icon <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" 
                               accept="image/jpeg,image/png,image/webp">
                        <span class="text-muted" style="font-size: 0.78rem;">Upload portrait editorial image (JPG, PNG, WebP max 5MB).</span>
                        <?php if (isset($errors['image'])): ?>
                            <div class="field-error-text"><?php echo htmlspecialchars($errors['image']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Garment Description & Styling Notes</label>
                        <textarea name="description" 
                                  rows="4" 
                                  class="form-input no-icon" 
                                  placeholder="Describe the fabric, neckline, silhouette, recommended occasion, and fit tips..."><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div style="display: flex; gap: 16px; margin-top: 24px;">
                        <button type="submit" class="btn btn-magenta btn-lg">
                            <i class="fa-solid fa-plus"></i> Save & Publish Dress
                        </button>
                        <a href="dresses.php" class="btn btn-subtle btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
