<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function ensure_upload_dirs(): void
{
    foreach ([
        UPLOAD_DIR,
        UPLOAD_DIR . DIRECTORY_SEPARATOR . 'destinations',
        UPLOAD_DIR . DIRECTORY_SEPARATOR . 'attractions',
        UPLOAD_DIR . DIRECTORY_SEPARATOR . 'gallery',
        UPLOAD_DIR . DIRECTORY_SEPARATOR . 'posters',
        TARGET_DIR,
    ] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

function allowed_image_types(): array
{
    return [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
}

function store_uploaded_image(array $file, string $folder, int $maxBytes = UPLOAD_MAX_BYTES): string
{
    ensure_upload_dirs();

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image could not be uploaded. Please try another file.');
    }
    if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) {
        throw new RuntimeException('The image is too large. Maximum size is 8 MB.');
    }

    $tmp = (string) $file['tmp_name'];
    $info = @getimagesize($tmp);
    if ($info === false) {
        throw new RuntimeException('The file is not a valid image.');
    }

    $mime = (string) ($info['mime'] ?? '');
    $map = allowed_image_types();
    if (!isset($map[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP and GIF images are allowed.');
    }

    $ext = $map[$mime];
    $name = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $relative = 'assets/uploads/' . trim($folder, '/') . '/' . $name;
    $dest = APP_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

    if (!move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('The server could not save the uploaded image.');
    }

    return $relative;
}

function store_target_file(string $binary, int $posterId): string
{
    ensure_upload_dirs();
    $name = 'poster-' . $posterId . '-' . date('YmdHis') . '.mind';
    $relative = 'assets/ar-targets/' . $name;
    $dest = TARGET_DIR . DIRECTORY_SEPARATOR . $name;
    if (file_put_contents($dest, $binary) === false) {
        throw new RuntimeException('Unable to save the compiled AR target.');
    }
    return $relative;
}

function delete_local_file(?string $path): void
{
    if (!$path || preg_match('~^https?://~i', $path)) {
        return;
    }
    $full = APP_ROOT . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));
    $root = realpath(APP_ROOT);
    $real = realpath($full);
    if ($root && $real && str_starts_with($real, $root) && is_file($real)) {
        @unlink($real);
    }
}
