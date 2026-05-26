<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/csrf.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

gamon_csrf_verify();
gamon_require_auth(['staff', 'admin']);

$format = $_GET['format'] ?? 'json';
$rows   = [];

if ($format === 'csv') {
    if (empty($_FILES['file']['tmp_name'])) { http_response_code(400); echo json_encode(['error' => 'No file']); exit; }
    $fh = fopen($_FILES['file']['tmp_name'], 'r');
    fgetcsv($fh); // skip header
    while (($row = fgetcsv($fh)) !== false) {
        $rows[] = ['neighborhood_id' => (int)$row[0], 'category_id' => (int)$row[1], 'description' => $row[2], 'severity' => (int)($row[3] ?? 2)];
    }
    fclose($fh);
} else {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $rows = $body;
}

$pdo      = gamon_pdo();
$user     = gamon_session_user();
$imported = 0;
$stmt     = $pdo->prepare('INSERT INTO accumulation_reports (reporter_id, neighborhood_id, category_id, description, severity) VALUES (?,?,?,?,?)');

foreach ($rows as $r) {
    if (empty($r['neighborhood_id']) || empty($r['description'])) continue;
    $stmt->execute([$user['id'], (int)$r['neighborhood_id'], !empty($r['category_id']) ? (int)$r['category_id'] : null, trim($r['description']), max(1, min(3, (int)($r['severity'] ?? 2)))]);
    $imported++;
}

echo json_encode(['imported' => $imported]);
