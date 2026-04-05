<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/fetch_pools.php';

$data_dir = __DIR__ . '/data';
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
}

init_db();
echo "✅ Database initialised<br>";

$key = google_api_key();
echo "🔑 API key: " . ($key ? substr($key, 0, 6) . '...' . substr($key, -4) : '<strong style="color:red">NOT SET — add GOOGLE_PLACES_API_KEY in Railway Variables</strong>') . "<br>";

$lat = defined('DEFAULT_LAT') ? DEFAULT_LAT : (float)(getenv('DEFAULT_LAT') ?: 51.5074);
$lon = defined('DEFAULT_LON') ? DEFAULT_LON : (float)(getenv('DEFAULT_LON') ?: -0.1278);

$added = fetch_pools_near($lat, $lon);

if ($added === 0) {
    // Re-run the query and show the raw API response for debugging
    $url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?' . http_build_query([
        'location' => "$lat,$lon", 'radius' => 10000, 'type' => 'swimming_pool', 'key' => $key,
    ]);
    $raw = file_get_contents($url);
    $resp = json_decode($raw, true);
    echo "⚠️ API status: <strong>" . ($resp['status'] ?? 'unknown') . "</strong>";
    if (!empty($resp['error_message'])) echo " — " . htmlspecialchars($resp['error_message']);
    echo "<br>";
}

echo "🏊 Fetched $added new pools near ($lat, $lon)<br><br>";
echo '<a href="/">→ Start swiping</a> &nbsp; <a href="/leaderboard.php">🏆 Leaderboard</a>';
