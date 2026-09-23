<?php
declare(strict_types=1);

define('APP_NAME', 'AR Tourism Explorer');
define('APP_VERSION', '1.0.0');
define('ASSET_VERSION', '1.5.0');

define('DB_HOST', 'localhost');
define('DB_NAME', 'ar_tourism');
define('DB_USER', 'root');
define('DB_PASS', '123qwe');

define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_MAX_BYTES', 8 * 1024 * 1024);
define('UPLOAD_DIR', APP_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads');
define('TARGET_DIR', APP_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'ar-targets');
define('PER_PAGE', 10);

$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? APP_ROOT) ?: APP_ROOT;
$appRootReal = realpath(APP_ROOT) ?: APP_ROOT;
$docNorm = strtolower(str_replace('\\', '/', $documentRoot));
$appNorm = strtolower(str_replace('\\', '/', $appRootReal));
$base = '';
if (str_starts_with($appNorm, $docNorm)) {
    $base = str_replace('\\', '/', substr($appRootReal, strlen($documentRoot)));
}
define('BASE_PATH', rtrim($base, '/'));
