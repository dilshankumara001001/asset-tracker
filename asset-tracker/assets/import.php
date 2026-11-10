<?php require '../header.php'; requireAdmin();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_FILES['csv']) && $_FILES['csv']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv']['tmp_name'];
        $handle = fopen($file, 'r');
        if ($handle !== false) {
            $header = fgetcsv($handle); // skip header
            $count = 0; $errors = 0;
            
            // Pre-fetch categories and locations for name-to-id mapping
            $cats = []; foreach ($pdo->query("SELECT id,name FROM categories")->fetchAll() as $c) $cats[strtolower(trim($c['name']))] = $c['id'];
            $locs = []; foreach ($pdo->query("SELECT id,name FROM locations")->fetchAll() as $l) $locs[strtolower(trim($l['name']))] = $l['id'];
            $users = []; foreach ($pdo->query("SELECT id,email,name FROM users")->fetchAll() as $u) { $users[strtolower(trim($u['email']))] = $u['id']; $users[strtolower(trim($u['name']))] = $u['id']; }

            $pdo->beginTransaction();
            try {
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) < 2) continue;
                    
                    $name = trim($row[0] ?? '');
                    if (!$name) continue;
                    
                    $catId = $cats[strtolower(trim($row[1] ?? ''))] ?? null;
                    $locId = $locs[strtolower(trim($row[2] ?? ''))] ?? null;
                    $custId = $users[strtolower(trim($row[3] ?? ''))] ?? null;
                    $status = in_array(strtolower(trim($row[4] ?? '')), ['available','in_use','under_service','lost','disposed']) ? strtolower(trim($row[4])) : 'available';
                    $desc = trim($row[5] ?? '');
                    
                    $code = 'AST-' . strtoupper(bin2hex(random_bytes(3)));
                    $pdo->prepare("INSERT INTO assets (asset_code,name,category_id,location_id,custodian_id,description,status) VALUES (?,?,?,?,?,?,?)")
                        ->execute([$code, $name, $catId, $locId, $custId, $desc, $status]);
                    $count++;
                }
                $pdo->commit();
                flash('success', "Successfully imported $count assets.");
                header('Location: index.php'); exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $msg = "Import failed: " . $e->getMessage();
            }
            fclose($handle);
        }
    } else {
        $msg = "File upload error.";
    }
}
?>
<h2>📥 Bulk Import Assets (CSV)</h2>
<?php if ($msg): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

<div class="card mb-4"><div class="card-body">
    <h5>Instructions:</h5>
    <p>Upload a CSV file with the following columns (in this exact order). <b>No headers needed.</b></p>
    <ol class="small">
        <li><b>Asset Name</b> (Required)</li>
        <li>Category Name (Must exist in Categories)</li>
        <li>Location Name (Must exist in Locations)</li>
        <li>Custodian Email or Name (Must exist in Users)</li>
        <li>Status (available, in_use, under_service, lost)</li>
        <li>Description</li>
    </ol>
    <a href="data:text/csv;charset=utf-8,My%20Laptop,Laptop,Main%20Office,admin%40example.com,available,Dell%20i5%0AMy%20File,File,Archive,,available,Confidential%20Doc" download="sample_import.csv" class="btn btn-sm btn-info">⬇️ Download Sample CSV</a>
</div></div>

<form method="POST" enctype="multipart/form-data" class="card p-3" style="max-width:600px">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Select CSV File</label>
        <input type="file" name="csv" accept=".csv" class="form-control" required>
    </div>
    <button class="btn btn-primary">📥 Start Import</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>
<?php require '../footer.php';