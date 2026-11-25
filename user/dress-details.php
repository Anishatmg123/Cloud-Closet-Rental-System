<?php
/**
 * Dress Details & Interactive Booking Flow
 */

$page_title = "Dress Details";
$path_prefix = '../';
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/header.php';

$userId = (int)$_SESSION['user_id'];
$dressId = (int)($_GET['id'] ?? 0);

if (!$dressId) {
    header("Location: dresses.php");
    exit();
}

// Fetch Dress Details with Category & Vendor
$stmt = $pdo->prepare("
    SELECT d.*, c.category_name, v.business_name, v.full_name as vendor_owner, v.phone as vendor_phone
    FROM dresses d
    LEFT JOIN categories c ON d.category_id = c.category_id
    LEFT JOIN vendors v ON d.vendor_id = v.vendor_id
    WHERE d.dress_id = :dress_id
    LIMIT 1
");
$stmt->execute([':dress_id' => $dressId]);
$dress = $stmt->fetch();

if (!$dress) {
    set_flash('error', 'The requested dress was not found.');
    header("Location: dresses.php");
    exit();
}

$errors = [];
$bookingSuccess = false;

// Handle Booking Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_booking') {
    $startDate = trim($_POST['rental_start_date'] ?? '');
    $endDate = trim($_POST['rental_end_date'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'cash_on_delivery');
    $esewaId = trim($_POST['esewa_id'] ?? '');
    $esewaPin = trim($_POST['esewa_pin'] ?? '');
    $today = date('Y-m-d');

    if (empty($startDate) || empty($endDate)) {
        $errors['general'] = "Please choose both rental start date and return date.";
    } elseif ($startDate < $today) {
        $errors['general'] = "Rental start date cannot be in the past.";
    } elseif ($endDate <= $startDate) {
        $errors['general'] = "Return date must be after the rental start date.";
    } elseif ($paymentMethod === 'esewa' && (empty($esewaId) || empty($esewaPin))) {
        $errors['general'] = "Please enter your eSewa ID (Mobile/Email) and MPIN/Password to complete the payment.";
    } else {
        // Server-side check for overlapping bookings
        $isAvailable = is_dress_available($pdo, $dressId, $startDate, $endDate);
        if (!$isAvailable) {
            $errors['general'] = "Sorry, this dress is already booked for the selected dates. Please choose another date range.";
        } else {
            try {
                $days = calculate_rental_days($startDate, $endDate);
                $rentalPrice = (float)$dress['rental_price'];
                $securityDeposit = (float)($dress['security_deposit'] ?? 0);
                $totalAmount = $rentalPrice + $securityDeposit;

                // 1. Insert Rental Record
                $rentalStmt = $pdo->prepare("
                    INSERT INTO rentals (user_id, dress_id, rental_start_date, rental_end_date, total_days, rental_amount, security_deposit, total_amount, rental_status, request_date)
                    VALUES (:user_id, :dress_id, :start_date, :end_date, :days, :rental_amount, :deposit, :total_amount, 'pending', NOW())
                ");
                $rentalStmt->execute([
                    ':user_id' => $userId,
                    ':dress_id' => $dressId,
                    ':start_date' => $startDate,
                    ':end_date' => $endDate,
                    ':days' => $days,
                    ':rental_amount' => $rentalPrice,
                    ':deposit' => $securityDeposit,
                    ':total_amount' => $totalAmount
                ]);
                $newRentalId = $pdo->lastInsertId();

                // 2. Insert Payment Record (Mock / Internal Gateway)
                $paymentStatus = ($paymentMethod === 'esewa') ? 'paid' : 'pending';
                $txnId = ($paymentMethod === 'esewa') ? 'TXN_ESEWA_' . rand(1000000, 9999999) : 'TXN_COD_' . rand(1000000, 9999999);

                $payStmt = $pdo->prepare("
                    INSERT INTO payments (rental_id, payment_method, amount, payment_status, transaction_id, payment_date)
                    VALUES (:rental_id, :method, :amount, :status, :txn, NOW())
                ");
                $payStmt->execute([
                    ':rental_id' => $newRentalId,
                    ':method' => $paymentMethod,
                    ':amount' => $totalAmount,
                    ':status' => $paymentStatus,
                    ':txn' => $txnId
                ]);

                // 3. Dispatch Notifications
                // Customer notification
                create_notification(
                    $pdo, 
                    'user', 
                    $userId, 
                    'Booking Submitted!', 
                    "Your booking request for {$dress['dress_name']} (#ORD-" . str_pad($newRentalId, 5, '0', STR_PAD_LEFT) . ") has been received.", 
                    'success', 
                    'user/booking-details.php?id=' . $newRentalId
                );

                // Vendor notification
                if (!empty($dress['vendor_id'])) {
                    create_notification(
                        $pdo, 
                        'vendor', 
                        $dress['vendor_id'], 
                        'New Rental Request', 
                        "Customer {$current_user['full_name']} requested to rent {$dress['dress_name']}.", 
                        'info', 
                        'vendor/bookings.php'
                    );
                }

                set_flash('success', "Booking request confirmed! Your order reference is #ORD-" . str_pad($newRentalId, 5, '0', STR_PAD_LEFT));
                header("Location: booking-details.php?id=" . $newRentalId);
                exit();

            } catch (PDOException $e) {
                $errors['general'] = "Booking failed: " . $e->getMessage();
            }
        }
    }
}

$userFavs = get_user_favorites_ids($pdo, $userId);
$isFav = in_array($dressId, $userFavs);
$imgSrc = get_dress_image_url($dress['image'], $dress['dress_name'], '../');
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/user_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <!-- Breadcrumb Navigation -->
            <div style="margin-bottom: 24px; font-size: 0.88rem; color: var(--text-muted);">
                <a href="dresses.php" class="text-magenta">&larr; Back to Catalog</a> / 
                <span><?php echo htmlspecialchars($dress['category_name'] ?? 'Dress'); ?></span> / 
                <span class="text-primary font-weight-bold"><?php echo htmlspecialchars($dress['dress_name']); ?></span>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                    <div class="alert-content"><?php echo htmlspecialchars($errors['general']); ?></div>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;" class="dress-detail-grid">
                <!-- Left: Large Image Gallery View -->
                <div class="dress-gallery-wrap">
                    <div class="dashboard-card" style="padding: 16px; position: relative;">
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                             alt="<?php echo htmlspecialchars($dress['dress_name']); ?>" 
                             id="main-dress-image"
                             style="width: 100%; height: 540px; object-fit: cover; border-radius: var(--radius-md);">

                        <button type="button" 
                                class="dress-fav-btn <?php echo $isFav ? 'active' : ''; ?>" 
                                onclick="toggleFavorite(<?php echo $dressId; ?>, this)"
                                style="top: 28px; right: 28px; width: 46px; height: 46px; font-size: 1.25rem;">
                            <i class="<?php echo $isFav ? 'fa-solid text-magenta' : 'fa-regular'; ?> fa-heart"></i>
                        </button>
                    </div>
                </div>

                <!-- Right: Information & Booking Card -->
                <div class="dress-info-booking-wrap">
                    <div class="dashboard-card">
                        <span class="section-pretitle"><?php echo htmlspecialchars($dress['category_name'] ?? 'Luxury Couture'); ?></span>
                        <h1 style="font-family: var(--font-heading); font-size: 2.2rem; margin: 6px 0 16px; color: var(--text-primary);">
                            <?php echo htmlspecialchars($dress['dress_name']); ?>
                        </h1>

                        <!-- Pricing Headline -->
                        <div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid rgba(15, 23, 42, 0.06);">
                            <span class="text-magenta" style="font-size: 2.2rem; font-weight: 800;"><?php echo format_currency($dress['rental_price']); ?></span>
                            <span class="text-muted" style="font-size: 0.95rem;">/ 4-day standard rental</span>
                            <?php if ($dress['security_deposit'] > 0): ?>
                                <span class="badge-secondary status-badge" style="margin-left: auto;">
                                    +<?php echo format_currency($dress['security_deposit']); ?> refundable deposit
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Specifications Grid -->
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px;">
                            <div class="stat-box" style="padding: 12px;">
                                <span class="stat-label">Size</span>
                                <span style="font-weight: 700; font-size: 1.1rem; color: var(--text-primary);"><?php echo htmlspecialchars($dress['size'] ?? 'M'); ?></span>
                            </div>
                            <div class="stat-box" style="padding: 12px;">
                                <span class="stat-label">Color</span>
                                <span style="font-weight: 700; font-size: 1.1rem; color: var(--text-primary);"><?php echo htmlspecialchars($dress['color'] ?? 'Custom'); ?></span>
                            </div>
                            <div class="stat-box" style="padding: 12px;">
                                <span class="stat-label">Availability</span>
                                <span style="font-weight: 700; font-size: 1rem; color: var(--status-success-text); text-transform: capitalize;">
                                    <?php echo htmlspecialchars($dress['availability']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div style="margin-bottom: 26px;">
                            <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 8px;">Garment Overview</h4>
                            <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.6;">
                                <?php echo nl2br(htmlspecialchars($dress['description'] ?? 'An exclusive designer gown tailored from premium fabrics. Hand-cleaned and sanitised following eco-friendly standards prior to every rental delivery.')); ?>
                            </p>
                        </div>

                        <!-- Boutique / Vendor details -->
                        <?php if (!empty($dress['business_name'])): ?>
                            <div style="display: flex; align-items: center; gap: 12px; padding: 14px; background: #f8fafc; border-radius: var(--radius-md); margin-bottom: 26px;">
                                <div class="user-avatar-circle vendor-avatar" style="width: 38px; height: 38px;">
                                    <i class="fa-solid fa-shop"></i>
                                </div>
                                <div>
                                    <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Curated by Vendor Boutique:</span>
                                    <span class="font-weight-bold" style="font-size: 0.92rem; color: var(--text-primary);"><?php echo htmlspecialchars($dress['business_name']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Interactive Rental Date Selection Form -->
                        <div style="background: var(--color-pink-soft); border: 1px solid rgba(225, 29, 72, 0.15); border-radius: var(--radius-md); padding: 24px;">
                            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-bottom: 16px;">
                                <i class="fa-solid fa-calendar-days text-magenta"></i> Select Rental Dates
                            </h3>

                            <form action="dress-details.php?id=<?php echo $dressId; ?>" method="POST" id="booking-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="create_booking">

                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label for="rental_start_date" class="form-label">Rental Start Date</label>
                                        <input type="date" 
                                               id="rental_start_date" 
                                               name="rental_start_date" 
                                               class="form-input no-icon" 
                                               min="<?php echo date('Y-m-d'); ?>" 
                                               required 
                                               onchange="checkLiveAvailability()">
                                    </div>
                                    <div class="form-group">
                                        <label for="rental_end_date" class="form-label">Return Date</label>
                                        <input type="date" 
                                               id="rental_end_date" 
                                               name="rental_end_date" 
                                               class="form-input no-icon" 
                                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                                               required 
                                               onchange="checkLiveAvailability()">
                                    </div>
                                </div>

                                <!-- Live Availability Status Box -->
                                <div id="availability-status-box" style="margin-bottom: 16px; display: none;"></div>

                                <!-- Payment Method Choice -->
                                <div class="form-group">
                                    <label class="form-label">Payment Method</label>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                        <label id="label-cod" style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; background: #ffffff; border-radius: var(--radius-md); border: 2px solid #60a5fa; cursor: pointer; transition: all 0.2s ease;">
                                            <input type="radio" name="payment_method" value="cash_on_delivery" id="pay-cod" checked onchange="togglePaymentBoxes()">
                                            <span style="font-weight: 600; font-size: 0.9rem; color: #1e293b;">
                                                <i class="fa-solid fa-truck" style="color: #2563eb; margin-right: 4px;"></i> Cash on Delivery
                                            </span>
                                        </label>
                                        <label id="label-esewa" style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; background: #ffffff; border-radius: var(--radius-md); border: 2px solid rgba(15, 23, 42, 0.1); cursor: pointer; transition: all 0.2s ease;">
                                            <input type="radio" name="payment_method" value="esewa" id="pay-esewa" onchange="togglePaymentBoxes()">
                                            <span style="font-weight: 600; font-size: 0.9rem; color: #1e293b;">
                                                <i class="fa-solid fa-wallet" style="color: #60bb46; margin-right: 4px;"></i> eSewa
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <!-- eSewa Login / Payment Box (shown only when eSewa is selected) -->
                                <div id="esewa-login-box" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #bbf7d0;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="background: #60bb46; color: #ffffff; font-weight: 900; font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; letter-spacing: 0.5px;">eSewa</span>
                                            <span style="font-weight: 700; font-size: 0.92rem; color: #166534;">Pay via eSewa Account</span>
                                        </div>
                                        <span style="font-size: 0.75rem; color: #15803d; background: #dcfce7; padding: 2px 8px; border-radius: 12px; font-weight: 600;">Secure Portal</span>
                                    </div>

                                    <div style="margin-bottom: 12px;">
                                        <label for="esewa_id" style="display: block; font-size: 0.82rem; font-weight: 600; color: #166534; margin-bottom: 4px;">eSewa ID (Mobile Number / Email)</label>
                                        <div style="position: relative;">
                                            <input type="text" 
                                                   id="esewa_id" 
                                                   name="esewa_id" 
                                                   class="form-input no-icon" 
                                                   placeholder="e.g. 98XXXXXXXX or email@domain.com"
                                                   style="padding: 10px 12px; font-size: 0.9rem; background: #ffffff; border: 1px solid #86efac; border-radius: 6px; width: 100%;">
                                        </div>
                                    </div>

                                    <div style="margin-bottom: 8px;">
                                        <label for="esewa_pin" style="display: block; font-size: 0.82rem; font-weight: 600; color: #166534; margin-bottom: 4px;">MPIN / Password</label>
                                        <div style="position: relative;">
                                            <input type="password" 
                                                   id="esewa_pin" 
                                                   name="esewa_pin" 
                                                   class="form-input no-icon" 
                                                   placeholder="Enter eSewa MPIN / Password"
                                                   maxlength="20"
                                                   style="padding: 10px 12px; font-size: 0.9rem; background: #ffffff; border: 1px solid #86efac; border-radius: 6px; width: 100%;">
                                        </div>
                                    </div>
                                    <small style="color: #15803d; font-size: 0.75rem; display: flex; align-items: center; gap: 4px; margin-top: 6px;">
                                        <i class="fa-solid fa-lock"></i> Your login details are securely encrypted and verified.
                                    </small>
                                </div>

                                <!-- Summary Calculation Breakdown -->
                                <div id="pricing-breakdown" style="background: #ffffff; border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 6px;">
                                        <span class="text-muted">Rental Fee:</span>
                                        <span class="font-weight-bold" id="breakdown-rental-fee"><?php echo format_currency($dress['rental_price']); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 10px;">
                                        <span class="text-muted">Security Deposit:</span>
                                        <span class="font-weight-bold" id="breakdown-deposit"><?php echo format_currency($dress['security_deposit']); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 800; padding-top: 10px; border-top: 1px dashed rgba(15, 23, 42, 0.1);">
                                        <span>Total Amount:</span>
                                        <span class="text-magenta" id="breakdown-total"><?php echo format_currency($dress['rental_price'] + $dress['security_deposit']); ?></span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-magenta btn-block btn-lg" id="submit-rental-btn">
                                    <i class="fa-solid fa-check"></i> Confirm & Book Dress
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Toggle eSewa Login box based on selected payment method
function togglePaymentBoxes() {
    const isEsewa = document.getElementById('pay-esewa').checked;
    const esewaBox = document.getElementById('esewa-login-box');
    const esewaIdInput = document.getElementById('esewa_id');
    const esewaPinInput = document.getElementById('esewa_pin');
    const labelCod = document.getElementById('label-cod');
    const labelEsewa = document.getElementById('label-esewa');

    if (isEsewa) {
        esewaBox.style.display = 'block';
        esewaIdInput.required = true;
        esewaPinInput.required = true;
        labelEsewa.style.borderColor = '#60bb46';
        labelCod.style.borderColor = 'rgba(15, 23, 42, 0.1)';
    } else {
        esewaBox.style.display = 'none';
        esewaIdInput.required = false;
        esewaPinInput.required = false;
        labelCod.style.borderColor = '#60a5fa';
        labelEsewa.style.borderColor = 'rgba(15, 23, 42, 0.1)';
    }
}

// Initial state on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePaymentBoxes();
});

// Real-time Date Availability Checker & Total Calculator
async function checkLiveAvailability() {
    const startInput = document.getElementById('rental_start_date');
    const endInput = document.getElementById('rental_end_date');
    const statusBox = document.getElementById('availability-status-box');
    const submitBtn = document.getElementById('submit-rental-btn');

    if (!startInput.value || !endInput.value) return;

    try {
        const response = await fetch(`../api/check-availability.php?dress_id=<?php echo $dressId; ?>&start_date=${startInput.value}&end_date=${endInput.value}`);
        const data = await response.json();

        statusBox.style.display = 'block';

        if (data.success && data.available) {
            statusBox.innerHTML = `
                <div class="alert alert-success" style="margin-bottom: 0;">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>${data.message} (${data.days} days rental)</div>
                </div>
            `;
            document.getElementById('breakdown-rental-fee').textContent = data.formatted_rental_price;
            document.getElementById('breakdown-deposit').textContent = data.formatted_deposit;
            document.getElementById('breakdown-total').textContent = data.formatted_total;
            submitBtn.disabled = false;
        } else {
            statusBox.innerHTML = `
                <div class="alert alert-error" style="margin-bottom: 0;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>${data.message || 'Selected dates are unavailable.'}</div>
                </div>
            `;
            submitBtn.disabled = true;
        }
    } catch (e) {
        console.error("Availability check failed", e);
    }
}
</script>

<style>
@media (max-width: 992px) {
    .dress-detail-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
