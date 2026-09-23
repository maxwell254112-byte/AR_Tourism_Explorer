<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/upload.php';
$adminSection = 'attractions';
$id = int_param('id');
$item = [
    'destination_id' => '', 'name' => '', 'short_description' => '', 'full_description' => '',
    'main_image' => '', 'youtube_url' => '', 'website_url' => '', 'google_maps_url' => '',
    'latitude' => '', 'longitude' => '', 'opening_hours' => '', 'entry_information' => '',
    'category_id' => '', 'is_featured' => 0, 'status' => 'active', 'display_order' => 0,
];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM attractions WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch() ?: $item;
}
$destinations = db()->query('SELECT id, name FROM destinations ORDER BY name')->fetchAll();
$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$gallery = [];
if ($id) {
    $g = db()->prepare('SELECT * FROM attraction_images WHERE attraction_id = ? ORDER BY display_order, id');
    $g->execute([$id]);
    $gallery = $g->fetchAll();
}
$pageTitle = $id ? 'Edit attraction' : 'Add attraction';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    verify_csrf();
    $imageId = int_param('image_id');
    $stmt = db()->prepare('SELECT * FROM attraction_images WHERE id = ? AND attraction_id = ?');
    $stmt->execute([$imageId, $id]);
    $image = $stmt->fetch();
    if ($image) {
        delete_local_file($image['image_path']);
        db()->prepare('DELETE FROM attraction_images WHERE id = ?')->execute([$imageId]);
        flash_set('success', 'Gallery image removed.');
    }
    redirect(admin_url('attractions/form.php?id=' . $id));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = str_param('name');
    $destinationId = int_param('destination_id');
    $image = $item['main_image'] ?? null;
    if (!empty($_FILES['main_image']['name'])) {
        $image = store_uploaded_image($_FILES['main_image'], 'attractions');
        if (!empty($item['main_image'])) {
            delete_local_file($item['main_image']);
        }
    }
    $data = [
        $destinationId, $name, str_param('short_description'), str_param('full_description'), $image,
        str_param('youtube_url'), str_param('website_url'), str_param('google_maps_url'),
        str_param('latitude') !== '' ? str_param('latitude') : null,
        str_param('longitude') !== '' ? str_param('longitude') : null,
        str_param('opening_hours'), str_param('entry_information'),
        int_param('category_id') ?: null, isset($_POST['is_featured']) ? 1 : 0,
        str_param('status') === 'inactive' ? 'inactive' : 'active', int_param('display_order'),
    ];
    if ($name === '' || !$destinationId) {
        flash_set('error', 'Name and destination are required.');
    } elseif ($id) {
        $data[] = $id;
        db()->prepare('UPDATE attractions SET destination_id=?, name=?, short_description=?, full_description=?, main_image=?, youtube_url=?, website_url=?, google_maps_url=?, latitude=?, longitude=?, opening_hours=?, entry_information=?, category_id=?, is_featured=?, status=?, display_order=? WHERE id=?')->execute($data);
        $savedId = $id;
        log_activity('attraction.update', 'Admin updated attraction ' . $name);
        flash_set('success', 'Attraction updated.');
    } else {
        db()->prepare('INSERT INTO attractions (destination_id, name, short_description, full_description, main_image, youtube_url, website_url, google_maps_url, latitude, longitude, opening_hours, entry_information, category_id, is_featured, status, display_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($data);
        $savedId = (int) db()->lastInsertId();
        log_activity('attraction.create', 'Admin added attraction ' . $name);
        flash_set('success', 'Attraction created.');
    }
    if (!empty($savedId) && !empty($_FILES['gallery']['name'][0])) {
        foreach ($_FILES['gallery']['name'] as $index => $filename) {
            if (!$filename) {
                continue;
            }
            $file = [
                'name' => $_FILES['gallery']['name'][$index],
                'type' => $_FILES['gallery']['type'][$index],
                'tmp_name' => $_FILES['gallery']['tmp_name'][$index],
                'error' => $_FILES['gallery']['error'][$index],
                'size' => $_FILES['gallery']['size'][$index],
            ];
            $path = store_uploaded_image($file, 'gallery');
            db()->prepare('INSERT INTO attraction_images (attraction_id, image_path, alt_text) VALUES (?, ?, ?)')->execute([$savedId, $path, $name]);
        }
    }
    if (!empty($savedId)) {
        redirect(admin_url('attractions/form.php?id=' . $savedId));
    }
}

require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="<?= e($item['name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Destination</label>
                <select class="form-select" name="destination_id" required>
                    <option value="">Select destination</option>
                    <?php foreach ($destinations as $destination): ?>
                        <option value="<?= (int) $destination['id'] ?>" <?= (int) $item['destination_id'] === (int) $destination['id'] ? 'selected' : '' ?>><?= e($destination['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Category</label>
                <select class="form-select" name="category_id">
                    <option value="">None</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (int) $item['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $item['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Order</label><input class="form-control" type="number" name="display_order" value="<?= e((string) $item['display_order']) ?>"></div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" <?= !empty($item['is_featured']) ? 'checked' : '' ?>><label class="form-check-label">Featured</label></div></div>
            <div class="col-12"><label class="form-label">Short description</label><textarea class="form-control" name="short_description" rows="2"><?= e($item['short_description']) ?></textarea></div>
            <div class="col-12"><label class="form-label">Full description</label><textarea class="form-control" name="full_description" rows="5"><?= e($item['full_description']) ?></textarea></div>
            <div class="col-md-6"><label class="form-label">YouTube URL</label><input class="form-control" name="youtube_url" value="<?= e($item['youtube_url']) ?>" placeholder="https://www.youtube.com/watch?v="></div>
            <div class="col-md-6"><label class="form-label">Website URL</label><input class="form-control" name="website_url" value="<?= e($item['website_url']) ?>"></div>
            <div class="col-md-6"><label class="form-label">Google Maps URL</label><input class="form-control" name="google_maps_url" value="<?= e($item['google_maps_url']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Latitude</label><input class="form-control" name="latitude" value="<?= e((string) $item['latitude']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Longitude</label><input class="form-control" name="longitude" value="<?= e((string) $item['longitude']) ?>"></div>
            <div class="col-md-6"><label class="form-label">Opening hours</label><input class="form-control" name="opening_hours" value="<?= e($item['opening_hours']) ?>"></div>
            <div class="col-md-6"><label class="form-label">Entry information</label><input class="form-control" name="entry_information" value="<?= e($item['entry_information']) ?>"></div>
            <div class="col-md-6"><label class="form-label">Main image</label><input class="form-control" type="file" name="main_image" accept="image/*">
                <?php if (!empty($item['main_image'])): ?><img class="mt-2" src="<?= e(media_url($item['main_image'])) ?>" alt="" style="max-width:220px"><?php endif; ?>
            </div>
            <div class="col-md-6"><label class="form-label">Gallery images</label><input class="form-control" type="file" name="gallery[]" accept="image/*" multiple></div>
        </div>
        <button class="btn btn-success mt-3">Save</button>
        <a class="btn btn-link" href="<?= e(admin_url('attractions/index.php')) ?>">Back</a>
    </form>
    <?php if ($gallery): ?>
        <h2 class="h5 mt-4">Gallery</h2>
        <div class="d-flex flex-wrap gap-3">
            <?php foreach ($gallery as $image): ?>
                <div>
                    <img src="<?= e(media_url($image['image_path'])) ?>" alt="" style="width:140px;height:90px;object-fit:cover;border-radius:8px">
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="image_id" value="<?= (int) $image['id'] ?>">
                        <button class="btn btn-sm btn-link text-danger" name="delete_image" value="1">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
