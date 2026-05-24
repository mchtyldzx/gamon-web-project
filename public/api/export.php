<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/export.php';

$format = $_GET['format'] ?? 'json';
$period = in_array($_GET['period'] ?? '', ['day','week','month'], true) ? $_GET['period'] : 'all';
$rows   = gamon_reports_list();

match ($format) {
    'csv'  => export_csv($rows),
    'html' => export_html($rows, $period),
    'pdf'  => export_pdf($rows, $period),
    default => export_json($rows),
};
