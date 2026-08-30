<?php
// Ensure session is active before checking login state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Compute the path prefix if not set
$prefix = isset($path_prefix) ? $path_prefix : '';
?>
<!-- Navigation Bar -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <!-- Logo -->
        <a href="<?php echo $prefix; ?>index.php" class="nav-logo" id="nav-logo">
            <!-- Elegant Closet Hanger SVG Icon -->
            <svg class="logo-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2a3 3 0 0 0-3 3v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3V5a3 3 0 0 0-3-3z"></path>
                <path d="M9 7h6"></path>
                <path d="M12 12v6"></path>
                <path d="M9 15h6"></path>
            </svg>
            <span class="logo-text">Cloud Closet</span>
        </a>

        <!-- Hamburger Menu for Mobile -->
        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <!-- Navigation Menu Links -->
        <div class="nav-menu" id="nav-menu">
            <ul class="nav-links">
                <li><a href="<?php echo $prefix; ?>index.php" class="nav-item">Home</a></li>
                <li><a href="<?php echo $prefix; ?>index.php#browse" class="nav-item">Browse Dresses</a></li>
                <li><a href="<?php echo $prefix; ?>index.php#about" class="nav-item">About</a></li>
            </ul>
            
            <!-- Auth Buttons (Login & Register or Dashboard & Logout) -->
            <div class="nav-auth">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $prefix; ?>user/dashboard.php" class="btn btn-outline" id="btn-dashboard">Dashboard</a>
                    <a href="<?php echo $prefix; ?>logout.php" class="btn btn-primary" id="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="<?php echo $prefix; ?>login.php" class="btn btn-outline" id="btn-login">Login</a>
                    <a href="<?php echo $prefix; ?>register-choice.php" class="btn btn-primary" id="btn-register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
