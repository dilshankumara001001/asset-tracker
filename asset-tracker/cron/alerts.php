<?php
// Email alerts for expiring assets
// Run daily: php C:\xampp\htdocs\asset-tracker\cron\alerts.php
// Or setup Windows Task Scheduler to run daily

require __DIR__ . '/../config.php';

// Config
define('ALERT_TO', 'admin@example.com');
define('ALERT_FROM', 'noreply@assettracker.local');

$expiring = $pdo->query("
    SELECT a.*, u.name AS custodian, u.email AS custodian_email
    FROM assets a
    LEFT JOIN users u ON a.custodian_id = u.id
    WHERE a.expiry_date IS NOT NULL
      AND a.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
      AND a.status NOT IN ('disposed','lost')
    ORDER BY a.expiry_date
")->fetchAll();

if (empty($expiring)) { echo "No expiring assets. " . date('Y-m-d H:i:s') . "\n"; exit; }

$body = "Asset Tracker - Expiry Alert\n\n";
$body .= "The following assets expire within 7 days:\n\n";
foreach ($expiring as $a) {
    $body .= "- {$a['asset_code']} | {$a['name']} | Expires: {$a['expiry_date']} | Custodian: " . ($a['custodian'] ?? 'None') . "\n";
}
$body .= "\nLog in: " . BASE_URL . "/dashboard.php\n";

$headers = "From: " . ALERT_FROM . "\r\n" . "Reply-To: " . ALERT_FROM . "\r\n";
$ok = @mail(ALERT_TO, 'Asset Expiry Alert - ' . date('Y-m-d'), $body, $headers);
echo ($ok ? "✅ Sent" : "⚠️ Failed (check mail config)") . " - " . count($expiring) . " assets\n";