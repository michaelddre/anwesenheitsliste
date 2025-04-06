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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
  <!-- Header mit Logout- und Admin-Buttons -->
  <header class="custom-header">
 
  <img src="images/logo.png" alt="Attendio" class="logo">

    <div id="notification-bubble" class="notification-bubble">
  <svg class="bell-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
    <path d="M12 2C10.3 2 9 3.3 9 5v1.1c-3.1.5-5.5 3.1-5.5 6.3v4l-1.5 1.5V19h18v-1.1L20.5 16v-4c0-3.2-2.4-5.8-5.5-6.3V5c0-1.7-1.3-3-3-3zm0 20c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2z"/>
  </svg>
  <span class="badge" id="notification-badge"></span>
</div>

  <span class="badge" id="notification-badge"></span>
</div>

  <img id="user-avatar" class="user-avatar" src="images/default.jpg" alt="Benutzerbild">

  <div class="user-greeting">
    <div><span>Hallo, <strong id="username">...</strong> 👋</span></div>
    <div class="last-login" id="last-login-time">Letzter Login: –</div>
  </div>
  <div id="online-users" class="online-users">
  <span class="label">Gerade online:</span>
  <div id="online-avatar-list" class="avatars"></div>
</div>


  <div class="header-buttons">
    <a href="admin.php" class="header-btn">Benutzerverwaltung</a>
    <a href="logout.php" class="header-btn">Logout</a>
  </div>
</header>



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

<!-- Socket.IO zuerst -->
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
<!-- Dann dein eigenes Script -->
<script src="script.js" defer></script>


</body>
</html>
