<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/auth.php';

header('Content-Type: application/json');

$user = gamon_session_user();
if ($user === null) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

echo json_encode($user);
