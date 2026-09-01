<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$prefix = isset($path_prefix) ? $path_prefix : '../';
$vendorBusiness = htmlspecialchars($_SESSION['business_name'] ?? $_SESSION['vendor_name'] ?? 'Boutique Partner');
$vendorEmail = htmlspecialchars($_SESSION['vendor_email'] ?? '');
$vendorInitials = strtoupper(substr($vendorBusiness, 0, 1));
?>
<!-- Vendor Dashboard Sidebar -->
<aside class="dashboard-sidebar" id="dashboard-sidebar">
    <div class="sidebar-header">
        <a href="<?php echo $prefix; ?>index.php" class="sidebar-brand">
            <div class="brand-icon-wrap vendor-badge">
                <i class="fa-solid fa-shop"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">Cloud Closet</span>
                <span class="brand-sub">Vendor Portal</span>
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
                    <a href="<?php echo $prefix; ?>vendor/dashboard.php" class="sidebar-nav-link <?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-gauge-high nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- CATALOG SECTION -->
                <li class="sidebar-section-heading">CATALOG</li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>vendor/dresses.php" class="sidebar-nav-link <?php echo ($currentPage === 'dresses.php' || $currentPage === 'edit-dress.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-vest-patches nav-icon"></i>
                        <span>Manage Dresses</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>vendor/add-dress.php" class="sidebar-nav-link <?php echo ($currentPage === 'add-dress.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-circle-plus nav-icon"></i>
                        <span>Add Dress</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>vendor/inventory.php" class="sidebar-nav-link <?php echo ($currentPage === 'inventory.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-boxes-stacked nav-icon"></i>
                        <span>Manage Inventory</span>
                    </a>
                </li>

                <!-- INVENTORY & BOOKINGS SECTION -->
                <li class="sidebar-section-heading">INVENTORY & BOOKINGS</li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>vendor/bookings.php" class="sidebar-nav-link <?php echo ($currentPage === 'bookings.php' && (!isset($_GET['status']) || $_GET['status'] !== 'returned')) ? 'active' : ''; ?>">
                        <i class="fa-solid fa-calendar-check nav-icon"></i>
                        <span>View Bookings</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>vendor/returns.php" class="sidebar-nav-link <?php echo ($currentPage === 'returns.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-rotate-left nav-icon"></i>
                        <span>Manage Returns</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>vendor/bookings.php?status=returned" class="sidebar-nav-link <?php echo (isset($_GET['status']) && $_GET['status'] === 'returned') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-clock-rotate-left nav-icon"></i>
                        <span>Rental History</span>
                    </a>
                </li>

                <!-- BUSINESS SECTION -->
                <li class="sidebar-section-heading">BUSINESS</li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>vendor/reports.php" class="sidebar-nav-link <?php echo ($currentPage === 'reports.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-chart-pie nav-icon"></i>
                        <span>Generate Reports</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>vendor/notifications.php" class="sidebar-nav-link <?php echo ($currentPage === 'notifications.php') ? 'active' : ''; ?>">
                        <i class="fa-regular fa-bell nav-icon"></i>
                        <span>Notifications</span>
                    </a>
                </li>

                <!-- ACCOUNT SECTION -->
                <li class="sidebar-section-heading">ACCOUNT</li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>vendor/profile.php" class="sidebar-nav-link <?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>">
                        <i class="fa-regular fa-user nav-icon"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?php echo $prefix; ?>vendor/logout.php" class="sidebar-nav-link text-danger">
                        <i class="fa-solid fa-arrow-right-from-bracket nav-icon"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Vendor Profile Card -->
    <div class="sidebar-user-card">
        <div class="user-avatar-circle vendor-avatar">
            <?php echo $vendorInitials; ?>
        </div>
        <div class="user-info-text">
            <span class="user-display-name"><?php echo $vendorBusiness; ?></span>
            <span class="user-role-badge badge-vendor">Vendor</span>
        </div>
        <a href="<?php echo $prefix; ?>vendor/logout.php" class="sidebar-logout-btn" title="Sign Out">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
    </div>
</aside>
