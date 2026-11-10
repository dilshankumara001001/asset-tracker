<?php require '../header.php'; requireAdmin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?"); $stmt->execute([$id]); $u = $stmt->fetch();
if (!$u) { flash('error', 'User not found'); header('Location: index.php'); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $errors = validate($_POST, ['name' => 'required|max:100', 'email' => 'required|email']);
    if (!empty($_POST['password']) && strlen($_POST['password']) < 6) $errors['password'] = 'Password min 6 chars';
    if (empty($errors)) {
        $sql = "UPDATE users SET name=?,email=?,role=?";
        $params = [$_POST['name'], $_POST['email'], $_POST['role']];
        if (!empty($_POST['password'])) { $sql .= ",password=?"; $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT); }
        $sql .= " WHERE id=?"; $params[] = $id;
        $pdo->prepare($sql)->execute($params);
        flash('success', 'User updated');
        header('Location: index.php'); exit;
    }
}
?>
<h2>✏️ Edit User</h2>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="POST" class="card p-3" style="max-width:500px">
<?= csrf_field() ?>
<div class="mb-3"><label class="form-label">Name</label><input name="name" value="<?= e($u['name']) ?>" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" value="<?= e($u['email']) ?>" class="form-control" required></div>
<div class="mb-3"><label class="form-label">New Password (leave blank to keep)</label><input name="password" type="password" class="form-control"></div>
<div class="mb-3"><label class="form-label">Role</label><select name="role" class="form-select"><option value="staff" <?= $u['role']==='staff'?'selected':'' ?>>Staff</option><option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option></select></div>
<button class="btn btn-primary">Update</button> <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>
<?php require '../footer.php';