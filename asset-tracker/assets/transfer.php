<?php require '../header.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM assets WHERE id=?"); $stmt->execute([$id]); $asset = $stmt->fetch();
if (!$asset) { flash('error', 'Asset not found'); header('Location: index.php'); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $newStatus = $_POST['new_status'];
    if (!can_transition($asset['status'], $newStatus)) {
        $errors[] = "Invalid status transition: {$asset['status']} → {$newStatus}";
    }
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $toUser = $_POST['to_user_id'] ?: null;
            $toLoc = $_POST['to_location_id'] ?: null;
            $pdo->prepare("UPDATE assets SET custodian_id=?,location_id=?,status=? WHERE id=?")->execute([$toUser,$toLoc,$newStatus,$id]);
            $pdo->prepare("INSERT INTO asset_history (asset_id,action,from_user_id,to_user_id,from_location_id,to_location_id,notes,performed_by) VALUES (?,?,?,?,?,?,?,?)")->execute([$id,$_POST['action'],$asset['custodian_id'],$toUser,$asset['location_id'],$toLoc,$_POST['notes'],$_SESSION['user_id']]);
            $pdo->commit();
            flash('success', 'Asset transferred');
            header('Location: view.php?id=' . $id); exit;
        } catch (Exception $e) { $pdo->rollBack(); $errors[] = $e->getMessage(); }
    }
}
$users = $pdo->query("SELECT * FROM users ORDER BY name")->fetchAll();
$locs = $pdo->query("SELECT * FROM locations ORDER BY name")->fetchAll();
?>
<h2>🔄 Transfer: <?= e($asset['name']) ?></h2>
<div class="alert alert-info"><b>Current:</b> Status = <?= e($asset['status']) ?>, Custodian = <?= e($asset['custodian_id'] ? 'User #'.$asset['custodian_id'] : 'None') ?>, Location = <?= e($asset['location_id'] ? 'Loc #'.$asset['location_id'] : 'None') ?></div>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="POST" class="card p-3"><div class="row g-3">
<?= csrf_field() ?>
<div class="col-md-6"><label class="form-label">Action</label><select name="action" class="form-select"><option value="transferred">Transferred</option><option value="checked_out">Checked Out</option><option value="checked_in">Checked In</option><option value="serviced">Serviced</option><option value="marked_lost">Marked Lost</option><option value="found">Found</option></select></div>
<div class="col-md-6"><label class="form-label">New Status</label><select name="new_status" class="form-select"><option value="available">Available</option><option value="in_use">In Use</option><option value="under_service">Under Service</option><option value="lost">Lost</option><option value="disposed">Disposed</option></select></div>
<div class="col-md-6"><label class="form-label">New Custodian</label><select name="to_user_id" class="form-select"><option value="">--</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">New Location</label><select name="to_location_id" class="form-select"><option value="">--</option><?php foreach ($locs as $l): ?><option value="<?= $l['id'] ?>"><?= e($l['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
</div>
<div class="mt-3"><button class="btn btn-primary">💾 Save Transfer</button> <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">Cancel</a></div>
</form>
<?php require '../footer.php';