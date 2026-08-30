<?php
/**
 * User Registration Module
 * Cloud Closet Rental System
 *
 * This file handles user registration. It processes the POST request,
 * validates the form inputs, hashes the password securely, and inserts
 * the user record into the MySQL database.
 */

// Define page title for the header
$page_title = "Register";

// Start standard session
session_start();

// Include the database connection
require_once 'config/database.php';

// Initialize variables to preserve form values on validation errors
$full_name = '';
$email = '';
$phone = '';
$address = '';
$citizenship_no = '';

// Array to store error messages for each field
$errors = [];
$success_message = '';

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize and retrieve form inputs
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $citizenship_no = trim($_POST['citizenship_no'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 2. Server-side validation

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
        // Check if email already exists in database
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $email_count = $stmt->fetchColumn();
            
            if ($email_count > 0) {
                $errors['email'] = "This email is already registered.";
            }
        } catch (PDOException $e) {
            $errors['general'] = "Database validation error: " . $e->getMessage();
        }
    }

    // Validate Phone Number
    if (empty($phone)) {
        $errors['phone'] = "Phone number is required.";
    }

    // Validate Address
    if (empty($address)) {
        $errors['address'] = "Delivery address is required.";
    }

    // Validate Citizenship Number (Optional, but must be unique if provided)
    if (!empty($citizenship_no)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE citizenship_no = :citizenship_no");
            $stmt->execute([':citizenship_no' => $citizenship_no]);
            $citizenship_count = $stmt->fetchColumn();
            
            if ($citizenship_count > 0) {
                $errors['citizenship_no'] = "This citizenship number is already registered.";
            }
        } catch (PDOException $e) {
            $errors['general'] = "Database validation error: " . $e->getMessage();
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
        $errors['confirm_password'] = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // 3. Insert into Database if there are no errors
    if (empty($errors)) {
        try {
            // Hash the password securely using BCrypt algorithm
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Prepared statement query (excluding user_id, profile_image and created_at as they are auto-handled)
            $sql = "INSERT INTO users (full_name, email, phone, password, address, citizenship_no, status) 
                    VALUES (:full_name, :email, :phone, :password, :address, :citizenship_no, :status)";
            
            $stmt = $pdo->prepare($sql);
            
            $result = $stmt->execute([
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $hashed_password,
                ':address' => $address,
                ':citizenship_no' => !empty($citizenship_no) ? $citizenship_no : null,
                ':status' => 'active'
            ]);

            if ($result) {
                $success_message = "Registration successful! Your account has been created.";
                // Clear form values on successful registration
                $full_name = $email = $phone = $address = $citizenship_no = '';
            } else {
                $errors['general'] = "Failed to register user. Please try again.";
            }

        } catch (PDOException $e) {
            $errors['general'] = "Database error: " . $e->getMessage();
        }
    }
}

// Include page header and navbar
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h2 class="auth-title">Create Account</h2>
            <p class="auth-subtitle">Join Cloud Closet today and rent luxury outfits sustainably.</p>
            
            <!-- General Success Message Banner -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" id="success-banner">
                    <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div>
                        <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
                        <br>
                        <a href="login.php" class="alert-link">Click here to Login</a>
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
                        <strong>Error!</strong> <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form action="register.php" method="POST" class="auth-form" id="register-form" novalidate>
                
                <!-- Full Name Field -->
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($full_name); ?>" 
                           placeholder="Enter your full name" 
                           required>
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['full_name']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email Address Field -->
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="Enter your email address" 
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Phone and Citizenship Number Row -->
                <div class="form-row">
                    <!-- Phone Number Field -->
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($phone); ?>" 
                               placeholder="e.g., 98XXXXXXXX" 
                               required>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Citizenship Number (Optional) Field -->
                    <div class="form-group">
                        <label for="citizenship_no">Citizenship Number <span class="optional">(Optional)</span></label>
                        <input type="text" 
                               id="citizenship_no" 
                               name="citizenship_no" 
                               class="form-control <?php echo isset($errors['citizenship_no']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($citizenship_no); ?>" 
                               placeholder="e.g., 12-34-56-78">
                        <?php if (isset($errors['citizenship_no'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['citizenship_no']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Delivery Address Field -->
                <div class="form-group">
                    <label for="address">Delivery Address <span class="required">*</span></label>
                    <input type="text" 
                           id="address" 
                           name="address" 
                           class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($address); ?>" 
                           placeholder="City, Street, Ward No." 
                           required>
                    <?php if (isset($errors['address'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['address']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password and Confirm Password Row -->
                <div class="form-row">
                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               placeholder="Min. 6 characters" 
                               required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                               placeholder="Re-enter password" 
                               required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-4" id="submit-btn">Create Account</button>
            </form>

            <!-- Card Footer Links -->
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php" id="login-link">Login here</a></p>
            </div>
        </div>
    </div>
</main>

<?php
// Include page footer
require_once 'includes/footer.php';
?>
