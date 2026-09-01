<?php
/**
 * User / Customer Login
 */

$page_title = "Customer Login";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// If user is already logged in, redirect to user dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: user/dashboard.php");
    exit();
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors['email'] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                if ($user['status'] === 'blocked') {
                    $errors['general'] = "Your account has been suspended. Please contact customer support.";
                } elseif (password_verify($password, $user['password'])) {
                    // Success
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];

                    $redirect = $_GET['redirect'] ?? 'user/dashboard.php';
                    header("Location: " . $redirect);
                    exit();
                } else {
                    $errors['general'] = "Invalid email or password.";
                }
            } else {
                $errors['general'] = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $errors['general'] = "A database error occurred. Please try again.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper-page">
    <div class="auth-brand-header">
        <a href="index.php" class="auth-brand-logo">
            <div class="logo-symbol">
                <i class="fa-solid fa-vest-patches"></i>
            </div>
            <span class="logo-main" style="font-size: 1.6rem;">CLOUD CLOSET</span>
        </a>
        <p class="text-muted" style="font-size: 0.88rem;">Sustainable Luxury Dress Rental</p>
    </div>

    <div class="auth-card-box">
        <!-- Role Switching Tabs -->
        <div class="auth-role-tabs">
            <a href="login.php" class="auth-role-tab active">Customer</a>
            <a href="vendor/login.php" class="auth-role-tab">Vendor</a>
            <a href="admin/login.php" class="auth-role-tab">Admin</a>
        </div>

        <h2 class="auth-card-title">Welcome Back</h2>
        <p class="auth-card-subtitle">Log in to explore curated gowns & manage your rentals</p>

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
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <div class="form-control-wrap">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           placeholder="name@example.com" 
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
                           placeholder="Enter your password" 
                           required>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="field-error-text"><?php echo htmlspecialchars($errors['password']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-magenta btn-block btn-lg" style="margin-top: 24px;" id="login-submit-btn">
                Sign In to Dashboard <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="auth-bottom-links">
            <p>Don't have an account? <a href="register-choice.php">Create Account</a></p>
            <p style="margin-top: 12px; font-size: 0.85rem;"><a href="index.php">&larr; Back to Main Website</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
