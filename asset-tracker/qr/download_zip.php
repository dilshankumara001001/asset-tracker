<?php
require '../config.php';
requireLogin();

// Check if ZipArchive is available
if (!class_exists('ZipArchive')) {
    die("ZipArchive extension is not enabled in PHP. Please enable it in php.ini (extension=zip).");
}

$assets = $pdo->query("SELECT asset_code FROM assets WHERE deleted_at IS NULL")->fetchAll();
if (empty($assets)) { die("No assets to download."); }

$zipFile = __DIR__ . "/../qrcodes/all_qrcodes.zip";
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Could not create ZIP file.");
}

$count = 0;
foreach ($assets as $a) {
    $qrFile = __DIR__ . "/../qrcodes/{$a['asset_code']}.png";
    if (!file_exists($qrFile)) {
        generateQRCode(BASE_URL . "/scan.php?code=" . urlencode($a['asset_code']), $qrFile);
    }
    if (file_exists($qrFile)) {
        $zip->addFile($qrFile, "{$a['asset_code']}.png");
        $count++;
    }
}
$zip->close();

if ($count === 0) { die("No QR codes found to zip."); }

// Force Download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="asset_qrcodes_' . date('Ymd_His') . '.zip"');
header('Content-Length: ' . filesize($zipFile));
readfile($zipFile);

// Cleanup
@unlink($zipFile);
exit;