<?php require '../header.php'; requireAdmin();

if (isset($_GET['restore']) && hash_equals(csrf_token(), $_GET['csrf'] ?? '')) {
    $pdo->prepare("UPDATE assets SET deleted_at = NULL WHERE id=?")->execute([(int)$_GET['restore']]);
    flash('success', 'Asset restored successfully'); header('Location: recycle_bin.php'); exit;
}
if (isset($_GET['purge']) && hash_equals(csrf_token(), $_GET['csrf'] ?? '')) {
    $id = (int)$_GET['purge'];
    $stmt = $pdo->prepare("SELECT asset_code FROM assets WHERE id=?"); $stmt->execute([$id]); $a = $stmt->fetch();
    if ($a) {
        @unlink(__DIR__ . "/../qrcodes/{$a['asset_code']}.png");
        $pdo->prepare("DELETE FROM assets WHERE id=?")->execute([$id]);
    }
    flash('success', 'Asset permanently deleted'); header('Location: recycle_bin.php'); exit;
}

$rows = $pdo->query("SELECT * FROM assets WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll();
?>
<h2>🗑️ Recycle Bin</h2>
<p class="text-muted">Deleted assets are kept here. You can restore them or delete them permanently.</p>

<table class="table table-bordered mt-3">
    <thead class="table-dark"><tr><th>Code</th><th>Name</th><th>Deleted At</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (empty($rows)): ?><tr><td colspan="4" class="text-center py-3">Recycle Bin is empty</td></tr><?php endif; ?>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><code><?= e($r['asset_code']) ?></code></td>
            <td><?= e($r['name']) ?></td>
            <td><?= date('Y-m-d H:i', strtotime($r['deleted_at'])) ?></td>
            <td>
                <a href="?restore=<?= $r['id'] ?>&csrf=<?= csrf_token() ?>" class="btn btn-sm btn-success">♻️ Restore</a>
                <a href="?purge=<?= $r['id'] ?>&csrf=<?= csrf_token() ?>" class="btn btn-sm btn-danger" data-confirm="Permanently delete this asset? This cannot be undone.">❌ Delete Forever</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require '../footer.php';