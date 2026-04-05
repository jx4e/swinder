<?php
require_once __DIR__ . '/db.php';

// Load config.php if present (local dev). On Railway, use environment variables.
$_config = __DIR__ . '/../config.php';
if (file_exists($_config)) require_once $_config;

function google_api_key(): string {
    if (defined('GOOGLE_PLACES_API_KEY')) return GOOGLE_PLACES_API_KEY;
    foreach (['GOOGLE_PLACES_API_KEY', 'GOOGLE_PLACES_API'] as $k) {
        $v = $_ENV[$k] ?? $_SERVER[$k] ?? getenv($k) ?: '';
        if ($v) return $v;
    }
    return '';
}

function fetch_pools_near(float $lat, float $lon): int {
    $db = get_db();
    $added = 0;

    $url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?' . http_build_query([
        'location' => "$lat,$lon",
        'radius'   => 10000,
        'keyword'  => 'swimming pool',
        'key'      => google_api_key(),
    ]);

    $response = json_decode(file_get_contents($url), true);

    if (empty($response['results'])) {
        return 0;
    }

    foreach ($response['results'] as $place) {
        $place_id = $place['place_id'];

        // Skip if already cached
        $check = $db->prepare("SELECT id FROM pools WHERE place_id = ?");
        $check->execute([$place_id]);
        if ($check->fetch()) continue;

        // Fetch place details + photo reference
        $details_url = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query([
            'place_id' => $place_id,
            'fields'   => 'name,formatted_address,rating,photos',
            'key'      => google_api_key(),
        ]);
        $details = json_decode(file_get_contents($details_url), true)['result'] ?? [];

        // Resolve photo to a CDN URL (no API key in the final URL)
        $photo_url = null;
        if (!empty($details['photos'][0]['photo_reference'])) {
            $photo_url = resolve_photo_url($details['photos'][0]['photo_reference']);
        }

        $stmt = $db->prepare("
            INSERT OR IGNORE INTO pools (place_id, name, address, photo_url, rating)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $place_id,
            $details['name'] ?? 'Mystery Pool 🤫',
            $details['formatted_address'] ?? null,
            $photo_url,
            $details['rating'] ?? null,
        ]);

        $added++;
    }

    return $added;
}

// Follow the Google Places photo redirect to get the actual CDN URL.
// This keeps the API key server-side only — the CDN URL is safe to embed in HTML.
function resolve_photo_url(string $photo_reference): string {
    $url = 'https://maps.googleapis.com/maps/api/place/photo?' . http_build_query([
        'maxwidth'        => 800,
        'photo_reference' => $photo_reference,
        'key'             => google_api_key(),
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $final = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    // Fall back to the original URL if redirect resolution failed
    return ($final && $final !== $url) ? $final : $url;
}
