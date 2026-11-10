<?php require '../header.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT a.*,c.name AS category,l.name AS location,u.name AS custodian FROM assets a LEFT JOIN categories c ON a.category_id=c.id LEFT JOIN locations l ON a.location_id=l.id LEFT JOIN users u ON a.custodian_id=u.id WHERE a.id=? AND a.deleted_at IS NULL");
$stmt->execute([$id]); $asset = $stmt->fetch();
if (!$asset) { flash('error', 'Asset not found'); header('Location: index.php'); exit; }

$h = $pdo->prepare("SELECT h.*,u.name AS performer,tu.name AS to_user FROM asset_history h LEFT JOIN users u ON h.performed_by=u.id LEFT JOIN users tu ON h.to_user_id=tu.id WHERE h.asset_id=? ORDER BY h.created_at DESC");
$h->execute([$id]); $history = $h->fetchAll();

// Maintenance logs
$maint = $pdo->prepare("SELECT * FROM maintenance_logs WHERE asset_id=? ORDER BY service_date DESC");
$maint->execute([$id]); $maintenance = $maint->fetchAll();

// Calculations (Depreciation)
$price = (float)$asset['purchase_price'];
$purchaseDate = $asset['purchase_date'] ? strtotime($asset['purchase_date']) : time();
$ageInYears = max(0, (time() - $purchaseDate) / (365.25 * 24 * 60 * 60));
$depRate = 0.20; // 20% per year
$depreciatedValue = max(0, $price - ($price * $depRate * $ageInYears));
$totalMaintCost = array_sum(array_column($maintenance, 'cost'));

// QR Code
$qrFile = __DIR__ . "/../qrcodes/{$asset['asset_code']}.png";
$qrUrl  = BASE_URL . "/qrcodes/{$asset['asset_code']}.png";
if (!file_exists($qrFile)) generateQRCode(BASE_URL . "/scan.php?code=" . urlencode($asset['asset_code']), $qrFile);

$badge = ['available'=>'success','in_use'=>'primary','under_service'=>'warning','lost'=>'danger','disposed'=>'secondary'][$asset['status']] ?? 'secondary';

// Attachments
$attachments = $pdo->prepare("SELECT * FROM asset_attachments WHERE asset_id=? ORDER BY created_at DESC");
$attachments->execute([$id]); $attachments = $attachments->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
<h2>📦 <?= e($asset['name']) ?></h2>
<div>
<a class="btn btn-warning" href="edit.php?id=<?= $id ?>">✏️ Edit</a>
<a class="btn btn-info" href="transfer.php?id=<?= $id ?>">🔄 Transfer</a>
<a class="btn btn-secondary" href="index.php">← Back</a>
</div></div>

<div class="row g-3">
<!-- Left Column -->
<div class="col-md-8">
    <div class="card mb-3"><div class="card-header">Details</div><div class="card-body">
    <table class="table table-sm mb-0">
    <tr><th width="30%">Code</th><td><code><?= e($asset['asset_code']) ?></code></td></tr>
    <tr><th>Category</th><td><?= e($asset['category'] ?? '-') ?></td></tr>
    <tr><th>Location</th><td><?= e($asset['location'] ?? '-') ?></td></tr>
    <tr><th>Custodian</th><td><?= e($asset['custodian'] ?? '-') ?></td></tr>
    <tr><th>Status</th><td><span class="badge bg-<?= $badge ?>"><?= e($asset['status']) ?></span></td></tr>
    <tr><th>Purchase Price</th><td>Rs. <?= number_format($price, 2) ?></td></tr>
    <tr><th>Depreciated Value</th><td class="text-success fw-bold">Rs. <?= number_format($depreciatedValue, 2) ?> <small>(<?= round($ageInYears, 1) ?> years old)</small></td></tr>
    <tr><th>Total Repair Cost</th><td class="text-danger">Rs. <?= number_format($totalMaintCost, 2) ?></td></tr>
    <tr><th>Purchase / Expiry</th><td><?= e($asset['purchase_date'] ?? '-') ?> / <?= e($asset['expiry_date'] ?? '-') ?></td></tr>
    <tr><th>Description</th><td><?= nl2br(e($asset['description'] ?? '-')) ?></td></tr>
    </table></div></div>

    <!-- Maintenance Section -->
    <div class="card mb-3"><div class="card-header d-flex justify-content-between align-items-center">
        <span>🛠️ Maintenance History</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMaintModal">➕ Add Record</button>
    </div><div class="card-body p-0">
        <?php if ($maintenance): ?>
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Description</th><th>Cost</th><th>Next Service</th></tr></thead>
            <tbody>
            <?php foreach ($maintenance as $m): ?>
                <tr><td><?= $m['service_date'] ?></td><td><?= e($m['description']) ?></td><td>Rs. <?= number_format($m['cost'], 2) ?></td><td><?= $m['next_service_date'] ?? '-' ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><p class="text-muted p-3 mb-0">No maintenance records yet.</p><?php endif; ?>
    </div></div>
