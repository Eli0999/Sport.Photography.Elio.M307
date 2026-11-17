CREATE TABLE IF NOT EXISTS anmeldungen (
    id INTEGER PRIMARY KEY,
    vorname TEXT NOT NULL,
    nachname TEXT NOT NULL,
    email TEXT NOT NULL,
    ort TEXT,
    fotografenname TEXT,
    fotogeschichte TEXT,
    sportart TEXT NOT NULL,
    tage TEXT NOT NULL,
    moment TEXT,
    foto_name TEXT,
    foto_typ TEXT,
    foto BLOB,
    erstellt_am TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
