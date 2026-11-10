<?php require __DIR__ . '/../config.php'; requireLogin();
$assets = $pdo->query("SELECT * FROM assets")->fetchAll();
$gen = 0; $skip = 0;
foreach ($assets as $a) {
    $qrFile = __DIR__ . "/../qrcodes/{$a['asset_code']}.png";
    if (!file_exists($qrFile)) {
        if (generateQRCode(BASE_URL . "/scan.php?code=" . urlencode($a['asset_code']), $qrFile)) {
            $pdo->prepare("UPDATE assets SET qr_path=? WHERE id=?")->execute(["qrcodes/{$a['asset_code']}.png",$a['id']]);
            $gen++;
        }
    } else $skip++;
}
?>
<!DOCTYPE html><html><head><title>Bulk QR</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-4"><div class="container">
<h2>🔲 Bulk QR Generator</h2>
<div class="alert alert-success">✅ Generated: <?= $gen ?> | ⏭ Skipped: <?= $skip ?></div>
<a href="<?= BASE_URL ?>/assets/index.php" class="btn btn-primary">📋 View Assets</a>
<a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-secondary">🏠 Dashboard</a>
</div></body></html>