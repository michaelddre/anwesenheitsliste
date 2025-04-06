<?php
$host = 'localhost';
$dbname = 'michael_HRS1';
$username = 'michael_HRS1';
$password = 'Lestat121212!!!'; // das Passwort, das du beim Erstellen der Datenbank gesetzt hast

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
}
?>
