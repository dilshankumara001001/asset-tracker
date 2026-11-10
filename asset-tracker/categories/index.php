<?php 
require_once '../config.php'; 
requireAdmin();

// 🔥 HTML එකට කලින් logic එක run කරනවා
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (!empty($_POST['name'])) {
        if (!empty($_POST['id'])) {
            $pdo->prepare("UPDATE categories SET name=? WHERE id=?")->execute([$_POST['name'], (int)$_POST['id']]);
        } else {
            $pdo->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$_POST['name']]);
        }
        flash('success', 'Saved');
        header('Location: index.php'); 
        exit;
    }
}

if (isset($_GET['delete']) && hash_equals(csrf_token(), $_GET['csrf'] ?? '')) {
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_GET['delete']]);
    flash('success', 'Deleted'); 
    header('Location: index.php'); 
    exit;
}

$edit = null;
if (isset($_GET['edit'])) { 
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?"); 
    $stmt->execute([(int)$_GET['edit']]); 
    $edit = $stmt->fetch(); 
}
$rows = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM assets WHERE category_id=c.id) AS cnt FROM categories c ORDER BY name")->fetchAll();

require '../header.php'; 
?>
<h2>🏷️ Categories</h2>
<form method="POST" class="card p-3 mb-3 d-flex gap-2 flex-row">
<?= csrf_field() ?>
<?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
<input name="name" value="<?= e($edit['name'] ?? '') ?>" class="form-control" placeholder="Category name" required>
<button class="btn btn-primary"><?= $edit?'Update':'Add' ?></button>
<?php if ($edit): ?><a href="index.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
</form>
<table class="table table-bordered"><thead class="table-dark"><tr><th>Name</th><th>Assets</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['name']) ?></td><td><?= $r['cnt'] ?></td><td class="table-actions">
<a class="btn btn-sm btn-warning" href="?edit=<?= $r['id'] ?>">Edit</a>
<a class="btn btn-sm btn-danger" href="?delete=<?= $r['id'] ?>&csrf=<?= csrf_token() ?>" data-confirm="Delete category?">Del</a>
</td></tr>
<?php endforeach; ?>
</tbody></table>
<?php require '../footer.php'; ?>