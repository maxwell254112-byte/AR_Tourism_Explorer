<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
$adminSection = 'posters';
$id = int_param('id');
$stmt = db()->prepare('SELECT * FROM ar_posters WHERE id = ?');
$stmt->execute([$id]);
$poster = $stmt->fetch();
if (!$poster) {
    flash_set('error', 'Poster not found.');
    redirect(admin_url('ar-posters/index.php'));
}
$hotspots = db()->prepare('SELECT * FROM ar_hotspots WHERE poster_id = ? ORDER BY z_index, id');
$hotspots->execute([$id]);
$attractions = db()->query(
    "SELECT a.id, a.name, d.name AS destination_name
     FROM attractions a JOIN destinations d ON d.id = a.destination_id
     WHERE a.status = 'active' ORDER BY d.name, a.name"
)->fetchAll();
$pageTitle = 'AR Poster Editor';
$adminScripts = [asset('js/hotspot-editor.js')];
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card mb-3">
    <div class="d-flex flex-wrap justify-content-between gap-2">
        <div>
            <h2 class="h5 mb-1"><?= e($poster['poster_name']) ?></h2>
            <p class="mb-0">Click the poster to add a hotspot. Drag to move, use the corner handle to resize. Coordinates are saved as 0–1 values.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" id="saveHotspots" type="button">Save</button>
            <a class="btn btn-outline-success" href="<?= e(admin_url('ar-posters/compile.php?id=' . $id)) ?>">Compile</a>
            <a class="btn btn-outline-success" href="<?= e(url('ar.php?poster=' . $id)) ?>">Preview</a>
        </div>
    </div>
</div>
<div class="poster-editor">
    <div class="poster-canvas-wrap">
        <div class="poster-canvas" id="posterCanvas">
            <img id="posterImage" src="<?= e(media_url($poster['poster_image'])) ?>" alt="<?= e($poster['poster_name']) ?>">
        </div>
    </div>
    <aside class="admin-card">
        <h2 class="h5">Hotspot</h2>
        <p id="hotspotEmpty">Select or create a hotspot.</p>
        <form id="hotspotForm" class="d-none">
            <div class="mb-2"><label class="form-label">Name</label><input class="form-control" id="hsName"></div>
            <div class="mb-2"><label class="form-label">Attraction</label>
                <select class="form-select" id="hsAttraction">
                    <option value="">None</option>
                    <?php foreach ($attractions as $attraction): ?>
                        <option value="<?= (int) $attraction['id'] ?>"><?= e($attraction['destination_name'] . ' — ' . $attraction['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-2"><label class="form-label">Video URL</label><input class="form-control" id="hsVideo"></div>
            <div class="mb-2"><label class="form-label">Content type</label>
                <select class="form-select" id="hsType">
                    <option value="card">Information card</option>
                    <option value="video">Video</option>
                    <option value="both">Card and video</option>
                </select>
            </div>
            <button class="btn btn-outline-danger btn-sm" id="deleteHotspot" type="button">Delete hotspot</button>
        </form>
    </aside>
</div>
<script>
window.EDITOR_CONFIG = {
    posterId: <?= (int) $id ?>,
    csrf: <?= json_encode(csrf_token()) ?>,
    saveUrl: <?= json_encode(url('api/ar/hotspots.php')) ?>,
    hotspots: <?= json_encode($hotspots->fetchAll(), JSON_UNESCAPED_UNICODE) ?>
};
</script>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
