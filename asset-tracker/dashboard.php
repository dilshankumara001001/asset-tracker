<?php require 'header.php';

$isStaff = $_SESSION['role'] === 'staff';
$uid = $_SESSION['user_id'];
$userFilter = $isStaff ? " AND custodian_id = $uid" : "";

$stats = [
    'total'   => $pdo->query("SELECT COUNT(*) FROM assets WHERE deleted_at IS NULL $userFilter")->fetchColumn(),
    'in_use'  => $pdo->query("SELECT COUNT(*) FROM assets WHERE status='in_use' AND deleted_at IS NULL $userFilter")->fetchColumn(),
    'lost'    => $pdo->query("SELECT COUNT(*) FROM assets WHERE status='lost' AND deleted_at IS NULL $userFilter")->fetchColumn(),
    'service' => $pdo->query("SELECT COUNT(*) FROM assets WHERE status='under_service' AND deleted_at IS NULL $userFilter")->fetchColumn(),
];

$totalValue = $pdo->query("SELECT SUM(purchase_price) FROM assets WHERE deleted_at IS NULL $userFilter")->fetchColumn() ?? 0;
$totalMaintenance = $isStaff ? 0 : ($pdo->query("SELECT SUM(cost) FROM maintenance_logs")->fetchColumn() ?? 0);

$expiring = $pdo->query("SELECT * FROM assets WHERE expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND deleted_at IS NULL $userFilter ORDER BY expiry_date ASC LIMIT 5")->fetchAll();
$maintAlerts = $pdo->query("SELECT m.*, a.name, a.asset_code FROM maintenance_logs m JOIN assets a ON m.asset_id=a.id WHERE m.next_service_date IS NOT NULL AND m.next_service_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND a.deleted_at IS NULL $userFilter ORDER BY m.next_service_date ASC LIMIT 5")->fetchAll();

$recent = $pdo->query("SELECT h.*,a.name AS asset_name,u.name AS performer FROM asset_history h JOIN assets a ON h.asset_id=a.id LEFT JOIN users u ON h.performed_by=u.id WHERE 1=1 $userFilter ORDER BY h.created_at DESC LIMIT 8")->fetchAll();

$statusData = $pdo->query("SELECT status, COUNT(*) as count FROM assets WHERE deleted_at IS NULL $userFilter GROUP BY status")->fetchAll();
$statusLabels = []; $statusCounts = [];
foreach ($statusData as $row) { $statusLabels[] = ucfirst($row['status']); $statusCounts[] = $row['count']; }
?>

<h2 class="mb-3">📊 <?= $isStaff ? 'My' : 'Global' ?> <?= __('dashboard') ?></h2>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card text-bg-primary"><div class="card-body"><small><?= $isStaff ? 'My' : 'Total' ?> <?= __('assets') ?></small><h3 class="mb-0"><?= $stats['total'] ?></h3></div></div></div>
    <div class="col-6 col-md-3"><div class="card text-bg-success"><div class="card-body"><small><?= $isStaff ? 'My' : 'Total' ?> <?= __('total_value') ?></small><h3 class="mb-0">Rs. <?= number_format($totalValue, 2) ?></h3></div></div></div>
    <?php if (!$isStaff): ?>
    <div class="col-6 col-md-3"><div class="card text-bg-warning text-dark"><div class="card-body"><small><?= __('total_maint') ?></small><h3 class="mb-0">Rs. <?= number_format($totalMaintenance, 2) ?></h3></div></div></div>
    <?php endif; ?>
    <div class="col-6 col-md-3"><div class="card text-bg-danger"><div class="card-body"><small><?= __('lost') ?></small><h3 class="mb-0"><?= $stats['lost'] ?></h3></div></div></div>
</div>

<?php if ($expiring || $maintAlerts): ?>
<div class="row mb-4">
    <div class="col-md-6">
        <?php if ($expiring): ?>
        <div class="alert alert-warning h-100 mb-0">
            <b>⚠️ <?= __('expiring_alerts') ?>:</b>
            <ul class="mb-0 mt-2 small"><?php foreach ($expiring as $a): ?><li><a href="<?= BASE_URL ?>/assets/view.php?id=<?= $a['id'] ?>"><?= e($a['name']) ?></a> — <?= $a['expiry_date'] ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <?php if ($maintAlerts): ?>
        <div class="alert alert-info h-100 mb-0">
            <b>🛠️ <?= __('maint_alerts') ?>:</b>
            <ul class="mb-0 mt-2 small"><?php foreach ($maintAlerts as $m): ?><li><a href="<?= BASE_URL ?>/assets/view.php?id=<?= $m['asset_id'] ?>"><?= e($m['name']) ?></a> — <?= $m['next_service_date'] ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">📈 <?= __('assets') ?> by Status</div>
            <div class="card-body"><canvas id="statusChart" style="max-height:250px"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">📜 <?= __('recent_activity') ?></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0"><thead><tr><th><?= __('date') ?></th><th><?= __('assets') ?></th><th><?= __('action') ?></th></tr></thead><tbody>
                <?php if (empty($recent)): ?><tr><td colspan="3" class="text-center text-muted py-3">No activity</td></tr><?php endif; ?>
                <?php foreach ($recent as $r): ?>
                <tr><td><?= date('Y-m-d',strtotime($r['created_at'])) ?></td><td><?= e($r['asset_name']) ?></td><td><span class="badge bg-secondary"><?= e($r['action']) ?></span></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <?php if (!$isStaff): ?><a href="<?= BASE_URL ?>/assets/add.php" class="btn btn-success">➕ <?= __('add_asset') ?></a><?php endif; ?>
    <a href="<?= BASE_URL ?>/assets/index.php" class="btn btn-secondary">📋 <?= __('assets') ?></a>
    <a href="<?= BASE_URL ?>/scan.php" class="btn btn-info">📷 Scan</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: { labels: <?= json_encode($statusLabels) ?>, datasets: [{ data: <?= json_encode($statusCounts) ?>, backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#dc3545', '#6c757d'] }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
<?php require 'footer.php'; ?>