<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';

$set = ar_photo_set();
if (!$set['posters']) {
    json_response(['ok' => false, 'error' => 'No AR photo set has been configured.'], 404);
}
if ($set['target'] === '' || !is_file(APP_ROOT . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $set['target']))) {
    json_response(['ok' => false, 'error' => 'AR tracking is not ready. Please compile the Penang photo set first.'], 409);
}

$targets = [];
foreach ($set['posters'] as $index => $poster) {
    $targets[] = [
        'index' => $index,
        'posterId' => (int) $poster['id'],
        'name' => $poster['poster_name'],
        'image' => media_url($poster['poster_image']),
        'hotspots' => poster_hotspots((int) $poster['id']),
    ];
}

json_response([
    'ok' => true,
    'target' => media_url($set['target']),
    'targets' => $targets,
]);
