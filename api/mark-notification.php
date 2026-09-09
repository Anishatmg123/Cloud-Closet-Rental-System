<?php
/**
 * API: Mark Notifications As Read
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$role = 'user';
$userId = 0;

if (isset($_SESSION['vendor_id'])) {
    $role = 'vendor';
    $userId = $_SESSION['vendor_id'];
} elseif (isset($_SESSION['user_id'])) {
    $role = 'user';
    $userId = $_SESSION['user_id'];
}

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$notifId = (int)($_POST['notification_id'] ?? 0);

try {
    if ($notifId > 0) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = :id AND user_type = :role AND user_id = :user_id");
        $stmt->execute([':id' => $notifId, ':role' => $role, ':user_id' => $userId]);
    } else {
        // Mark all as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_type = :role AND user_id = :user_id");
        $stmt->execute([':role' => $role, ':user_id' => $userId]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
