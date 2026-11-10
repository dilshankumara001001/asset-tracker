<?php
require '../config.php';
requireLogin();

if (isset($_GET['delete'])) {
    if (!hash_equals(csrf_token(), $_GET['csrf'] ?? '')) { http_response_code(403); die('CSRF failed'); }
    $atId = (int)$_GET['delete'];
    $aid = (int)$_GET['asset_id'];
    $stmt = $pdo->prepare("SELECT * FROM asset_attachments WHERE id=?"); $stmt->execute([$atId]); $at = $stmt->fetch();
    if ($at && isAdmin()) {
        $f = __DIR__ . '/../uploads/attachments/' . $at['filename'];
        if (file_exists($f)) @unlink($f);
        $pdo->prepare("DELETE FROM asset_attachments WHERE id=?")->execute([$atId]);
        flash('success', 'File deleted');
    }
    header('Location: view.php?id=' . $aid); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $assetId = (int)($_POST['asset_id'] ?? 0);
    if (!$assetId || empty($_FILES['file'])) { flash('error', 'No file'); header('Location: index.php'); exit; }
    $f = $_FILES['file'];
    if ($f['error'] !== UPLOAD_ERR_OK) { flash('error', 'Upload error'); header('Location: view.php?id=' . $assetId); exit; }
    if ($f['size'] > MAX_UPLOAD_SIZE) { flash('error', 'File too large (max 5 MB)'); header('Location: view.php?id=' . $assetId); exit; }
    $allowed = ['image/jpeg','image/png','image/gif','application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','text/plain'];
    $mime = mime_content_type($f['tmp_name']);
    if (!in_array($mime, $allowed)) { flash('error', 'File type not allowed'); header('Location: view.php?id=' . $assetId); exit; }
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $safe = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
    $dest = __DIR__ . '/../uploads/attachments/' . $safe;
    if (!move_uploaded_file($f['tmp_name'], $dest)) { flash('error', 'Save failed'); header('Location: view.php?id=' . $assetId); exit; }
    $pdo->prepare("INSERT INTO asset_attachments (asset_id,filename,original_name,mime_type,size,uploaded_by) VALUES (?,?,?,?,?,?)")
        ->execute([$assetId, $safe, $f['name'], $mime, $f['size'], $_SESSION['user_id']]);
    flash('success', 'File uploaded');
    header('Location: view.php?id=' . $assetId); exit;
}
header('Location: index.php');