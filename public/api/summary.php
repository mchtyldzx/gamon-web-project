<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/summary.php';
require_once __DIR__ . '/../app/auth.php';

header('Content-Type: application/json');

$period   = in_array($_GET['period'] ?? '', ['day', 'week', 'month'], true)
              ? $_GET['period'] : 'week';
$hood_id  = !empty($_GET['neighborhood_id']) ? (int)$_GET['neighborhood_id'] : null;

if (isset($_GET['ranking'])) {
    echo json_encode(gamon_ranking());
} elseif (isset($_GET['trend'])) {
    echo json_encode(gamon_trend($period));
} else {
    echo json_encode(gamon_summary($period, $hood_id));
}
