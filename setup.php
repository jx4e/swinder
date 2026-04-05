<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/fetch_pools.php';

$data_dir = __DIR__ . '/data';
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
}

init_db();
echo "✅ Database initialised<br>";

$added = fetch_pools_near(DEFAULT_LAT, DEFAULT_LON);
echo "🏊 Fetched $added new pools near (" . DEFAULT_LAT . ", " . DEFAULT_LON . ")<br><br>";
echo '<a href="/">→ Start swiping</a> &nbsp; <a href="/leaderboard.php">🏆 Leaderboard</a>';
