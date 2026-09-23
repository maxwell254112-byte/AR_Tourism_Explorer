<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
$adminSection = 'compile';
$id = int_param('id');
if ($id) {
    $stmt = db()->prepare('SELECT * FROM ar_posters WHERE id = ?');
    $stmt->execute([$id]);
    $posters = $stmt->fetchAll();
} else {
    $posters = db()->query('SELECT * FROM ar_posters ORDER BY poster_name')->fetchAll();
}
$pageTitle = 'Target Compilation';
$adminScripts = [
    'https://cdn.jsdelivr.net/npm/mind-ar@1.2.5/dist/mindar-image.prod.js',
    asset('js/compiler.js'),
];
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <h2 class="h5">Preparing AR Target</h2>
    <p>The MindAR compiler runs in this browser, then the generated <code>.mind</code> file is stored with the poster. Compilation can take a minute on large images.</p>
    <div class="mb-3">
        <label class="form-label">Poster</label>
        <select class="form-select" id="compilePoster">
            <?php foreach ($posters as $poster): ?>
                <option value="<?= (int) $poster['id'] ?>" data-image="<?= e(media_url($poster['poster_image'])) ?>" <?= $id === (int) $poster['id'] ? 'selected' : '' ?>>
                    <?= e($poster['poster_name']) ?> — <?= e($poster['target_status']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="compile-bar mb-2"><span id="compileFill"></span></div>
    <p id="compileText">Waiting to start…</p>
    <button class="btn btn-success" id="compileBtn" type="button">Compile target</button>
    <a class="btn btn-outline-success" href="<?= e(admin_url('ar-posters/index.php')) ?>">Back to posters</a>
    <hr class="my-4">
    <h2 class="h6">Or upload a precompiled .mind file</h2>
    <p>If in-browser compilation is unavailable, compile the poster with the official MindAR compiler and upload the <code>.mind</code> file here.</p>
    <form method="post" enctype="multipart/form-data" action="<?= e(url('api/ar/save-target.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="poster_id" id="uploadPosterId" value="<?= (int) ($id ?: ($posters[0]['id'] ?? 0)) ?>">
        <div class="mb-3"><input class="form-control" type="file" name="target" accept=".mind,application/octet-stream" required></div>
        <button class="btn btn-outline-success">Upload .mind file</button>
    </form>
</div>
<script>
window.COMPILER_CONFIG = {
    csrf: <?= json_encode(csrf_token()) ?>,
    saveUrl: <?= json_encode(url('api/ar/save-target.php')) ?>
};
</script>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
