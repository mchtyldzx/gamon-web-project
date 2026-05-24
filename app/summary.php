<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function gamon_summary(string $period = 'week', ?int $neighborhood_id = null): array
{
    $pdo    = gamon_pdo();
    $params = [];

    $date_filter = match ($period) {
        'day'   => "date(created_at) = date('now')",
        'month' => "strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')",
        default => "date(created_at) >= date('now', '-7 days')",
    };

    $hood_filter = '';
    if ($neighborhood_id !== null) {
        $hood_filter = ' AND r.neighborhood_id = ?';
        $params[]    = $neighborhood_id;
    }

    $base = "FROM accumulation_reports r WHERE $date_filter$hood_filter";

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
         WHERE $date_filter$hood_filter
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

function gamon_ranking(): array
{
    $pdo  = gamon_pdo();
    $stmt = $pdo->query(
        "SELECT n.id, n.name, n.locality,
                COUNT(r.id)  AS total,
                SUM(CASE WHEN r.status = 'resolved' THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN r.status = 'open'     THEN 1 ELSE 0 END) AS open
         FROM neighborhoods n
         LEFT JOIN accumulation_reports r ON r.neighborhood_id = n.id
         GROUP BY n.id
         ORDER BY open ASC, resolved DESC"
    );
    return $stmt->fetchAll();
}

function gamon_trend(string $period = 'week'): array
{
    $pdo  = gamon_pdo();
    $days = $period === 'month' ? 30 : ($period === 'day' ? 1 : 7);
    $stmt = $pdo->prepare(
        "SELECT date(created_at) AS day, COUNT(*) AS cnt
         FROM accumulation_reports
         WHERE date(created_at) >= date('now', ? || ' days')
         GROUP BY day ORDER BY day ASC"
    );
    $stmt->execute(["-$days"]);
    return $stmt->fetchAll();
}
