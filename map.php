<?php
require_once __DIR__ . '/includes/db.php';
$db = get_db();

$pools = $db->query("
    SELECT
        id, name, address, lat, lon, photo_url,
        swipe_rights, swipe_lefts,
        CASE WHEN (swipe_rights + swipe_lefts) > 0
             THEN ROUND(CAST(swipe_rights AS FLOAT) / (swipe_rights + swipe_lefts) * 100)
             ELSE NULL END AS score
    FROM pools
    WHERE lat IS NOT NULL AND lon IS NOT NULL
    ORDER BY (swipe_rights + swipe_lefts) DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pool Map 🗺️ — Swinder</title>
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            width: 100%;
            flex: 1;
            min-height: 0;
            border-radius: 20px;
            overflow: hidden;
        }
        main.map-page {
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 100%;
            padding: 12px 16px 20px;
            gap: 12px;
            min-height: 0;
        }
        body { height: 100dvh; }
        .map-legend {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            padding: 0 4px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: var(--muted);
        }
        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
            flex-shrink: 0;
        }
        /* Leaflet popup overrides */
        .leaflet-popup-content-wrapper {
            background: #1e1e32;
            color: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        }
        .leaflet-popup-tip { background: #1e1e32; }
        .leaflet-popup-content { margin: 14px 16px; }
        .popup-name { font-weight: 700; font-size: 0.95rem; margin-bottom: 4px; }
        .popup-addr { font-size: 0.78rem; color: rgba(255,255,255,0.55); margin-bottom: 8px; }
        .popup-score { font-size: 0.85rem; display: flex; gap: 10px; align-items: center; }
        .popup-score .score-pct { font-weight: 800; font-size: 1.1rem; }
        .popup-score .score-green { color: #00e676; }
        .popup-score .score-orange { color: #ffab40; }
        .popup-score .score-red   { color: #ff1744; }
        .popup-score .score-grey  { color: rgba(255,255,255,0.4); }
        .popup-votes { font-size: 0.78rem; color: rgba(255,255,255,0.45); }
        .popup-img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            display: block;
        }
        .leaflet-container { background: #0d0d1a; }
    </style>
</head>
<body>
<header>
    <h1>🗺️ Pool Map</h1>
</header>

<main class="map-page">
    <div class="map-legend">
        <div class="legend-item"><div class="legend-dot" style="background:#00e676"></div> Liked</div>
        <div class="legend-item"><div class="legend-dot" style="background:#ffab40"></div> Mixed</div>
        <div class="legend-item"><div class="legend-dot" style="background:#ff1744"></div> Disliked</div>
        <div class="legend-item"><div class="legend-dot" style="background:rgba(255,255,255,0.2)"></div> Not rated</div>
    </div>
    <div id="map"></div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const pools = <?= json_encode($pools) ?>;

// Centre on saved location or first pool
const savedLat = parseFloat(localStorage.getItem('swinder_lat') || 0);
const savedLon = parseFloat(localStorage.getItem('swinder_lon') || 0);
const centre   = savedLat && savedLon
    ? [savedLat, savedLon]
    : pools.length ? [pools[0].lat, pools[0].lon] : [49.2827, -123.1207];

const map = L.map('map', { zoomControl: true }).setView(centre, 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
}).addTo(map);

function markerColour(score) {
    if (score === null) return 'rgba(255,255,255,0.25)';
    if (score >= 60)    return '#00e676';
    if (score >= 40)    return '#ffab40';
    return '#ff1744';
}

function scoreClass(score) {
    if (score === null) return 'score-grey';
    if (score >= 60)    return 'score-green';
    if (score >= 40)    return 'score-orange';
    return 'score-red';
}

pools.forEach(pool => {
    if (!pool.lat || !pool.lon) return;

    const colour = markerColour(pool.score);
    const rated  = pool.swipe_rights > 0 || pool.swipe_lefts > 0;

    const icon = L.divIcon({
        className: '',
        html: `<div style="
            width:18px; height:18px; border-radius:50%;
            background:${colour};
            border: 3px solid rgba(255,255,255,${rated ? '0.85' : '0.25'});
            box-shadow: 0 2px 8px rgba(0,0,0,0.5);
            ${!rated ? 'opacity:0.5' : ''}
        "></div>`,
        iconSize: [18, 18],
        iconAnchor: [9, 9],
    });

    const scoreHtml = pool.score !== null
        ? `<span class="score-pct ${scoreClass(pool.score)}">${pool.score}%</span>
           <span class="popup-votes">${pool.swipe_rights}👍 ${pool.swipe_lefts}👎</span>`
        : `<span class="score-grey">not rated yet</span>`;

    const imgHtml = pool.photo_url
        ? `<img src="${pool.photo_url}" class="popup-img" alt="">`
        : '';

    const popup = `
        ${imgHtml}
        <div class="popup-name">${pool.name}</div>
        <div class="popup-addr">${pool.address || ''}</div>
        <div class="popup-score">${scoreHtml}</div>
    `;

    L.marker([pool.lat, pool.lon], { icon })
     .addTo(map)
     .bindPopup(popup, { maxWidth: 220 });
});
</script>
<?php require_once __DIR__ . '/includes/nav.php'; ?>
</body>
</html>
