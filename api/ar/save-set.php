<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/upload.php';

if (!current_admin() && !is_local_request()) {
    json_response(['ok' => false, 'error' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

if (current_admin()) {
    verify_csrf((string) ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
}

$binary = compiled_target_binary();
if ($binary === null) {
    json_response(['ok' => false, 'error' => 'Compiled target file is missing'], 400);
}

function compiled_target_binary(): ?string
{
    $file = $_FILES['target'] ?? null;
    if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $fromFile = file_get_contents((string) $file['tmp_name']);
        if (is_string($fromFile) && $fromFile !== '') {
            return $fromFile;
        }
    }
    $raw = (string) ($_POST['target_b64'] ?? '');
    if ($raw === '') {
        return null;
    }
    $decoded = base64_decode($raw, true);
    return (is_string($decoded) && $decoded !== '') ? $decoded : null;
}

ensure_upload_dirs();
$old = setting('ar_set_target');
if ($old) {
    delete_local_file($old);
}

$name = 'penang-set-' . date('YmdHis') . '.mind';
$relative = 'assets/ar-targets/' . $name;
if (file_put_contents(TARGET_DIR . DIRECTORY_SEPARATOR . $name, $binary) === false) {
    json_response(['ok' => false, 'error' => 'Unable to save the compiled AR target'], 500);
}

$ids = json_decode((string) ($_POST['poster_ids'] ?? setting('ar_set_ids', '[]')), true);
if (!is_array($ids) || !$ids) {
    $ids = ar_photo_set()['ids'];
}

$stmt = db()->prepare(
    'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
);
$stmt->execute(['ar_set_target', $relative]);
$stmt->execute(['ar_set_ids', json_encode(array_map('intval', $ids))]);

if ($ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $update = db()->prepare("UPDATE ar_posters SET target_file = ?, target_status = 'READY', target_compiled_at = NOW() WHERE id IN ($placeholders)");
    $update->execute(array_merge([$relative], array_map('intval', $ids)));
}

log_activity('poster.compile', 'Compiled Penang photo AR set');
if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    json_response(['ok' => true, 'target' => $relative]);
}
if (current_admin() || is_local_request()) {
    flash_set('success', 'AR Target Ready. You can scan the Penang photos now.');
    header('Location: ' . url('ar.php'));
    exit;
}
json_response(['ok' => true, 'target' => $relative]);
