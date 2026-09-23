<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$posterId = int_param('poster');
$posters = db()->query(
    "SELECT id, poster_name, poster_image, description, target_file, target_status
     FROM ar_posters
     WHERE status = 'active'
     ORDER BY poster_name"
)->fetchAll();
$selected = null;
if ($posterId) {
    foreach ($posters as $poster) {
        if ((int) $poster['id'] === $posterId) {
            $selected = $poster;
            break;
        }
    }
}
$set = ar_photo_set();
$setReady = $set['target'] !== '' && is_file(APP_ROOT . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $set['target']));
$useSet = !$selected && $setReady && $set['posters'];
$pageTitle = 'AR Experience · ' . app_title();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/ar.css')) ?>">
</head>
<body class="ar-body">
<header class="ar-top">
    <a href="<?= e(url('index.php')) ?>">← Back</a>
    <strong>AR TOURISM</strong>
    <span class="ar-status" id="arStatus">READY</span>
</header>

<section class="ar-welcome" id="welcome">
    <div>
        <h1>Welcome to AR Tourism</h1>
        <p>You are inside the system. Allow the camera, then point it at a Penang <strong>photo</strong>. When the photo is recognized, the YouTube video starts. Do not scan a QR code at this step.</p>
        <?php if (!is_secure_context()): ?>
            <p class="https-warn">Camera access requires HTTPS. Please access this website using an HTTPS URL. localhost is allowed for local development.</p>
        <?php endif; ?>

        <?php if ($useSet): ?>
            <div class="ar-photo-row">
                <?php foreach ($set['posters'] as $poster): ?>
                    <figure>
                        <img src="<?= e(media_url($poster['poster_image'])) ?>" alt="<?= e($poster['poster_name']) ?>">
                        <figcaption><?= e($poster['poster_name']) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
            <p class="ar-notice">Print these photos or open them on another screen, then scan with the rear camera.</p>
            <button class="btn-start" id="startBtn" type="button" data-mode="set">START AR</button>
        <?php else: ?>
            <?php
            $readyPosters = array_values(array_filter($posters, static fn(array $poster): bool => $poster['target_status'] === 'READY' && !empty($poster['target_file'])));
            $ready = null;
            if ($selected && $selected['target_status'] === 'READY') {
                $ready = $selected;
            } elseif (!$selected && count($readyPosters) === 1) {
                $ready = $readyPosters[0];
            }
            ?>
            <?php if ($ready): ?>
                <p class="ar-notice"><?= e($ready['poster_name']) ?></p>
                <button class="btn-start" id="startBtn" type="button" data-poster="<?= (int) $ready['id'] ?>">START AR</button>
            <?php elseif ($readyPosters): ?>
                <p class="ar-notice">Choose a compiled photo, then start the camera.</p>
                <?php foreach ($readyPosters as $poster): ?>
                    <a class="btn-start" style="display:inline-block;margin:6px;text-decoration:none" href="<?= e(url('ar.php?poster=' . (int) $poster['id'])) ?>">
                        <?= e($poster['poster_name']) ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="ar-notice">AR tracking is not ready. Compile the Penang photos first, or tap a photo on the home page to watch YouTube now.</p>
                <a class="btn-start" style="display:inline-block;text-decoration:none" href="<?= e(url('index.php')) ?>">Open Penang photos</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<section class="ar-stage hidden" id="stage">
    <div id="ar-container"></div>
    <div class="ar-hint" id="arHint">Point your camera at a Penang photo</div>
    <article class="ar-card hidden" id="arCard"></article>
    <div class="ar-bottom">
        <button type="button" id="muteBtn"><i class="fa-solid fa-volume-xmark"></i><br>Mute</button>
        <button type="button" id="infoBtn"><i class="fa-solid fa-circle-info"></i><br>Info</button>
        <a id="youtubeBtn" href="#" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i><br>YouTube</a>
        <a id="mapBtn" href="#" target="_blank" rel="noopener"><i class="fa-solid fa-map"></i><br>Map</a>
        <button type="button" id="closeCardBtn"><i class="fa-solid fa-xmark"></i><br>Close</button>
    </div>
</section>

<script>
window.AR_CONFIG = {
    csrf: <?= json_encode(csrf_token()) ?>,
    mode: <?= json_encode($useSet ? 'set' : 'poster') ?>,
    posterEndpoint: <?= json_encode(url('api/ar/poster.php')) ?>,
    setEndpoint: <?= json_encode(url('api/ar/set.php')) ?>,
    secure: <?= is_secure_context() ? 'true' : 'false' ?>
};
</script>
<script src="https://cdn.jsdelivr.net/npm/three@0.157.0/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/hiukim/mind-ar-js@1.1.5/dist/mindar-image-three.prod.js"></script>
<script src="<?= e(asset('js/ar-experience.js')) ?>"></script>
</body>
</html>
