<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

gamon_session_destroy();
echo json_encode(['ok' => true]);
