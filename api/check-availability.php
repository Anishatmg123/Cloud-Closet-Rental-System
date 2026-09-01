<?php
/**
 * API: Real-Time Dress Date Availability & Price Calculator
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$dress_id = (int)($_GET['dress_id'] ?? $_POST['dress_id'] ?? 0);
$start_date = trim($_GET['start_date'] ?? $_POST['start_date'] ?? '');
$end_date = trim($_GET['end_date'] ?? $_POST['end_date'] ?? '');

if (!$dress_id || empty($start_date) || empty($end_date)) {
    echo json_encode([
        'success' => false,
        'available' => false,
        'message' => 'Please provide valid start and end dates.'
    ]);
    exit();
}

$today = date('Y-m-d');
if ($start_date < $today) {
    echo json_encode([
        'success' => false,
        'available' => false,
        'message' => 'Rental start date cannot be in the past.'
    ]);
    exit();
}

if ($end_date <= $start_date) {
    echo json_encode([
        'success' => false,
        'available' => false,
        'message' => 'Return date must be after the rental start date.'
    ]);
    exit();
}

try {
    // 1. Fetch dress
    $stmt = $pdo->prepare("SELECT * FROM dresses WHERE dress_id = :dress_id LIMIT 1");
    $stmt->execute([':dress_id' => $dress_id]);
    $dress = $stmt->fetch();

    if (!$dress) {
        echo json_encode([
            'success' => false,
            'available' => false,
            'message' => 'Dress not found.'
        ]);
        exit();
    }

    if ($dress['availability'] === 'unavailable') {
        echo json_encode([
            'success' => true,
            'available' => false,
            'message' => 'This dress is currently marked as unavailable for rent.'
        ]);
        exit();
    }

    // 2. Check collision with existing active/approved/pending rentals
    $isAvailable = is_dress_available($pdo, $dress_id, $start_date, $end_date);

    $days = calculate_rental_days($start_date, $end_date);
    $rentalPrice = (float)$dress['rental_price'];
    $securityDeposit = (float)($dress['security_deposit'] ?? 0);
    $totalAmount = $rentalPrice + $securityDeposit;

    if ($isAvailable) {
        echo json_encode([
            'success' => true,
            'available' => true,
            'days' => $days,
            'rental_price' => $rentalPrice,
            'formatted_rental_price' => format_currency($rentalPrice),
            'security_deposit' => $securityDeposit,
            'formatted_deposit' => format_currency($securityDeposit),
            'total_amount' => $totalAmount,
            'formatted_total' => format_currency($totalAmount),
            'message' => 'Dress is available for these dates!'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'available' => false,
            'message' => 'Selected dates conflict with an existing booking. Please select different dates.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'available' => false,
        'message' => 'Error checking availability: ' . $e->getMessage()
    ]);
}
