<?php
/**
 * Admin User Management
 */

$page_title = "Manage Users";
$path_prefix = '../';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/header.php';

// Handle Toggle Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_user_status') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? 'active');

    if ($userId && in_array($newStatus, ['active', 'blocked'])) {
        $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE user_id = :user_id");
        $stmt->execute([':status' => $newStatus, ':user_id' => $userId]);

        set_flash('success', "User account status updated to " . ucfirst($newStatus) . ".");
        header("Location: users.php");
        exit();
    }
}

// Search & Filter
$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');

$sql = "
    SELECT u.*,
           (SELECT COUNT(*) FROM rentals r WHERE r.user_id = u.user_id) as total_rentals,
           (SELECT COALESCE(SUM(rental_amount), 0) FROM rentals r WHERE r.user_id = u.user_id AND r.rental_status != 'cancelled') as total_spend
    FROM users u
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.full_name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($statusFilter !== 'all') {
    $sql .= " AND u.status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Registered Customers</h1>
                    <p class="page-subtitle-text">Manage customer accounts, review booking engagement, and security permissions.</p>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <div class="dashboard-card" style="padding: 18px; margin-bottom: 24px;">
                <form action="users.php" method="GET" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                    <div style="flex: 2; min-width: 240px;">
                        <input type="text" name="q" class="form-input no-icon" placeholder="Search by customer name, email, phone..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div style="flex: 1; min-width: 160px;">
                        <select name="status" class="form-input no-icon" onchange="this.form.submit()">
                            <option value="all">All Statuses</option>
                            <option value="active" <?php echo ($statusFilter === 'active') ? 'selected' : ''; ?>>Active Customers</option>
                            <option value="blocked" <?php echo ($statusFilter === 'blocked') ? 'selected' : ''; ?>>Suspended / Blocked</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-magenta">Search</button>
                    <?php if (!empty($search) || $statusFilter !== 'all'): ?>
                        <a href="users.php" class="btn btn-subtle">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Users Table -->
            <div class="dashboard-card">
                <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Contact</th>
                                    <th>Delivery Address</th>
                                    <th>Rentals</th>
                                    <th>Total Spend</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th style="text-align: right;">Account Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td>
                                            <div class="table-user-cell">
                                                <div class="user-avatar-circle" style="width: 36px; height: 36px;">
                                                    <?php echo strtoupper(substr($u['full_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <span class="font-weight-bold" style="color: var(--text-primary); display: block;"><?php echo htmlspecialchars($u['full_name']); ?></span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">ID #USR-<?php echo $u['user_id']; ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.88rem;"><?php echo htmlspecialchars($u['email']); ?></span>
                                            <span class="text-muted" style="display: block; font-size: 0.75rem;"><?php echo htmlspecialchars($u['phone']); ?></span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; color: var(--text-secondary); max-width: 200px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?php echo htmlspecialchars($u['address'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?php echo $u['total_rentals']; ?></span> bookings
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta"><?php echo format_currency($u['total_spend']); ?></span>
                                        </td>
                                        <td>
                                            <?php echo get_status_badge($u['status']); ?>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.82rem; color: var(--text-muted);">
                                                <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <form action="users.php" method="POST" style="margin: 0; display: inline-block;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="toggle_user_status">
                                                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">

                                                <?php if ($u['status'] === 'active'): ?>
                                                    <button type="submit" name="new_status" value="blocked" class="btn btn-sm btn-subtle text-danger" onclick="return confirm('Suspend this user account?');" title="Suspend User">
                                                        <i class="fa-solid fa-ban"></i> Suspend
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="new_status" value="active" class="btn btn-sm btn-subtle text-success" title="Reactivate User">
                                                        <i class="fa-solid fa-check"></i> Activate
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="text-align: center; padding: 40px 0;">No registered users found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
