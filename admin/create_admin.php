<?php
/**
 * Temporary Super Admin Account Creator
 * Cloud Closet Rental System
 *
 * This file allows setting up the FIRST Super Admin account for testing.
 * 
 * IMPORTANT SECURITY NOTE:
 * This is a temporary setup file. Once the admin account is successfully created,
 * this file MUST be deleted from the server to prevent unauthorized administration privileges.
 */

// Define page title and path prefix
$path_prefix = '../';
$page_title = "Super Admin Setup";

// Include database connection configuration
require_once '../config/database.php';

// Initialize variables
$full_name = '';
$email = '';
$errors = [];
$success_message = '';

// Handle POST request when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim inputs
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 1. Validation checks
    
    // Validate Full Name
    if (empty($full_name)) {
        $errors['full_name'] = "Full Name is required.";
    } elseif (strlen($full_name) < 3) {
        $errors['full_name'] = "Full Name must be at least 3 characters long.";
    }

    // Validate Email
    if (empty($email)) {
        $errors['email'] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    } else {
        // Check if email already exists in admins table
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $errors['email'] = "This email address is already registered.";
            }
        } catch (PDOException $e) {
            $errors['general'] = "Database query error: " . $e->getMessage();
        }
    }

    // Validate Password
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters long.";
    }

    // Validate Confirm Password
    if (empty($confirm_password)) {
        $errors['confirm_password'] = "Confirm Password is required.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // 2. Perform database insertion if no validation errors exist
    if (empty($errors)) {
        try {
            // Hash the password securely using bcrypt
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Default role is 'super_admin' and status is 'active'
            $role = 'super_admin';
            $status = 'active';

            // Insert into the admins database table
            $stmt = $pdo->prepare("INSERT INTO admins (full_name, email, password, role, status) VALUES (:full_name, :email, :password, :role, :status)");
            $stmt->execute([
                ':full_name' => $full_name,
                ':email' => $email,
                ':password' => $hashed_password,
                ':role' => $role,
                ':status' => $status
            ]);

            // Success feedback
            $success_message = "Super Admin account created successfully!";
            
            // Clear inputs on success
            $full_name = '';
            $email = '';
        } catch (PDOException $e) {
            $errors['general'] = "Failed to create account: " . $e->getMessage();
        }
    }
}

// Include standard header template
require_once '../includes/header.php';
?>

<!-- Custom Premium CSS Overrides (similar theme as login.php) -->
<style>
    .admin-navbar {
        background-color: #0f172a;
        color: #ffffff;
        padding: 1.25rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .admin-navbar .logo-text {
        font-weight: 700;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-family: 'Inter', sans-serif;
    }
    .admin-badge {
        background-color: #ef4444; /* Red badge for Super Admin setup */
        color: #ffffff;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .admin-auth-card {
        background: rgba(30, 41, 59, 0.75);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 3rem 2.5rem;
        color: #f8fafc;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        border-top: 5px solid #ef4444 !important; /* Premium Red Border */
    }
    .admin-input {
        width: 100%;
        padding: 0.85rem 1.1rem;
        background-color: #0f172a;
        border: 1px solid #334155;
        border-radius: 8px;
        color: #f8fafc;
        outline: none;
        font-size: 0.95rem;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
    }
    .admin-input:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
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
    }
    .admin-btn {
        width: 100%;
        padding: 0.95rem;
        background-color: #ef4444;
        border: none;
        border-radius: 8px;
        color: #ffffff;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.25s ease, transform 0.15s ease;
    }
    .admin-btn:hover {
        background-color: #dc2626;
    }
    .admin-btn:active {
        transform: scale(0.985);
    }
    .security-warning-box {
        background-color: rgba(239, 68, 68, 0.1);
        border: 1px dashed rgba(239, 68, 68, 0.5);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 2rem;
        color: #f87171;
        font-size: 0.875rem;
        line-height: 1.5;
    }
</style>

<!-- Header Navigation -->
<header class="admin-navbar">
    <div class="logo-text">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #ef4444;">
            <path d="M12 2a3 3 0 0 0-3 3v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3V5a3 3 0 0 0-3-3z"></path>
        </svg>
        Cloud Closet <span class="admin-badge">Setup Portal</span>
    </div>
    <div>
        <a href="login.php" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;" onmouseover="this.style.color='#f8fafc'" onmouseout="this.style.color='#94a3b8'">Go to Login</a>
    </div>
