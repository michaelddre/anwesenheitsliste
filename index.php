<?php
require 'auth.php';
require 'db.php';
require 'holidays.php';  // Feiertage einbinden

// Setzt die Locale auf Deutsch, damit die Wochentage auf Deutsch erscheinen
setlocale(LC_TIME, 'de_DE.UTF-8');  // Locale auf Deutsch setzen

$year = date('Y');
$weeksByMonth = [];

$months = [
    'januar' => 1,
    'februar' => 2,
    'märz' => 3,
    'april' => 4,
    'mai' => 5,
    'juni' => 6,
    'juli' => 7,
    'august' => 8,
    'september' => 9,
    'oktober' => 10,
    'november' => 11,
    'dezember' => 12
];

// Abrufen der Benutzer-Daten
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

for ($w = 1; $w <= 53; $w++) {
    $start = new DateTime();
    $start->setISODate($year, $w, 1);
    if ((int)$start->format('Y') !== (int)$year) continue;

    $end = clone $start;
    $end->modify('+4 days');
    $month = strftime('%B', $start->getTimestamp());

    $days = [];
    for ($d = 0; $d < 5; $d++) {
        $day = clone $start;
        $day->modify("+$d day");
        $days[] = [
            'date' => $day->format('d.m.Y'),
            'isHoliday' => isHoliday($day->format('d-m'))  // Feiertag prüfen
        ];
    }

    $weeksByMonth[$month][] = [
        'week' => $w,
        'start' => $start->format('d.m.'),
        'end' => $end->format('d.m.'),
        'days' => $days  // Speichern der Tage und Feiertagsprüfung
    ];
}

uksort($weeksByMonth, function ($a, $b) use ($months) {
    return $months[strtolower($a)] - $months[strtolower($b)];
});
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Anwesenheitsübersicht <?= $year ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Header mit Logout- und Admin-Buttons -->
  <div class="header">
    <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    <button class="admin-btn" onclick="window.location.href='admin.php'">Benutzerverwaltung</button>
  </div>

  <div class="year-container">
    <h1 class="accordion-title">Anwesenheitsübersicht für <?= $year ?></h1>

    <?php foreach ($weeksByMonth as $month => $monthWeeks): ?>
      <div class="month-box">
        <div class="month-header" onclick="this.nextElementSibling.classList.toggle('open'); this.classList.toggle('expanded');">
          <h2 class="month-title"><?= ucfirst($month) ?></h2>
          <span class="month-arrow">▼</span>
        </div>
        <div class="month-content open">
          <div class="flex-header">
            <div class="flex-name-column"></div>
            <?php foreach ($monthWeeks as $week): ?>
              <div class="week-block">
                <div class="week-label">
                  KW <?= $week['week'] ?><br>
                  <span class="accordion-dates"><?= $week['start'] ?> – <?= $week['end'] ?></span>
                </div>
                <div class="week-days">
                  <?php foreach ($week['days'] as $index => $day): ?>
                    <div class="day-cell">
                      <div class="weekday"><?= strftime('%a', strtotime($day['date'])) ?></div>  <!-- Deutscher Wochentag -->
                      <div class="day-date"><?= date('d.m.', strtotime($day['date'])) ?></div>  <!-- Datum -->
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="month-body" data-weeks="<?= implode(',', array_column($monthWeeks, 'week')) ?>">
            <!-- Benutzerbilder und Hover-Effekte -->
            <?php foreach ($users as $user): ?>
                         <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <script src="http://localhost:3000/socket.io/socket.io.js"></script>
  <script src="script.js"></script>
</body>
</html>
