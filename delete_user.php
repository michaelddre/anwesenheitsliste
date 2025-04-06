<?php
session_start();
require 'db.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Benutzer aus der Datenbank löschen
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        // Erfolgreich gelöscht, zurück zur Admin-Seite
        header("Location: admin.php");
        exit();
    } else {
        // Fehler beim Löschen
        echo "Fehler beim Löschen des Benutzers.";
    }
}

$pdo = null;
?>
