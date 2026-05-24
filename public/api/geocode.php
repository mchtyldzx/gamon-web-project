<?php
declare(strict_types=1);
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if ($q === '') { echo '[]'; exit; }

$url = 'https://nominatim.openstreetmap.org/search?format=json&limit=3&q=' . urlencode($q);
$ch  = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERAGENT      => 'GaMon/1.0 (course-project)',
    CURLOPT_TIMEOUT        => 5,
]);
$res = curl_exec($ch);
curl_close($ch);
echo $res ?: '[]';
