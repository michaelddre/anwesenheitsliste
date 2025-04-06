<?php
// Start der Session
session_start();

// Datenbankverbindung
require 'db.php';

// Überprüfung, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");  // Weiterleitung zur Login-Seite, wenn der Benutzer nicht eingeloggt ist
  exit();
}

$error = "";
$success = false;

// Benutzer hinzufügen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_user'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $fullname = $_POST['fullname'];

  // Bild hochladen
  $filename = basename($_FILES["image"]["name"]);
  $targetDir = "images/";
  $targetFile = $targetDir . $filename;
  $allowed = ['image/jpeg', 'image/png', 'image/jpg'];

  if (in_array($_FILES["image"]["type"], $allowed)) {
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
      // Passwort hashen
      $hash = password_hash($password, PASSWORD_DEFAULT);
      // Neuen Benutzer in der Datenbank speichern
      $stmt = $pdo->prepare("INSERT INTO users (username, password, name, image) VALUES (?, ?, ?, ?)");
      $stmt->execute([$username, $hash, $fullname, $filename]);
      $success = true;
    } else {
      $error = "Bild konnte nicht hochgeladen werden.";
    }
  } else {
    $error = "Nur JPG und PNG erlaubt.";
  }
}

// Benutzer aus der Datenbank abrufen
$sql = "SELECT * FROM users";
$result = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Bereich</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Header mit den Buttons -->
  <div class="header">
  <button class="admin-btn" onclick="window.location.href='index.php'">Zurück zur Anwesenheitsliste</button>
  </div>

  <div class="admin-container">
    <!-- Formular zum Benutzer hinzufügen -->
    <div class="add-user-form">
      <h3>Neuen Benutzer anlegen</h3>
      <form action="admin.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="username">Benutzername:</label>
          <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
          <label for="fullname">Vollständiger Name:</label>
          <input type="text" id="fullname" name="fullname" required>
        </div>
        
        <div class="form-group">
          <label for="password">Passwort:</label>
          <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
          <label for="image">Profilbild:</label>
          <input type="file" id="image" name="image" accept="image/jpeg, image/png" required>
        </div>
        
        <button type="submit" name="add_user">Benutzer anlegen</button>
      </form>

      <?php if ($success): ?>
        <p class="success">✅ Benutzer erfolgreich hinzugefügt!</p>
      <?php elseif ($error): ?>
        <p class="error">❌ <?= $error ?></p>
      <?php endif; ?>
    </div>

    <!-- Benutzerübersicht -->
    <div class="user-list">
      <h3>Benutzerübersicht</h3>
      <table>
        <thead>
          <tr>
            <th>Benutzername</th>
            <th>Vollständiger Name</th>
            <th>Aktionen</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Benutzer aus der Datenbank anzeigen
          while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                    <td>" . $row['username'] . "</td>
                    <td>" . $row['name'] . "</td>
                    <td>
                      <a href='edit_user.php?id=" . $row['id'] . "'>Bearbeiten</a> | 
                      <a href='delete_user.php?id=" . $row['id'] . "' onclick='return confirm(\"Möchten Sie diesen Benutzer wirklich löschen?\");'>Löschen</a>
                    </td>
                  </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>

<?php
// Verbindung schließen
$pdo = null;
?>
