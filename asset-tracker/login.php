<?php
require 'config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $attempts = login_attempts_count($email);
    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        $error = 'Too many failed attempts. Try again in ' . LOGIN_LOCKOUT_MINUTES . ' minutes.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?"); $stmt->execute([$email]); $user = $stmt->fetch();
        $ok = $user && password_verify($_POST['password'] ?? '', $user['password']);
        $pdo->prepare("INSERT INTO login_attempts (email, success, ip) VALUES (?,?,?)")->execute([$email, (int)$ok, $_SERVER['REMOTE_ADDR'] ?? '']);
        
        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $pdo->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
            $pdo->prepare("DELETE FROM login_attempts WHERE email=? AND success=0")->execute([$email]);
            log_action('Login', 'User logged in successfully');
            header('Location: ' . BASE_URL . '/dashboard.php'); exit;
        } else {
            log_action('Login Failed', 'Failed login attempt for ' . $email);
            $remaining = MAX_LOGIN_ATTEMPTS - $attempts - 1;
            $error = 'Invalid credentials. ' . ($remaining > 0 ? "$remaining attempts remaining." : 'Account locked.');
        }
    }
}
?>
<!DOCTYPE html><html><head><title>Login</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container" style="max-width:420px;margin-top:80px;">
<div class="card shadow"><div class="card-body p-4">
<h3 class="mb-4 text-center">📦 Asset Tracker</h3>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="POST">
<?= csrf_field() ?>
<label class="form-label">Email</label>
<input name="email" type="email" class="form-control mb-3" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
<label class="form-label">Password</label>
<input name="password" type="password" class="form-control mb-3" required>
<button class="btn btn-primary w-100">Login</button>
</form></div></div></div></body></html>