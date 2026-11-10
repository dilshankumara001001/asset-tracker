<?php require '../header.php';
$assets = $pdo->query("SELECT * FROM assets ORDER BY asset_code")->fetchAll();
?>
<h2>🖨️ Bulk QR Print</h2>
<p class="text-muted">All assets' QR codes arranged for A4 printing. Ctrl+P to print.</p>
<div class="row g-2">
<?php foreach ($assets as $a):
    $qrFile = __DIR__ . "/../qrcodes/{$a['asset_code']}.png";
    if (!file_exists($qrFile)) generateQRCode(BASE_URL . "/scan.php?code=" . urlencode($a['asset_code']), $qrFile);
?>
<div class="col-6 col-md-3">
<div class="card text-center p-2" style="page-break-inside:avoid;">
<img src="<?= BASE_URL ?>/qrcodes/<?= e($a['asset_code']) ?>.png" style="max-width:100%;height:120px;">
<div class="fw-bold small"><?= e($a['asset_code']) ?></div>
<div class="small text-truncate"><?= e($a['name']) ?></div>
</div></div>
<?php endforeach; ?>
</div>
<?php require '../footer.php';