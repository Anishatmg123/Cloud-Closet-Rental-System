
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
                <svg class="logo-mark" viewBox="0 0 32 32"
                    fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 9.5V7C16 5.343 17.343 4 19 4"
                        stroke="#ffffff" stroke-width="1.8"/>
                    <path d="M8.5 25.5H23.5C26.538 25.5 29 23.038 29 20"
                        stroke="#ffffff" stroke-width="1.8"/>
                    <path d="M16 10.5L7 18.5H25L16 10.5Z"
                        stroke="#ffffff" stroke-width="1.7"/>
                </svg>
            </div>

            <div class="logo-text-group">
                <span class="logo-main">CLOUD CLOSET</span>
                <span class="logo-tag">Luxury Edition Rental</span>
            </div>
        </a>

        <!-- Navigation Links -->
        <nav class="nav-links-container" id="nav-menu">
            <ul class="nav-menu-list">
                <li><a href="<?php echo $prefix; ?>index.php" class="nav-link active">Home</a></li>
                <li><a href="<?php echo $prefix; ?>index.php#browse" class="nav-link">Collection</a></li>
                <li><a href="<?php echo $prefix; ?>index.php#how-it-works" class="nav-link">How It Works</a></li>
                <li><a href="<?php echo $prefix; ?>index.php#about" class="nav-link">About</a></li>
            </ul>
        </nav>

        <!-- Action Buttons -->
        <div class="nav-action-buttons">

            <?php if (isset($_SESSION['vendor_id'])): ?>

                <a href="<?php echo $prefix; ?>vendor/dashboard.php"
                   class="btn btn-sm btn-outline-magenta">
                    <i class="fa-solid fa-shop"></i> Vendor Portal
                </a>

                <a href="<?php echo $prefix; ?>vendor/logout.php"
                   class="btn btn-sm btn-subtle">Logout</a>

            <?php elseif (isset($_SESSION['user_id'])): ?>

                <a href="<?php echo $prefix; ?>user/dashboard.php"
                   class="btn btn-sm btn-magenta">
                    <i class="fa-regular fa-user"></i> My Dashboard
                </a>

                <a href="<?php echo $prefix; ?>logout.php"
                   class="btn btn-sm btn-subtle">Logout</a>

            <?php else: ?>

                <a href="<?php echo $prefix; ?>login.php"
                   class="btn btn-sm btn-subtle" id="nav-login-btn">
                    <i class="fa-regular fa-user"></i> Login
                </a>

                <a href="<?php echo $prefix; ?>register-choice.php"
                   class="btn btn-sm btn-magenta" id="nav-register-btn">
                    Get Started
                </a>

            <?php endif; ?>

            <!-- Mobile Toggle -->
            <button class="mobile-nav-toggle" id="nav-toggle"
                    aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>

        </div>
    </div>
</header>
