<?php
/**
 * Vendor Delete Dress Action
 */

require_once __DIR__ . '/../includes/vendor_auth.php';

$vendorId = (int)$_SESSION['vendor_id'];
$dressId = (int)($_GET['id'] ?? 0);

if (!$dressId) {
    header("Location: dresses.php");
    exit();
}

try {
    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM dresses WHERE dress_id = :dress_id AND vendor_id = :vendor_id LIMIT 1");
    $stmt->execute([':dress_id' => $dressId, ':vendor_id' => $vendorId]);
    $dress = $stmt->fetch();

    if (!$dress) {
        set_flash('error', 'Dress not found or access denied.');
        header("Location: dresses.php");
        exit();
    }

    // Check if dress has historical bookings
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE dress_id = :dress_id");
    $checkStmt->execute([':dress_id' => $dressId]);
    $rentalCount = (int)$checkStmt->fetchColumn();

    if ($rentalCount > 0) {
        // Safe archive: mark unavailable
        $pdo->prepare("UPDATE dresses SET availability = 'unavailable' WHERE dress_id = :dress_id")->execute([':dress_id' => $dressId]);
        set_flash('info', "Dress '{$dress['dress_name']}' has active/historical rental records, so it was marked as unavailable instead of deleting.");
    } else {
        // Safe delete
        $pdo->prepare("DELETE FROM favorites WHERE dress_id = :dress_id")->execute([':dress_id' => $dressId]);
        $pdo->prepare("DELETE FROM dresses WHERE dress_id = :dress_id")->execute([':dress_id' => $dressId]);
        set_flash('success', "Dress '{$dress['dress_name']}' was deleted successfully.");
    }

} catch (Exception $e) {
    set_flash('error', "Error deleting dress: " . $e->getMessage());
}

header("Location: dresses.php");
exit();
