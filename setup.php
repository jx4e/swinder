<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/fetch_pools.php';

$data_dir = __DIR__ . '/data';
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
}

init_db();
echo "✅ Database initialised<br>";

$lat = defined('DEFAULT_LAT') ? DEFAULT_LAT : (float)(getenv('DEFAULT_LAT') ?: 51.5074);
$lon = defined('DEFAULT_LON') ? DEFAULT_LON : (float)(getenv('DEFAULT_LON') ?: -0.1278);

$added = fetch_pools_near($lat, $lon);
echo "🏊 Fetched $added new pools near ($lat, $lon)<br><br>";
echo '<a href="/">→ Start swiping</a> &nbsp; <a href="/leaderboard.php">🏆 Leaderboard</a>';
