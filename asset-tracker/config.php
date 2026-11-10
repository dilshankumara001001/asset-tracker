<?php
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') { ini_set('session.cookie_secure', 1); }
session_start();

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

define('DB_HOST', 'localhost');
define('DB_NAME', 'asset_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/asset-tracker'); 
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $e) { die("DB Error: " . $e->getMessage()); }

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function requireLogin() { if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . '/login.php'); exit; } }
function requireAdmin() {
    requireLogin();
    if (($_SESSION['role'] ?? '') !== 'admin') { flash('error', __('access_denied')); header('Location: ' . BASE_URL . '/dashboard.php'); exit; }
}
function isAdmin() { return ($_SESSION['role'] ?? '') === 'admin'; }
function flash($key, $msg = null) {
    if ($msg === null) { $v = $_SESSION[$key] ?? null; unset($_SESSION[$key]); return $v; }
    $_SESSION[$key] = $msg;
}
function show_flash() {
    foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $class) {
        $msg = flash($key);
        if ($msg) {
            echo '<div class="alert alert-' . $class . ' alert-dismissible fade show">' . e($msg);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }
    }
}
function csrf_token() { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(403); die('CSRF validation failed.'); }
}

// 🔥 නැතිවෙච්ච ෆන්ක්ෂන්ස් ටික ආයේ දැම්මා
function validate($data, $rules) {
    $errors = [];
    foreach ($rules as $field => $ruleStr) {
        $list = explode('|', $ruleStr); $val = trim($data[$field] ?? '');
        foreach ($list as $r) {
            if ($r === 'required' && $val === '') { $errors[$field] = ucfirst($field) . ' is required'; break; }
            if ($val === '') continue;
            if (str_starts_with($r, 'max:')) { $max = (int)substr($r, 4); if (strlen($val) > $max) { $errors[$field] = ucfirst($field) . " max $max chars"; break; } }
            if (str_starts_with($r, 'min:')) { $min = (int)substr($r, 4); if (strlen($val) < $min) { $errors[$field] = ucfirst($field) . " min $min chars"; break; } }
            if ($r === 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) { $errors[$field] = 'Invalid email'; break; }
            if (str_starts_with($r, 'date_after:')) {
                $other = substr($r, 11);
                if (!empty($data[$other]) && $val < $data[$other]) { $errors[$field] = ucfirst($field) . " must be after " . $other; break; }
            }
        }
    }
    return $errors;
}

function login_attempts_count($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL " . LOGIN_LOCKOUT_MINUTES . " MINUTE)");
    $stmt->execute([$email]); return (int)$stmt->fetchColumn();
}

function can_transition($from, $to) {
    $valid = ['available'=>['in_use','under_service','lost','disposed'],'in_use'=>['available','under_service','lost'],'under_service'=>['available','in_use','disposed'],'lost'=>['available'],'disposed'=>['available']];
    if ($from === $to) return true; return in_array($to, $valid[$from] ?? []);
}

function log_action($action, $details = '') {
    global $pdo;
    $userId = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?,?,?,?)");
    $stmt->execute([$userId, $action, $details, $ip]);
}

function generateQRCode($text, $path) {
    $lib = __DIR__ . '/libs/phpqrcode/qrlib.php';
    if (file_exists($lib)) { require_once $lib; if (class_exists('QRcode')) { QRcode::png($text, $path, QR_ECLEVEL_L, 10); return true; } }
    $api = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($text);
    $img = @file_get_contents($api);
    if ($img !== false) { file_put_contents($path, $img); return true; }
    return false;
}

function get_setting($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return $val !== false ? $val : $default;
}

// 🔥 භාෂා පරිවර්තන (Translations)
if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'en';
function __($key) {
    global $translations;
    return $translations[$_SESSION['lang']][$key] ?? $translations['en'][$key] ?? $key;
}

