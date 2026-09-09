<?php
/**
 * Vendor Boutique Login
 */

$page_title = "Vendor Portal Login";
$path_prefix = '../';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if (isset($_SESSION['vendor_id'])) {
    header("Location: dashboard.php");
    exit();
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors['email'] = "Business email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM vendors WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $vendor = $stmt->fetch();

            if ($vendor) {
                if ($vendor['status'] === 'blocked') {
                    $errors['general'] = "Your vendor account has been suspended. Please contact support.";
                } elseif (password_verify($password, $vendor['password'])) {
                    // Vendor Login
                    $_SESSION['vendor_id'] = $vendor['vendor_id'];
                    $_SESSION['vendor_name'] = $vendor['full_name'];
                    $_SESSION['business_name'] = $vendor['business_name'] ?? $vendor['full_name'];
                    $_SESSION['vendor_email'] = $vendor['email'];

                    $redirect = $_GET['redirect'] ?? 'dashboard.php';
                    header("Location: " . $redirect);
                    exit();
                } else {
                    $errors['general'] = "Invalid email or password.";
                }
            } else {
                $errors['general'] = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $errors['general'] = "Database error occurred. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper-page">
    <div class="auth-brand-header">
        <a href="../index.php" class="auth-brand-logo">
            <div class="logo-symbol">
                <i class="fa-solid fa-shop"></i>
            </div>
            <span class="logo-main" style="font-size: 1.6rem;">CLOUD CLOSET</span>
        </a>
        <p class="text-muted" style="font-size: 0.88rem;">Vendor Boutique Portal</p>
    </div>

    <div class="auth-card-box">
        <!-- Role Switching Tabs -->
        <div class="auth-role-tabs">
            <a href="../login.php" class="auth-role-tab">Customer</a>
            <a href="login.php" class="auth-role-tab active">Vendor</a>
        </div>

        <h2 class="auth-card-title">Vendor Portal</h2>
        <p class="auth-card-subtitle">Manage your boutique inventory & rental bookings</p>

        <?php echo render_flash(); ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                <div class="alert-content"><?php echo htmlspecialchars($errors['general']); ?></div>
            </div>
        <?php endif; ?>

        <form action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" method="POST" novalidate>
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="email" class="form-label">Business Email Address <span class="text-danger">*</span></label>
                <div class="form-control-wrap">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           placeholder="vendor@boutique.com" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           required>
                </div>
                <?php if (isset($errors['email'])): ?>
                    <div class="field-error-text"><?php echo htmlspecialchars($errors['email']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <div class="form-control-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                           placeholder="Enter vendor password" 
                           required>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="field-error-text"><?php echo htmlspecialchars($errors['password']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-magenta btn-block btn-lg" style="margin-top: 24px;" id="vendor-login-btn">
                Enter Vendor Portal <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="auth-bottom-links">
            <p>Want to partner with us? <a href="register.php">Submit Vendor Application</a></p>
            <p style="margin-top: 12px; font-size: 0.85rem;"><a href="../index.php">&larr; Back to Main Website</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
