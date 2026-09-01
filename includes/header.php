<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$prefix = isset($path_prefix) ? $path_prefix : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cloud Closet - Luxury designer dress rental platform. Experience high-fashion sustainability for galas, weddings, and special events.">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " | Cloud Closet" : "Cloud Closet | Luxury Dress Rental"; ?></title>
    
    <!-- Google Fonts: Playfair Display (Editorial Serif) & Plus Jakarta Sans / Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Main Global & Dashboard Stylesheets -->
    <link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/style.css?v=2.0">
    <link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/dashboard.css?v=2.0">
    <link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/auth.css?v=2.0">

    <!-- Chart.js for Visual Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="<?php echo isset($body_class) ? htmlspecialchars($body_class) : ''; ?>">