$translations = [
    'en' => [
        'dashboard' => 'Dashboard', 'assets' => 'Assets', 'add_asset' => 'Add Asset', 
        'save' => 'Save', 'cancel' => 'Cancel', 'name' => 'Name', 'status' => 'Status',
        'total_assets' => 'Total Assets', 'access_denied' => 'Admin access required',
        'settings' => 'Settings', 'logout' => 'Logout', 'import_csv' => 'Import CSV',
        'code' => 'Code', 'category' => 'Category', 'location' => 'Location', 
        'custodian' => 'Custodian', 'actions' => 'Actions', 'view' => 'View', 
        'edit' => 'Edit', 'delete' => 'Delete', 'search' => 'Search name/code...',
        'all_status' => 'All Status', 'all_categories' => 'All Categories', 'all_locations' => 'All Locations',
        'filter' => 'Filter', 'reset' => 'Reset', 'total_value' => 'Total Asset Value',
        'total_maint' => 'Total Maint. Cost', 'lost' => 'Lost Assets', 'recent_activity' => 'Recent Activity',
        'date' => 'Date', 'action' => 'Action', 'by' => 'By', 'expiring_alerts' => 'Expiring in 30 days',
        'maint_alerts' => 'Maintenance Due (30 days)'
    ],
    'si' => [
        'dashboard' => 'පාලන පුවරුව', 'assets' => 'වත්කම්', 'add_asset' => 'වත්කමක් එකතු කරන්න', 
        'save' => 'සුරකින්න', 'cancel' => 'අවලංගු කරන්න', 'name' => 'නම', 'status' => 'තත්ත්වය',
        'total_assets' => 'මුළු වත්කම්', 'access_denied' => 'පරිපාලක අවසර අවශ්‍යයි',
        'settings' => 'සැකසුම්', 'logout' => 'පිටවීම', 'import_csv' => 'CSV ගෙන්වා ගන්න',
        'code' => 'කේතය', 'category' => 'කාණ්ඩය', 'location' => 'ස්ථානය', 
        'custodian' => 'භාරකරු', 'actions' => 'ක්‍රියාකාරකම්', 'view' => 'බලන්න', 
        'edit' => 'සංස්කරණය', 'delete' => 'මකන්න', 'search' => 'නම හෝ කේතය සොයන්න...',
        'all_status' => 'සියලු තත්ත්වයන්', 'all_categories' => 'සියලු කාණ්ඩ', 'all_locations' => 'සියලු ස්ථාන',
        'filter' => 'පෙරහන් කරන්න', 'reset' => 'යළි සකසන්න', 'total_value' => 'මුළු වත්කම් වටිනාකම',
        'total_maint' => 'මුළු අලුත්වැඩියා වියදම', 'lost' => 'නැතිවූ වත්කම්', 'recent_activity' => 'මෑත ක්‍රියාකාරකම්',
        'date' => 'දිනය', 'action' => 'ක්‍රියාව', 'by' => 'විසින්', 'expiring_alerts' => 'දින 30කින් කල් ඉකුත් වන',
        'maint_alerts' => 'අලුත්වැඩියා කළ යුතු (දින 30)'
    ],
    'ta' => [
        'dashboard' => 'கட்டுப்பாட்டு பலகை', 'assets' => 'சொத்துகள்', 'add_asset' => 'சொத்து சேர்க்க', 
        'save' => 'சேமி', 'cancel' => 'ரத்து செய்', 'name' => 'பெயர்', 'status' => 'நிலை',
        'total_assets' => 'மொத்த சொத்துகள்', 'access_denied' => 'நிர்வாகி அனுமதி தேவை',
        'settings' => 'அமைப்புகள்', 'logout' => 'வெளியேறு', 'import_csv' => 'CSV இறக்குமதி',
        'code' => 'குறியீடு', 'category' => 'வகை', 'location' => 'இடம்', 
        'custodian' => 'பாதுகாவலர்', 'actions' => 'செயல்கள்', 'view' => 'பார்', 
        'edit' => 'திருத்து', 'delete' => 'நீக்கு', 'search' => 'பெயர்/குறியீடு தேடு...',
        'all_status' => 'அனைத்து நிலைகள்', 'all_categories' => 'அனைத்து வகைகள்', 'all_locations' => 'அனைத்து இடங்கள்',
        'filter' => 'வடிகட்டு', 'reset' => 'மீட்டமை', 'total_value' => 'மொத்த சொத்து மதிப்பு',
        'total_maint' => 'மொத்த பராமரிப்பு செலவு', 'lost' => 'இழந்த சொத்துகள்', 'recent_activity' => 'சமீபத்திய செயல்பாடு',
        'date' => 'தேதி', 'action' => 'செயல்', 'by' => 'மூலம்', 'expiring_alerts' => '30 நாட்களில் காலாவதியாகும்',
        'maint_alerts' => 'பராமரிப்பு தேவை (30 நாட்கள்)'
    ]
];