<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$email    = trim($body['email'] ?? '');
$password = $body['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'email and password are required']);
    exit;
}

$pdo  = gamon_pdo();
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
    exit;
}

gamon_session_set($user);

echo json_encode([
    'id'        => (int)$user['id'],
    'email'     => $user['email'],
    'role'      => $user['role'],
    'full_name' => $user['full_name'],
]);
