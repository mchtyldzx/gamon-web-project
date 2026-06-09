<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function gamon_summary(string $period = 'all', ?int $city_id = null): array
{
    $pdo    = gamon_pdo();
    $params = [];
    $conditions = [];

    if ($city_id !== null) {
        $conditions[] = 'r.city_id = ?';
        $params[]    = $city_id;
    }

    $date_filter = match ($period) {
        'day'   => "r.created_at >= datetime('now', '-1 day')",
        'week'  => "r.created_at >= datetime('now', '-7 days')",
        'month' => "r.created_at >= datetime('now', '-1 month')",
        default => ''
    };

    if ($date_filter) {
        $conditions[] = $date_filter;
    }

    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    $base = "FROM accumulation_reports r $where";

    $total    = $pdo->prepare("SELECT COUNT(*) $base");
    $total->execute($params);
    
    $by_status = $pdo->prepare(
        "SELECT status, COUNT(*) AS cnt $base GROUP BY status"
    );
    $by_status->execute($params);
    
    $by_cat = $pdo->prepare(
        "SELECT wc.name AS category, COUNT(*) AS cnt
         FROM accumulation_reports r
         LEFT JOIN waste_categories wc ON wc.id = r.category_id
         $where
         GROUP BY r.category_id ORDER BY cnt DESC"
    );
    $by_cat->execute($params);

    return [
        'period'      => $period,
        'total'       => (int) $total->fetchColumn(),
        'by_status'   => $by_status->fetchAll(),
        'by_category' => $by_cat->fetchAll(),
    ];
}

function gamon_ranking(string $period = 'all'): array
{
    $pdo  = gamon_pdo();
    $date_filter = match ($period) {
        'day'   => " AND r.created_at >= datetime('now', '-1 day')",
        'week'  => " AND r.created_at >= datetime('now', '-7 days')",
        'month' => " AND r.created_at >= datetime('now', '-1 month')",
        default => ''
    };

    $stmt = $pdo->query(
        "SELECT n.id, n.locality AS name, n.locality,
                COUNT(r.id)  AS total,
                SUM(CASE WHEN r.status = 'resolved' THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN r.status = 'open'     THEN 1 ELSE 0 END) AS open
         FROM cities n
         LEFT JOIN accumulation_reports r ON r.city_id = n.id $date_filter
         GROUP BY n.id
         ORDER BY open ASC, resolved DESC"
    );
    return $stmt->fetchAll();
}
