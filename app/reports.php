<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function gamon_reports_list(array $filters = []): array
{
    $pdo = gamon_pdo();
    $where = [];
    $params = [];
    if (!empty($filters['neighborhood_id'])) { $where[] = 'r.neighborhood_id = ?'; $params[] = (int)$filters['neighborhood_id']; }
    if (!empty($filters['category_id']))     { $where[] = 'r.category_id = ?';     $params[] = (int)$filters['category_id']; }
    if (!empty($filters['status']))          { $where[] = 'r.status = ?';          $params[] = $filters['status']; }
    if (!empty($filters['reporter_id']))     { $where[] = 'r.reporter_id = ?';     $params[] = (int)$filters['reporter_id']; }

    $sql = 'SELECT r.id, r.description, r.status, r.severity, r.lat, r.lng, r.created_at, r.resolved_at,
                   u.full_name AS reporter_name,
                   n.id AS neighborhood_id, n.name AS neighborhood_name, n.locality,
                   wc.id AS category_id, wc.name AS category_name, wc.code AS category_code
            FROM   accumulation_reports r
            JOIN   users u ON u.id = r.reporter_id
            JOIN   neighborhoods n ON n.id = r.neighborhood_id
            LEFT JOIN waste_categories wc ON wc.id = r.category_id'
        . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY r.created_at DESC LIMIT 200';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function gamon_reports_get(int $id): ?array
{
    $pdo  = gamon_pdo();
    $stmt = $pdo->prepare(
        'SELECT r.*, u.full_name AS reporter_name,
                n.name AS neighborhood_name, n.locality,
                wc.name AS category_name, wc.code AS category_code
         FROM   accumulation_reports r
         JOIN   users u ON u.id = r.reporter_id
         JOIN   neighborhoods n ON n.id = r.neighborhood_id
         LEFT JOIN waste_categories wc ON wc.id = r.category_id
         WHERE  r.id = ?'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function gamon_reports_create(array $data, int $reporter_id): int
{
    $pdo  = gamon_pdo();
    $stmt = $pdo->prepare(
        'INSERT INTO accumulation_reports
         (reporter_id, neighborhood_id, category_id, description, severity, lat, lng)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $reporter_id,
        (int)$data['neighborhood_id'],
        !empty($data['category_id']) ? (int)$data['category_id'] : null,
        trim($data['description']),
        isset($data['severity']) ? max(1, min(3, (int)$data['severity'])) : 2,
        isset($data['lat']) && $data['lat'] !== '' ? (float)$data['lat'] : null,
        isset($data['lng']) && $data['lng'] !== '' ? (float)$data['lng'] : null,
    ]);
    return (int)$pdo->lastInsertId();
}

function gamon_reports_update_status(int $id, string $status, array $actor, string $note = ''): bool
{
    $allowed = ['open', 'assigned', 'resolved', 'rejected'];
    if (!in_array($status, $allowed, true)) return false;

    $pdo         = gamon_pdo();
    $resolved_at = in_array($status, ['resolved', 'rejected'], true) ? date('Y-m-d H:i:s') : null;

    $stmt = $pdo->prepare('UPDATE accumulation_reports SET status = ?, resolved_at = ? WHERE id = ?');
    $stmt->execute([$status, $resolved_at, $id]);

    if ($stmt->rowCount() > 0) {
        $log = $pdo->prepare('INSERT INTO cleanup_logs (report_id, staff_id, action, note) VALUES (?, ?, ?, ?)');
        $log->execute([$id, $actor['id'], $status, $note ?: null]);
    }
    return $stmt->rowCount() > 0;
}
