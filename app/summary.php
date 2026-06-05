<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function gamon_summary(string $period = 'all', ?int $city_id = null): array
{
    $pdo    = gamon_pdo();
    $params = [];

    $hood_filter = '';
    if ($city_id !== null) {
        $hood_filter = ' WHERE r.city_id = ?';
        $params[]    = $city_id;
    }

    $base = "FROM accumulation_reports r $hood_filter";

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
         $hood_filter
         GROUP BY r.category_id ORDER BY cnt DESC"
    );
    $by_cat->execute($params);

    return [
        'period'      => 'all',
        'total'       => (int) $total->fetchColumn(),
        'by_status'   => $by_status->fetchAll(),
        'by_category' => $by_cat->fetchAll(),
    ];
}

function gamon_ranking(): array
{
    $pdo  = gamon_pdo();
    $stmt = $pdo->query(
        "SELECT n.id, n.locality AS name, n.locality,
                COUNT(r.id)  AS total,
                SUM(CASE WHEN r.status = 'resolved' THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN r.status = 'open'     THEN 1 ELSE 0 END) AS open
         FROM cities n
         LEFT JOIN accumulation_reports r ON r.city_id = n.id
         GROUP BY n.id
         ORDER BY open ASC, resolved DESC"
    );
    return $stmt->fetchAll();
}

