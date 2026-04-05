<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$pool_id   = (int)($data['pool_id'] ?? 0);
$direction = $data['direction'] ?? '';

if (!$pool_id || !in_array($direction, ['left', 'right'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid request']);
    exit;
}

$db = get_db();

$db->prepare("INSERT INTO swipes (pool_id, direction) VALUES (?, ?)")
   ->execute([$pool_id, $direction]);

$col = $direction === 'right' ? 'swipe_rights' : 'swipe_lefts';
$db->prepare("UPDATE pools SET $col = $col + 1 WHERE id = ?")
   ->execute([$pool_id]);

echo json_encode(['ok' => true]);
