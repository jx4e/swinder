<?php

function get_db(): PDO {
    static $db = null;
    if ($db === null) {
        $path = __DIR__ . '/../data/swinder.db';
        $db = new PDO('sqlite:' . $path);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $db;
}

function init_db(): void {
    $db = get_db();
    $db->exec("
        CREATE TABLE IF NOT EXISTS pools (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            place_id     TEXT    UNIQUE NOT NULL,
            name         TEXT    NOT NULL,
            address      TEXT,
            photo_url    TEXT,
            rating       REAL,
            swipe_rights INTEGER NOT NULL DEFAULT 0,
            swipe_lefts  INTEGER NOT NULL DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS swipes (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            pool_id    INTEGER NOT NULL,
            direction  TEXT    NOT NULL CHECK (direction IN ('left', 'right')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
}
