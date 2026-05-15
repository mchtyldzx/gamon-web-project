<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

function gamon_table_exists(PDO $pdo, string $name): bool
{
    $stmt = $pdo->prepare(
        "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = :name LIMIT 1"
    );
    $stmt->execute(['name' => $name]);
    return (bool) $stmt->fetchColumn();
}

try {
    $pdo = gamon_pdo();
    if (!gamon_table_exists($pdo, 'waste_categories')) {
        gamon_json_response(
            [
                'error' => 'Database not initialized.',
                'hint' => 'Run: php scripts/init-database.php',
            ],
            503
        );
        return;
    }
    $categories = $pdo->query(
        'SELECT code, name, description FROM waste_categories ORDER BY name ASC'
    )->fetchAll();
    $neighborhoods = $pdo->query(
        'SELECT id, name, locality FROM neighborhoods ORDER BY locality ASC, name ASC'
    )->fetchAll();
    gamon_json_response(
        [
            'categories' => $categories,
            'neighborhoods' => $neighborhoods,
        ]
    );
} catch (Throwable $e) {
    gamon_json_response(
        [
            'error' => 'Meta endpoint failed.',
            'detail' => $e->getMessage(),
        ],
        500
    );
}
