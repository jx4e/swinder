<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/fetch_pools.php';

$lat = (float)($_GET['lat'] ?? 0);
$lon = (float)($_GET['lon'] ?? 0);

if (!$lat || !$lon) {
    http_response_code(400);
    echo json_encode(['error' => 'lat and lon required']);
    exit;
}

$added = fetch_pools_near($lat, $lon);
echo json_encode(['ok' => true, 'added' => $added]);