</header>

<!-- Main Page Body -->
<main class="auth-page" style="min-height: calc(100vh - 170px); display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at top left, #1e1b4b, #0f172a 75%); padding: 2rem 1rem;">
    <div class="auth-container" style="max-width: 500px; width: 100%;">
        <div class="admin-auth-card">
            
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Playfair Display', Georgia, serif; font-size: 2.25rem; font-weight: 700; margin-bottom: 0.5rem; color: #ffffff;">Create Super Admin</h2>
                <p style="color: #94a3b8; font-size: 0.925rem;">Deploy the first high-level administrative account.</p>
            </div>

            <!-- Urgent Security Warning Box -->
            <div class="security-warning-box">
                <strong><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline; vertical-align:middle; margin-right:4px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg> SECURITY WARNING:</strong> 
                This setup script is temporary. After successfully creating your Super Admin account, you must <strong>DELETE</strong> the file <code>admin/create_admin.php</code> immediately to secure the application.
            </div>

            <!-- Success Alert Banner -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" style="background-color: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 1rem; border-radius: 8px; margin-bottom: 1.75rem; font-size: 0.925rem;">
                    <div style="display: flex; gap: 0.75rem; align-items: flex-start; margin-bottom: 0.75rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <div>
                            <strong><?php echo htmlspecialchars($success_message); ?></strong>
                            <p style="color: #a7f3d0; margin-top: 0.25rem; font-size: 0.85rem;">Your account is active. You can now use these credentials to log in.</p>
                        </div>
                    </div>
                    <a href="login.php" class="admin-btn" style="display: block; text-align: center; text-decoration: none; background-color: #10b981; margin-top: 0.5rem;">Proceed to Admin Login</a>
                </div>
            <?php endif; ?>

            <!-- General Error Banner -->
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error" style="background-color: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 0.85rem 1.1rem; border-radius: 8px; display: flex; gap: 0.75rem; margin-bottom: 1.75rem; font-size: 0.9rem; line-height: 1.4;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top:0.1rem;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <div><?php echo htmlspecialchars($errors['general']); ?></div>
                </div>
            <?php endif; ?>

            <!-- Creator Form -->
            <form action="create_admin.php" method="POST" novalidate>
                
                <!-- Full Name -->
                <div style="margin-bottom: 1.25rem;">
                    <label for="full_name" class="admin-label">Full Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           class="admin-input <?php echo isset($errors['full_name']) ? 'is-invalid-border' : ''; ?>" 
                           value="<?php echo htmlspecialchars($full_name); ?>" 
                           placeholder="John Doe" 
                           required>
                    <?php if (isset($errors['full_name'])): ?>
                        <div style="color: #f87171; font-size: 0.825rem; margin-top: 0.4rem; font-weight: 500;"><?php echo htmlspecialchars($errors['full_name']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email Address -->
                <div style="margin-bottom: 1.25rem;">
                    <label for="email" class="admin-label">Email Address <span style="color: #ef4444;">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="admin-input <?php echo isset($errors['email']) ? 'is-invalid-border' : ''; ?>" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="superadmin@cloudcloset.com" 
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <div style="color: #f87171; font-size: 0.825rem; margin-top: 0.4rem; font-weight: 500;"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div style="margin-bottom: 1.25rem;">
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

                <!-- Confirm Password -->
                <div style="margin-bottom: 2.25rem;">
                    <label for="confirm_password" class="admin-label">Confirm Password <span style="color: #ef4444;">*</span></label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           class="admin-input <?php echo isset($errors['confirm_password']) ? 'is-invalid-border' : ''; ?>" 
                           placeholder="••••••••" 
                           required>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div style="color: #f87171; font-size: 0.825rem; margin-top: 0.4rem; font-weight: 500;"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Action Button -->
                <button type="submit" class="admin-btn">
                    Create Super Admin Account
                </button>
            </form>
        </div>
    </div>
</main>

<?php
// Include standard footer template
require_once '../includes/footer.php';
?>
