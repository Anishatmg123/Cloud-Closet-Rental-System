<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$prefix = isset($path_prefix) ? $path_prefix : '../';
$adminName = htmlspecialchars($_SESSION['full_name'] ?? 'Administrator');
$adminRole = htmlspecialchars($_SESSION['role'] ?? 'super_admin');
$adminInitials = strtoupper(substr($adminName, 0, 1));
?>
<!-- Administrator Dashboard Sidebar -->
<aside class="dashboard-sidebar admin-theme-sidebar" id="dashboard-sidebar">
    <div class="sidebar-header">
        <a href="<?php echo $prefix; ?>index.php" class="sidebar-brand">
            <div class="brand-icon-wrap admin-badge">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">Cloud Closet</span>
                <span class="brand-sub">Admin Central</span>
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
                <!-- OVERVIEW SECTION -->
                <li class="sidebar-section-heading">OVERVIEW</li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>admin/dashboard.php" class="sidebar-nav-link <?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-chart-line nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- MANAGEMENT SECTION -->
                <li class="sidebar-section-heading">MANAGEMENT</li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>admin/users.php" class="sidebar-nav-link <?php echo ($currentPage === 'users.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-users nav-icon"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>admin/vendors.php" class="sidebar-nav-link <?php echo ($currentPage === 'vendors.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-store nav-icon"></i>
                        <span>Manage Vendors</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>admin/categories.php" class="sidebar-nav-link <?php echo ($currentPage === 'categories.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-tags nav-icon"></i>
                        <span>Manage Categories</span>
                    </a>
                </li>

                <!-- OPERATIONS SECTION -->
                <li class="sidebar-section-heading">OPERATIONS</li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>admin/bookings.php" class="sidebar-nav-link <?php echo ($currentPage === 'bookings.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-calendar-check nav-icon"></i>
                        <span>View All Bookings</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>admin/transactions.php" class="sidebar-nav-link <?php echo ($currentPage === 'transactions.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-receipt nav-icon"></i>
                        <span>View Transactions</span>
                    </a>
                </li>

                <!-- INSIGHTS SECTION -->
                <li class="sidebar-section-heading">INSIGHTS</li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>admin/reports.php" class="sidebar-nav-link <?php echo ($currentPage === 'reports.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-file-invoice-dollar nav-icon"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>admin/analytics.php" class="sidebar-nav-link <?php echo ($currentPage === 'analytics.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-chart-pie nav-icon"></i>
                        <span>Analytics</span>
                    </a>
                </li>

                <!-- ACCOUNT SECTION -->
                <li class="sidebar-section-heading">ACCOUNT</li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>admin/logout.php" class="sidebar-nav-link text-danger">
                        <i class="fa-solid fa-arrow-right-from-bracket nav-icon"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Admin Profile Card -->
    <div class="sidebar-user-card admin-user-card">
        <div class="user-avatar-circle admin-avatar">
            <?php echo $adminInitials; ?>
        </div>
        <div class="user-info-text">
            <span class="user-display-name"><?php echo $adminName; ?></span>
            <span class="user-role-badge badge-admin"><?php echo ucwords(str_replace('_', ' ', $adminRole)); ?></span>
        </div>
        <a href="<?php echo $prefix; ?>admin/logout.php" class="sidebar-logout-btn" title="Sign Out">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
    </div>
</aside>
