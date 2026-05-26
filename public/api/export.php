<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/export.php';

$rows = gamon_reports_list();
$_GET['format'] === 'csv' ? export_csv($rows) : export_json($rows);
