<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/upload.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(admin_url('ar-posters/index.php'));
}
verify_csrf();
$id = int_param('id');
$stmt = db()->prepare('SELECT * FROM ar_posters WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if ($item) {
    delete_local_file($item['poster_image'] ?? null);
    delete_local_file($item['target_file'] ?? null);
    db()->prepare('DELETE FROM ar_posters WHERE id = ?')->execute([$id]);
    log_activity('poster.delete', 'Admin deleted AR poster ' . $item['poster_name']);
    flash_set('success', 'Poster deleted.');
}
redirect(admin_url('ar-posters/index.php'));
