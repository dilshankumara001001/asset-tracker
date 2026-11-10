<?php
require '../config.php';
requireAdmin();
if (!hash_equals(csrf_token(), $_GET['csrf'] ?? '')) { http_response_code(403); die('CSRF failed'); }
$id = (int)($_GET['id'] ?? 0);

// 🔥 මේකෙන් වෙන්නේ සම්පූර්ණයෙන්ම මකන්නේ නැතුව, 'deleted_at' එකට වෙලාව දාන එක (Soft Delete)
$stmt = $pdo->prepare("UPDATE assets SET deleted_at = NOW() WHERE id=?");
$stmt->execute([$id]);

flash('success', 'Asset moved to Recycle Bin');
header('Location: index.php'); 
exit;