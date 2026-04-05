<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$seen = $_GET['seen'] ?? '';
$seen_ids = array_values(array_filter(array_map('intval', explode(',', $seen))));

$db = get_db();

if ($seen_ids) {
    $placeholders = implode(',', array_fill(0, count($seen_ids), '?'));
    $stmt = $db->prepare("
        SELECT id, name, address, photo_url, rating
        FROM pools
        WHERE id NOT IN ($placeholders)
        ORDER BY RANDOM()
        LIMIT 1
    ");
    $stmt->execute($seen_ids);
} else {
    $stmt = $db->query("
        SELECT id, name, address, photo_url, rating
        FROM pools
        ORDER BY RANDOM()
        LIMIT 1
    ");
}

$pool = $stmt->fetch();
echo json_encode(['pool' => $pool ?: null]);
