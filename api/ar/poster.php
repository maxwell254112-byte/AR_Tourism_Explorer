<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';

$id = int_param('id');
$stmt = db()->prepare(
    "SELECT id, poster_name, poster_image, description, target_file, target_status
     FROM ar_posters
     WHERE id = ? AND status = 'active'"
);
$stmt->execute([$id]);
$poster = $stmt->fetch();
if (!$poster) {
    json_response(['ok' => false, 'error' => 'Poster not found.'], 404);
}
if ($poster['target_status'] !== 'READY' || !$poster['target_file']) {
    json_response(['ok' => false, 'error' => 'AR tracking is not ready. Please ask the administrator to compile this poster.'], 409);
}

json_response([
    'ok' => true,
    'poster' => [
        'id' => (int) $poster['id'],
        'name' => $poster['poster_name'],
        'image' => media_url($poster['poster_image']),
        'target' => media_url($poster['target_file']),
    ],
    'hotspots' => poster_hotspots((int) $poster['id']),
]);
