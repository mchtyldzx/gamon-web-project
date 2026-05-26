<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/summary.php';

header('Content-Type: application/json');

$period  = in_array($_GET['period'] ?? '', ['day','week','month'], true) ? $_GET['period'] : 'week';
$hood_id = !empty($_GET['neighborhood_id']) ? (int)$_GET['neighborhood_id'] : null;

echo json_encode(
    isset($_GET['ranking']) ? gamon_ranking() : gamon_summary($period, $hood_id)
);
