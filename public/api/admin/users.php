<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/admin.php';
require_once dirname(__DIR__, 3) . '/app/auth.php';

header('Content-Type: application/json');

gamon_require_auth(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!isset($body['id']) || !isset($body['role'])) {
        http_response_code(400); echo json_encode(['error' => 'Missing id or role']); exit;
    }
    if (!in_array($body['role'], ['citizen', 'staff', 'decision_maker'])) {
        http_response_code(400); echo json_encode(['error' => 'Invalid role']); exit;
    }
    
    $pdo = gamon_pdo();
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ? AND role != 'admin'");
    $stmt->execute([$body['role'], (int)$body['id']]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(gamon_admin_users());
