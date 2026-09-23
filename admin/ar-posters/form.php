<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/upload.php';
$adminSection = 'posters';
$id = int_param('id');
$item = ['poster_name' => '', 'poster_image' => '', 'description' => '', 'status' => 'active', 'target_status' => 'NOT_COMPILED'];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM ar_posters WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch() ?: $item;
}
$pageTitle = $id ? 'Edit AR poster' : 'Add AR poster';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = str_param('poster_name');
    $image = $item['poster_image'] ?? '';
    $resetTarget = false;
    if (!empty($_FILES['poster_image']['name'])) {
        $image = store_uploaded_image($_FILES['poster_image'], 'posters');
        if (!empty($item['poster_image'])) {
            delete_local_file($item['poster_image']);
        }
        $resetTarget = true;
    }
    $status = str_param('status') === 'inactive' ? 'inactive' : 'active';
    if ($name === '' || $image === '') {
        flash_set('error', 'Poster name and image are required.');
    } elseif ($id) {
        if ($resetTarget) {
            db()->prepare("UPDATE ar_posters SET poster_name=?, poster_image=?, description=?, status=?, target_status='NOT_COMPILED', target_file=NULL, target_compiled_at=NULL WHERE id=?")
                ->execute([$name, $image, str_param('description'), $status, $id]);
        } else {
            db()->prepare('UPDATE ar_posters SET poster_name=?, poster_image=?, description=?, status=? WHERE id=?')
                ->execute([$name, $image, str_param('description'), $status, $id]);
        }
        log_activity('poster.update', 'Admin updated AR poster ' . $name);
        flash_set('success', $resetTarget ? 'Poster replaced. Target status is now NOT COMPILED.' : 'Poster updated.');
        redirect(admin_url('ar-posters/form.php?id=' . $id));
    } else {
        db()->prepare("INSERT INTO ar_posters (poster_name, poster_image, description, status, target_status) VALUES (?,?,?,?,'NOT_COMPILED')")
            ->execute([$name, $image, str_param('description'), $status]);
        $newId = (int) db()->lastInsertId();
        log_activity('poster.create', 'Admin uploaded AR poster ' . $name);
        flash_set('success', 'Poster created. Next, add hotspots and compile the target.');
        redirect(admin_url('ar-posters/editor.php?id=' . $newId));
    }
}
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Poster name</label><input class="form-control" name="poster_name" value="<?= e($item['poster_name']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?= e($item['description']) ?></textarea></div>
        <div class="mb-3"><label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $item['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="mb-3"><label class="form-label">Poster image</label><input class="form-control" type="file" name="poster_image" accept="image/*" <?= $id ? '' : 'required' ?>>
            <small class="text-muted">This image becomes the MindAR tracking target. Replacing it resets compilation.</small>
            <?php if (!empty($item['poster_image'])): ?><div class="mt-2"><img src="<?= e(media_url($item['poster_image'])) ?>" alt="" style="max-width:320px"></div><?php endif; ?>
        </div>
        <?php if ($id): ?><p>Target status: <?= status_badge($item['target_status']) ?></p><?php endif; ?>
        <button class="btn btn-success">Save</button>
        <?php if ($id): ?>
            <a class="btn btn-outline-success" href="<?= e(admin_url('ar-posters/editor.php?id=' . $id)) ?>">Configure hotspots</a>
            <a class="btn btn-outline-success" href="<?= e(admin_url('ar-posters/compile.php?id=' . $id)) ?>">Compile target</a>
        <?php endif; ?>
        <a class="btn btn-link" href="<?= e(admin_url('ar-posters/index.php')) ?>">Back</a>
    </form>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
