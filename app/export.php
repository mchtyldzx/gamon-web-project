<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/reports.php';

function export_csv(array $rows): void {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="gamon-reports.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','status','severity','city','category','description','reporter','created_at']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'],$r['status'],$r['severity'],$r['city_name'],$r['category_name']??'',$r['description'],$r['reporter_name'],$r['created_at']]);
    }
    fclose($out);
    exit;
}

function export_json(array $rows): void {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="gamon-reports.json"');
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
