<?php require 'config.php';
$code = $_GET['code'] ?? null;
if ($code) {
    $stmt = $pdo->prepare("SELECT id FROM assets WHERE asset_code=?");
    $stmt->execute([$code]); $row = $stmt->fetch();
    if ($row) { header('Location: ' . BASE_URL . '/assets/view.php?id=' . $row['id']); exit; }
}
?>
<!DOCTYPE html><html><head><title>Scan QR</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#111;color:#fff;min-height:100vh}#reader{width:100%;max-width:500px;margin:auto;border-radius:10px;overflow:hidden}</style>
</head><body>
<div class="container py-4 text-center">
<h3 class="mb-3">📷 Scan Asset QR</h3>
<?php if ($code && empty($row)): ?><div class="alert alert-danger">Asset not found: <?= e($code) ?></div><?php endif; ?>
<div id="reader" class="mb-3"></div>
<p class="text-muted">Point your camera at the QR code</p>
<a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-outline-light mt-2">← Back</a>
</div>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const scanner = new Html5Qrcode("reader");
scanner.start({facingMode:"environment"},{fps:10,qrbox:{width:250,height:250}},(text)=>{scanner.stop().then(()=>{window.location.href=text;});},()=>{}).catch(err=>{document.getElementById("reader").innerHTML="<div class='alert alert-danger'>Camera: "+err+"</div>";});
</script>
</body></html>