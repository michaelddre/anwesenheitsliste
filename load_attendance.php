<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Alle Einträge zurückgeben – nicht nur für einen User!
try {
  $stmt = $pdo->query("SELECT user_id, calendar_week, year, weekday, status FROM attendance");
  $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['success' => true, 'entries' => $entries]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
