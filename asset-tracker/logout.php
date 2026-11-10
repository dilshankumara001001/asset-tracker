<?php
require 'config.php';
if (isset($_SESSION['user_id'])) {
    log_action('Logout', isset($_GET['timeout']) ? 'Auto-logged out due to inactivity' : 'User logged out manually');
}
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;