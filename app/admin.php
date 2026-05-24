<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function gamon_admin_users(): array
{
    $pdo  = gamon_pdo();
    $stmt = $pdo->query(
        'SELECT id, email, full_name, role, created_at FROM users ORDER BY created_at DESC'
    );
    return $stmt->fetchAll();
}

function gamon_admin_stats(): array
{
    $pdo = gamon_pdo();
    $r   = fn(string $sql) => (int) $pdo->query($sql)->fetchColumn();
    return [
        'users'       => $r('SELECT COUNT(*) FROM users'),
        'reports'     => $r('SELECT COUNT(*) FROM accumulation_reports'),
        'open'        => $r("SELECT COUNT(*) FROM accumulation_reports WHERE status = 'open'"),
        'resolved'    => $r("SELECT COUNT(*) FROM accumulation_reports WHERE status = 'resolved'"),
        'collections' => $r('SELECT COUNT(*) FROM collection_events'),
    ];
}
