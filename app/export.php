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

function export_html(array $rows): void {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="gamon-reports.html"');
    $html = "<!DOCTYPE html><html><head><meta charset='utf-8'><title>GaMon Reports</title>";
    $html .= "<style>body{font-family:sans-serif;padding:20px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f4f4f4}</style>";
    $html .= "</head><body><h1>GaMon Reports</h1><table>";
    $html .= "<tr><th>ID</th><th>Status</th><th>Severity</th><th>City</th><th>Category</th><th>Description</th><th>Reporter</th><th>Created At</th></tr>";
    foreach ($rows as $r) {
        $html .= sprintf(
            "<tr><td>%d</td><td>%s</td><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
            $r['id'], htmlspecialchars($r['status']), $r['severity'], htmlspecialchars($r['city_name']),
            htmlspecialchars($r['category_name'] ?? ''), htmlspecialchars($r['description']),
            htmlspecialchars($r['reporter_name']), htmlspecialchars($r['created_at'])
        );
    }
    $html .= "</table></body></html>";
    echo $html;
    exit;
}
