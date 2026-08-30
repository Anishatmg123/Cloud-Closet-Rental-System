<?php
/**
 * User Dashboard
 * Cloud Closet Rental System
 *
 * This file represents the secure user dashboard. Access is restricted
 * to logged-in users only. Direct access attempts are redirected to login.php.
 */

// Start secure session
session_start();

// Security Guard: Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Save access request page (optional, for redirect after login)
    $_SESSION['registration_success'] = "Please log in to access your dashboard.";
    header("Location: ../login.php");
    exit();
}

// Set path prefix for includes since this file is in a subdirectory (user/)
$path_prefix = '../';
$page_title = "User Dashboard";

// Include dynamic header and navbar
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="dashboard-page" style="padding-top: 120px; min-height: 80vh; background-color: var(--color-bg-light);">
    <div class="section-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header-block" style="background-color: var(--color-primary); color: var(--color-white); padding: 40px; border-radius: var(--border-radius-lg); margin-bottom: 45px; box-shadow: 0 10px 30px rgba(30, 53, 47, 0.1);">
            <span style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; color: var(--color-accent); font-weight: 600;">Welcome Back</span>
            <h1 style="font-family: var(--font-heading); font-size: 2.8rem; margin: 10px 0 15px;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h1>
            <p style="font-weight: 300; font-size: 1.05rem; opacity: 0.9;">
                Manage your closet rentals, update billing delivery profiles, and track active bookings.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;" class="dashboard-grid">
            <!-- Main Dashboard Area -->
            <div class="dashboard-main">
                <h2 style="font-family: var(--font-heading); color: var(--color-primary); margin-bottom: 20px;">Your Active Rentals</h2>
                
                <!-- Mock empty state showing modern UI guidelines -->
                <div style="background-color: var(--color-white); border: 2px dashed rgba(30, 53, 47, 0.15); border-radius: var(--border-radius-lg); padding: 50px 20px; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 15px;">🛍️</div>
                    <h3 style="color: var(--color-primary); margin-bottom: 8px;">No Active Rentals Yet</h3>
                    <p style="color: var(--color-text-muted); max-width: 400px; margin: 0 auto 25px; font-size: 0.95rem;">
                        You don't have any items rented right now. Rent a designer dress for your upcoming special occasion!
                    </p>
                    <a href="../index.php#browse" class="btn btn-primary">Browse Dresses</a>
                </div>
            </div>

            <!-- Profile Summary Sidebar -->
            <div class="dashboard-sidebar">
                <div style="background-color: var(--color-white); padding: 30px; border-radius: var(--border-radius-lg); box-shadow: 0 4px 20px var(--color-shadow); border: 1px solid rgba(30, 53, 47, 0.02);">
                    <h3 style="font-family: var(--font-heading); color: var(--color-primary); margin-bottom: 20px; border-bottom: 1px solid rgba(30, 53, 47, 0.05); padding-bottom: 10px;">
                        Profile details
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <span style="font-size: 0.8rem; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; display: block;">Email</span>
                            <span style="font-size: 0.95rem; font-weight: 500; color: var(--color-text-dark);"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                        </div>
                        <div>
                            <span style="font-size: 0.8rem; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; display: block;">Account Status</span>
                            <span style="font-size: 0.85rem; font-weight: 600; background-color: #eef9f2; color: #1c5f35; padding: 4px 10px; border-radius: var(--border-radius-pill); display: inline-block; margin-top: 4px;">Active Member</span>
                        </div>
                    </div>

                    <div style="margin-top: 35px; border-top: 1px solid rgba(30, 53, 47, 0.05); padding-top: 20px;">
                        <a href="../logout.php" class="btn btn-outline btn-block">Sign Out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add dashboard media overrides dynamically inside main style to stack grid on smaller viewports -->
<style>
@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
    }
}
</style>

<?php
// Include dynamic footer
require_once '../includes/footer.php';
?>
