<?php
session_start();

// Wenn der Benutzer nicht eingeloggt ist, weiterleiten zur Login-Seite
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
