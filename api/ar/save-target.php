<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/upload.php';
if (!current_admin()) {
    json_response(['ok' => false, 'error' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

verify_csrf((string) ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
$posterId = int_param('poster_id');
$stmt = db()->prepare('SELECT * FROM ar_posters WHERE id = ?');
$stmt->execute([$posterId]);
$poster = $stmt->fetch();
if (!$poster) {
    json_response(['ok' => false, 'error' => 'Poster not found'], 404);
}

$file = $_FILES['target'] ?? null;
if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    json_response(['ok' => false, 'error' => 'Compiled target file is missing'], 400);
}
if (($file['size'] ?? 0) > 20 * 1024 * 1024) {
    json_response(['ok' => false, 'error' => 'Target file is too large'], 400);
}

$binary = file_get_contents((string) $file['tmp_name']);
if ($binary === false || $binary === '') {
    json_response(['ok' => false, 'error' => 'Unable to read the compiled target'], 400);
}

if (!empty($poster['target_file'])) {
    delete_local_file($poster['target_file']);
}

$path = store_target_file($binary, $posterId);
db()->prepare("UPDATE ar_posters SET target_file = ?, target_status = 'READY', target_compiled_at = NOW() WHERE id = ?")
    ->execute([$path, $posterId]);
log_activity('poster.compile', 'Admin compiled AR target for ' . $poster['poster_name']);
if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    json_response(['ok' => true, 'target' => $path]);
}
flash_set('success', 'AR Target Ready');
redirect(admin_url('ar-posters/compile.php?id=' . $posterId));
