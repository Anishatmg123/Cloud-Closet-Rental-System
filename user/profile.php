<?php
/**
 * Customer Profile & Settings
 */

$page_title = "My Profile";
$path_prefix = '../';
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/header.php';

$userId = (int)$_SESSION['user_id'];
$errors = [];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $citizenshipNo = trim($_POST['citizenship_no'] ?? '');

        if (empty($fullName)) {
            $errors['full_name'] = "Full name cannot be empty.";
        }
        if (empty($phone)) {
            $errors['phone'] = "Phone number is required.";
        }
        if (empty($address)) {
            $errors['address'] = "Delivery address is required.";
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = :full_name, phone = :phone, address = :address, citizenship_no = :citizenship_no 
                    WHERE user_id = :user_id
                ");
                $stmt->execute([
                    ':full_name' => $fullName,
                    ':phone' => $phone,
                    ':address' => $address,
                    ':citizenship_no' => !empty($citizenshipNo) ? $citizenshipNo : null,
                    ':user_id' => $userId
                ]);

                $_SESSION['full_name'] = $fullName;
                $current_user['full_name'] = $fullName;
                $current_user['phone'] = $phone;
                $current_user['address'] = $address;
                $current_user['citizenship_no'] = $citizenshipNo;

                set_flash('success', 'Profile details updated successfully.');
                header("Location: profile.php");
                exit();
            } catch (Exception $e) {
                $errors['general'] = "Error updating profile: " . $e->getMessage();
            }
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            $errors['password'] = "All password fields are required.";
        } elseif (!password_verify($currentPassword, $current_user['password'])) {
            $errors['password'] = "Current password is incorrect.";
        } elseif (strlen($newPassword) < 6) {
            $errors['password'] = "New password must be at least 6 characters.";
        } elseif ($newPassword !== $confirmPassword) {
            $errors['password'] = "New passwords do not match.";
        } else {
            try {
                $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
                $stmt->execute([':password' => $newHash, ':user_id' => $userId]);

                set_flash('success', 'Password changed successfully.');
                header("Location: profile.php");
                exit();
            } catch (Exception $e) {
                $errors['password'] = "Error updating password.";
            }
        }
    }
}
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/user_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Profile & Account Settings</h1>
                    <p class="page-subtitle-text">Manage your personal credentials, contact info, and security preferences.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;" class="dashboard-columns-2-1">
                <div>
                    <!-- Personal Information Form -->
                    <div class="dashboard-card">
                        <h3 class="card-title-text" style="margin-bottom: 20px;">Personal Information</h3>

                        <form action="profile.php" method="POST" novalidate>
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="update_profile">

                            <div class="form-group">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="full_name" 
                                       class="form-input no-icon <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($current_user['full_name']); ?>" 
                                       required>
                                <?php if (isset($errors['full_name'])): ?>
                                    <div class="field-error-text"><?php echo htmlspecialchars($errors['full_name']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-row-2">
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" 
                                           class="form-input no-icon" 
                                           value="<?php echo htmlspecialchars($current_user['email']); ?>" 
                                           disabled 
                                           style="background: #f1f5f9; cursor: not-allowed;">
                                    <span class="text-muted" style="font-size: 0.75rem;">Email address is permanent.</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" 
                                           name="phone" 
                                           class="form-input no-icon <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($current_user['phone']); ?>" 
                                           required>
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="field-error-text"><?php echo htmlspecialchars($errors['phone']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="address" 
                                       class="form-input no-icon <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($current_user['address'] ?? ''); ?>" 
                                       required>
                                <?php if (isset($errors['address'])): ?>
                                    <div class="field-error-text"><?php echo htmlspecialchars($errors['address']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Citizenship / National ID <span class="text-muted">(Optional)</span></label>
                                <input type="text" 
                                       name="citizenship_no" 
                                       class="form-input no-icon" 
                                       value="<?php echo htmlspecialchars($current_user['citizenship_no'] ?? ''); ?>">
                            </div>

                            <button type="submit" class="btn btn-magenta" style="margin-top: 10px;">
                                Save Profile Changes
                            </button>
                        </form>
                    </div>

                    <!-- Password Security Form -->
                    <div class="dashboard-card">
                        <h3 class="card-title-text" style="margin-bottom: 20px;">Change Password</h3>

                        <?php if (isset($errors['password'])): ?>
                            <div class="alert alert-error">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <div><?php echo htmlspecialchars($errors['password']); ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="profile.php" method="POST" novalidate>
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="change_password">

                            <div class="form-group">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-input no-icon" required>
                            </div>

                            <div class="form-row-2">
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-input no-icon" placeholder="Min. 6 chars" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-input no-icon" placeholder="Re-enter password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-outline-magenta" style="margin-top: 10px;">
                                Update Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Account Status Card -->
                <div>
                    <div class="dashboard-card text-center" style="padding: 36px 24px;">
                        <div class="user-avatar-circle" style="width: 72px; height: 72px; font-size: 2rem; margin: 0 auto 16px;">
                            <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
                        </div>
                        <h3 style="font-family: var(--font-heading); font-size: 1.4rem; color: var(--text-primary); margin-bottom: 4px;">
                            <?php echo htmlspecialchars($current_user['full_name']); ?>
                        </h3>
                        <span class="text-muted" style="font-size: 0.88rem; display: block; margin-bottom: 16px;">
                            <?php echo htmlspecialchars($current_user['email']); ?>
                        </span>

                        <div style="padding: 16px; background: #f8fafc; border-radius: var(--radius-md); text-align: left; margin-bottom: 20px;">
                            <div style="margin-bottom: 10px;">
                                <span class="stat-label">Membership Status</span>
                                <span class="badge-success status-badge" style="margin-top: 4px;">Active Member</span>
                            </div>
                            <div>
                                <span class="stat-label">Member Since</span>
                                <span style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary); display: block; margin-top: 2px;">
                                    <?php echo date('F Y', strtotime($current_user['created_at'])); ?>
                                </span>
                            </div>
                        </div>

                        <a href="../logout.php" class="btn btn-subtle btn-block text-danger">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
