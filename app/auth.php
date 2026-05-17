<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function gamon_session_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('gamon_sess');
        session_start();
    }
}

function gamon_session_user(): ?array
{
    gamon_session_start();
    return $_SESSION['user'] ?? null;
}

function gamon_require_auth(array $roles = []): array
{
    $user = gamon_session_user();
    if ($user === null) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
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
    }
    session_destroy();
}
