<?php
$role = 'user';
$userId = 0;
$avatarInitial = 'U';
$displayName = 'User';
$profileLink = '../user/profile.php';
$logoutLink = '../logout.php';
$searchPlaceholder = "Find your dream gown...";
$searchAction = "../user/dresses.php";
$showWishlist = true;

if (isset($_SESSION['admin_id'])) {
    $role = 'admin';
    $userId = $_SESSION['admin_id'];
    $displayName = $_SESSION['full_name'] ?? 'Admin';
    $avatarInitial = strtoupper(substr($displayName, 0, 1));
    $profileLink = '../admin/dashboard.php';
    $logoutLink = '../admin/logout.php';
    $searchPlaceholder = "Search users, bookings, records...";
    $searchAction = "../admin/bookings.php";
    $showWishlist = false;
} elseif (isset($_SESSION['vendor_id'])) {
    $role = 'vendor';
    $userId = $_SESSION['vendor_id'];
    $displayName = $_SESSION['business_name'] ?? $_SESSION['vendor_name'] ?? 'Vendor';
    $avatarInitial = strtoupper(substr($displayName, 0, 1));
    $profileLink = '../vendor/profile.php';
    $logoutLink = '../vendor/logout.php';
    $searchPlaceholder = "Search inventory, bookings...";
    $searchAction = "../vendor/dresses.php";
    $showWishlist = false;
} elseif (isset($_SESSION['user_id'])) {
    $role = 'user';
    $userId = $_SESSION['user_id'];
    $displayName = $_SESSION['full_name'] ?? 'Customer';
    $avatarInitial = strtoupper(substr($displayName, 0, 1));
    $profileLink = '../user/profile.php';
    $logoutLink = '../logout.php';
    $searchPlaceholder = "Find your dream gown...";
    $searchAction = "../user/dresses.php";
    $showWishlist = true;
}

// Fetch unread notifications count
$unreadNotifCount = 0;
$recentNotifs = [];
if (isset($pdo) && $userId) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_type = :user_type AND user_id = :user_id AND is_read = 0");
        $stmt->execute([':user_type' => $role, ':user_id' => $userId]);
        $unreadNotifCount = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_type = :user_type AND user_id = :user_id ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([':user_type' => $role, ':user_id' => $userId]);
        $recentNotifs = $stmt->fetchAll();
    } catch (Exception $e) {}
}

// Fetch wishlist count for user
$wishlistCount = 0;
if (isset($pdo) && $role === 'user' && $userId) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $wishlistCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {}
}
?>
<!-- Universal Dashboard Topbar -->
<header class="dashboard-topbar">
    <div class="topbar-left">
        <!-- Mobile Sidebar Toggle -->
        <button class="topbar-toggle-btn d-lg-none" onclick="toggleMobileSidebar()" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Topbar Search Bar -->
        <form action="<?php echo $searchAction; ?>" method="GET" class="topbar-search-form">
            <div class="search-input-group">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" 
                       name="q" 
                       class="topbar-search-input" 
                       placeholder="<?php echo htmlspecialchars($searchPlaceholder); ?>"
                       value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
            </div>
        </form>
    </div>

    <div class="topbar-right">
        <!-- Wishlist Icon (User role only) -->
        <?php if ($showWishlist): ?>
            <a href="../user/wishlist.php" class="topbar-action-btn" title="Saved Dresses">
                <i class="fa-regular fa-heart"></i>
                <?php if ($wishlistCount > 0): ?>
                    <span class="action-badge-pill" id="wishlist-badge"><?php echo $wishlistCount; ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <!-- Notification Bell Dropdown -->
        <div class="topbar-dropdown-wrap">
            <button class="topbar-action-btn" id="notif-toggle-btn" onclick="toggleDropdown('notif-dropdown')" title="Notifications">
                <i class="fa-regular fa-bell"></i>
                <?php if ($unreadNotifCount > 0): ?>
                    <span class="action-badge-pill" id="notif-badge"><?php echo $unreadNotifCount; ?></span>
                <?php endif; ?>
            </button>

            <!-- Notifications Dropdown Menu -->
            <div class="dropdown-menu notif-dropdown-menu" id="notif-dropdown">
                <div class="dropdown-header">
                    <span class="dropdown-title">Notifications</span>
                    <span class="dropdown-count"><?php echo $unreadNotifCount; ?> unread</span>
                </div>
                <div class="dropdown-body">
                    <?php if (!empty($recentNotifs)): ?>
                        <?php foreach ($recentNotifs as $notif): ?>
                            <div class="notif-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                                <div class="notif-icon-circle <?php echo htmlspecialchars($notif['type'] ?? 'info'); ?>">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                                <div class="notif-text-wrap">
                                    <span class="notif-item-title"><?php echo htmlspecialchars($notif['title']); ?></span>
                                    <p class="notif-item-desc"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    <span class="notif-item-time"><?php echo date('M d, g:i A', strtotime($notif['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-dropdown">
                            <i class="fa-regular fa-bell-slash"></i>
                            <p>No notifications yet</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="dropdown-footer">
                    <?php if ($role === 'user'): ?>
                        <a href="../user/notifications.php">View all notifications &rarr;</a>
                    <?php elseif ($role === 'vendor'): ?>
                        <a href="../vendor/notifications.php">View all notifications &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="topbar-dropdown-wrap">
            <button class="topbar-profile-btn" onclick="toggleDropdown('profile-dropdown')">
                <div class="topbar-avatar"><?php echo $avatarInitial; ?></div>
                <span class="topbar-user-name d-none d-md-inline"><?php echo $displayName; ?></span>
                <i class="fa-solid fa-chevron-down topbar-chevron"></i>
            </button>

            <!-- Profile Dropdown Menu -->
            <div class="dropdown-menu profile-dropdown-menu" id="profile-dropdown">
                <div class="profile-dropdown-header">
                    <div class="topbar-avatar large"><?php echo $avatarInitial; ?></div>
                    <div>
                        <div class="font-weight-bold"><?php echo $displayName; ?></div>
                        <div class="text-muted small"><?php echo ucfirst($role); ?></div>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="<?php echo $profileLink; ?>" class="dropdown-item">
                    <i class="fa-regular fa-user"></i> My Profile
                </a>
                <a href="<?php echo $logoutLink; ?>" class="dropdown-item text-danger">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                </a>
            </div>
        </div>
    </div>
</header>