</div>

<!-- Right Column -->
<div class="col-md-4">
    <div class="card text-center mb-3"><div class="card-header">QR Code</div><div class="card-body">
        <img src="<?= $qrUrl ?>?t=<?= time() ?>" class="img-fluid mb-2" style="max-width:180px;">
        <div class="d-grid gap-2">
            <a href="<?= $qrUrl ?>" download class="btn btn-sm btn-outline-primary">⬇ Download</a>
            <button onclick="window.open('<?= $qrUrl ?>','_blank')" class="btn btn-sm btn-outline-secondary">🖨 Print</button>
        </div>
    </div></div>

    <!-- Attachments with File Preview -->
    <div class="card mb-3"><div class="card-header">📎 Attachments (<?= count($attachments) ?>)</div><div class="card-body">
    <?php foreach ($attachments as $at): 
        $isImage = str_starts_with($at['mime_type'], 'image/');
        $isPdf = $at['mime_type'] === 'application/pdf';
        $fileUrl = BASE_URL . "/uploads/attachments/" . e($at['filename']);
    ?>
    <div class="border rounded p-2 mb-2">
        <div class="d-flex justify-content-between align-items-center">
            <span class="small text-truncate fw-bold"><?= e($at['original_name']) ?></span>
            <div>
                <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-info">👁 View</a>
                <?php if (isAdmin()): ?><a href="upload.php?delete=<?= $at['id'] ?>&asset_id=<?= $id ?>&csrf=<?= csrf_token() ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete file?">×</a><?php endif; ?>
            </div>
        </div>
        <?php if ($isImage): ?>
            <img src="<?= $fileUrl ?>" class="img-fluid mt-2 rounded" style="max-height:100px;">
        <?php elseif ($isPdf): ?>
            <embed src="<?= $fileUrl ?>" type="application/pdf" width="100%" height="150px" class="mt-2">
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <form method="POST" action="upload.php" enctype="multipart/form-data" class="d-flex gap-2 mt-3">
        <?= csrf_field() ?>
        <input type="hidden" name="asset_id" value="<?= $id ?>">
        <input type="file" name="file" class="form-control form-control-sm" required>
        <button class="btn btn-sm btn-primary">Up</button>
    </form>
    </div></div>
</div></div>

<!-- Add Maintenance Modal -->
<div class="modal fade" id="addMaintModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="maintenance_add.php">
    <?= csrf_field() ?>
    <input type="hidden" name="asset_id" value="<?= $id ?>">
    <div class="modal-header"><h5 class="modal-title">🛠️ Add Maintenance Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Service Date</label><input type="date" name="service_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
        <div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" required placeholder="e.g., Screen replaced"></div>
        <div class="mb-3"><label class="form-label">Cost (Rs.)</label><input type="number" step="0.01" name="cost" class="form-control" required value="0.00"></div>
        <div class="mb-3"><label class="form-label">Next Service Date (Optional)</label><input type="date" name="next_service_date" class="form-control"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Record</button></div>
    </form>
  </div></div>
</div>

<h4 class="mt-4">📜 Asset History</h4>
<div class="card"><div class="card-body p-0">
<table class="table table-sm mb-0"><thead class="table-light"><tr><th>Date</th><th>Action</th><th>By</th><th>To</th><th>Notes</th></tr></thead><tbody>
<?php if (empty($history)): ?><tr><td colspan="5" class="text-center text-muted py-3">No history</td></tr><?php endif; ?>
<?php foreach ($history as $hi): ?>
<tr><td><?= date('Y-m-d H:i',strtotime($hi['created_at'])) ?></td><td><span class="badge bg-secondary"><?= e($hi['action']) ?></span></td><td><?= e($hi['performer'] ?? '-') ?></td><td><?= e($hi['to_user'] ?? '-') ?></td><td><?= e($hi['notes'] ?? '') ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php require '../footer.php';