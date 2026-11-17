<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

function antworte(int $status, array $daten): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($daten, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    antworte(405, ['ok' => false, 'meldung' => 'Bitte sende das Formular per POST.']);
}

if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 12 * 1024 * 1024) {
    antworte(413, ['ok' => false, 'meldung' => 'Die Anfrage ist zu gross. Fotos dürfen maximal 10 MB gross sein.']);
}

$grenzen = [
    'vorname' => 100, 'nachname' => 100, 'email' => 254,
    'ort' => 200, 'fotografenname' => 150, 'fotogeschichte' => 4000, 'sportart' => 30, 'moment' => 4000,
];
$daten = [];
$fehler = [];
foreach ($grenzen as $feld => $limit) {
    $wert = $_POST[$feld] ?? '';
    if (!is_string($wert) || !preg_match('//u', $wert)) {
        $fehler[$feld] = 'Eine Eingabe hat ein ungültiges Format.';
        $wert = '';
    }
    $wert = trim($wert);
    if (strlen($wert) > $limit) {
        $fehler[$feld] = 'Eine Eingabe ist zu lang.';
    }
    $daten[$feld] = $wert === '' ? null : $wert;
}

foreach (['vorname' => 'Vorname', 'nachname' => 'Nachname'] as $feld => $label) {
    if ($daten[$feld] === null) {
        $fehler[$feld] = 'Bitte gib deinen ' . $label . ' ein.';
    }
}
if (!filter_var($daten['email'], FILTER_VALIDATE_EMAIL)) {
    $fehler['email'] = 'Bitte gib eine gültige E-Mail-Adresse ein.';
}
$sportarten = ['Fussball', 'Basketball', 'Volleyball', 'Leichtathletik', 'Eishockey', 'Andere'];
if (!in_array($daten['sportart'], $sportarten, true)) {
    $fehler['sportart'] = 'Bitte wähle eine Sportart aus.';
}

$tage = $_POST['tage'] ?? [];
$wochentage = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
if (!is_array($tage) || count($tage) > count($wochentage)
    || array_filter($tage, static fn ($tag) => !in_array($tag, $wochentage, true))) {
    $fehler['tage'] = 'Bitte wähle gültige Wochentage aus.';
} else {
    $daten['tage'] = json_encode(array_values(array_unique($tage)));
}

$foto = $_FILES['foto'] ?? null;
$daten['foto_name'] = null;
$daten['foto_typ'] = null;
$fotoPfad = null;
if ($foto !== null && $foto['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($foto['error'] !== UPLOAD_ERR_OK || $foto['size'] > 10 * 1024 * 1024
        || !is_uploaded_file($foto['tmp_name'])) {
        $fehler['foto'] = 'Das Foto konnte nicht hochgeladen werden. Es darf maximal 10 MB gross sein.';
    } else {
        $bild = @getimagesize($foto['tmp_name']);
        $typ = $bild['mime'] ?? '';
        if (!in_array($typ, ['image/jpeg', 'image/png'], true)) {
            $fehler['foto'] = 'Bitte wähle ein JPG- oder PNG-Foto.';
        } else {
            $fotoPfad = $foto['tmp_name'];
            $daten['foto_name'] = basename(str_replace('\\', '/', $foto['name']));
            $daten['foto_typ'] = $typ;
        }
    }
}

if ($fehler) {
    antworte(422, ['ok' => false, 'meldung' => 'Bitte überprüfe deine Eingaben.', 'fehler' => $fehler]);
}

try {
    $pdo = verbindeDatenbank();
    $stmt = $pdo->prepare(
        'INSERT INTO anmeldungen
            (vorname, nachname, email, ort, fotografenname, fotogeschichte, sportart, tage, moment, foto_name, foto_typ, foto)
         VALUES
            (:vorname, :nachname, :email, :ort, :fotografenname, :fotogeschichte, :sportart, :tage, :moment, :foto_name, :foto_typ, :foto)'
    );
    foreach ($daten as $feld => $wert) {
        $stmt->bindValue(':' . $feld, $wert, $wert === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }
    $fotoDaten = $fotoPfad === null ? null : file_get_contents($fotoPfad);
    if ($fotoDaten === false) {
        throw new RuntimeException('Foto konnte nicht gelesen werden.');
    }
    $stmt->bindValue(':foto', $fotoDaten, $fotoDaten === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
    $stmt->execute();

    antworte(201, [
        'ok' => true,
        'meldung' => 'Danke! Deine Anmeldung wurde gespeichert.',
        'id' => (int) $pdo->lastInsertId(),
    ]);
} catch (Throwable $fehler) {
    error_log($fehler->getMessage());
    antworte(500, ['ok' => false, 'meldung' => 'Die Anmeldung konnte nicht gespeichert werden. Bitte versuche es erneut.']);
}
