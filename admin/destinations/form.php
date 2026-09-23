<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/upload.php';
$adminSection = 'destinations';
$id = int_param('id');
$item = [
    'name' => '', 'slug' => '', 'short_description' => '', 'full_description' => '',
    'cover_image' => '', 'location' => '', 'latitude' => '', 'longitude' => '',
    'google_maps_url' => '', 'category_id' => '', 'is_featured' => 0, 'status' => 'active',
];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM destinations WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch() ?: $item;
}
$categories = db()->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$pageTitle = $id ? 'Edit destination' : 'Add destination';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = str_param('name');
    $slug = unique_slug('destinations', str_param('slug') ?: $name, $id ?: null);
    $cover = $item['cover_image'] ?? null;
    if (!empty($_FILES['cover_image']['name'])) {
        $cover = store_uploaded_image($_FILES['cover_image'], 'destinations');
        if (!empty($item['cover_image'])) {
            delete_local_file($item['cover_image']);
        }
    }
    $data = [
        $name,
        $slug,
        str_param('short_description'),
        str_param('full_description'),
        $cover,
        str_param('location'),
        str_param('latitude') !== '' ? str_param('latitude') : null,
        str_param('longitude') !== '' ? str_param('longitude') : null,
        str_param('google_maps_url'),
        int_param('category_id') ?: null,
        isset($_POST['is_featured']) ? 1 : 0,
        str_param('status') === 'inactive' ? 'inactive' : 'active',
    ];
    if ($name === '') {
        flash_set('error', 'Destination name is required.');
    } elseif ($id) {
        $data[] = $id;
        db()->prepare('UPDATE destinations SET name=?, slug=?, short_description=?, full_description=?, cover_image=?, location=?, latitude=?, longitude=?, google_maps_url=?, category_id=?, is_featured=?, status=? WHERE id=?')->execute($data);
        log_activity('destination.update', 'Admin updated destination ' . $name);
        flash_set('success', 'Destination updated.');
        redirect(admin_url('destinations/form.php?id=' . $id));
    } else {
        db()->prepare('INSERT INTO destinations (name, slug, short_description, full_description, cover_image, location, latitude, longitude, google_maps_url, category_id, is_featured, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')->execute($data);
        log_activity('destination.create', 'Admin added destination ' . $name);
        flash_set('success', 'Destination created.');
        redirect(admin_url('destinations/index.php'));
    }
}
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="<?= e($item['name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Slug</label><input class="form-control" name="slug" value="<?= e($item['slug']) ?>"></div>
            <div class="col-md-6"><label class="form-label">Category</label>
                <select class="form-select" name="category_id">
                    <option value="">None</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (int) $item['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $item['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" <?= !empty($item['is_featured']) ? 'checked' : '' ?>><label class="form-check-label">Featured</label></div></div>
            <div class="col-12"><label class="form-label">Short description</label><textarea class="form-control" name="short_description" rows="2"><?= e($item['short_description']) ?></textarea></div>
            <div class="col-12"><label class="form-label">Full description</label><textarea class="form-control" name="full_description" rows="5"><?= e($item['full_description']) ?></textarea></div>
            <div class="col-md-6"><label class="form-label">Location</label><input class="form-control" name="location" value="<?= e($item['location']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Latitude</label><input class="form-control" name="latitude" value="<?= e((string) $item['latitude']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Longitude</label><input class="form-control" name="longitude" value="<?= e((string) $item['longitude']) ?>"></div>
            <div class="col-12"><label class="form-label">Google Maps URL</label><input class="form-control" name="google_maps_url" value="<?= e($item['google_maps_url']) ?>"></div>
            <div class="col-12"><label class="form-label">Cover image</label><input class="form-control" type="file" name="cover_image" accept="image/*">
                <?php if (!empty($item['cover_image'])): ?><img class="mt-2" src="<?= e(media_url($item['cover_image'])) ?>" alt="" style="max-width:220px"><?php endif; ?>
            </div>
        </div>
        <button class="btn btn-success mt-3">Save</button>
        <a class="btn btn-link" href="<?= e(admin_url('destinations/index.php')) ?>">Back</a>
    </form>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
