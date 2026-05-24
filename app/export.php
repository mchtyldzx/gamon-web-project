<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/reports.php';

function export_csv(array $rows): void {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="gamon-reports.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','status','severity','neighborhood','category','description','reporter','created_at']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'],$r['status'],$r['severity'],$r['neighborhood_name'],$r['category_name']??'',$r['description'],$r['reporter_name'],$r['created_at']]);
    }
    fclose($out); exit;
}

function export_json(array $rows): void {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="gamon-reports.json"');
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); exit;
}

function export_html(array $rows, string $period): void {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="gamon-report.html"');
    echo "<!DOCTYPE html><html lang='en'><head><meta charset='utf-8'><title>GaMon Report</title>";
    echo "<style>body{font-family:sans-serif;padding:2rem}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:.4rem .7rem;font-size:.85rem}th{background:#eee}</style></head><body>";
    echo "<h1>GaMon Report &mdash; $period</h1><p>Generated: " . date('Y-m-d H:i') . "</p><table>";
    echo "<tr><th>ID</th><th>Status</th><th>Neighbourhood</th><th>Category</th><th>Description</th><th>Reporter</th><th>Date</th></tr>";
    foreach ($rows as $r) {
        echo "<tr><td>{$r['id']}</td><td>{$r['status']}</td><td>" . htmlspecialchars($r['neighborhood_name']) . "</td><td>" . htmlspecialchars($r['category_name'] ?? '') . "</td><td>" . htmlspecialchars($r['description']) . "</td><td>" . htmlspecialchars($r['reporter_name']) . "</td><td>{$r['created_at']}</td></tr>";
    }
    echo "</table></body></html>"; exit;
}

function export_pdf(array $rows, string $period): void {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="gamon-report.pdf"');

    $stream = '';
    $y      = 780;
    $lines  = ["GaMon Report - $period", str_repeat('=', 50), ''];
    foreach ($rows as $r) {
        $lines[] = "#{$r['id']} [{$r['status']}] " . mb_substr($r['neighborhood_name'], 0, 30);
        $lines[] = '  ' . mb_substr($r['description'], 0, 90);
        $lines[] = '';
    }
    foreach ($lines as $line) {
        if ($y < 40) break;
        $safe    = preg_replace('/[()\\\\]/', ' ', $line);
        $stream .= "BT /F1 9 Tf 30 $y Td ($safe) Tj ET\n";
        $y -= 13;
    }

    $len = strlen($stream);
    $pdf = "%PDF-1.4\n";
    $o   = [];
    $o[] = strlen($pdf); $pdf .= "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n";
    $o[] = strlen($pdf); $pdf .= "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n";
    $o[] = strlen($pdf); $pdf .= "3 0 obj<</Type/Page/MediaBox[0 0 595 842]/Parent 2 0 R/Resources<</Font<</F1 4 0 R>>>>/Contents 5 0 R>>endobj\n";
    $o[] = strlen($pdf); $pdf .= "4 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n";
    $o[] = strlen($pdf); $pdf .= "5 0 obj<</Length $len>>\nstream\n{$stream}\nendstream\nendobj\n";
    $xref = strlen($pdf);
    $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    foreach ($o as $off) $pdf .= sprintf("%010d 00000 n \n", $off);
    $pdf .= "trailer<</Size 6/Root 1 0 R>>\nstartxref\n$xref\n%%EOF";
    echo $pdf; exit;
}
