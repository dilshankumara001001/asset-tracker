<?php require 'header.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?"); $stmt->execute([$_SESSION['user_id']]); $u = $stmt->fetch();
    if (!password_verify($_POST['current'] ?? '', $u['password'])) $errors[] = 'Current password wrong';
    if (strlen($_POST['new'] ?? '') < 6) $errors[] = 'New password min 6 chars';
    if (($_POST['new'] ?? '') !== ($_POST['confirm'] ?? '')) $errors[] = 'Passwords do not match';
    if (empty($errors)) {
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($_POST['new'], PASSWORD_DEFAULT), $_SESSION['user_id']]);
        log_action('Password Change', 'User changed their password');
        flash('success', 'Password changed successfully');
        header('Location: dashboard.php'); exit;
    }
}
?>
<h2>🔑 Change Password</h2>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="POST" class="card p-3" style="max-width:500px">
<?= csrf_field() ?>
<div class="mb-3"><label class="form-label">Current Password</label><input name="current" type="password" class="form-control" required></div>
<div class="mb-3">
    <label class="form-label">New Password (min 6)</label>
    <input name="new" id="newPass" type="password" class="form-control" required onkeyup="checkStrength()">
    <div class="progress mt-2" style="height: 5px;"><div id="strengthBar" class="progress-bar" style="width: 0%"></div></div>
    <small id="strengthText" class="text-muted"></small>
</div>
<div class="mb-3"><label class="form-label">Confirm New</label><input name="confirm" type="password" class="form-control" required></div>
<button class="btn btn-primary">Change Password</button>
</form>

<script>
function checkStrength() {
    const val = document.getElementById('newPass').value;
    const bar = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let strength = 0;
    if (val.length >= 6) strength++;
    if (val.length >= 10) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const colors = ['bg-danger', 'bg-warning', 'bg-info', 'bg-success'];
    const texts = ['Very Weak', 'Weak', 'Medium', 'Strong'];
    
    bar.className = 'progress-bar ' + colors[Math.min(3, Math.floor(strength/1.5))];
    bar.style.width = (strength * 20) + '%';
    text.textContent = texts[Math.min(3, Math.floor(strength/1.5))] || 'Very Weak';
}
</script>
<?php require 'footer.php';