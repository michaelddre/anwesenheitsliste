<?php
session_start();
require 'db.php';

$error = "";
$success = false;

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    // Benutzerinformationen aus der Datenbank abrufen
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header("Location: admin.php"); // Falls der Benutzer nicht existiert, zurück zur Admin-Seite
        exit();
    }
}

// Benutzer aktualisieren
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_user'])) {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $password = $_POST['password'];
    
    // Bild hochladen
    $filename = $user['image']; // Behalte das bestehende Bild, falls kein neues hochgeladen wird
    if (!empty($_FILES["image"]["name"])) {
        $filename = basename($_FILES["image"]["name"]);
        $targetDir = "images/";
        $targetFile = $targetDir . $filename;
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        
        if (in_array($_FILES["image"]["type"], $allowed)) {
            move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
        } else {
            $error = "Nur JPG und PNG erlaubt.";
        }
    }

    // Passwort hashen
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Benutzer in der Datenbank aktualisieren
    $stmt = $pdo->prepare("UPDATE users SET username = ?, name = ?, password = ?, image = ? WHERE id = ?");
    if ($stmt->execute([$username, $fullname, $hash, $filename, $user_id])) {
        $success = true;
    } else {
        $error = "Fehler beim Aktualisieren des Benutzers.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Benutzer bearbeiten</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="header">
  <button class="admin-btn" onclick="window.location.href='admin.php'">Zurück zur Benutzerverwaltung</button>
  </div>
  <div class="admin-container">
    <h3>Benutzer bearbeiten</h3>
    <?php if ($success): ?>
      <p class="success">✅ Benutzer erfolgreich aktualisiert!</p>
    <?php elseif ($error): ?>
      <p class="error">❌ <?= $error ?></p>
    <?php endif; ?>

    <form action="edit_user.php?id=<?= $user_id ?>" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="username">Benutzername:</label>
        <input type="text" id="username" name="username" value="<?= $user['username'] ?>" required>
      </div>

      <div class="form-group">
        <label for="fullname">Vollständiger Name:</label>
        <input type="text" id="fullname" name="fullname" value="<?= $user['name'] ?>" required>
      </div>

      <div class="form-group">
        <label for="password">Passwort:</label>
        <input type="password" id="password" name="password" required>
      </div>

      <div class="form-group">
        <label for="image">Profilbild:</label>
        <input type="file" id="image" name="image" accept="image/jpeg, image/png">
      </div>

      <button type="submit" name="edit_user">Benutzer aktualisieren</button>
    </form>
  </div>
  
  <script src="script.js"></script>
</body>
</html>

<?php
// Verbindung schließen
$pdo = null;
?>
