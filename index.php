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
        <a href="/leaderboard.php">🏆 Rankings</a>
    </header>

    <main>
        <div id="card-container">
            <div id="card" class="card">
                <div id="like-stamp" class="stamp like">SPLASH 💦</div>
                <div id="nope-stamp" class="stamp nope">NOPE 🏃</div>
                <div class="card-image" id="card-image"></div>
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
