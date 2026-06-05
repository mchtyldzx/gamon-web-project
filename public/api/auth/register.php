<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$email     = trim($body['email'] ?? '');
$password  = $body['password'] ?? '';
$full_name = trim($body['full_name'] ?? '');
$role      = $body['role'] ?? 'citizen';

if ($email === '' || $password === '' || $full_name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'email, password and full_name are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit;
}

$role = 'citizen';

$pdo  = gamon_pdo();
$chk  = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$chk->execute([$email]);
if ($chk->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'Email already registered']);
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$ins  = $pdo->prepare('INSERT INTO users (email, password_hash, role, full_name) VALUES (?, ?, ?, ?)');
$ins->execute([$email, $hash, $role, $full_name]);
$id = (int)$pdo->lastInsertId();

gamon_session_set(['id' => $id, 'email' => $email, 'role' => $role, 'full_name' => $full_name]);

http_response_code(201);
echo json_encode(['id' => $id, 'email' => $email, 'role' => $role, 'full_name' => $full_name]);
