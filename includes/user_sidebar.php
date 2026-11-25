<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$prefix = isset($path_prefix) ? $path_prefix : '../';
$userName = htmlspecialchars($_SESSION['full_name'] ?? 'Fashion Lover');
$userEmail = htmlspecialchars($_SESSION['email'] ?? '');
$userInitials = strtoupper(substr($userName, 0, 1));
?>
<!-- Customer Dashboard Sidebar -->
<aside class="dashboard-sidebar" id="dashboard-sidebar">
    <div class="sidebar-header">
        <a href="<?php echo $prefix; ?>index.php" class="sidebar-brand">
            <div class="brand-icon-wrap">
                <i class="fa-solid fa-vest-patches"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">Cloud Closet</span>
                <span class="brand-sub">Sustainable Couture</span>
            </div>
        </a>
        <button class="sidebar-close-btn d-lg-none" onclick="toggleMobileSidebar()" aria-label="Close sidebar">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-menu-wrapper">
        <nav class="sidebar-nav">
            <ul class="sidebar-nav-list">
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>user/dashboard.php" class="sidebar-nav-link <?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-table-cells-large nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>user/dresses.php" class="sidebar-nav-link <?php echo ($currentPage === 'dresses.php' || $currentPage === 'dress-details.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-vest-patches nav-icon"></i>
                        <span>Dresses</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>user/dresses.php?sort=popularity" class="sidebar-nav-link">
                        <i class="fa-solid fa-compass nav-icon"></i>
                        <span>Explore</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>user/bookings.php" class="sidebar-nav-link <?php echo ($currentPage === 'bookings.php' || $currentPage === 'booking-details.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-calendar-check nav-icon"></i>
                        <span>Bookings</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>user/payments.php" class="sidebar-nav-link <?php echo ($currentPage === 'payments.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-credit-card nav-icon"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>user/wishlist.php" class="sidebar-nav-link <?php echo ($currentPage === 'wishlist.php') ? 'active' : ''; ?>">
                        <i class="fa-regular fa-heart nav-icon"></i>
                        <span>Wishlist</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>user/notifications.php" class="sidebar-nav-link <?php echo ($currentPage === 'notifications.php') ? 'active' : ''; ?>">
                        <i class="fa-regular fa-bell nav-icon"></i>
                        <span>Updates</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>user/profile.php" class="sidebar-nav-link <?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>">
                        <i class="fa-regular fa-user nav-icon"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- "+ RENT A DRESS" Quick Action Button -->
        <div class="sidebar-cta-box">
            <a href="<?php echo $prefix; ?>user/dresses.php" class="btn btn-primary-magenta btn-block btn-pill sidebar-rent-btn">
                <i class="fa-solid fa-plus"></i> RENT A DRESS
            </a>
        </div>
    </div>

    <!-- User Profile Card -->
    <div class="sidebar-user-card">
        <div class="user-avatar-circle">
            <?php echo $userInitials; ?>
        </div>
        <div class="user-info-text">
            <span class="user-display-name"><?php echo $userName; ?></span>
            <span class="user-role-badge">Customer</span>
        </div>
        <a href="<?php echo $prefix; ?>logout.php" class="sidebar-logout-btn" title="Sign Out">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
    </div>
</aside>
