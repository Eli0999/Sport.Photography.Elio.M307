<?php
declare(strict_types=1);

function verbindeDatenbank(): PDO
{
    $ordner = __DIR__ . '/storage';
    if (!is_dir($ordner) && !@mkdir($ordner, 0700) && !is_dir($ordner)) {
        throw new RuntimeException('Datenbankordner konnte nicht erstellt werden.');
    }

    $pdo = new PDO('sqlite:' . $ordner . '/sportblick.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA busy_timeout = 5000');
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));
    return $pdo;
}
