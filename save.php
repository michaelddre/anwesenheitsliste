<?php
session_start();
require 'db.php';

// POST: name, jahr, woche, tag, status
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id']) || !$data) {
    http_response_code(403);
    exit;
}

$name = $data['name'];
$jahr = $data['jahr'];
$woche = $data['woche'];
$tag = $data['tag']; // 0 bis 4
$status = $data['status'];

// Wenn bereits ein Eintrag existiert: UPDATE
$stmt = $pdo->prepare("SELECT id FROM attendance WHERE name = ? AND jahr = ? AND woche = ? AND tag = ?");
$stmt->execute([$name, $jahr, $woche, $tag]);
$existing = $stmt->fetch();

if ($existing) {
    $update = $pdo->prepare("UPDATE attendance SET status = ? WHERE id = ?");
    $update->execute([$status, $existing['id']]);
} else {
    $insert = $pdo->prepare("INSERT INTO attendance (name, jahr, woche, tag, status) VALUES (?, ?, ?, ?, ?)");
    $insert->execute([$name, $jahr, $woche, $tag, $status]);
}

echo json_encode(['success' => true]);
