<?php
/**
 * Cloud Closet - Global Helper Functions
 */

if (!function_exists('format_currency')) {
    function format_currency($amount) {
        return '$' . number_format((float)$amount, 2);
    }
}

if (!function_exists('format_date')) {
    function format_date($date_str, $format = 'M d, Y') {
        if (empty($date_str)) return 'N/A';
        $time = strtotime($date_str);
        return $time ? date($format, $time) : 'N/A';
    }
}

if (!function_exists('get_status_badge')) {
    function get_status_badge($status) {
        $status = strtolower(trim($status ?? ''));
        $badgeClass = 'badge-default';
        $label = ucfirst($status);

        switch ($status) {
            case 'available':
            case 'active':
            case 'approved':
            case 'completed':
            case 'paid':
            case 'successful':
                $badgeClass = 'badge-success';
                break;

            case 'pending':
            case 'waiting':
            case 'in transit':
            case 'in_transit':
            case 'upcoming':
            case 'progress':
                $badgeClass = 'badge-warning';
                $label = ucwords(str_replace('_', ' ', $status));
                break;

            case 'returned':
                $badgeClass = 'badge-info';
                break;

            case 'cancelled':
            case 'rejected':
            case 'blocked':
            case 'failed':
            case 'unavailable':
            case 'refunded':
                $badgeClass = 'badge-danger';
                break;

            case 'low stock':
            case 'low_stock':
                $badgeClass = 'badge-secondary';
                $label = 'Low Stock';
                break;
        }

        return '<span class="status-badge ' . $badgeClass . '">' . htmlspecialchars($label) . '</span>';
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('calculate_rental_days')) {
    function calculate_rental_days($start_date, $end_date) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        $days = (int)$interval->format('%r%a');
        return max(1, $days);
    }
}

if (!function_exists('is_dress_available')) {
    /**
     * Checks if a dress is free of conflicting active/approved bookings during given dates.
     */
    function is_dress_available($pdo, $dress_id, $start_date, $end_date, $exclude_rental_id = null) {
        // 1. Check dress baseline availability flag
        $stmt = $pdo->prepare("SELECT availability FROM dresses WHERE dress_id = :dress_id LIMIT 1");
        $stmt->execute([':dress_id' => $dress_id]);
        $dress = $stmt->fetch();
        if (!$dress || $dress['availability'] === 'unavailable') {
            return false;
        }

        // 2. Check overlapping bookings (approved, active, or pending)
        $sql = "SELECT COUNT(*) FROM rentals 
                WHERE dress_id = :dress_id 
                  AND rental_status IN ('approved', 'active', 'pending')
                  AND (
                      (rental_start_date <= :end_date AND rental_end_date >= :start_date)
                  )";
        
        $params = [
            ':dress_id' => $dress_id,
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ];

        if ($exclude_rental_id) {
            $sql .= " AND rental_id != :exclude_rental_id";
            $params[':exclude_rental_id'] = $exclude_rental_id;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $conflictCount = (int)$stmt->fetchColumn();

        return ($conflictCount === 0);
    }
}

if (!function_exists('create_notification')) {
    function create_notification($pdo, $user_type, $user_id, $title, $message, $type = 'info', $link = null) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_type, user_id, title, message, type, is_read, link)
                VALUES (:user_type, :user_id, :title, :message, :type, 0, :link)
            ");
            return $stmt->execute([
                ':user_type' => $user_type,
                ':user_id' => $user_id,
                ':title' => $title,
                ':message' => $message,
                ':type' => $type,
                ':link' => $link
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('get_unread_notifications_count')) {
    function get_unread_notifications_count($pdo, $user_type, $user_id) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_type = :user_type AND user_id = :user_id AND is_read = 0");
            $stmt->execute([':user_type' => $user_type, ':user_id' => $user_id]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('get_user_favorites_ids')) {
    function get_user_favorites_ids($pdo, $user_id) {
        if (!$user_id) return [];
        try {
            $stmt = $pdo->prepare("SELECT dress_id FROM favorites WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}
