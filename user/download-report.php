<?php
/**
 * Customer Rental Report CSV Exporter
 */

require_once __DIR__ . '/../includes/user_auth.php';

$userId = (int)$_SESSION['user_id'];

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Cloud_Closet_Rental_Report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Header Row
fputcsv($output, ['Order ID', 'Dress Name', 'Category', 'Rental Start', 'Rental Return', 'Duration (Days)', 'Rental Fee ($)', 'Security Deposit ($)', 'Total Paid ($)', 'Payment Method', 'Booking Status', 'Booking Date']);

try {
    $stmt = $pdo->prepare("
        SELECT r.rental_id, d.dress_name, c.category_name, r.rental_start_date, r.rental_end_date, 
               r.total_days, r.rental_amount, r.security_deposit, r.total_amount,
               p.payment_method, r.rental_status, r.request_date
        FROM rentals r
        JOIN dresses d ON r.dress_id = d.dress_id
        LEFT JOIN categories c ON d.category_id = c.category_id
        LEFT JOIN payments p ON r.rental_id = p.rental_id
        WHERE r.user_id = :user_id
        ORDER BY r.request_date DESC
    ");
    $stmt->execute([':user_id' => $userId]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            '#ORD-' . str_pad($row['rental_id'], 5, '0', STR_PAD_LEFT),
            $row['dress_name'],
            $row['category_name'] ?? 'Couture',
            $row['rental_start_date'],
            $row['rental_end_date'],
            $row['total_days'],
            number_format($row['rental_amount'], 2),
            number_format($row['security_deposit'], 2),
            number_format($row['total_amount'], 2),
            strtoupper(str_replace('_', ' ', $row['payment_method'] ?? 'COD')),
            ucfirst($row['rental_status']),
            $row['request_date']
        ]);
    }
} catch (Exception $e) {}

fclose($output);
exit();
