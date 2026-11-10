<?php require '../header.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM assets WHERE id=? AND deleted_at IS NULL"); $stmt->execute([$id]); $asset = $stmt->fetch();
if (!$asset) { flash('error', 'Asset not found'); header('Location: index.php'); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $errors = validate($_POST, ['name' => 'required|max:200', 'expiry_date' => 'date_after:purchase_date']);
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE assets SET name=?,category_id=?,location_id=?,custodian_id=?,description=?,purchase_date=?,service_date=?,expiry_date=?,status=?,purchase_price=? WHERE id=?")->execute([$_POST['name'],$_POST['category_id']?:null,$_POST['location_id']?:null,$_POST['custodian_id']?:null,$_POST['description']?:null,$_POST['purchase_date']?:null,$_POST['service_date']?:null,$_POST['expiry_date']?:null,$_POST['status'],$_POST['purchase_price']?:0,$id]);
            $pdo->prepare("INSERT INTO asset_history (asset_id,action,performed_by,notes) VALUES (?,?,?,?)")->execute([$id,'updated',$_SESSION['user_id'],'Asset updated']);
            $pdo->commit();
            flash('success', 'Asset updated');
            header('Location: view.php?id=' . $id); exit;
        } catch (Exception $e) { $pdo->rollBack(); $errors['general'] = $e->getMessage(); }
    }
}
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$locs = $pdo->query("SELECT * FROM locations ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT * FROM users ORDER BY name")->fetchAll();
?>
<h2>✏️ Edit: <?= e($asset['name']) ?></h2>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="POST" class="card p-3"><div class="row g-3">
<?= csrf_field() ?>
<div class="col-md-8"><label class="form-label">Name *</label><input name="name" value="<?= e($asset['name']) ?>" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach (['available','in_use','under_service','lost','disposed'] as $s): ?><option value="<?= $s ?>" <?= $asset['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">--</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $asset['category_id']==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Location</label><select name="location_id" class="form-select"><option value="">--</option><?php foreach ($locs as $l): ?><option value="<?= $l['id'] ?>" <?= $asset['location_id']==$l['id']?'selected':'' ?>><?= e($l['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Custodian</label><select name="custodian_id" class="form-select"><option value="">--</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>" <?= $asset['custodian_id']==$u['id']?'selected':'' ?>><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Purchase Price (Rs.)</label><input type="number" step="0.01" name="purchase_price" value="<?= e($asset['purchase_price']) ?>" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" value="<?= e($asset['purchase_date']) ?>" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Service Date</label><input type="date" name="service_date" value="<?= e($asset['service_date']) ?>" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Warranty/Expiry</label><input type="date" name="expiry_date" value="<?= e($asset['expiry_date']) ?>" class="form-control"></div>
<div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= e($asset['description']) ?></textarea></div>
</div>
<div class="mt-3"><button class="btn btn-primary">💾 Update</button> <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">Cancel</a></div>
</form>
<?php require '../footer.php';