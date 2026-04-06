<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$seen     = $_GET['seen'] ?? '';
$seen_ids = array_values(array_filter(array_map('intval', explode(',', $seen))));

// Location filter — only return pools within the current search area
$lat    = (float)($_GET['lat']    ?? 0);
$lon    = (float)($_GET['lon']    ?? 0);
$radius = (int)  ($_GET['radius'] ?? 10); // km

$db = get_db();

// Build bounding box from lat/lon + radius (1 degree lat ≈ 111 km)
$where_parts = [];
$params      = [];

if ($lat && $lon) {
    $delta_lat = $radius / 111.0;
    $delta_lon = $radius / (111.0 * cos(deg2rad($lat)));
    $where_parts[] = "lat BETWEEN ? AND ?";
    $where_parts[] = "lon BETWEEN ? AND ?";
    array_push($params, $lat - $delta_lat, $lat + $delta_lat);
    array_push($params, $lon - $delta_lon, $lon + $delta_lon);
}

if ($seen_ids) {
    $placeholders  = implode(',', array_fill(0, count($seen_ids), '?'));
    $where_parts[] = "id NOT IN ($placeholders)";
    array_push($params, ...$seen_ids);
}

$where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

$stmt = $db->prepare("
    SELECT id, name, address, photo_url, photo_refs, rating
    FROM pools
    $where
    ORDER BY RANDOM()
    LIMIT 1
");
$stmt->execute($params);

$pool = $stmt->fetch();
if ($pool) {
    $pool['photo_refs'] = json_decode($pool['photo_refs'] ?? '[]', true) ?: [];

    // Attach top reaction counts
    $r = $db->prepare("
        SELECT emoji, COUNT(*) as count
        FROM reactions
        WHERE pool_id = ?
        GROUP BY emoji
        ORDER BY count DESC
        LIMIT 8
    ");
    $r->execute([$pool['id']]);
    $pool['reactions'] = $r->fetchAll();
}
echo json_encode(['pool' => $pool ?: null]);
