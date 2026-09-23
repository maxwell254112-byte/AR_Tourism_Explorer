<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    if ($https) {
        ini_set('session.cookie_secure', '1');
    }
    session_name('artourism_session');
    session_start();
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $admin = false;
    if ($admin !== false) {
        return $admin;
    }
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin' AND status = 'active' LIMIT 1");
    $stmt->execute([(int) $_SESSION['admin_id']]);
    $admin = $stmt->fetch() ?: null;
    return $admin;
}

function require_admin(): void
{
    if (!current_admin()) {
        $next = urlencode((string) ($_SERVER['REQUEST_URI'] ?? 'index.php'));
        header('Location: ' . admin_url('login.php?next=' . $next));
        exit;
    }
}

function admin_url(string $path = ''): string
{
    $base = BASE_PATH === '' ? '' : BASE_PATH;
    return $base . '/admin/' . ltrim($path, '/');
}

function login_allowed(): bool
{
    $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    $stmt = db()->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE ip_address = ? AND created_at >= (NOW() - INTERVAL 10 MINUTE) AND success = 0"
    );
    $stmt->execute([$ip]);
    return (int) $stmt->fetchColumn() < 5;
}

function record_login_attempt(string $username, bool $success): void
{
    $stmt = db()->prepare('INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, ?)');
    $stmt->execute([$username, substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45), $success ? 1 : 0]);
}

function attempt_admin_login(string $identity, string $password, bool $remember): bool
{
    $stmt = db()->prepare(
        "SELECT * FROM users
         WHERE (username = ? OR email = ?) AND role = 'admin' AND status = 'active'
         LIMIT 1"
    );
    $stmt->execute([$identity, $identity]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        record_login_attempt($identity, false);
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $user['id'];
    $_SESSION['admin_name'] = $user['username'];
    record_login_attempt($identity, true);

    db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([(int) $user['id']]);

    if ($remember) {
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), [
            'expires' => time() + 60 * 60 * 24 * 14,
            'path' => $params['path'] ?: '/',
            'secure' => !empty($params['secure']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    return true;
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => $params['path'] ?: '/',
            'secure' => !empty($params['secure']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
}
