<?php require '../header.php'; requireAdmin();
$rows = $pdo->query("SELECT * FROM users ORDER BY id")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
<h2>👥 Users (<?= count($rows) ?>)</h2>
<a href="add.php" class="btn btn-success">➕ Add User</a>
</div>
<div class="table-responsive"><table class="table table-bordered table-striped">
<thead class="table-dark"><tr><th>Name</th><th>Email</th><th>Role</th><th>Last Login</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><span class="badge bg-<?= $r['role']==='admin'?'danger':'secondary' ?>"><?= e($r['role']) ?></span></td><td><?= e($r['last_login'] ?? 'Never') ?></td>
<td class="table-actions">
<a class="btn btn-sm btn-warning" href="edit.php?id=<?= $r['id'] ?>">Edit</a>
<?php if ($r['id'] != $_SESSION['user_id']): ?>
<a class="btn btn-sm btn-danger" href="delete.php?id=<?= $r['id'] ?>&csrf=<?= csrf_token() ?>" data-confirm="Delete this user?">Del</a>
<?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php require '../footer.php';