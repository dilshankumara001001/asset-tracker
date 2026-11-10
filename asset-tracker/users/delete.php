<?php
require '../config.php';
requireAdmin();
if (!hash_equals(csrf_token(), $_GET['csrf'] ?? '')) { http_response_code(403); die('CSRF failed'); }
$id = (int)($_GET['id'] ?? 0);
if ($id === (int)$_SESSION['user_id']) { flash('error', 'Cannot delete yourself'); header('Location: index.php'); exit; }
$pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
flash('success', 'User deleted');
header('Location: index.php'); exit;