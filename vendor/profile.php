<?php
/**
 * Vendor Profile & Boutique Settings
 */

$page_title = "Vendor Profile";
$path_prefix = '../';
require_once __DIR__ . '/../includes/vendor_auth.php';
require_once __DIR__ . '/../includes/header.php';

$vendorId = (int)$_SESSION['vendor_id'];
$errors = [];

// Handle Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $businessName = trim($_POST['business_name'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($businessName)) $errors['business_name'] = "Business name is required.";
        if (empty($fullName)) $errors['full_name'] = "Owner name is required.";
        if (empty($phone)) $errors['phone'] = "Phone number is required.";
        if (empty($address)) $errors['address'] = "Address is required.";

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE vendors 
                    SET business_name = :business_name, full_name = :full_name, phone = :phone, address = :address 
                    WHERE vendor_id = :vendor_id
                ");
                $stmt->execute([
                    ':business_name' => $businessName,
                    ':full_name' => $fullName,
                    ':phone' => $phone,
                    ':address' => $address,
                    ':vendor_id' => $vendorId
                ]);

                $_SESSION['business_name'] = $businessName;
                $_SESSION['vendor_name'] = $fullName;
                $current_vendor['business_name'] = $businessName;
                $current_vendor['full_name'] = $fullName;
                $current_vendor['phone'] = $phone;
                $current_vendor['address'] = $address;

                set_flash('success', 'Boutique profile updated successfully.');
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
        } elseif (!password_verify($currentPassword, $current_vendor['password'])) {
            $errors['password'] = "Current password is incorrect.";
        } elseif (strlen($newPassword) < 6) {
            $errors['password'] = "New password must be at least 6 characters.";
        } elseif ($newPassword !== $confirmPassword) {
            $errors['password'] = "New passwords do not match.";
        } else {
            try {
                $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE vendors SET password = :password WHERE vendor_id = :vendor_id");
                $stmt->execute([':password' => $newHash, ':vendor_id' => $vendorId]);

                set_flash('success', 'Vendor password updated successfully.');
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
    <?php require_once __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Boutique Profile & Settings</h1>
                    <p class="page-subtitle-text">Manage your store information, contact information, and login credentials.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;" class="dashboard-columns-2-1">
                <div>
                    <!-- Store Profile Form -->
                    <div class="dashboard-card">
                        <h3 class="card-title-text" style="margin-bottom: 20px;">Boutique Information</h3>

                        <form action="profile.php" method="POST" novalidate>
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="update_profile">

                            <div class="form-row-2">
                                <div class="form-group">
                                    <label class="form-label">Boutique / Store Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="business_name" 
                                           class="form-input no-icon <?php echo isset($errors['business_name']) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($current_vendor['business_name'] ?? ''); ?>" 
                                           required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Contact Person Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="full_name" 
                                           class="form-input no-icon <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($current_vendor['full_name'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="form-row-2">
                                <div class="form-group">
                                    <label class="form-label">Business Email</label>
                                    <input type="email" 
                                           class="form-input no-icon" 
                                           value="<?php echo htmlspecialchars($current_vendor['email'] ?? ''); ?>" 
                                           disabled 
                                           style="background: #f1f5f9; cursor: not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" 
                                           name="phone" 
                                           class="form-input no-icon <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($current_vendor['phone'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Store Location Address <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="address" 
                                       class="form-input no-icon <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($current_vendor['address'] ?? ''); ?>" 
                                       required>
                            </div>

                            <button type="submit" class="btn btn-magenta" style="margin-top: 10px;">
                                Save Boutique Details
                            </button>
                        </form>
                    </div>

                    <!-- Password Update -->
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

                <!-- Right Store Summary -->
                <div>
                    <div class="dashboard-card text-center" style="padding: 36px 24px;">
                        <div class="user-avatar-circle vendor-avatar" style="width: 72px; height: 72px; font-size: 2rem; margin: 0 auto 16px;">
                            <?php echo strtoupper(substr($current_vendor['business_name'] ?? 'V', 0, 1)); ?>
                        </div>
                        <h3 style="font-family: var(--font-heading); font-size: 1.4rem; color: var(--text-primary); margin-bottom: 4px;">
                            <?php echo htmlspecialchars($current_vendor['business_name'] ?? ''); ?>
                        </h3>
                        <span class="text-muted" style="font-size: 0.88rem; display: block; margin-bottom: 16px;">
                            Owner: <?php echo htmlspecialchars($current_vendor['full_name'] ?? ''); ?>
                        </span>

                        <div style="padding: 16px; background: #f8fafc; border-radius: var(--radius-md); text-align: left; margin-bottom: 20px;">
                            <div style="margin-bottom: 10px;">
                                <span class="stat-label">Store Status</span>
                                <span class="badge-success status-badge" style="margin-top: 4px;">Verified Boutique</span>
                            </div>
                            <div>
                                <span class="stat-label">Partner Since</span>
                                <span style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary); display: block; margin-top: 2px;">
                                    <?php echo date('F Y', strtotime($current_vendor['created_at'])); ?>
                                </span>
                            </div>
                        </div>

                        <a href="logout.php" class="btn btn-subtle btn-block text-danger">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
