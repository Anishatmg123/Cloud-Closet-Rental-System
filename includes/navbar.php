<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$prefix = isset($path_prefix) ? $path_prefix : '';
?>
<!-- Luxury Top Navigation -->
<header class="public-navbar" id="navbar">
    <div class="nav-wrapper">
        <!-- Logo -->
        <a href="<?php echo $prefix; ?>index.php" class="brand-logo">
            <div class="logo-symbol">
                <svg class="logo-mark" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <!-- Fashion Hanger Hook ascending from top -->
                    <path d="M16 9.5V7C16 5.343 17.343 4 19 4C20.105 4 21 3.105 21 2C21 0.895 20.105 0 19 0C16.791 0 15 1.791 15 4" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round"/>
                    <!-- Modern Cloud Contour -->
                    <path d="M8.5 25.5H23.5C26.538 25.5 29 23.038 29 20C29 17.18 26.88 14.85 24.12 14.54C23.6 10.27 19.98 7 15.5 7C11.53 7 8.21 9.61 7.23 13.23C4.33 13.88 2.2 16.44 2.2 19.5C2.2 22.81 4.89 25.5 8.2 25.5H8.5Z" fill="rgba(255, 255, 255, 0.16)" stroke="#ffffff" stroke-width="1.8" stroke-linejoin="round"/>
                    <!-- Integrated Luxury Coat Hanger Frame -->
                    <path d="M16 10.5L7 18.5H25L16 10.5Z" stroke="#ffffff" stroke-width="1.7" stroke-linejoin="round" fill="rgba(255, 255, 255, 0.25)"/>
                    <!-- Couture Dress / Wardrobe Draping Lines -->
                    <path d="M11.5 18.5L9.5 24.5M20.5 18.5L22.5 24.5" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Sparkling Couture Accent -->
                    <path d="M23 10L23.5 11.5L25 12L23.5 12.5L23 14L22.5 12.5L21 12L22.5 11.5L23 10Z" fill="#ffffff"/>
                </svg>
            </div>
            <div class="logo-text-group">
                <span class="logo-main">CLOUD CLOSET</span>
                <span class="logo-tag">Luxury Editorial Rental</span>
            </div>
        </a>

        <!-- Center Navigation Links -->
        <nav class="nav-links-container" id="nav-menu">
            <ul class="nav-menu-list">
                <li><a href="<?php echo $prefix; ?>index.php" class="nav-link active">Home</a></li>
                <li><a href="<?php echo $prefix; ?>index.php#browse" class="nav-link">Collection</a></li>
                <li><a href="<?php echo $prefix; ?>index.php#how-it-works" class="nav-link">How It Works</a></li>
                <li><a href="<?php echo $prefix; ?>index.php#about" class="nav-link">About</a></li>
            </ul>
        </nav>

        <!-- Right Role Action Buttons -->
        <div class="nav-action-buttons">
            <?php if (isset($_SESSION['vendor_id'])): ?>
                <a href="<?php echo $prefix; ?>vendor/dashboard.php" class="btn btn-sm btn-outline-magenta">
                    <i class="fa-solid fa-shop"></i> Vendor Portal
                </a>
                <a href="<?php echo $prefix; ?>vendor/logout.php" class="btn btn-sm btn-subtle">Logout</a>
            <?php elseif (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo $prefix; ?>user/dashboard.php" class="btn btn-sm btn-magenta">
                    <i class="fa-regular fa-user"></i> My Dashboard
                </a>
                <a href="<?php echo $prefix; ?>logout.php" class="btn btn-sm btn-subtle">Logout</a>
            <?php else: ?>
                <a href="<?php echo $prefix; ?>login.php" class="btn btn-sm btn-subtle" id="nav-login-btn">
                    <i class="fa-regular fa-user"></i> Login
                </a>
                <a href="<?php echo $prefix; ?>register-choice.php" class="btn btn-sm btn-magenta" id="nav-register-btn">
                    Get Started
                </a>
            <?php endif; ?>

            <!-- Mobile Hamburger Toggle -->
            <button class="mobile-nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
</header>
