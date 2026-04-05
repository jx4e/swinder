<?php
require_once __DIR__ . '/includes/db.php';
$db = get_db();

$top = $db->query("
    SELECT *,
        ROUND(CAST(swipe_rights AS FLOAT) / (swipe_rights + swipe_lefts) * 100) AS score
    FROM pools
    WHERE swipe_rights + swipe_lefts >= 3
    ORDER BY score DESC, swipe_rights DESC
    LIMIT 5
")->fetchAll();

$bottom = $db->query("
    SELECT *,
        ROUND(CAST(swipe_rights AS FLOAT) / (swipe_rights + swipe_lefts) * 100) AS score
    FROM pools
    WHERE swipe_rights + swipe_lefts >= 3
    ORDER BY score ASC, swipe_lefts DESC
    LIMIT 5
")->fetchAll();

$total_swipes = $db->query("SELECT COUNT(*) FROM swipes")->fetchColumn();
$total_pools  = $db->query("SELECT COUNT(*) FROM pools")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rankings 🏆 — Swinder</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <h1>🏊 Swinder</h1>
        <a href="/">← Swipe</a>
    </header>

    <main class="leaderboard">
        <div class="lb-stats">
            <div class="lb-stat"><span><?= number_format($total_swipes) ?></span>total swipes</div>
            <div class="lb-stat"><span><?= number_format($total_pools) ?></span>pools rated</div>
        </div>

        <section>
            <h2>🔥 Hottest Pools</h2>
            <?php if (empty($top)): ?>
                <p class="lb-empty">not enough swipes yet 🤷<br><a href="/">go swipe some pools</a></p>
            <?php else: ?>
                <div class="lb-list">
                    <?php foreach ($top as $i => $pool): ?>
                    <div class="lb-row">
                        <span class="lb-rank"><?= $i + 1 ?></span>
                        <?php if ($pool['photo_url']): ?>
                            <img src="<?= htmlspecialchars($pool['photo_url']) ?>" class="lb-thumb" alt="">
                        <?php else: ?>
                            <div class="lb-thumb lb-thumb-placeholder">🏊</div>
                        <?php endif; ?>
                        <div class="lb-info">
                            <strong><?= htmlspecialchars($pool['name']) ?></strong>
                            <small><?= htmlspecialchars($pool['address'] ?? '') ?></small>
                            <small class="lb-votes"><?= $pool['swipe_rights'] ?>👍 &nbsp; <?= $pool['swipe_lefts'] ?>👎</small>
                        </div>
                        <span class="lb-score like"><?= $pool['score'] ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section>
            <h2>💀 Cursed Pools</h2>
            <?php if (empty($bottom)): ?>
                <p class="lb-empty">not enough swipes yet 🤷<br><a href="/">go swipe some pools</a></p>
            <?php else: ?>
                <div class="lb-list">
                    <?php foreach ($bottom as $i => $pool): ?>
                    <div class="lb-row">
                        <span class="lb-rank"><?= $i + 1 ?></span>
                        <?php if ($pool['photo_url']): ?>
                            <img src="<?= htmlspecialchars($pool['photo_url']) ?>" class="lb-thumb" alt="">
                        <?php else: ?>
                            <div class="lb-thumb lb-thumb-placeholder">🏊</div>
                        <?php endif; ?>
                        <div class="lb-info">
                            <strong><?= htmlspecialchars($pool['name']) ?></strong>
                            <small><?= htmlspecialchars($pool['address'] ?? '') ?></small>
                            <small class="lb-votes"><?= $pool['swipe_rights'] ?>👍 &nbsp; <?= $pool['swipe_lefts'] ?>👎</small>
                        </div>
                        <span class="lb-score nope"><?= $pool['score'] ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
