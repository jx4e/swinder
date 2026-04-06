<?php $page = basename($_SERVER['PHP_SELF']); ?>
<nav class="bottom-nav">
    <a href="/" class="nav-item <?= $page === 'index.php' ? 'active' : '' ?>">
        <span class="nav-icon">🏊</span>
        <span class="nav-label">Swipe</span>
    </a>
    <a href="/map.php" class="nav-item <?= $page === 'map.php' ? 'active' : '' ?>">
        <span class="nav-icon">🗺️</span>
        <span class="nav-label">Map</span>
    </a>
    <a href="/leaderboard.php" class="nav-item <?= $page === 'leaderboard.php' ? 'active' : '' ?>">
        <span class="nav-icon">🏆</span>
        <span class="nav-label">Ranks</span>
    </a>
    <?php if ($page === 'index.php'): ?>
    <button class="nav-item" onclick="openLocationModal()">
        <span class="nav-icon">📍</span>
        <span class="nav-label">Location</span>
    </button>
    <?php else: ?>
    <a href="/" class="nav-item">
        <span class="nav-icon">📍</span>
        <span class="nav-label">Location</span>
    </a>
    <?php endif; ?>
</nav>
