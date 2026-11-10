<?php 
require_once '../config.php'; 
requireAdmin();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $errors = validate($_POST, ['name' => 'required|max:100', 'email' => 'required|email', 'password' => 'required|min:6']);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?"); $stmt->execute([$_POST['email'] ?? '']);
    if ($stmt->fetch()) $errors['email'] = 'Email already exists';
    
    if (empty($errors)) {
        $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)")
            ->execute([$_POST['name'], $_POST['email'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['role']]);
        flash('success', 'User created');
        header('Location: index.php'); 
        exit;
    }
}

require '../header.php'; 
?>

<h2>➕ Add User</h2>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<form method="POST" class="card p-3" style="max-width:500px">
<?= csrf_field() ?>
<div class="mb-3"><label class="form-label">Name</label><input name="name" value="<?= e($_POST['name'] ?? '') ?>" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" value="<?= e($_POST['email'] ?? '') ?>" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Password (min 6)</label><input name="password" type="password" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Role</label><select name="role" class="form-select"><option value="staff">Staff</option><option value="admin">Admin</option></select></div>
<button class="btn btn-primary">Save</button> <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require '../footer.php'; ?>