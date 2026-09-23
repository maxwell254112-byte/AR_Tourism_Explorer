<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/upload.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(admin_url('attractions/index.php'));
}
verify_csrf();
$id = int_param('id');
$stmt = db()->prepare('SELECT * FROM attractions WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if ($item) {
    $images = db()->prepare('SELECT image_path FROM attraction_images WHERE attraction_id = ?');
    $images->execute([$id]);
    foreach ($images as $image) {
        delete_local_file($image['image_path']);
    }
    delete_local_file($item['main_image'] ?? null);
    db()->prepare('DELETE FROM attractions WHERE id = ?')->execute([$id]);
    log_activity('attraction.delete', 'Admin deleted attraction ' . $item['name']);
    flash_set('success', 'Attraction deleted.');
}
redirect(admin_url('attractions/index.php'));
