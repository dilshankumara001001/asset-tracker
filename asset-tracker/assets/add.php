<?php 
require_once '../config.php'; 
requireLogin(); // ලොග් වෙලා ඉන්න ඕනේ

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $errors = validate($_POST, ['name' => 'required|max:200', 'expiry_date' => 'date_after:purchase_date']);
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Use dynamic prefix
            $prefix = get_setting('asset_code_prefix', 'AST');
            $code = $prefix . '-' . strtoupper(bin2hex(random_bytes(3)));
            
            $stmt = $pdo->prepare("INSERT INTO assets (asset_code,name,category_id,location_id,custodian_id,description,purchase_date,service_date,expiry_date,status,purchase_price) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$code,$_POST['name'],$_POST['category_id']?:null,$_POST['location_id']?:null,$_POST['custodian_id']?:null,$_POST['description']?:null,$_POST['purchase_date']?:null,$_POST['service_date']?:null,$_POST['expiry_date']?:null,$_POST['status'],$_POST['purchase_price']?:0]);
            $id = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO asset_history (asset_id,action,performed_by,notes) VALUES (?,?,?,?)")->execute([$id,'created',$_SESSION['user_id'],'Asset created']);
            $pdo->commit();
            
            log_action('Asset Create', 'Created asset ' . $code);
            flash('success', 'Asset created successfully');
            header('Location: view.php?id=' . $id); 
            exit;
        } catch (Exception $e) { 
            $pdo->rollBack(); 
            $errors['general'] = $e->getMessage(); 
        }
    }
}

require '../header.php'; // 🔥 HTML ටික මෙතනින් පස්සේ තමයි load වෙන්නේ

$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$locs = $pdo->query("SELECT * FROM locations ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT * FROM users ORDER BY name")->fetchAll();
?>

<h2>➕ <?= __('add_asset') ?></h2>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<form method="POST" class="card p-3"><div class="row g-3">
<?= csrf_field() ?>
<div class="col-md-8"><label class="form-label"><?= __('name') ?> *</label><input name="name" value="<?= e($_POST['name'] ?? '') ?>" class="form-control" required></div>
<div class="col-md-4"><label class="form-label"><?= __('status') ?></label><select name="status" class="form-select"><option value="available">Available</option><option value="in_use">In Use</option><option value="under_service">Under Service</option></select></div>
<div class="col-md-4"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">--</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Location</label><select name="location_id" class="form-select"><option value="">--</option><?php foreach ($locs as $l): ?><option value="<?= $l['id'] ?>"><?= e($l['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Custodian</label><select name="custodian_id" class="form-select"><option value="">--</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Purchase Price (Rs.)</label><input type="number" step="0.01" name="purchase_price" value="<?= e($_POST['purchase_price'] ?? '0.00') ?>" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" value="<?= e($_POST['purchase_date'] ?? '') ?>" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Service Date</label><input type="date" name="service_date" value="<?= e($_POST['service_date'] ?? '') ?>" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Warranty/Expiry</label><input type="date" name="expiry_date" value="<?= e($_POST['expiry_date'] ?? '') ?>" class="form-control"></div>
<div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= e($_POST['description'] ?? '') ?></textarea></div>
</div>
<div class="mt-3"><button class="btn btn-primary">💾 <?= __('save') ?></button> <a href="index.php" class="btn btn-secondary"><?= __('cancel') ?></a></div>
</form>

<?php require '../footer.php'; ?>