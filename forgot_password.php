<?php
// Datenbankverbindung
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Überprüfen, ob die E-Mail-Adresse in der Datenbank vorhanden ist
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Hier könntest du einen Token erstellen und die E-Mail zum Zurücksetzen des Passworts verschicken
        // E-Mail zum Zurücksetzen des Passworts
        $token = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->execute([$token, $email]);

        // Sende die E-Mail (hier kommt der Mailversand-Code, wie zum Beispiel mit PHP mail() oder PHPMailer)
        $resetLink = "https://deine-website.com/reset_password.php?token=$token";

        // Hier solltest du den Mailversand implementieren
        // mail($email, "Passwort zurücksetzen", "Klicke hier, um dein Passwort zurückzusetzen: $resetLink");

        echo "Eine E-Mail zum Zurücksetzen des Passworts wurde gesendet.";
    } else {
        echo "E-Mail-Adresse nicht gefunden.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort vergessen</title>
</head>
<body>
    <h1>Passwort zurücksetzen</h1>
    <form action="forgot_password.php" method="POST">
        <label for="email">E-Mail-Adresse:</label>
        <input type="email" name="email" required>
        <button type="submit">Passwort zurücksetzen</button>
    </form>
</body>
</html>
