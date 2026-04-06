<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Swinder 🏊</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <h1>🏊 Swinder</h1>
        <div class="header-right">
            <button class="btn-location" id="btn-location" onclick="openLocationModal()" title="Change location">
                📍 <span id="location-label">Vancouver</span>
            </button>
            <a href="/map.php">🗺️ Map</a>
            <a href="/leaderboard.php">🏆 Rankings</a>
        </div>
    </header>

    <!-- Location modal -->
    <div id="location-modal" class="modal-overlay hidden" onclick="closeLocationModal(event)">
        <div class="modal-card">
            <h3>📍 Change location</h3>
            <button class="btn-geolocate" id="btn-geolocate">
                🎯 Use my location
            </button>
            <div class="modal-divider">or search</div>
            <div class="city-search-row">
                <input type="text" id="city-input" placeholder="e.g. London, Tokyo, Sydney…" autocomplete="off" />
                <button id="btn-city-search">Go</button>
            </div>
            <div class="radius-row">
                <label for="radius-input">Search radius: <strong id="radius-label">10 km</strong></label>
                <input type="range" id="radius-input" min="1" max="50" value="10" step="1" />
            </div>
            <p id="location-status" class="location-status"></p>
        </div>
    </div>

    <main>
        <div id="card-container">
            <div id="card" class="card">
                <div id="like-stamp" class="stamp like">SPLASH 💦</div>
                <div id="nope-stamp" class="stamp nope">NOPE 🏃</div>
                <div class="card-image" id="card-image">
                    <div class="photo-dots" id="photo-dots"></div>
                </div>
                <div class="card-info">
                    <h2 id="pool-name">Loading pools...</h2>
                    <p id="pool-address"></p>
                    <div class="pool-meta">
                        <span id="pool-rating"></span>
                    </div>
                </div>
            </div>
            <div id="empty-state" class="hidden">
                <div class="empty-emoji">🌊</div>
                <p>you've swiped on every pool.<br>go outside.</p>
                <a href="/leaderboard.php">see the rankings →</a>
            </div>
        </div>

        <div class="buttons" id="buttons">
            <button id="btn-nope" onclick="swipeLeft()">
                <span class="btn-icon">👎</span>
                <span class="btn-label">Nope</span>
            </button>
            <button id="btn-like" onclick="swipeRight()">
                <span class="btn-icon">💦</span>
                <span class="btn-label">Splash</span>
            </button>
        </div>
    </main>

    <script src="/assets/app.js"></script>
</body>
</html>
