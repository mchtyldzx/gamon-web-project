<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/summary.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

header('Content-Type: application/json');

gamon_require_auth(['decision_maker', 'admin']);

$hood_id = !empty($_GET['city_id']) ? (int)$_GET['city_id'] : null;

echo json_encode(
    isset($_GET['ranking']) ? gamon_ranking() : gamon_summary('all', $hood_id)
);
