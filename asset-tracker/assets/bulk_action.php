<?php
require '../config.php';
requireLogin();
csrf_check();

$ids = $_POST['ids'] ?? [];
$action = $_POST['action'] ?? '';

if (empty($ids) || !$action) {
    flash('error', 'No assets selected or no action chosen');
    header('Location: index.php'); exit;
}

$inQuery = implode(',', array_fill(0, count($ids), '?'));
$params = array_map('intval', $ids);

try {
    $pdo->beginTransaction();
    if ($action === 'delete') {
        if (!isAdmin()) throw new Exception('Admin required to delete');
        $stmt = $pdo->prepare("UPDATE assets SET deleted_at = NOW() WHERE id IN ($inQuery)");
        $stmt->execute($params);
        flash('success', count($ids) . ' assets moved to Recycle Bin');
    } elseif ($action === 'status_available' || $action === 'status_lost') {
        $newStatus = $action === 'status_lost' ? 'lost' : 'available';
        $stmt = $pdo->prepare("UPDATE assets SET status = ? WHERE id IN ($inQuery)");
        $stmt->execute(array_merge([$newStatus], $params));
        flash('success', count($ids) . ' assets updated to ' . $newStatus);
    }
    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash('error', $e->getMessage());
}
header('Location: index.php'); exit;