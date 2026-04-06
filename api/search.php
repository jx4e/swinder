<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/fetch_pools.php';

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$db = get_db();

// Search local DB
$like = "%$q%";
$stmt = $db->prepare("
    SELECT id, name, address, lat, lon, photo_url, photo_refs, rating,
           swipe_rights, swipe_lefts,
           CASE WHEN (swipe_rights + swipe_lefts) > 0
                THEN ROUND(CAST(swipe_rights AS FLOAT) / (swipe_rights + swipe_lefts) * 100)
                ELSE NULL END AS score
    FROM pools
    WHERE name LIKE ? OR address LIKE ?
    ORDER BY (swipe_rights + swipe_lefts) DESC
    LIMIT 10
");
$stmt->execute([$like, $like]);
$local = $stmt->fetchAll();

// If fewer than 5 local hits, top up from Google Places text search
if (count($local) < 5) {
    $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?' . http_build_query([
        'query' => $q . ' swimming pool',
        'key'   => google_api_key(),
    ]);
    $response = json_decode(@file_get_contents($url), true);

    $local_place_ids = array_column($local, 'place_id');

    foreach (array_slice($response['results'] ?? [], 0, 8) as $place) {
        $place_id = $place['place_id'];
        if (in_array($place_id, $local_place_ids)) continue;

        // Check if already in DB (different location, not in local results)
        $exists = $db->prepare("SELECT id, name, address, photo_url, photo_refs, rating, swipe_rights, swipe_lefts,
            CASE WHEN (swipe_rights + swipe_lefts) > 0
                 THEN ROUND(CAST(swipe_rights AS FLOAT) / (swipe_rights + swipe_lefts) * 100)
                 ELSE NULL END AS score FROM pools WHERE place_id = ?");
        $exists->execute([$place_id]);
        if ($row = $exists->fetch()) {
            $local[] = $row;
            continue;
        }

        // New pool — fetch details and cache it
        $details_url = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query([
            'place_id' => $place_id,
            'fields'   => 'name,formatted_address,rating,photos,geometry',
            'key'      => google_api_key(),
        ]);
        $details = json_decode(@file_get_contents($details_url), true)['result'] ?? [];

        $lat = $place['geometry']['location']['lat'] ?? null;
        $lon = $place['geometry']['location']['lng'] ?? null;

        // Grab first photo ref only (fast — no redirect resolution needed yet)
        $first_ref  = $details['photos'][0]['photo_reference'] ?? null;
        $all_refs   = array_column(array_slice($details['photos'] ?? [], 0, 10), 'photo_reference');
        $photo_url  = $first_ref ? resolve_photo_url($first_ref) : null;

        $ins = $db->prepare("INSERT OR IGNORE INTO pools (place_id, name, address, lat, lon, photo_url, photo_refs, rating) VALUES (?,?,?,?,?,?,?,?)");
        $ins->execute([
            $place_id,
            $details['name'] ?? $place['name'] ?? 'Unknown Pool',
            $details['formatted_address'] ?? null,
            $lat, $lon,
            $photo_url,
            json_encode($all_refs),
            $details['rating'] ?? $place['rating'] ?? null,
        ]);

        $new = $db->prepare("SELECT id, name, address, photo_url, photo_refs, rating, swipe_rights, swipe_lefts, NULL as score FROM pools WHERE place_id = ?");
        $new->execute([$place_id]);
        if ($row = $new->fetch()) $local[] = $row;

        if (count($local) >= 10) break;
    }
}

// Decode photo_refs for each result
foreach ($local as &$pool) {
    $pool['photo_refs'] = json_decode($pool['photo_refs'] ?? '[]', true) ?: [];
}

echo json_encode(['results' => array_slice($local, 0, 10)]);
