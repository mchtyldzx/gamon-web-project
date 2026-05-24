<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/admin.php';
require_once __DIR__ . '/../../app/auth.php';

header('Content-Type: application/json');

gamon_require_auth(['admin']);
echo json_encode(gamon_admin_stats());
