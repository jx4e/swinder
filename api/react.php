<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$pool_id = (int)($data['pool_id'] ?? 0);
$emoji   = $data['emoji'] ?? '';

$allowed = ['💦','🔥','🥶','🤢','💎','🏆','😱','🦆'];

if (!$pool_id || !in_array($emoji, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid']);
    exit;
}

$db = get_db();
$db->prepare("INSERT INTO reactions (pool_id, emoji) VALUES (?, ?)")->execute([$pool_id, $emoji]);

// Return updated counts for this pool
$stmt = $db->prepare("
    SELECT emoji, COUNT(*) as count
    FROM reactions
    WHERE pool_id = ?
    GROUP BY emoji
    ORDER BY count DESC
    LIMIT 8
");
$stmt->execute([$pool_id]);
echo json_encode(['ok' => true, 'reactions' => $stmt->fetchAll()]);
