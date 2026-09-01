<?php
/**
 * API: Wishlist Favorite Toggle
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'status' => 'unauthorized', 'message' => 'Please log in.']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$dress_id = (int)($_POST['dress_id'] ?? 0);

if (!$dress_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid dress ID.']);
    exit();
}

try {
    // Check if already in favorites
    $stmt = $pdo->prepare("SELECT favorite_id FROM favorites WHERE user_id = :user_id AND dress_id = :dress_id LIMIT 1");
    $stmt->execute([':user_id' => $user_id, ':dress_id' => $dress_id]);
    $fav = $stmt->fetch();

    if ($fav) {
        // Remove
        $del = $pdo->prepare("DELETE FROM favorites WHERE user_id = :user_id AND dress_id = :dress_id");
        $del->execute([':user_id' => $user_id, ':dress_id' => $dress_id]);
        $action = 'removed';
    } else {
        // Add
        $ins = $pdo->prepare("INSERT INTO favorites (user_id, dress_id) VALUES (:user_id, :dress_id)");
        $ins->execute([':user_id' => $user_id, ':dress_id' => $dress_id]);
        $action = 'added';
    }

    // Count total favorites
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :user_id");
    $countStmt->execute([':user_id' => $user_id]);
    $total = (int)$countStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'total_favorites' => $total
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
