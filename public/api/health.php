<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/health.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

echo json_encode(
    gamon_health_payload(),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
