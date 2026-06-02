<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required']);
    exit;
}

$user = gamon_require_auth();
$pdo  = gamon_pdo();

$format = $_GET['format'] ?? '';

/* --- JSON import --- */
if ($format === 'json') {
    $raw  = file_get_contents('php://input');
    $rows = json_decode($raw, true);
    if (!is_array($rows)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON array']);
        exit;
    }
    $count = importRows($pdo, $rows, (int)$user['id']);
    echo json_encode(['imported' => $count]);
    exit;
}

/* --- CSV import --- */
if ($format === 'csv') {
    if (empty($_FILES['file']['tmp_name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded']);
        exit;
    }
    $handle = fopen($_FILES['file']['tmp_name'], 'r');
    $header = fgetcsv($handle);
    $rows   = [];
    while (($line = fgetcsv($handle)) !== false) {
        $rows[] = array_combine($header, $line);
    }
    fclose($handle);
    $count = importRows($pdo, $rows, (int)$user['id']);
    echo json_encode(['imported' => $count]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'format=csv or format=json required']);

/* --- shared insert logic --- */
function importRows(PDO $pdo, array $rows, int $userId): int {
    $stmt = $pdo->prepare(
        'INSERT INTO accumulation_reports (neighborhood_id, category_id, user_id, description, severity, status)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $count = 0;
    foreach ($rows as $r) {
        if (empty($r['neighborhood_id']) || empty($r['description'])) continue;
        $stmt->execute([
            (int)$r['neighborhood_id'],
            !empty($r['category_id']) ? (int)$r['category_id'] : null,
            $userId,
            $r['description'],
            (int)($r['severity'] ?? 2),
            $r['status'] ?? 'open',
        ]);
        $count++;
    }
    return $count;
}
