<?php
// Liste der bayerischen Feiertage (fixe Feiertage)
$holidays = [
    '01-01', // Neujahrstag
    '06-01', // Heilige Drei Könige
    '01-05', // Tag der Arbeit
    '15-08', // Mariä Himmelfahrt
    '03-10', // Tag der Deutschen Einheit
    '25-12', // Weihnachten
    '26-12', // Zweiter Weihnachtstag
    // Weitere bayerische Feiertage
    '01-11', // Allerheiligen
    '08-12'  // Mariä Empfängnis
];

// Funktion zum Prüfen, ob ein Datum ein Feiertag ist
function isHoliday($date) {
    global $holidays;
    return in_array($date, $holidays);
}
?>
