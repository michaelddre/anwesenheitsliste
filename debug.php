<?php
require 'db.php';

$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Debug: Nutzeranzeige</title>
  <style>
    .user-block {
      text-align: center;
      margin-bottom: 30px;
    }

    .user-avatar {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      object-fit: cover;
      display: block;
      margin: 0 auto 10px auto;
    }

    .status-circle {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      background-color: #a7f3d0;
      color: #000;
      font-weight: bold;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 20px;
      margin: auto;
    }

    body {
      font-family: sans-serif;
      padding: 40px;
      max-width: 600px;
      margin: auto;
    }
  </style>
</head>
<body>
  <h2>Debug: Nutzeranzeige</h2>

  <?php foreach ($users as $user): ?>
    <div class="user-block">
      <img src="images/<?= htmlspecialchars($user['image']) ?>" alt="<?= htmlspecialchars($user['name']) ?>" class="user-avatar">
      <div><?= htmlspecialchars($user['name']) ?></div>
      <div class="status-circle">A</div>
    </div>
  <?php endforeach; ?>

</body>
</html>
