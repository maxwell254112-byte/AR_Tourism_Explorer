<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/functions.php';

if (!is_local_request() && !current_admin()) {
    http_response_code(403);
    exit('This compiler can only be opened on this computer or by an administrator.');
}

$set = ar_photo_set();
$images = [];
foreach ($set['posters'] as $poster) {
    $images[] = [
        'id' => (int) $poster['id'],
        'name' => $poster['poster_name'],
        'src' => media_url($poster['poster_image']),
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compile Penang AR photos</title>
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <style>
        body { font-family: Outfit, sans-serif; padding: 32px; max-width: 720px; margin: auto; }
        .preview { display: flex; gap: 10px; flex-wrap: wrap; margin: 16px 0; }
        .preview img { width: 140px; height: 100px; object-fit: cover; border-radius: 10px; }
    </style>
</head>
<body>
    <h1>Compile Penang AR photos</h1>
    <p>This prepares the three Penang photos so a phone camera can recognize them and play YouTube.</p>
    <div class="preview">
        <?php foreach ($images as $image): ?>
            <img src="<?= e($image['src']) ?>" alt="<?= e($image['name']) ?>">
        <?php endforeach; ?>
    </div>
    <div class="compile-bar mb-2"><span id="compileFill"></span></div>
    <p id="compileText">Starting compiler…</p>
    <button class="btn btn-success" id="compileBtn" type="button">Compile now</button>
    <hr>
    <h2>Or upload a .mind file</h2>
    <p>If in-browser compile fails, use the official MindAR compiler with the three Penang photos, then upload the downloaded <code>targets.mind</code> here.</p>
    <form method="post" enctype="multipart/form-data" action="<?= e(url('api/ar/save-set.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="poster_ids" value="<?= e(json_encode(array_column($images, 'id'))) ?>">
        <p><input type="file" name="target" accept=".mind,application/octet-stream" required></p>
        <button class="btn btn-success" type="submit">Upload .mind file</button>
    </form>
    <p><a href="<?= e(url('ar.php')) ?>">Open AR Experience</a></p>
<script>
window.COMPILER_SET = {
    csrf: <?= json_encode(csrf_token()) ?>,
    saveUrl: <?= json_encode(url('api/ar/save-set.php')) ?>,
    ids: <?= json_encode(array_column($images, 'id')) ?>,
    images: <?= json_encode($images, JSON_UNESCAPED_SLASHES) ?>
};
</script>
<script src="https://cdn.jsdelivr.net/gh/hiukim/mind-ar-js@1.1.5/dist/mindar-image.prod.js"></script>
<script src="<?= e(asset('js/compiler-set.js')) ?>"></script>
</body>
</html>
