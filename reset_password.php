<?php
// DB-Verbindung und Session starten
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Überprüfen, ob der Benutzername und der Code eingegeben wurden
    $username = $_POST['username'];
    $reset_code = $_POST['reset_code'];

    // Holen des Benutzers anhand des Benutzernamens
    $stmt = $pdo->prepare("SELECT id, reset_code FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['reset_code'] == $reset_code) {
        // Benutzer gefunden und Code stimmt überein
        // Benutzer kann nun ein neues Passwort setzen
        if (isset($_POST['password'])) {
            $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Passwort in der DB aktualisieren
            $update_stmt = $pdo->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE id = ?");
            $update_stmt->execute([$new_password, $user['id']]);

            echo "<p>✅ Passwort erfolgreich zurückgesetzt!</p>";
        }
    } else {
        echo "<p>❌ Benutzername oder Reset-Code ist falsch!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Passwort zurücksetzen</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="login-container">
    <form action="reset_password.php" method="POST" class="login-form">
      <h2>Passwort zurücksetzen</h2>

      <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
      <?php endif; ?>

      <div class="input-group">
        <input type="text" name="username" placeholder="Benutzername" required>
      </div>

      <div class="input-group">
        <input type="text" name="reset_code" placeholder="Vierstelliger Code" maxlength="4" required>
      </div>

      <div class="input-group">
        <input type="password" name="password" placeholder="Neues Passwort" required>
      </div>

      <button type="submit" class="login-button">Passwort zurücksetzen</button>
    </form>

    <!-- Button für "Zurück zum Login" -->
    <div class="back-to-login">
      <a href="login.php" class="back-to-login-button">Zurück zum Login</a>
    </div>
  </div>
</body>
</html>
