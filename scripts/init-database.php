<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config.php';
require_once dirname(__DIR__) . '/app/db.php';

function gamon_exec_sql_file(PDO $pdo, string $path): void
{
    if (!is_readable($path)) {
        fwrite(STDERR, "Missing file: {$path}\n");
        exit(1);
    }
    $sql = file_get_contents($path);
    if ($sql === false) {
        fwrite(STDERR, "Cannot read: {$path}\n");
        exit(1);
    }
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        static fn (string $s): bool => $s !== ''
    );
    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }
}

$pdo = gamon_pdo();
$root = dirname(__DIR__);
gamon_exec_sql_file($pdo, $root . '/sql/schema.sql');
gamon_exec_sql_file($pdo, $root . '/sql/seed.sql');
echo "Database ready at " . GAMON_DB_PATH . PHP_EOL;
