<?php require 'header.php';
$q = trim($_GET['q'] ?? '');
$assets = []; $users = [];
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM assets WHERE name LIKE ? OR asset_code LIKE ? LIMIT 20");
    $stmt->execute(["%$q%", "%$q%"]);
    $assets = $stmt->fetchAll();

    if (isAdmin()) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ? LIMIT 20");
        $stmt->execute(["%$q%", "%$q%"]);
        $users = $stmt->fetchAll();
    }
}
?>
<h2>🔍 Search Results for: "<?= e($q) ?>"</h2>

<?php if (empty($assets) && empty($users)): ?>
    <div class="alert alert-info mt-3">No results found for "<?= e($q) ?>".</div>
<?php endif; ?>

<?php if ($assets): ?>
    <h4 class="mt-4">📦 Assets (<?= count($assets) ?>)</h4>
    <div class="row g-3">
    <?php foreach ($assets as $a): ?>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><?= e($a['name']) ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted"><?= e($a['asset_code']) ?></h6>
                    <a href="assets/view.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-primary">View Asset</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($users): ?>
    <h4 class="mt-4">👥 Users (<?= count($users) ?>)</h4>
    <ul class="list-group">
    <?php foreach ($users as $u): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= e($u['name']) ?> (<?= e($u['email']) ?>)
            <a href="users/edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require 'footer.php'; ?>