<?php
/**
 * Admin Vendor Management & Boutique Approvals
 */

$page_title = "Manage Vendors";
$path_prefix = '../';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/header.php';

// Handle Vendor Approval / Suspension
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_vendor_status') {
    $vendorId = (int)($_POST['vendor_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? '');

    if ($vendorId && in_array($newStatus, ['approved', 'blocked', 'pending'])) {
        $stmt = $pdo->prepare("UPDATE vendors SET status = :status WHERE vendor_id = :vendor_id");
        $stmt->execute([':status' => $newStatus, ':vendor_id' => $vendorId]);

        if ($newStatus === 'approved') {
            create_notification($pdo, 'vendor', $vendorId, 'Boutique Approved!', 'Your vendor store application has been approved. You can now list dresses and receive rental bookings.', 'success', 'vendor/dashboard.php');
        }

        set_flash('success', "Vendor boutique status updated to " . ucfirst($newStatus) . ".");
        header("Location: vendors.php");
        exit();
    }
}

// Search & Filter
$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');

$sql = "
    SELECT v.*,
           (SELECT COUNT(*) FROM dresses d WHERE d.vendor_id = v.vendor_id) as total_dresses,
           (SELECT COUNT(*) FROM rentals r JOIN dresses d ON r.dress_id = d.dress_id WHERE d.vendor_id = v.vendor_id AND r.rental_status = 'active') as active_rentals,
           (SELECT COALESCE(SUM(rental_amount), 0) FROM rentals r JOIN dresses d ON r.dress_id = d.dress_id WHERE d.vendor_id = v.vendor_id AND r.rental_status IN ('approved', 'active', 'returned')) as total_revenue
    FROM vendors v
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (v.business_name LIKE :search OR v.full_name LIKE :search OR v.email LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($statusFilter !== 'all') {
    $sql .= " AND v.status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY v.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vendors = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">Vendor Boutiques & Approvals</h1>
                    <p class="page-subtitle-text">Review boutique partner applications, verify inventory authenticity, and manage store statuses.</p>
                </div>
            </div>

            <!-- Status Filter Tabs -->
            <div class="dashboard-card" style="padding: 14px 20px; margin-bottom: 24px;">
                <div class="chart-toggle-group" style="background: transparent; flex-wrap: wrap; gap: 8px;">
                    <a href="vendors.php?status=all" class="chart-toggle-btn <?php echo ($statusFilter === 'all') ? 'active' : ''; ?>">All Boutiques</a>
                    <a href="vendors.php?status=pending" class="chart-toggle-btn <?php echo ($statusFilter === 'pending') ? 'active' : ''; ?>">Pending Approvals</a>
                    <a href="vendors.php?status=approved" class="chart-toggle-btn <?php echo ($statusFilter === 'approved') ? 'active' : ''; ?>">Verified Partners</a>
                    <a href="vendors.php?status=blocked" class="chart-toggle-btn <?php echo ($statusFilter === 'blocked') ? 'active' : ''; ?>">Suspended</a>
                </div>
            </div>

            <!-- Vendors Table -->
            <div class="dashboard-card">
                <?php if (!empty($vendors)): ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Boutique Store</th>
                                    <th>Contact Owner</th>
                                    <th>Store Location</th>
                                    <th>Dresses</th>
                                    <th>Active Rentals</th>
                                    <th>Total Revenue</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Approval Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vendors as $v): ?>
                                    <tr>
                                        <td>
                                            <div class="table-user-cell">
                                                <div class="user-avatar-circle vendor-avatar" style="width: 38px; height: 38px;">
                                                    <i class="fa-solid fa-shop"></i>
                                                </div>
                                                <div>
                                                    <span class="font-weight-bold" style="color: var(--text-primary); display: block;"><?php echo htmlspecialchars($v['business_name'] ?? 'Boutique'); ?></span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">ID #VND-<?php echo $v['vendor_id']; ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?php echo htmlspecialchars($v['full_name']); ?></span>
                                            <span class="text-muted" style="display: block; font-size: 0.75rem;"><?php echo htmlspecialchars($v['email']); ?> &bull; <?php echo htmlspecialchars($v['phone']); ?></span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($v['address'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?php echo $v['total_dresses']; ?></span> pieces
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?php echo $v['active_rentals']; ?></span> active
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-magenta"><?php echo format_currency($v['total_revenue']); ?></span>
                                        </td>
                                        <td>
                                            <?php echo get_status_badge($v['status']); ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <form action="vendors.php" method="POST" style="margin: 0; display: inline-flex; gap: 6px;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="update_vendor_status">
                                                <input type="hidden" name="vendor_id" value="<?php echo $v['vendor_id']; ?>">

                                                <?php if ($v['status'] === 'pending'): ?>
                                                    <button type="submit" name="new_status" value="approved" class="btn btn-sm btn-magenta" title="Approve Application">
                                                        <i class="fa-solid fa-check"></i> Approve
                                                    </button>
                                                    <button type="submit" name="new_status" value="blocked" class="btn btn-sm btn-subtle text-danger" title="Reject Application" onclick="return confirm('Decline this vendor application?');">
                                                        <i class="fa-solid fa-xmark"></i> Decline
                                                    </button>
                                                <?php elseif ($v['status'] === 'approved'): ?>
                                                    <button type="submit" name="new_status" value="blocked" class="btn btn-sm btn-subtle text-danger" onclick="return confirm('Suspend this vendor store?');" title="Suspend Boutique">
                                                        <i class="fa-solid fa-ban"></i> Suspend
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="new_status" value="approved" class="btn btn-sm btn-subtle text-success" title="Reactivate Boutique">
                                                        <i class="fa-solid fa-check"></i> Reactivate
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="text-align: center; padding: 40px 0;">No vendor records match the selected filter.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
