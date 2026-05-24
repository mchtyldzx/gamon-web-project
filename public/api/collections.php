<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/auth.php';
header('Content-Type: application/json');

$pdo = gamon_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query(
        'SELECT ce.id, ce.quantity_kg, ce.collected_at, ce.notes,
                n.name AS neighborhood_name, wc.name AS category_name, u.full_name AS staff_name
         FROM collection_events ce
         JOIN neighborhoods n ON n.id = ce.neighborhood_id
         JOIN waste_categories wc ON wc.id = ce.category_id
         JOIN users u ON u.id = ce.staff_id
         ORDER BY ce.collected_at DESC LIMIT 100'
    );
    echo json_encode($stmt->fetchAll()); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = gamon_require_auth(['staff', 'admin']);
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($body['neighborhood_id']) || empty($body['category_id'])) {
        http_response_code(400); echo json_encode(['error' => 'neighborhood_id and category_id required']); exit;
    }
    $stmt = $pdo->prepare('INSERT INTO collection_events (neighborhood_id, category_id, staff_id, quantity_kg, notes) VALUES (?,?,?,?,?)');
    $stmt->execute([(int)$body['neighborhood_id'], (int)$body['category_id'], $user['id'], (float)($body['quantity_kg'] ?? 0), $body['notes'] ?? null]);
    http_response_code(201); echo json_encode(['id' => (int)$pdo->lastInsertId()]); exit;
}

http_response_code(405); echo json_encode(['error' => 'Method not allowed']);
