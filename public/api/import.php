<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required']);
    exit;
}

$user = gamon_require_auth();
if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: Only admin can import data']);
    exit;
}
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
    $stmtWithDate = $pdo->prepare(
        'INSERT INTO accumulation_reports (city_id, category_id, reporter_id, description, severity, status, lat, lng, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmtNoDate = $pdo->prepare(
        'INSERT INTO accumulation_reports (city_id, category_id, reporter_id, description, severity, status, lat, lng)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    
    // Cache for city coordinates
    $nCache = [];
    
    $count = 0;
    foreach ($rows as $r) {
        $nid = !empty($r['city_id']) ? (int)$r['city_id'] : null;
        if (!$nid && !empty($r['city'])) {
            $stmt = $pdo->prepare("SELECT id FROM cities WHERE locality = ?");
            $stmt->execute([trim($r['city'])]);
            $nid = $stmt->fetchColumn() ?: null;
        }
        if (!$nid || empty($r['description'])) continue;
        

        $lat = isset($r['lat']) && $r['lat'] !== '' ? (float)$r['lat'] : null;
        $lng = isset($r['lng']) && $r['lng'] !== '' ? (float)$r['lng'] : null;
        
        if ($lat === null || $lng === null) {
            if (!array_key_exists($nid, $nCache)) {
                $nStmt = $pdo->prepare('SELECT lat, lng FROM cities WHERE id = ?');
                $nStmt->execute([$nid]);
                $nRow = $nStmt->fetch();
                $nCache[$nid] = $nRow ? $nRow : false;
            }
            if ($nCache[$nid]) {
                $lat = $nCache[$nid]['lat'] !== null ? (float)$nCache[$nid]['lat'] : null;
                $lng = $nCache[$nid]['lng'] !== null ? (float)$nCache[$nid]['lng'] : null;
            }
        }
        
        $catId = !empty($r['category_id']) ? (int)$r['category_id'] : null;
        if (!$catId && !empty($r['category'])) {
            $stmt = $pdo->prepare("SELECT id FROM waste_categories WHERE name = ?");
            $stmt->execute([trim($r['category'])]);
            $catId = $stmt->fetchColumn() ?: null;
        }
        
        $params = [
            $nid,
            $catId,
            $userId,
            $r['description'],
            (int)($r['severity'] ?? 2),
            $r['status'] ?? 'open',
            $lat,
            $lng,
        ];
        
        if (!empty($r['created_at'])) {
            $params[] = $r['created_at'];
            $stmtWithDate->execute($params);
        } else {
            $stmtNoDate->execute($params);
        }
        $count++;
    }
    return $count;
}
