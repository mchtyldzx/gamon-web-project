<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function gamon_session_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('gamon_sess');
        session_start();
    }
    
    // Generate CSRF token if not exists
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    // Make token available to JS via a readable cookie
    if (!isset($_COOKIE['XSRF-TOKEN']) || $_COOKIE['XSRF-TOKEN'] !== $_SESSION['csrf_token']) {
        setcookie('XSRF-TOKEN', $_SESSION['csrf_token'], 0, '/', '', false, false);
    }
}

function gamon_session_user(): ?array
{
    gamon_session_start();
    if (!isset($_SESSION['user'])) {
        return null;
    }
    
    // Fetch latest user data from DB to ensure role changes are instant
    $pdo = gamon_pdo();
    $stmt = $pdo->prepare('SELECT id, email, role, full_name FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user']['id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        gamon_session_destroy();
        return null;
    }
    
    // Update session with fresh data
    $_SESSION['user'] = [
        'id'        => (int) $user['id'],
        'email'     => $user['email'],
        'role'      => $user['role'],
        'full_name' => $user['full_name'],
    ];
    
    return $_SESSION['user'];
}

function gamon_require_auth(array $roles = []): array
{
    $user = gamon_session_user();
    if ($user === null) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
    
    // CSRF Check for state-changing requests
    if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PATCH', 'DELETE', 'PUT'])) {
        $token = $_SERVER['HTTP_X_XSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }
    }

    if (!empty($roles) && !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Insufficient permissions']);
        exit;
    }
    return $user;
}

function gamon_session_set(array $user): void
{
    gamon_session_start();
    $_SESSION['user'] = [
        'id'        => (int) $user['id'],
        'email'     => $user['email'],
        'role'      => $user['role'],
        'full_name' => $user['full_name'],
    ];
}

function gamon_session_destroy(): void
{
    gamon_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        setcookie('XSRF-TOKEN', '', time() - 3600, '/', '', false, false);
    }
    session_destroy();
}
