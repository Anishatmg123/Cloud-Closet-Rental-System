<?php
/**
 * Customer Registration
 */

$page_title = "Customer Registration";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    header("Location: user/dashboard.php");
    exit();
}

$full_name = '';
$email = '';
$phone = '';
$address = '';
$citizenship_no = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $citizenship_no = trim($_POST['citizenship_no'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($full_name)) {
        $errors['full_name'] = "Full name is required.";
    }

    if (empty($email)) {
        $errors['email'] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = "This email is already registered.";
        }
    }

    if (empty($phone)) {
        $errors['phone'] = "Phone number is required.";
    }

    if (empty($address)) {
        $errors['address'] = "Delivery address is required.";
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
                INSERT INTO users (full_name, email, phone, password, address, citizenship_no, status)
                VALUES (:full_name, :email, :phone, :password, :address, :citizenship_no, 'active')
            ");

            $result = $stmt->execute([
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $hashedPassword,
                ':address' => $address,
                ':citizenship_no' => !empty($citizenship_no) ? $citizenship_no : null
            ]);

            if ($result) {
                $newUserId = $pdo->lastInsertId();

                // Create welcome notification
                create_notification($pdo, 'user', $newUserId, 'Welcome to Cloud Closet!', 'Your customer account has been created. Start exploring designer gowns now.', 'success', 'user/dresses.php');

                // Auto-login
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;

                set_flash('success', 'Welcome to Cloud Closet! Your account has been created.');
                header("Location: user/dashboard.php");
                exit();
            }
        } catch (PDOException $e) {
            $errors['general'] = "Registration failed: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper-page" style="padding-top: 40px; padding-bottom: 60px;">
    <div class="auth-brand-header">
        <a href="index.php" class="auth-brand-logo">
            <div class="logo-symbol">
                <i class="fa-solid fa-vest-patches"></i>
            </div>
            <span class="logo-main" style="font-size: 1.6rem;">CLOUD CLOSET</span>
        </a>
        <p class="text-muted" style="font-size: 0.88rem;">Customer Account Creation</p>
    </div>

    <div class="auth-card-box" style="max-width: 600px;">
        <h2 class="auth-card-title">Join Cloud Closet</h2>
        <p class="auth-card-subtitle">Rent luxury designer couture for your next special event</p>

        <?php echo render_flash(); ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                <div class="alert-content"><?php echo htmlspecialchars($errors['general']); ?></div>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" novalidate>
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <div class="form-control-wrap">
                    <i class="fa-regular fa-user"></i>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           class="form-input <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                           placeholder="e.g. Jane Doe" 
                           value="<?php echo htmlspecialchars($full_name); ?>" 
                           required>
                </div>
                <?php if (isset($errors['full_name'])): ?>
                    <div class="field-error-text"><?php echo htmlspecialchars($errors['full_name']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <div class="form-control-wrap">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           placeholder="jane@example.com" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           required>
                </div>
                <?php if (isset($errors['email'])): ?>
                    <div class="field-error-text"><?php echo htmlspecialchars($errors['email']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
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

                <div class="form-group">
                    <label for="citizenship_no" class="form-label">ID / Citizenship No. <span class="text-muted">(Optional)</span></label>
                    <div class="form-control-wrap">
                        <i class="fa-solid fa-id-card"></i>
                        <input type="text" 
                               id="citizenship_no" 
                               name="citizenship_no" 
                               class="form-input" 
                               placeholder="e.g. 27-01-79-1234" 
                               value="<?php echo htmlspecialchars($citizenship_no); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Delivery Address <span class="text-danger">*</span></label>
                <div class="form-control-wrap">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" 
                           id="address" 
                           name="address" 
                           class="form-input <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                           placeholder="Street, City, Ward / Apartment No." 
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

            <button type="submit" class="btn btn-magenta btn-block btn-lg" style="margin-top: 20px;" id="register-submit-btn">
                Complete Registration <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="auth-bottom-links">
            <p>Already have an account? <a href="login.php">Log in here</a></p>
            <p style="margin-top: 8px;"><a href="vendor/register.php">Register as a Vendor Boutique instead</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
