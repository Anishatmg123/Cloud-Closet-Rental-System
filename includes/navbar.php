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
                <i class="fa-solid fa-vest-patches"></i>
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
            <?php if (isset($_SESSION['admin_id'])): ?>
                <a href="<?php echo $prefix; ?>admin/dashboard.php" class="btn btn-sm btn-outline-magenta">
                    <i class="fa-solid fa-shield-halved"></i> Admin Central
                </a>
                <a href="<?php echo $prefix; ?>admin/logout.php" class="btn btn-sm btn-subtle">Logout</a>
            <?php elseif (isset($_SESSION['vendor_id'])): ?>
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
