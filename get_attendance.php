<?php
require 'db.php';
header('Content-Type: application/json');

$week = isset($_GET['week']) ? (int)$_GET['week'] : null;

if (!$week) {
    http_response_code(400);
    echo json_encode(['error' => 'Woche fehlt']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id, day, status FROM attendance WHERE week = ?");
    $stmt->execute([$week]);
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($attendance);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Fehler beim Laden der Anwesenheiten: ' . $e->getMessage()]);
}
