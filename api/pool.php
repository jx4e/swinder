<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['error' => 'id required']); exit; }

$db   = get_db();
$stmt = $db->prepare("SELECT id, name, address, photo_url, photo_refs, rating, swipe_rights, swipe_lefts FROM pools WHERE id = ?");
$stmt->execute([$id]);
$pool = $stmt->fetch();

if (!$pool) { echo json_encode(['pool' => null]); exit; }

$pool['photo_refs'] = json_decode($pool['photo_refs'] ?? '[]', true) ?: [];

$r = $db->prepare("SELECT emoji, COUNT(*) as count FROM reactions WHERE pool_id = ? GROUP BY emoji ORDER BY count DESC LIMIT 8");
$r->execute([$id]);
$pool['reactions'] = $r->fetchAll();

echo json_encode(['pool' => $pool]);
