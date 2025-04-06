<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

try {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!$data || !isset($data['user_id'], $data['week'], $data['day'], $data['status'])) {
        throw new Exception("Fehlende Eingabedaten");
    }

    $user_id = (int)$data['user_id'];
    $week = (int)$data['week'];
    $day = (int)$data['day'];
    $status = $data['status'];

    $changed_by_id = $_SESSION['user_id'] ?? null;

    $stmt = $pdo->prepare("SELECT status FROM attendance WHERE user_id = ? AND week = ? AND day = ?");
    $stmt->execute([$user_id, $week, $day]);
    $old = $stmt->fetchColumn();

    if ($old !== false) {
        $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE user_id = ? AND week = ? AND day = ?");
        $stmt->execute([$status, $user_id, $week, $day]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO attendance (user_id, week, day, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $week, $day, $status]);
    }

    if ($old !== $status) {
        $year = date("Y");
        $jan4 = strtotime("$year-01-04");
        $start = strtotime("+".($week - 1)." weeks", $jan4);
        $monday = strtotime("last Monday", $start);
        if (date("N", $start) == 1) {
            $monday = $start;
        }
        $timestamp = $monday + ($day - 1) * 86400;
        $date = date("Y-m-d", $timestamp);

        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_name = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$changed_by_id]);
        $changed_by_name = $stmt->fetchColumn();

        $stmt = $pdo->prepare("INSERT INTO notifications 
          (user_id, user_name, date, old_status, new_status, changed_by_id, changed_by_name)
          VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $user_name, $date, $old ?? '', $status, $changed_by_id, $changed_by_name]);

        echo json_encode(["success" => true]);
        exit;
    }

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
