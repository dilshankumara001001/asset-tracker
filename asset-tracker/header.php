<?php 
require_once __DIR__ . '/config.php'; 
requireLogin(); 

// 🔥 FIX: මෙතනින් තමයි භාෂාව මාරු කරන්නේ (HTML එකට කලින්)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en','si','ta'])) {
    $_SESSION['lang'] = $_GET['lang'];
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}
?>
<!DOCTYPE html><html lang="<?= $_SESSION['lang'] ?>">
<head>
    <title>Asset Tracker</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="theme-color" content="#212529">
    
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/2897/2897785.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-brand{font-weight:bold} body{padding-bottom:50px}
        .table-actions .btn{padding:2px 6px}
        [data-bs-theme="dark"] .bg-light { background-color: #2b3035 !important; }
        [data-bs-theme="dark"] .card { background-color: #212529; border-color: #373b3e; }
    </style>
    <script>
        const theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    </script>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
<div class="container-fluid">
<a class="navbar-brand" href="<?= BASE_URL ?>/dashboard.php">📦 Asset Tracker</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="navMenu">
<div class="navbar-nav me-auto">
<a class="nav-link" href="<?= BASE_URL ?>/dashboard.php">🏠 <?= __('dashboard') ?></a>
<a class="nav-link" href="<?= BASE_URL ?>/assets/index.php">📋 <?= __('assets') ?></a>
<a class="nav-link" href="<?= BASE_URL ?>/scan.php">📷 Scan</a>
<?php if (isAdmin()): ?>
<div class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">⚙️ Admin</a>
<ul class="dropdown-menu">
<li><a class="dropdown-item" href="<?= BASE_URL ?>/users/index.php">👥 Users</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/audit_logs/index.php">📝 Audit Log</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/categories/index.php">🏷️ Categories</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/locations/index.php">📍 Locations</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/settings/index.php">⚙️ <?= __('settings') ?></a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/assets/import.php">📥 Bulk Import CSV</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/assets/recycle_bin.php">🗑️ Recycle Bin</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/qr/bulk_print.php">🖨️ Bulk QR Print</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/qr/download_zip.php">⬇️ Download QRs (ZIP)</a></li>
</ul></div>
<?php endif; ?>
</div>

<div class="navbar-nav align-items-center gap-2">
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">🌐 <?= strtoupper($_SESSION['lang']) ?></button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="?lang=en">English</a></li>
            <li><a class="dropdown-item" href="?lang=si">සිංහල</a></li>
            <li><a class="dropdown-item" href="?lang=ta">தமிழ்</a></li>
        </ul>
    </div>
    
    <button class="btn btn-sm btn-outline-light" onclick="toggleTheme()" id="themeBtn">🌙</button>

    <span class="navbar-text text-white ms-2">👤 <?= e($_SESSION['user_name'] ?? 'User') ?></span>
    <a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php"><?= __('logout') ?></a>
</div>
</div></div></nav>
<div class="container-fluid px-4">
<?php show_flash(); ?>

<script>
function toggleTheme() {
    const current = document.documentElement.getAttribute('data-bs-theme');
    const target = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', target);
    localStorage.setItem('theme', target);
    updateThemeIcon(target);
}
function updateThemeIcon(theme) {
    document.getElementById('themeBtn').textContent = theme === 'dark' ? '☀️' : '🌙';
}
document.addEventListener('DOMContentLoaded', () => updateThemeIcon(localStorage.getItem('theme') || 'light'));

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js').then(() => {
        console.log('Service Worker Registered');
    });
}
</script>