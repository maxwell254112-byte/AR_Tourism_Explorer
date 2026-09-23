<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
if (!current_admin()) {
    json_response(['ok' => false, 'error' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    json_response(['ok' => false, 'error' => 'Invalid JSON'], 400);
}

verify_csrf((string) ($payload['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
$posterId = (int) ($payload['poster_id'] ?? 0);
$hotspots = $payload['hotspots'] ?? [];
if ($posterId < 1 || !is_array($hotspots)) {
    json_response(['ok' => false, 'error' => 'Invalid hotspot payload'], 400);
}

$exists = db()->prepare('SELECT id FROM ar_posters WHERE id = ?');
$exists->execute([$posterId]);
if (!$exists->fetch()) {
    json_response(['ok' => false, 'error' => 'Poster not found'], 404);
}

db()->beginTransaction();
db()->prepare('DELETE FROM ar_hotspots WHERE poster_id = ?')->execute([$posterId]);
$insert = db()->prepare(
    'INSERT INTO ar_hotspots (poster_id, name, attraction_id, x, y, width, height, z_index, video_url, content_type)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
foreach ($hotspots as $hotspot) {
    $x = max(0, min(1, (float) ($hotspot['x'] ?? 0.4)));
    $y = max(0, min(1, (float) ($hotspot['y'] ?? 0.4)));
    $w = max(0.04, min(1, (float) ($hotspot['width'] ?? 0.18)));
    $h = max(0.04, min(1, (float) ($hotspot['height'] ?? 0.16)));
    $insert->execute([
        $posterId,
        substr((string) ($hotspot['name'] ?? 'Hotspot'), 0, 180),
        !empty($hotspot['attraction_id']) ? (int) $hotspot['attraction_id'] : null,
        $x, $y, $w, $h,
        (int) ($hotspot['z_index'] ?? 1),
        $hotspot['video_url'] ?? null,
        in_array($hotspot['content_type'] ?? 'card', ['card', 'video', 'both'], true) ? $hotspot['content_type'] : 'card',
    ]);
}
db()->commit();
log_activity('hotspot.save', 'Admin saved hotspots for poster #' . $posterId);
json_response(['ok' => true]);
