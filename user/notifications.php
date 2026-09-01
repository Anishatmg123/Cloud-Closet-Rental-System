<?php
/**
 * Customer Notifications Feed
 */

$page_title = "My Notifications";
$path_prefix = '../';
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/header.php';

$userId = (int)$_SESSION['user_id'];

// Handle Mark All Read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_type = 'user' AND user_id = :user_id")->execute([':user_id' => $userId]);
    set_flash('success', 'All notifications marked as read.');
    header("Location: notifications.php");
    exit();
}

// Fetch all notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_type = 'user' AND user_id = :user_id ORDER BY created_at DESC");
$stmt->execute([':user_id' => $userId]);
$notifications = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/user_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Updates & Notifications</h1>
                    <p class="page-subtitle-text">Stay up to date with your rental approvals, delivery tracking, and style credits.</p>
                </div>
                <div>
                    <form action="notifications.php" method="POST" style="margin: 0;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="mark_all_read">
                        <button type="submit" class="btn btn-subtle">
                            <i class="fa-solid fa-check-double"></i> Mark All as Read
                        </button>
                    </form>
                </div>
            </div>

            <div class="dashboard-card" style="padding: 20px;">
                <?php if (!empty($notifications)): ?>
                    <div class="activity-stream" style="gap: 12px;">
                        <?php foreach ($notifications as $notif): ?>
                            <div class="notif-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>" style="border-radius: var(--radius-md); padding: 18px;">
                                <div class="notif-icon-circle <?php echo htmlspecialchars($notif['type'] ?? 'info'); ?>" style="width: 42px; height: 42px; font-size: 1.1rem;">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                                <div class="notif-text-wrap">
                                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                                        <span class="notif-item-title" style="font-size: 1rem;"><?php echo htmlspecialchars($notif['title']); ?></span>
                                        <span class="notif-item-time"><?php echo date('M d, Y - g:i A', strtotime($notif['created_at'])); ?></span>
                                    </div>
                                    <p class="notif-item-desc" style="font-size: 0.9rem; margin-top: 4px;"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    <?php if (!empty($notif['link'])): ?>
                                        <a href="../<?php echo htmlspecialchars($notif['link']); ?>" class="text-magenta" style="font-size: 0.82rem; font-weight: 600; margin-top: 6px; display: inline-block;">
                                            View Related Details &rarr;
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 50px 20px;">
                        <i class="fa-regular fa-bell-slash text-muted" style="font-size: 3rem; margin-bottom: 12px;"></i>
                        <h4 style="color: var(--text-primary); margin-bottom: 6px;">No Notifications</h4>
                        <p class="text-muted" style="font-size: 0.92rem;">You're all caught up! Booking updates and alerts will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
