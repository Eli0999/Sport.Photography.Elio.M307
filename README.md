# Sportblick – Modul 307

Das Anmeldeformular sendet seine Daten als `FormData` per POST an `anfrage.php`. PHP validiert die Eingaben und speichert sie über PDO in SQLite. Die JSON-Antwort liefert den Status für die Meldung im Formular.

## Lokal starten

In PowerShell im Ordner `Sport.Photography.Elio.M307` ausführen.

```powershell
$phpDir = Split-Path (Get-Command php).Source
php -d "extension_dir=$phpDir/ext" -d extension=pdo_sqlite -d upload_max_filesize=10M -d post_max_size=12M -S localhost:8000 router.php
```

[Sportblick öffnen](http://localhost:8000). Der Router gibt die Dateien der Anwendung frei. Beim ersten gültigen POST erstellt PHP `storage/sportblick.sqlite` aus `schema.sql`. Die Datenbank enthält die Anmeldedaten und optional die hochgeladenen Fotos.

## Datenfluss prüfen

1. Formular leer absenden. Die Antwort hat den Status `422` und die Pflichtfelder sind markiert.
2. Eine gültige Anmeldung mit einer Testadresse absenden. Die Antwort hat den Status `201`, die Meldung erscheint grün und das Formular wird zurückgesetzt.
3. `storage/sportblick.sqlite` in einem SQLite-Viewer öffnen und diese Abfrage ausführen.

```sql
SELECT id, vorname, nachname, email, sportart, tage, foto_name, erstellt_am
FROM anmeldungen
ORDER BY id DESC
LIMIT 5;
```

4. Einen ungültigen Datensatz absenden und die Abfrage wiederholen. Die Anzahl der Einträge bleibt gleich.
5. Die Seite geöffnet lassen, den PHP-Server mit `Ctrl+C` stoppen und erneut absenden. Das Formular meldet den Verbindungsfehler.
