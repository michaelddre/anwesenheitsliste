<?php
require 'db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, name, image FROM users ORDER BY name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Fehler beim Laden der Nutzer: ' . $e->getMessage()]);
}
