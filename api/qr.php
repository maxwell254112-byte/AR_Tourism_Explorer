<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/config.php';

$data = trim((string) ($_GET['data'] ?? ''));
$size = (int) ($_GET['size'] ?? 280);
$size = max(120, min(600, $size));

if ($data === '' || strlen($data) > 800 || !preg_match('#^https?://#i', $data)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Invalid QR data';
    exit;
}

$cacheDir = APP_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'qr-cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$hash = hash('sha256', $size . '|' . $data);
$cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $hash . '.png';

if (!is_file($cacheFile) || filesize($cacheFile) < 80) {
    $remote = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&margin=8&data=' . rawurlencode($data);
    $png = fetch_qr_png($remote);
    if ($png === null) {
        http_response_code(502);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'QR service unavailable';
        exit;
    }
    file_put_contents($cacheFile, $png);
}

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');
header('Content-Length: ' . (string) filesize($cacheFile));
readfile($cacheFile);

function fetch_qr_png(string $remote): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($remote);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'AR-Tourism-Explorer/1.0',
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        if ($body !== false && $code === 200 && str_contains($type, 'image') && strncmp($body, "\x89PNG", 4) === 0) {
            return $body;
        }
    }

    $body = @file_get_contents($remote);
    if (is_string($body) && strncmp($body, "\x89PNG", 4) === 0) {
        return $body;
    }
    return null;
}
