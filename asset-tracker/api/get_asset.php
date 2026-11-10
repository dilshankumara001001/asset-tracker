<?php
require __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Not authenticated']); exit; }
$code = $_GET['code'] ?? ''; $id = (int)($_GET['id'] ?? 0);
if (!$code && !$id) { http_response_code(400); echo json_encode(['error'=>'Provide code or id']); exit; }
$sql = "SELECT a.*,c.name AS category,l.name AS location,u.name AS custodian FROM assets a LEFT JOIN categories c ON a.category_id=c.id LEFT JOIN locations l ON a.location_id=l.id LEFT JOIN users u ON a.custodian_id=u.id";
if ($code) { $stmt = $pdo->prepare($sql . " WHERE a.asset_code=?"); $stmt->execute([$code]); }
else { $stmt = $pdo->prepare($sql . " WHERE a.id=?"); $stmt->execute([$id]); }
$asset = $stmt->fetch();
if (!$asset) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }
echo json_encode(['asset'=>$asset], JSON_PRETTY_PRINT);