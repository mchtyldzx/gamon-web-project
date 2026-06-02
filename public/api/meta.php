<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

header('Content-Type: application/json');

try {
    $pdo = gamon_pdo();
    $cats  = $pdo->query('SELECT id, code, name FROM waste_categories ORDER BY name')->fetchAll();
    $hoods = $pdo->query('SELECT id, name, locality FROM neighborhoods ORDER BY locality, name')->fetchAll();
    echo json_encode(['categories' => $cats, 'neighborhoods' => $hoods]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
