<?php
/**
 * Admin Transactions Ledger
 */

$page_title = "Platform Transactions";
$path_prefix = '../';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/header.php';

// Handle Manual Payment Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_payment_status') {
    $paymentId = (int)($_POST['payment_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? '');

    if ($paymentId && in_array($newStatus, ['paid', 'pending', 'failed', 'refunded'])) {
        $stmt = $pdo->prepare("UPDATE payments SET payment_status = :status WHERE payment_id = :payment_id");
        $stmt->execute([':status' => $newStatus, ':payment_id' => $paymentId]);
        set_flash('success', "Payment status updated to " . ucfirst($newStatus) . ".");
        header("Location: transactions.php");
        exit();
    }
}

// Fetch transactions
$statusFilter = trim($_GET['status'] ?? 'all');
$sql = "
    SELECT p.*, r.total_days, r.rental_status, u.full_name as customer_name, u.email as customer_email, d.dress_name
    FROM payments p
    JOIN rentals r ON p.rental_id = r.rental_id
    JOIN users u ON r.user_id = u.user_id
    JOIN dresses d ON r.dress_id = d.dress_id
    WHERE 1=1
";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND p.payment_status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY p.payment_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Total platform revenue
$totalPaid = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'paid'")->fetchColumn();
$totalPending = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'pending'")->fetchColumn();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Financial Ledger & Transactions</h1>
                    <p class="page-subtitle-text">Review payment gateway logs, cash-on-delivery settlements, and refund history.</p>
                </div>
            </div>

            <!-- 2 Summary Cards -->
            <div class="stats-grid-4" style="grid-template-columns: 1fr 1fr; margin-bottom: 24px;">
                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">SETTLED PAYMENTS</span>
                        <div class="stat-icon-circle emerald"><i class="fa-solid fa-circle-check"></i></div>
                    </div>
                    <div class="stat-card-value"><?php echo format_currency($totalPaid); ?></div>
                    <div class="stat-card-trend"><span class="trend-up">Completed transactions</span></div>
                </div>

                <div class="stat-card-widget">
                    <div class="stat-card-top">
                        <span class="stat-card-label">PENDING SETTLEMENTS</span>
                        <div class="stat-icon-circle amber"><i class="fa-solid fa-clock"></i></div>
                    </div>
                    <div class="stat-card-value"><?php echo format_currency($totalPending); ?></div>
                    <div class="stat-card-trend"><span>Awaiting COD delivery collection</span></div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="dashboard-card">
                <div class="card-header-flex">
                    <h3 class="card-title-text">All Payment Records</h3>
                    <div class="chart-toggle-group" style="background: transparent;">
                        <a href="transactions.php?status=all" class="chart-toggle-btn <?php echo ($statusFilter === 'all') ? 'active' : ''; ?>">All</a>
                        <a href="transactions.php?status=paid" class="chart-toggle-btn <?php echo ($statusFilter === 'paid') ? 'active' : ''; ?>">Paid</a>
                        <a href="transactions.php?status=pending" class="chart-toggle-btn <?php echo ($statusFilter === 'pending') ? 'active' : ''; ?>">Pending</a>
                        <a href="transactions.php?status=refunded" class="chart-toggle-btn <?php echo ($statusFilter === 'refunded') ? 'active' : ''; ?>">Refunded</a>
                    </div>
                </div>

                <?php if (!empty($transactions)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Transaction Ref</th>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Gown Details</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Update Settlement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold" style="font-family: monospace; font-size: 0.85rem; color: var(--text-primary);">
                                                <?php echo htmlspecialchars($t['transaction_id'] ?? ('TXN-' . $t['payment_id'])); ?>
                                            </span>
                                            <span class="text-muted" style="display: block; font-size: 0.72rem;"><?php echo date('M d, Y - g:i A', strtotime($t['payment_date'] ?? 'now')); ?></span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">#ORD-<?php echo str_pad($t['rental_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold" style="color: var(--text-primary); display: block;"><?php echo htmlspecialchars($t['customer_name']); ?></span>
                                            <span class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($t['customer_email']); ?></span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?php echo htmlspecialchars($t['dress_name']); ?></span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase;">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $t['payment_method'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta" style="font-size: 1.05rem;">
                                                <?php echo format_currency($t['amount']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo get_status_badge($t['payment_status']); ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <form action="transactions.php" method="POST" style="margin: 0; display: inline-flex; gap: 6px;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="update_payment_status">
                                                <input type="hidden" name="payment_id" value="<?php echo $t['payment_id']; ?>">

                                                <?php if ($t['payment_status'] === 'pending'): ?>
                                                    <button type="submit" name="new_status" value="paid" class="btn btn-sm btn-subtle text-success" title="Mark as Settled / Paid">
                                                        <i class="fa-solid fa-check"></i> Mark Paid
                                                    </button>
                                                <?php elseif ($t['payment_status'] === 'paid'): ?>
                                                    <button type="submit" name="new_status" value="refunded" class="btn btn-sm btn-subtle text-danger" title="Issue Refund" onclick="return confirm('Process refund for this payment?');">
                                                        <i class="fa-solid fa-rotate-left"></i> Refund
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted" style="font-size: 0.78rem;">Settled</span>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="text-align: center; padding: 40px 0;">No transaction records found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
