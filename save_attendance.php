<?php
require 'db.php';

// Überprüfen, ob das Formular korrekt abgesendet wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['week'], $_POST['day'], $_POST['new_status'])) {
    $userId = $_POST['user_id'];  // ID des Benutzers
    $week = $_POST['week'];       // Kalenderwoche
    $day = $_POST['day'];         // Tag der Woche
    $newStatus = $_POST['new_status']; // Neue Abwesenheitsart
    $oldStatus = $_POST['old_status']; // Alte Abwesenheitsart, falls verfügbar

    // Sicherstellen, dass alle Parameter vorhanden sind
    if ($userId && $week && $day && $newStatus) {
        try {
            // Aktualisierung der Abwesenheit in der Tabelle "attendance"
            $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE user_id = ? AND week = ? AND day = ?");
            $stmt->execute([$newStatus, $userId, $week, $day]);

            // Erfolgreiche Speicherung
            echo json_encode(['status' => 'success', 'message' => 'Abwesenheit wurde erfolgreich gespeichert.']);
        } catch (PDOException $e) {
            // Fehlerbehandlung
            echo json_encode(['status' => 'error', 'message' => 'Fehler beim Speichern der Abwesenheit: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Fehlende Daten.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Anfrage.']);
}
?>
