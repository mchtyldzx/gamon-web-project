<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/app/admin.php';
require_once dirname(__DIR__, 3) . '/app/auth.php';

header('Content-Type: application/json');

gamon_require_auth(['admin']);
echo json_encode(gamon_admin_stats());
