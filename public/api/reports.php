<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/reports.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = gamon_require_auth(); // Anonymous cannot see reports
    
    $filters = [];
    if (!empty($_GET['city_id'])) $filters['city_id'] = $_GET['city_id'];
    if (!empty($_GET['category_id']))     $filters['category_id']     = $_GET['category_id'];
    if (!empty($_GET['status']))          $filters['status']          = $_GET['status'];
    if (!empty($_GET['period']))          $filters['period']          = $_GET['period'];

    if ($user && $user['role'] === 'citizen') {
        $filters['reporter_id'] = $user['id'];
    }

    if (!empty($_GET['id'])) {
        $report = gamon_reports_get((int)$_GET['id']);
        if (!$report) { http_response_code(404); echo json_encode(['error' => 'Not found']); exit; }
        echo json_encode($report);
    } else {
        echo json_encode(gamon_reports_list($filters));
    }
    exit;
}

if ($method === 'POST') {
    $user = gamon_require_auth(['citizen', 'staff', 'decision_maker', 'admin']);
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    if (empty($body['city_id']) || empty($body['description'])) {
        http_response_code(400);
        echo json_encode(['error' => 'city_id and description are required']);
        exit;
    }

    $id = gamon_reports_create($body, $user['id']);
    http_response_code(201);
    echo json_encode(['id' => $id]);
    exit;
}

if ($method === 'PATCH') {
    $user = gamon_require_auth(['staff', 'admin']);
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = (int)($_GET['id'] ?? 0);

    if ($id <= 0 || empty($body['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'id and status are required']);
        exit;
    }

    $ok = gamon_reports_update_status($id, $body['status'], $user, $body['note'] ?? '');
    echo json_encode(['ok' => $ok]);
    exit;
}

if ($method === 'DELETE') {
    $user = gamon_require_auth(['admin']);
    $id   = (int)($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400); echo json_encode(['error' => 'Valid ID required']); exit;
    }
    
    $pdo = gamon_pdo();
    $pdo->prepare('DELETE FROM cleanup_logs WHERE report_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM accumulation_reports WHERE id = ?')->execute([$id]);
    
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
