<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/upload.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(admin_url('destinations/index.php'));
}
verify_csrf();
$id = int_param('id');
$stmt = db()->prepare('SELECT * FROM destinations WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if ($item) {
    delete_local_file($item['cover_image'] ?? null);
    db()->prepare('DELETE FROM destinations WHERE id = ?')->execute([$id]);
    log_activity('destination.delete', 'Admin deleted destination ' . $item['name']);
    flash_set('success', 'Destination deleted.');
}
redirect(admin_url('destinations/index.php'));
