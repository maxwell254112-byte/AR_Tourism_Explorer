<?php
declare(strict_types=1);

/**
 * One-time admin password reset for cPanel.
 * Open once in browser, then delete this file.
 * Sets username=admin password=admin123
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

header('Content-Type: text/plain; charset=utf-8');

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo = db();
    @$pdo->exec('DELETE FROM login_attempts');

    $stmt = $pdo->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
    $user = $stmt->fetch();

    if ($user) {
        $pdo->prepare("UPDATE users SET password_hash = ?, email = 'admin@artourism.local', role = 'admin', status = 'active' WHERE id = ?")
            ->execute([$hash, (int) $user['id']]);
        echo "OK\nUpdated existing admin.\n";
    } else {
        $pdo->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES ('admin', 'admin@artourism.local', ?, 'admin', 'active')")
            ->execute([$hash]);
        echo "OK\nCreated admin account.\n";
    }

    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "Login: " . (isset($_SERVER['HTTP_HOST']) ? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']) : '') . (defined('BASE_PATH') ? BASE_PATH : '') . "/admin/login.php\n";
    echo "\nIMPORTANT: Delete install/reset-admin.php after use.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "ERROR: " . $e->getMessage() . "\n";
}
