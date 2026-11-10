<?php
require '../config.php';
requireLogin();
csrf_check();

$assetId = (int)($_POST['asset_id'] ?? 0);
if (!$assetId) { flash('error', 'Invalid asset'); header('Location: index.php'); exit; }

$pdo->prepare("INSERT INTO maintenance_logs (asset_id, description, cost, service_date, next_service_date) VALUES (?,?,?,?,?)")
    ->execute([
        $assetId,
        $_POST['description'] ?? '',
        $_POST['cost'] ?? 0,
        $_POST['service_date'] ?? date('Y-m-d'),
        $_POST['next_service_date'] ?: null
    ]);

// Log in history
$pdo->prepare("INSERT INTO asset_history (asset_id,action,performed_by,notes) VALUES (?,?,?,?)")
    ->execute([$assetId, 'maintenance', $_SESSION['user_id'], 'Added maintenance record: ' . ($_POST['description'] ?? '')]);

flash('success', 'Maintenance record added');
header('Location: view.php?id=' . $assetId);
exit;