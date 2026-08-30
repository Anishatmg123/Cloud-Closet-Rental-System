<?php
/**
 * User Login Module
 * Cloud Closet Rental System
 *
 * This file handles user authentication. It validates credentials,
 * verifies hashed passwords, handles session assignment, and checks status.
 */

// Define page title for the header
$page_title = "Login";

// Start secure session
session_start();

// If user is already logged in, redirect directly to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: user/dashboard.php");
    exit();
}

// Include database connection
require_once 'config/database.php';

// Initialize variables
$email = '';
$errors = [];
$success_message = '';

// Check if registration success was passed via session
if (isset($_SESSION['registration_success'])) {
    $success_message = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']); // Clear message after display
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize and retrieve inputs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 2. Simple validations
    if (empty($email)) {
        $errors['email'] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    // 3. Process Authentication if no validation errors
    if (empty($errors)) {
        try {
            // Retrieve user details by email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                // Check if user status is blocked
                if (isset($user['status']) && $user['status'] === 'blocked') {
                    $errors['general'] = "Your account has been blocked. Please contact admin support.";
                } 
                // Verify password against secure hash stored in database
                elseif (password_verify($password, $user['password'])) {
                    // Password is correct, initialize session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];

                    // Redirect to user dashboard
                    header("Location: user/dashboard.php");
                    exit();
                } else {
                    // Password does not match
                    $errors['general'] = "Invalid email or password.";
                }
            } else {
                // Email address not found in the users table
                $errors['general'] = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $errors['general'] = "Database error occurred: " . $e->getMessage();
        }
    }
}

// Include page header and navbar
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="auth-page">
    <div class="auth-container" style="max-width: 480px;">
        <div class="auth-card">
            <h2 class="auth-title">Welcome Back</h2>
            <p class="auth-subtitle">Login to manage your rentals and explore new outfits.</p>

            <!-- Success Banner (from registration or logout) -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" id="success-banner">
                    <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- General Error Banner -->
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error" id="error-banner">
                    <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <div>
                        <strong>Login Failed!</strong> <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="login.php" method="POST" class="auth-form" id="login-form" novalidate>
                
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="Enter your email" 
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <label for="password">Password <span class="required">*</span></label>
                    </div>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                           placeholder="Enter your password" 
                           required>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-4" id="login-btn">Login</button>
            </form>

            <!-- Card Footer Links -->
            <div class="auth-footer">
                <p>New to Cloud Closet? <a href="register-choice.php" id="register-link">Create an account</a></p>
            </div>
        </div>
    </div>
</main>

<?php
// Include page footer
require_once 'includes/footer.php';
?>
