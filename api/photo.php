<?php
// Proxy Google Places photos server-side so the API key is never exposed to the browser.
require_once __DIR__ . '/../includes/fetch_pools.php';

$ref = $_GET['ref'] ?? '';
// Sanity-check only: non-empty, not absurdly long, no null bytes
if (!$ref || strlen($ref) > 5000 || str_contains($ref, "\0")) {
    http_response_code(400);
    exit;
}

$url = 'https://maps.googleapis.com/maps/api/place/photo?' . http_build_query([
    'maxwidth'        => 800,
    'photo_reference' => $ref,
    'key'             => google_api_key(),
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$data     = curl_exec($ch);
$type     = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$data || $status !== 200) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'type' => $type, 'ref_len' => strlen($ref), 'curl_error' => curl_error($ch ?? null)]);
    exit;
}

header('Content-Type: ' . $type);
header('Cache-Control: public, max-age=86400');
echo $data;
