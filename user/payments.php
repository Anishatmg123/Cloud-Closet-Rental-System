<?php
/**
 * Customer Payments Ledger & Transaction History
 */

$page_title = "My Payments";
$path_prefix = '../';
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/header.php';

$userId = (int)$_SESSION['user_id'];

// Fetch payments for this user
$stmt = $pdo->prepare("
    SELECT p.*, r.total_days, r.rental_start_date, r.rental_end_date, d.dress_name, d.image
    FROM payments p
    JOIN rentals r ON p.rental_id = r.rental_id
    JOIN dresses d ON r.dress_id = d.dress_id
    WHERE r.user_id = :user_id
    ORDER BY p.payment_date DESC
");
$stmt->execute([':user_id' => $userId]);
$payments = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/user_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Payment Transactions</h1>
                    <p class="page-subtitle-text">Review your rental charges, security deposits, and settlement status.</p>
                </div>
            </div>

            <div class="dashboard-card">
                <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Order Reference</th>
                                    <th>Gown Name</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $pay): ?>
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold" style="font-family: monospace; font-size: 0.88rem; color: var(--text-primary);">
                                                <?php echo htmlspecialchars($pay['transaction_id'] ?? ('TXN-' . $pay['payment_id'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="booking-details.php?id=<?php echo $pay['rental_id']; ?>" class="text-magenta font-weight-bold">
                                                #ORD-<?php echo str_pad($pay['rental_id'], 5, '0', STR_PAD_LEFT); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?php echo htmlspecialchars($pay['dress_name']); ?></span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; color: var(--text-muted);">
                                                <?php echo date('M d, Y - g:i A', strtotime($pay['payment_date'] ?? 'now')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-weight: 700; font-size: 0.82rem; text-transform: uppercase;">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $pay['payment_method'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta" style="font-size: 1.05rem;">
                                                <?php echo format_currency($pay['amount']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo get_status_badge($pay['payment_status']); ?>
                                        </td>
                                        <td>
                                            <a href="booking-details.php?id=<?php echo $pay['rental_id']; ?>" class="btn btn-sm btn-subtle">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 40px 20px;">
                        <i class="fa-solid fa-credit-card text-muted" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
                        <h4 style="color: var(--text-primary); margin-bottom: 6px;">No Payment Records Found</h4>
                        <p class="text-muted" style="font-size: 0.92rem;">Your transaction receipts will appear here automatically once you book a gown.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
