<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config.php';
require_once dirname(__DIR__) . '/app/db.php';

$pdo = gamon_pdo();

$email = 'admin@gamon.com';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
$fullName = 'System Admin';
$role = 'admin';

$stmt = $pdo->prepare('INSERT OR REPLACE INTO users (email, password_hash, full_name, role) VALUES (?, ?, ?, ?)');
$stmt->execute([$email, $hash, $fullName, $role]);

echo "Admin account created/updated successfully.\n";
echo "Email: admin@gamon.com\n";
echo "Password: admin123\n";
