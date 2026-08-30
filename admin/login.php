<?php
/**
 * Admin Login Module
 * Cloud Closet Rental System
 *
 * This file handles administrator authentication:
 * 1. Checks if the admin is already logged in (redirects if they are).
 * 2. Processes POST submissions for Email and Password.
 * 3. Sanitizes inputs, performs server-side validation.
 * 4. Queries the database using PDO prepared statements (securing against SQL injection).
 * 5. Verifies if the admin status is 'active'.
 * 6. Verifies the password hash using PHP's secure password_verify().
 * 7. Initiates a secure PHP session and stores: admin_id, full_name, email, role.
 * 8. Renders a premium, glassmorphic dark-themed admin login interface.
 */

// 1. Start standard PHP Session
session_start();

// 2. Redirect to dashboard if the admin is already authenticated
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Set paths and titles for standard templates
$path_prefix = '../'; // Tells include files to look one directory up for style.css, etc.
$page_title = "Admin Login";

// 3. Include the database connection configuration
require_once '../config/database.php';

// Initialize form inputs and error messages array
$email = '';
$errors = [];
$success_message = '';

// Check if redirected from a successful logout action
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_message = "You have been logged out successfully.";
}

// 4. Handle POST Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim inputs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Field-level Validation
    if (empty($email)) {
        $errors['email'] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    // Attempt login if no validation errors exist
    if (empty($errors)) {
        try {
            // Retrieve admin details matching the unique email
            // Using prepared statements prevents SQL injection attacks
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if ($admin) {
                // Verify account status (only 'active' admins can login)
                if (isset($admin['status']) && $admin['status'] !== 'active') {
                    $errors['general'] = "Your account is currently inactive. Please contact the system administrator.";
                }
                // Verify the plain password against the secure hashed password in the DB
                elseif (password_verify($password, $admin['password'])) {
                    // Password matches! Initialize the session variables
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['full_name'] = $admin['full_name'];
                    $_SESSION['email'] = $admin['email'];
                    $_SESSION['role'] = $admin['role'];

                    // Redirect to the admin dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    // Password mismatch
                    $errors['general'] = "Invalid email or password.";
                }
            } else {
                // Email address not found in the database
                $errors['general'] = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $errors['general'] = "Database error: " . $e->getMessage();
        }
    }
}

// Include the standard page header containing font links and general metadata
require_once '../includes/header.php';
?>

<!-- Custom Premium Admin Panel CSS Overrides (Glassmorphism & Dark Slate Palette) -->
<style>
    /* Admin navigation header style */
    .admin-navbar {
        background-color: #0f172a; /* Sleek slate 900 background */
        color: #ffffff;
        padding: 1.25rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .admin-navbar .logo-text {
        font-weight: 700;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-family: 'Inter', sans-serif;
        letter-spacing: -0.025em;
    }
    .admin-badge {
        background-color: #3b82f6; /* Modern Blue 500 */
        color: #ffffff;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .back-link {
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: color 0.25s ease;
    }
    .back-link:hover {
        color: #f8fafc;
    }
    
    /* Authentic administrative card layout */
    .admin-auth-card {
        background: rgba(30, 41, 59, 0.75); /* Translucent slate-800 */
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 3rem 2.5rem;
        color: #f8fafc;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        border-top: 5px solid #2563eb !important; /* Premium Blue Top border */
    }
    .admin-input {
        width: 100%;
        padding: 0.85rem 1.1rem;
        background-color: #0f172a; /* Slate 900 */
        border: 1px solid #334155; /* Slate 700 */
        border-radius: 8px;
        color: #f8fafc;
        outline: none;
        font-size: 0.95rem;
        font-family: 'Inter', sans-serif;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
    }
    .admin-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .admin-input.is-invalid-border {
        border-color: #ef4444 !important;
    }
    .admin-label {
        display: block;
        font-weight: 500;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        color: #cbd5e1;
        letter-spacing: -0.01em;
    }
    .admin-btn {
        width: 100%;
        padding: 0.95rem;
        background-color: #2563eb; /* Blue 600 */
        border: none;
        border-radius: 8px;
        color: #ffffff;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: background-color 0.25s ease, transform 0.15s ease, box-shadow 0.25s ease;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }
    .admin-btn:hover {
        background-color: #1d4ed8; /* Blue 700 */
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
    }
    .admin-btn:active {
        transform: scale(0.985);
    }
</style>

<!-- Minimalist Corporate Admin Navigation -->
<header class="admin-navbar">
    <a href="../index.php" class="logo-text" style="color: white; text-decoration: none;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #3b82f6;">
            <path d="M12 2a3 3 0 0 0-3 3v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3V5a3 3 0 0 0-3-3z"></path>
        </svg>
        Cloud Closet <span class="admin-badge">Admin Panel</span>
    </a>
    <div>
        <a href="../index.php" class="back-link">Back to Main Website</a>
    </div>
</header>

<!-- Main Page Background: Gradient and centering -->
<main class="auth-page" style="min-height: calc(100vh - 170px); display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at top left, #1e1b4b, #0f172a 75%); padding: 2rem 1rem;">
    <div class="auth-container" style="max-width: 460px; width: 100%;">
        <div class="admin-auth-card">
            
            <!-- Welcome Header -->
            <div style="text-align: center; margin-bottom: 2.25rem;">
                <h2 style="font-family: 'Playfair Display', Georgia, serif; font-size: 2.25rem; font-weight: 700; margin-bottom: 0.6rem; color: #ffffff; letter-spacing: -0.02em;">Admin Login</h2>
                <p style="color: #94a3b8; font-size: 0.925rem; line-height: 1.5;">Authenticate secure session to access system controls.</p>
            </div>

            <!-- Success Alert (e.g. following a clean logout) -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" style="background-color: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 0.85rem 1.1rem; border-radius: 8px; display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.75rem; font-size: 0.9rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div><?php echo htmlspecialchars($success_message); ?></div>
                </div>
            <?php endif; ?>

            <!-- General Validation & Authentication Errors -->
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error" style="background-color: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 0.85rem 1.1rem; border-radius: 8px; display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 1.75rem; font-size: 0.9rem; line-height: 1.4;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 0.1rem;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <div>
                        <strong>Login Failed:</strong> <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Interactive Login Form -->
            <form action="login.php" method="POST" id="admin-login-form" novalidate>
                
                <!-- Email Input Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="email" class="admin-label">Admin Email Address <span style="color: #ef4444;">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="admin-input <?php echo isset($errors['email']) ? 'is-invalid-border' : ''; ?>" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="name@cloudcloset.com" 
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <div style="color: #f87171; font-size: 0.825rem; margin-top: 0.4rem; font-weight: 500;"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password Input Field -->
                <div style="margin-bottom: 2.25rem;">
                    <label for="password" class="admin-label">Password <span style="color: #ef4444;">*</span></label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="admin-input <?php echo isset($errors['password']) ? 'is-invalid-border' : ''; ?>" 
                           placeholder="••••••••" 
                           required>
                    <?php if (isset($errors['password'])): ?>
                        <div style="color: #f87171; font-size: 0.825rem; margin-top: 0.4rem; font-weight: 500;"><?php echo htmlspecialchars($errors['password']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Submission Action Button -->
                <button type="submit" class="admin-btn" id="login-btn">
                    Authenticate & Enter
                </button>
            </form>
        </div>
    </div>
</main>

<?php
// Include the standard footer template to close page tags
require_once '../includes/footer.php';
?>
