<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Nicht eingeloggt"]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("UPDATE users SET last_seen_notifications = NOW() WHERE id = ?");
$stmt->execute([$user_id]);

echo json_encode(["success" => true]);
