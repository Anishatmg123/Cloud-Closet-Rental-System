<?php
/**
 * Registration Role Selection
 */

$page_title = "Choose Registration Type";
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="auth-wrapper-page" style="padding-top: 110px;">
    <div class="container" style="max-width: 860px;">
        <div class="text-center" style="margin-bottom: 40px;">
            <span class="hero-pill-badge" style="background: var(--color-magenta-subtle); color: var(--color-magenta); border: 1px solid rgba(225, 29, 72, 0.2);">
                <i class="fa-solid fa-sparkles"></i> JOIN CLOUD CLOSET
            </span>
            <h1 style="font-family: var(--font-heading); font-size: 2.8rem; margin: 12px 0 8px; color: var(--text-primary);">Create Your Account</h1>
            <p class="text-muted" style="font-size: 1.05rem;">Choose how you want to experience the sustainable fashion revolution</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;" class="choice-cards-container">
            <!-- Card 1: Customer User -->
            <div class="hero-role-card" style="padding: 44px 32px; background: #ffffff;">
                <div class="role-icon-box" style="width: 64px; height: 64px; font-size: 1.8rem; margin-bottom: 20px;">
                    <i class="fa-solid fa-user-heart"></i>
                </div>
                <h2 style="font-family: var(--font-heading); font-size: 1.7rem; font-weight: 700; margin-bottom: 12px; color: var(--text-primary);">Customer Account</h2>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px; flex: 1;">
                    Rent luxury designer dresses, manage your bookings, enjoy door-to-door delivery, and shine at every special occasion.
                </p>
                <a href="register.php" class="btn btn-magenta btn-block btn-lg" id="choice-user-register-btn">
                    Register as Customer <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <!-- Card 2: Vendor Boutique -->
            <div class="hero-role-card" style="padding: 44px 32px; background: #ffffff;">
                <div class="role-icon-box" style="width: 64px; height: 64px; font-size: 1.8rem; margin-bottom: 20px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-store"></i>
                </div>
                <h2 style="font-family: var(--font-heading); font-size: 1.7rem; font-weight: 700; margin-bottom: 12px; color: var(--text-primary);">Vendor Boutique</h2>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px; flex: 1;">
                    Monetize your luxury wardrobe and designer inventory. List dresses, manage rental requests, and earn steady revenue.
                </p>
                <a href="vendor/register.php" class="btn btn-outline-magenta btn-block btn-lg" id="choice-vendor-register-btn">
                    Register as Vendor <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="text-center" style="margin-top: 40px;">
            <p class="text-muted">Already have an account? <a href="login.php" class="text-magenta font-weight-bold">Log in here</a></p>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .choice-cards-container {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
