<?php 
require_once '../config.php'; 
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $prefix = strtoupper(trim($_POST['prefix']));
    if (preg_match('/^[A-Z0-9-]{2,10}$/', $prefix)) {
        $pdo->prepare("UPDATE settings SET setting_value=? WHERE setting_key='asset_code_prefix'")->execute([$prefix]);
        log_action('Settings Update', 'Changed asset prefix to ' . $prefix);
        flash('success', 'Settings saved successfully');
        header('Location: index.php'); 
        exit;
    } else {
        flash('error', 'Invalid prefix. Use 2-10 letters/numbers.');
    }
}

require '../header.php'; 
$prefix = get_setting('asset_code_prefix', 'AST');
?>

<h2>⚙️ System Settings</h2>
<div class="card p-3" style="max-width:500px">
    <form method="POST">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Asset Code Prefix</label>
            <input type="text" name="prefix" value="<?= e($prefix) ?>" class="form-control" required maxlength="10">
            <div class="form-text">Used when generating new Asset Codes (e.g., <b><?= e($prefix) ?>-A1B2C3</b>).</div>
        </div>
        <button class="btn btn-primary">Save Settings</button>
    </form>
</div>
<?php require '../footer.php'; ?>