<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Letzten Gelesen-Zeitpunkt & Login-Zeitpunkt laden
$stmt = $pdo->prepare("SELECT last_seen_notifications, last_login FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$lastSeen = $userData['last_seen_notifications'] ?: '2000-01-01 00:00:00';
$lastLogin = $userData['last_login'] ?: '2000-01-01 00:00:00';

// Nur neue Benachrichtigungen seit "gelesen"
$stmt = $pdo->prepare("
    SELECT user_name, date, old_status, new_status, changed_by_name, created_at
    FROM notifications 
    WHERE created_at > ? 
    ORDER BY created_at ASC
");
$stmt->execute([$lastSeen]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Rückgabe: Benachrichtigungen + Zeit des letzten Logins
echo json_encode([
    "notifications" => $notifications,
    "last_login" => $lastLogin
]);
