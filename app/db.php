<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function gamon_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    if (!is_dir(GAMON_DATA_DIR)) {
        mkdir(GAMON_DATA_DIR, 0755, true);
    }
    $pdo = new PDO('sqlite:' . GAMON_DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON;');
    return $pdo;
}
