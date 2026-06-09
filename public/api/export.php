<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/export.php';

$user = gamon_require_auth();
if ($user['role'] !== 'decision_maker' && $user['role'] !== 'admin') {
    http_response_code(403);
    die('Forbidden: You do not have permission to export data.');
}

$rows = gamon_reports_list();
$format = $_GET['format'] ?? 'json';
if ($format === 'csv') export_csv($rows);
elseif ($format === 'html') export_html($rows);
else export_json($rows);
