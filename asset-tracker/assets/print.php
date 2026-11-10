<?php require '../config.php'; requireLogin(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Asset Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>@media print { .no-print { display: none; } body { padding: 0; } }</style>
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2>📋 Asset Master Report</h2>
        <div>
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print / Save as PDF</button>
            <a href="<?= BASE_URL ?>/assets/index.php" class="btn btn-secondary">Back</a>
        </div>
    </div>
    
    <h3 class="text-center d-none d-print-block">Asset Master Report - <?= date('Y-m-d') ?></h3>
    
    <table class="table table-bordered table-sm mt-3">
        <thead class="table-dark">
            <tr><th>Code</th><th>Name</th><th>Category</th><th>Location</th><th>Custodian</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php 
        $rows = $pdo->query("SELECT a.*,c.name AS cat,l.name AS loc,u.name AS cust FROM assets a LEFT JOIN categories c ON a.category_id=c.id LEFT JOIN locations l ON a.location_id=l.id LEFT JOIN users u ON a.custodian_id=u.id ORDER BY a.id")->fetchAll();
        foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['asset_code']) ?></td>
                <td><?= e($r['name']) ?></td>
                <td><?= e($r['cat']) ?></td>
                <td><?= e($r['loc']) ?></td>
                <td><?= e($r['cust']) ?></td>
                <td><?= ucfirst($r['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>