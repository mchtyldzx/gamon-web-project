<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/auth.php';
require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'POST required']); exit;
}

gamon_require_auth(['admin']);

$pdo = gamon_pdo();
$pdo->exec('DELETE FROM cleanup_logs');
$count = $pdo->exec('DELETE FROM accumulation_reports');

echo json_encode(['success' => true, 'deleted' => $count]);
