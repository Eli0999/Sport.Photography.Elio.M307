<?php
declare(strict_types=1);

// Der lokale PHP-Server liefert nur die Dateien der Sportblick-Anwendung aus.
$pfad = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($pfad === '/') {
    header('Content-Type: text/html; charset=utf-8');
    readfile(__DIR__ . '/index.html');
    return true;
}
if (in_array($pfad, ['/index.html', '/style.css', '/script.js', '/anfrage.php'], true)) {
    return false;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'Seite nicht gefunden.';
