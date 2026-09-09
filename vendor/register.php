<?php
/**
 * Vendor Boutique Registration
 */

$page_title = "Vendor Partner Registration";
$path_prefix = '../';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if (isset($_SESSION['vendor_id'])) {
    header("Location: dashboard.php");
    exit();
}

$full_name = '';
$business_name = '';
$email = '';
$phone = '';
$address = '';
$errors = [];
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $business_name = trim($_POST['business_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($business_name)) {
        $errors['business_name'] = "Boutique / Store name is required.";
    }

    if (empty($full_name)) {
        $errors['full_name'] = "Owner / Contact person name is required.";
    }

    if (empty($email)) {
        $errors['email'] = "Business email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendors WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = "This email is already registered as a vendor.";
        }
    }

    if (empty($phone)) {
        $errors['phone'] = "Phone number is required.";
    }

    if (empty($address)) {
        $errors['address'] = "Store location address is required.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO vendors (full_name, business_name, email, phone, address, password, status)
                VALUES (:full_name, :business_name, :email, :phone, :address, :password, 'approved')
            ");

            $result = $stmt->execute([
                ':full_name' => $full_name,
                ':business_name' => $business_name,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address,
                ':password' => $hashedPassword
            ]);

            if ($result) {
                $vendorId = $pdo->lastInsertId();

                // Auto login vendor
                $_SESSION['vendor_id'] = $vendorId;
                $_SESSION['vendor_name'] = $full_name;
                $_SESSION['business_name'] = $business_name;
                $_SESSION['vendor_email'] = $email;

                // Create welcome notification
                create_notification($pdo, 'vendor', $vendorId, 'Welcome to Cloud Closet!', 'Your vendor boutique account is active. Start listing your dresses now.', 'success', 'vendor/dashboard.php');

                set_flash('success', "Welcome to Cloud Closet, {$business_name}! Your boutique account is ready.");
                header("Location: dashboard.php");
                exit();
            }
        } catch (PDOException $e) {
            $errors['general'] = "Registration failed: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper-page" style="padding-top: 40px; padding-bottom: 60px;">
    <div class="auth-brand-header">
        <a href="../index.php" class="auth-brand-logo">
            <div class="logo-symbol">
                <i class="fa-solid fa-shop"></i>
            </div>
            <span class="logo-main" style="font-size: 1.6rem;">CLOUD CLOSET</span>
        </a>
        <p class="text-muted" style="font-size: 0.88rem;">Vendor Boutique Registration</p>
    </div>

    <div class="auth-card-box" style="max-width: 620px;">
        <h2 class="auth-card-title">Register Your Boutique</h2>
        <p class="auth-card-subtitle">Turn your premium wardrobe inventory into continuous rental income</p>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                    <div class="alert-content"><?php echo htmlspecialchars($errors['general']); ?></div>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" novalidate>
                <?php echo csrf_field(); ?>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="business_name" class="form-label">Store / Boutique Name <span class="text-danger">*</span></label>
                        <div class="form-control-wrap">
                            <i class="fa-solid fa-store"></i>
                            <input type="text" 
                                   id="business_name" 
                                   name="business_name" 
                                   class="form-input <?php echo isset($errors['business_name']) ? 'is-invalid' : ''; ?>" 
                                   placeholder="e.g. Royal Atelier" 
                                   value="<?php echo htmlspecialchars($business_name); ?>" 
                                   required>
                        </div>
                        <?php if (isset($errors['business_name'])): ?>
                            <div class="field-error-text"><?php echo htmlspecialchars($errors['business_name']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="full_name" class="form-label">Contact Person Name <span class="text-danger">*</span></label>
                        <div class="form-control-wrap">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" 
                                   id="full_name" 
                                   name="full_name" 
                                   class="form-input <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                                   placeholder="e.g. Sarah Jenkins" 
                                   value="<?php echo htmlspecialchars($full_name); ?>" 
                                   required>
                        </div>
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="field-error-text"><?php echo htmlspecialchars($errors['full_name']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="email" class="form-label">Business Email <span class="text-danger">*</span></label>
                        <div class="form-control-wrap">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                   placeholder="info@boutique.com" 
                                   value="<?php echo htmlspecialchars($email); ?>" 
                                   required>
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <div class="field-error-text"><?php echo htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Contact Phone <span class="text-danger">*</span></label>
                        <div class="form-control-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-input <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                   placeholder="e.g. 98XXXXXXXX" 
                                   value="<?php echo htmlspecialchars($phone); ?>" 
                                   required>
                        </div>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="field-error-text"><?php echo htmlspecialchars($errors['phone']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Boutique / Store Address <span class="text-danger">*</span></label>
                    <div class="form-control-wrap">
                        <i class="fa-solid fa-location-dot"></i>
                        <input type="text" 
                               id="address" 
                               name="address" 
                               class="form-input <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                               placeholder="Boutique Location, City, Street" 
                               value="<?php echo htmlspecialchars($address); ?>" 
                               required>
                    </div>
                    <?php if (isset($errors['address'])): ?>
                        <div class="field-error-text"><?php echo htmlspecialchars($errors['address']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="form-control-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-input <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                   placeholder="Min. 6 characters" 
                                   required>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="field-error-text"><?php echo htmlspecialchars($errors['password']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="form-control-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-input <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                   placeholder="Re-enter password" 
                                   required>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="field-error-text"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-magenta btn-block btn-lg" style="margin-top: 20px;" id="vendor-register-submit-btn">
                    Create Boutique Account <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <div class="auth-bottom-links">
                <p>Already registered as a vendor? <a href="login.php">Vendor Login here</a></p>
                <p style="margin-top: 8px;"><a href="../register.php">Register as a Customer instead</a></p>
            </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
