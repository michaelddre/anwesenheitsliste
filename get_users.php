<?php
require_once 'db.php';
header('Content-Type: application/json');

$sql = "SELECT id, username, fullname, image FROM users ORDER BY fullname";
$result = $conn->query($sql);

$users = [];
while ($row = $result->fetch_assoc()) {
  $users[] = $row;
}

echo json_encode($users);
