<?php
/**
 * Vendor Registration Module
 * Cloud Closet Rental System
 *
 * This file handles vendor application submissions. It processes the POST request,
 * validates the fields, checks for duplicate emails, securely hashes the password,
 * and inserts the vendor record with a default 'pending' status requiring admin approval.
 */

// Define page title
$page_title = "Vendor Register";

// Start secure session
session_start();

// Include the database connection
require_once '../config/database.php';

// Initialize variables to preserve form values on validation errors
$full_name = '';
$email = '';
$phone = '';
$address = '';
$business_name = '';

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
    $business_name = trim($_POST['business_name'] ?? '');
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
        // Check if email already exists in the vendors table
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendors WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $email_count = $stmt->fetchColumn();
            
            if ($email_count > 0) {
                $errors['email'] = "This email is already registered as a vendor.";
            }
        } catch (PDOException $e) {
            $errors['general'] = "Database validation error: " . $e->getMessage();
        }
    }

    // Validate Phone Number
    if (empty($phone)) {
        $errors['phone'] = "Phone number is required.";
    }

    // Validate Business Name
    if (empty($business_name)) {
        $errors['business_name'] = "Business name is required.";
    }

    // Validate Address
    if (empty($address)) {
        $errors['address'] = "Business address is required.";
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

            // Prepared statement query setting default status as 'pending'
            $sql = "INSERT INTO vendors (full_name, email, phone, password, address, business_name, status) 
                    VALUES (:full_name, :email, :phone, :password, :address, :business_name, :status)";
            
            $stmt = $pdo->prepare($sql);
            
            $result = $stmt->execute([
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $hashed_password,
                ':address' => $address,
                ':business_name' => $business_name,
                ':status' => 'pending' // New vendor applications are pending by default
            ]);

            if ($result) {
                $success_message = "Your vendor registration has been submitted and is waiting for admin approval.";
                // Clear form values on successful registration
                $full_name = $email = $phone = $address = $business_name = '';
            } else {
                $errors['general'] = "Failed to submit vendor application. Please try again.";
            }

        } catch (PDOException $e) {
            $errors['general'] = "Database error: " . $e->getMessage();
        }
    }
}

// Set path prefix for resources as this file is inside vendor/ directory
$path_prefix = '../';

// Include page header and navbar
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h2 class="auth-title">Vendor Application</h2>
            <p class="auth-subtitle">Partner with Cloud Closet and list your luxury wardrobe for rent.</p>
            
            <!-- Application Success Message Banner -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" id="success-banner" style="background-color: #f0f7ff; color: #1e40af; border: 1px solid rgba(30, 64, 175, 0.15);">
                    <svg class="alert-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #2563eb;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <div>
                        <strong>Application Submitted!</strong> 
                        <p style="margin-top: 5px; font-size: 0.9rem; line-height: 1.6; color: #374151;">
                            <?php echo $success_message; ?>
                        </p>
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
            <form action="register.php" method="POST" class="auth-form" id="vendor-register-form" novalidate>
                
                <!-- Business Name Field -->
                <div class="form-group">
                    <label for="business_name">Business / Store Name <span class="required">*</span></label>
                    <input type="text" 
                           id="business_name" 
                           name="business_name" 
                           class="form-control <?php echo isset($errors['business_name']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($business_name); ?>" 
                           placeholder="Enter your registered business or boutique name" 
                           required>
                    <?php if (isset($errors['business_name'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['business_name']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Full Name Field -->
                <div class="form-group">
                    <label for="full_name">Owner's Full Name <span class="required">*</span></label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($full_name); ?>" 
                           placeholder="Enter full name of the primary contact" 
                           required>
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['full_name']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email Address Field -->
                <div class="form-group">
                    <label for="email">Business Email Address <span class="required">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="e.g., info@yourstore.com" 
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Phone and Address Row -->
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
                    
                    <!-- Business Address Field -->
                    <div class="form-group">
                        <label for="address">Business Address <span class="required">*</span></label>
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
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-4" id="submit-btn">Submit Application</button>
            </form>

            <!-- Card Footer Links -->
            <div class="auth-footer">
                <p>Already registered? <a href="login.php" id="vendor-login-link">Vendor Login here</a></p>
                <p style="margin-top: 10px; font-size: 0.85rem;">Want to rent clothes instead? <a href="../register.php" id="customer-register-link">Register as Customer</a></p>
            </div>
        </div>
    </div>
</main>

<?php
// Include page footer
require_once '../includes/footer.php';
?>
