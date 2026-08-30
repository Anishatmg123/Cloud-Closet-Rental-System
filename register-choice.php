<?php
/**
 * Registration Role Selection
 * Cloud Closet Rental System
 *
 * This page allows visitors to choose whether they wish to register
 * as a customer/user (to rent clothes) or as a vendor (to list items).
 */

// Define page title
$page_title = "Choose Registration Type";

// Start secure session
session_start();

// Include header and navbar
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="auth-page choice-page">
    <div class="auth-container" style="max-width: 800px;">
        <!-- Page Title & Subtitle -->
        <div class="choice-header text-center" style="margin-bottom: 45px;">
            <h1 class="choice-title" style="font-family: var(--font-heading); font-size: 2.8rem; color: var(--color-primary); margin-bottom: 12px; letter-spacing: -0.5px;">Create Your Account</h1>
            <p class="choice-subtitle" style="font-size: 1.1rem; color: var(--color-text-muted); font-weight: 300;">Choose how you want to register with Cloud Closet</p>
            <div class="title-underline" style="margin-top: 15px;"></div>
        </div>

        <!-- Choice Card Grid -->
        <div class="choice-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 20px;">
            
            <!-- Card 1: Customer / User -->
            <div class="choice-card text-center" style="background-color: var(--color-white); padding: 50px 30px; border-radius: var(--border-radius-lg); box-shadow: 0 15px 40px rgba(30, 53, 47, 0.08); border: 1px solid rgba(30, 53, 47, 0.02); display: flex; flex-direction: column; align-items: center; justify-content: space-between; transition: var(--transition-smooth);">
                <div class="choice-icon" style="font-size: 4rem; margin-bottom: 25px; filter: drop-shadow(0 4px 12px rgba(30, 53, 47, 0.15));">👤</div>
                <h2 style="font-family: var(--font-heading); color: var(--color-primary); font-size: 1.75rem; margin-bottom: 15px; font-weight: 600;">Register as User</h2>
                <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 35px; flex-grow: 1; max-width: 290px;">
                    Rent dresses and manage your rentals. Access hundreds of designer items for your special occasions.
                </p>
                <a href="register.php" class="btn btn-primary btn-block btn-lg" id="choice-user-btn">Register as User</a>
            </div>

            <!-- Card 2: Vendor -->
            <div class="choice-card text-center" style="background-color: var(--color-white); padding: 50px 30px; border-radius: var(--border-radius-lg); box-shadow: 0 15px 40px rgba(30, 53, 47, 0.08); border: 1px solid rgba(30, 53, 47, 0.02); display: flex; flex-direction: column; align-items: center; justify-content: space-between; transition: var(--transition-smooth);">
                <div class="choice-icon" style="font-size: 4rem; margin-bottom: 25px; filter: drop-shadow(0 4px 12px rgba(30, 53, 47, 0.15));">🏪</div>
                <h2 style="font-family: var(--font-heading); color: var(--color-primary); font-size: 1.75rem; margin-bottom: 15px; font-weight: 600;">Register as Vendor</h2>
                <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 35px; flex-grow: 1; max-width: 290px;">
                    List your dresses and manage rental items. Turn your high-end wardrobe items into active income.
                </p>
                <a href="vendor/register.php" class="btn btn-outline btn-block btn-lg" id="choice-vendor-btn">Register as Vendor</a>
            </div>
        </div>
    </div>
</main>

<style>
/* Hover animation for card elements */
.choice-card {
    border: 1px solid rgba(30, 53, 47, 0.05) !important;
}
.choice-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 45px rgba(30, 53, 47, 0.12) !important;
    border-color: var(--color-accent) !important;
}

/* Responsiveness overrides */
@media (max-width: 768px) {
    .choice-grid {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
    }
    .choice-card {
        padding: 40px 25px !important;
    }
}
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
