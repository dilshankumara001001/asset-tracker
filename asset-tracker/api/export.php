<?php
require '../config.php';
requireLogin();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="assets-' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); // BOM for Excel
fputcsv($out, ['Code','Name','Category','Location','Custodian','Status','Purchase','Service','Expiry','Created']);
$rows = $pdo->query("SELECT a.*,c.name AS cat,l.name AS loc,u.name AS cust FROM assets a LEFT JOIN categories c ON a.category_id=c.id LEFT JOIN locations l ON a.location_id=l.id LEFT JOIN users u ON a.custodian_id=u.id ORDER BY a.id")->fetchAll();
foreach ($rows as $r) {
    fputcsv($out, [$r['asset_code'],$r['name'],$r['cat'],$r['loc'],$r['cust'],$r['status'],$r['purchase_date'],$r['service_date'],$r['expiry_date'],$r['created_at']]);
}
fclose($out);