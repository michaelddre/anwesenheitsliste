<?php
require 'db.php';
header('Content-Type: application/json');

try {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!$data) {
        $data = $_POST;
    }

    if (
        !isset($data['user_id']) ||
        !isset($data['week']) ||
        !isset($data['day']) ||
        !isset($data['status'])
    ) {
        throw new Exception("Fehlende Eingabedaten");
    }

    $user_id = (int)$data['user_id'];
    $week = (int)$data['week'];
    $day = (int)$data['day'];
    $status = $data['status'];

    $debug = [
        "received_data" => $data
    ];

    // Alten Status abfragen
    $stmt = $pdo->prepare("SELECT status FROM attendance WHERE user_id = ? AND week = ? AND day = ?");
    $stmt->execute([$user_id, $week, $day]);
    $old = $stmt->fetchColumn();
    $debug["old_status"] = $old;

    // Speichern (insert oder update)
    if ($old !== false) {
        $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE user_id = ? AND week = ? AND day = ?");
        $stmt->execute([$status, $user_id, $week, $day]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO attendance (user_id, week, day, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $week, $day, $status]);
    }

    $debug["new_status"] = $status;
    $debug["change_detected"] = ($old !== $status);

    // Wenn sich etwas geändert hat: Benachrichtigung speichern
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
        $debug["calculated_date"] = $date;

        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_name = $stmt->fetchColumn();

        $debug["user_name"] = $user_name;

        if (!$user_name) {
            throw new Exception("Benutzername nicht gefunden");
        }

        // Eintrag speichern
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, user_name, date, old_status, new_status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $user_name, $date, $old ?? '', $status]);

        $debug["notification_inserted"] = true;

        echo json_encode([
            "success" => true,
            "message" => "Benachrichtigung gespeichert",
            "debug" => $debug
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Kein Statuswechsel → keine Benachrichtigung",
        "debug" => $debug
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
