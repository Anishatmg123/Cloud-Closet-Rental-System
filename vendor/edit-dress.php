<?php
/**
 * Vendor Edit Dress Listing
 */

$page_title = "Edit Dress";
$path_prefix = '../';
require_once __DIR__ . '/../includes/vendor_auth.php';
require_once __DIR__ . '/../includes/header.php';

$vendorId = (int)$_SESSION['vendor_id'];
$dressId = (int)($_GET['id'] ?? 0);

if (!$dressId) {
    header("Location: dresses.php");
    exit();
}

// Fetch dress ensuring it belongs to current vendor
$stmt = $pdo->prepare("SELECT * FROM dresses WHERE dress_id = :dress_id AND vendor_id = :vendor_id LIMIT 1");
$stmt->execute([':dress_id' => $dressId, ':vendor_id' => $vendorId]);
$dress = $stmt->fetch();

if (!$dress) {
    set_flash('error', 'Dress not found or access denied.');
    header("Location: dresses.php");
    exit();
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

$errors = [];
$dressName = $dress['dress_name'];
$categoryId = (int)$dress['category_id'];
$size = $dress['size'];
$color = $dress['color'];
$rentalPrice = $dress['rental_price'];
$securityDeposit = $dress['security_deposit'];
$description = $dress['description'];
$availability = $dress['availability'];
$currentImage = $dress['image'];

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

    // Optional New Image Upload Handling
    $imagePath = $currentImage;
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
                    $errors['image'] = "Failed to save new image.";
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE dresses 
                SET category_id = :category_id, dress_name = :dress_name, description = :description, 
                    size = :size, color = :color, rental_price = :rental_price, 
                    security_deposit = :security_deposit, image = :image, availability = :availability 
                WHERE dress_id = :dress_id AND vendor_id = :vendor_id
            ");
            $stmt->execute([
                ':category_id' => $categoryId,
                ':dress_name' => $dressName,
                ':description' => $description,
                ':size' => $size,
                ':color' => $color,
                ':rental_price' => $rentalPrice,
                ':security_deposit' => $securityDeposit,
                ':image' => $imagePath,
                ':availability' => $availability,
                ':dress_id' => $dressId,
                ':vendor_id' => $vendorId
            ]);

            set_flash('success', "Dress details updated successfully.");
            header("Location: dresses.php");
            exit();
        } catch (PDOException $e) {
            $errors['general'] = "Database error: " . $e->getMessage();
        }
    }
}

$thumb = get_dress_image_url($currentImage, $dress['dress_name'], '../');
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
                    <h1 class="page-title-text" style="margin-top: 4px;">Edit Dress Listing</h1>
                </div>
            </div>

            <div class="dashboard-card" style="max-width: 860px;">
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                        <div class="alert-content"><?php echo htmlspecialchars($errors['general']); ?></div>
                    </div>
                <?php endif; ?>

                <form action="edit-dress.php?id=<?php echo $dressId; ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <?php echo csrf_field(); ?>

                    <div class="form-group">
                        <label class="form-label">Dress Name / Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="dress_name" 
                               class="form-input no-icon <?php echo isset($errors['dress_name']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($dressName); ?>" 
                               required>
                        <?php if (isset($errors['dress_name'])): ?>
                            <div class="field-error-text"><?php echo htmlspecialchars($errors['dress_name']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-input no-icon" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" <?php echo ($categoryId === (int)$cat['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
                            <input type="text" name="color" class="form-input no-icon" value="<?php echo htmlspecialchars($color); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Availability Status</label>
                            <select name="availability" class="form-input no-icon">
                                <option value="available" <?php echo ($availability === 'available') ? 'selected' : ''; ?>>Available for Rent</option>
                                <option value="rented" <?php echo ($availability === 'rented') ? 'selected' : ''; ?>>Currently Rented Out</option>
                                <option value="unavailable" <?php echo ($availability === 'unavailable') ? 'selected' : ''; ?>>Unavailable / Cleaning</option>
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
                                   value="<?php echo htmlspecialchars($rentalPrice); ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Security Deposit ($)</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="security_deposit" 
                                   class="form-input no-icon" 
                                   value="<?php echo htmlspecialchars($securityDeposit); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gown Image</label>
                        <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 12px;">
                            <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Current Image" style="width: 80px; height: 100px; border-radius: 8px; object-fit: cover;">
                            <div>
                                <span class="text-muted" style="font-size: 0.85rem; display: block; margin-bottom: 6px;">Upload a new image to replace current:</span>
                                <input type="file" name="dress_image" class="form-input no-icon" accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                        <?php if (isset($errors['image'])): ?>
                            <div class="field-error-text"><?php echo htmlspecialchars($errors['image']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Garment Description</label>
                        <textarea name="description" rows="4" class="form-input no-icon"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div style="display: flex; gap: 16px; margin-top: 24px;">
                        <button type="submit" class="btn btn-magenta btn-lg">
                            <i class="fa-solid fa-check"></i> Update Dress Listing
                        </button>
                        <a href="dresses.php" class="btn btn-subtle btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
