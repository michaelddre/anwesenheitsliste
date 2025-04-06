<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Benutzer auslesen
    $query = "SELECT id, password FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Login erfolgreich → Session speichern
        $_SESSION['user_id'] = $user['id'];

        // Zeitstempel des letzten Logins aktualisieren
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        header("Location: index.php");
        exit();
    } else {
        // Login fehlgeschlagen
        header("Location: login.php?error=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <div class="login-container">
    <form action="login.php" method="POST" class="login-form">
      <h2>Login</h2>
      <?php if (isset($_GET['error'])): ?>
        <p class="error">❌ Benutzername oder Passwort ist falsch</p>
      <?php endif; ?>

      <div class="input-group">
        <input type="text" name="username" placeholder="Benutzername" required>
      </div>

      <div class="input-group password-container">
        <input type="password" name="password" placeholder="Passwort" required id="password">
        <button type="button" class="toggle-password-button" onclick="togglePassword()">
          <i class="fa fa-eye"></i>
        </button>
      </div>

      <button type="submit" class="login-button">Einloggen</button>
    </form>

    <div class="forgot-password">
      <a href="reset_password.php" class="forgot-password-button">Passwort vergessen?</a>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordField = document.getElementById('password');
      const toggleButton = document.querySelector('.toggle-password-button i');
      if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleButton.classList.remove('fa-eye');
        toggleButton.classList.add('fa-eye-slash');
      } else {
        passwordField.type = "password";
        toggleButton.classList.remove('fa-eye-slash');
        toggleButton.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>
