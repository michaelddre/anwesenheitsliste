<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$jahr = $_GET['jahr'] ?? date("Y");
$woche = $_GET['woche'] ?? date("W");

$stmt = $pdo->prepare("SELECT name, tag, status FROM attendance WHERE jahr = ? AND woche = ?");
$stmt->execute([$jahr, $woche]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
