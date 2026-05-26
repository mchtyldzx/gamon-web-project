<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/reports.php';
require_once __DIR__ . '/../app/csrf.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $filters = [];
    if (!empty($_GET['neighborhood_id'])) $filters['neighborhood_id'] = $_GET['neighborhood_id'];
    if (!empty($_GET['category_id']))     $filters['category_id']     = $_GET['category_id'];
    if (!empty($_GET['status']))          $filters['status']          = $_GET['status'];

    $user = gamon_session_user();
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
    gamon_csrf_verify();
    $user = gamon_require_auth(['citizen', 'staff', 'admin']);
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    if (empty($body['neighborhood_id']) || empty($body['description'])) {
        http_response_code(400);
        echo json_encode(['error' => 'neighborhood_id and description are required']);
        exit;
    }

    $id = gamon_reports_create($body, $user['id']);
    http_response_code(201);
    echo json_encode(['id' => $id]);
    exit;
}

if ($method === 'PATCH') {
    gamon_csrf_verify();
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

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
