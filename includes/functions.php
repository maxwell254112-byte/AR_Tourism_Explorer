<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443') {
        return true;
    }
    return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

function is_secure_context(): bool
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $hostname = explode(':', $host)[0];
    return is_https() || in_array($hostname, ['localhost', '127.0.0.1', '::1'], true);
}

function is_local_request(): bool
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    return in_array($ip, ['127.0.0.1', '::1'], true);
}

function hotspot_payload(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'x' => (float) $row['x'],
        'y' => (float) $row['y'],
        'width' => (float) $row['width'],
        'height' => (float) $row['height'],
        'zIndex' => (int) $row['z_index'],
        'videoUrl' => $row['video_url'] ?: $row['youtube_url'],
        'contentType' => $row['content_type'] ?: 'card',
        'attraction' => $row['attraction_id'] ? [
            'id' => (int) $row['attraction_id'],
            'name' => $row['attraction_name'],
            'destination' => $row['destination_name'],
            'shortDescription' => $row['short_description'],
            'image' => media_url($row['main_image']),
            'youtubeUrl' => $row['youtube_url'],
            'websiteUrl' => $row['website_url'],
            'mapUrl' => maps_url($row),
            'openingHours' => $row['opening_hours'],
            'entryInformation' => $row['entry_information'],
        ] : null,
    ];
}

function poster_hotspots(int $posterId): array
{
    $hotspots = db()->prepare(
        "SELECT h.id, h.name, h.x, h.y, h.width, h.height, h.z_index, h.video_url, h.content_type,
                a.id AS attraction_id, a.name AS attraction_name, a.short_description, a.full_description,
                a.main_image, a.youtube_url, a.website_url, a.google_maps_url, a.latitude, a.longitude,
                a.opening_hours, a.entry_information, d.name AS destination_name
         FROM ar_hotspots h
         LEFT JOIN attractions a ON a.id = h.attraction_id AND a.status = 'active'
         LEFT JOIN destinations d ON d.id = a.destination_id
         WHERE h.poster_id = ?
         ORDER BY h.z_index, h.id"
    );
    $hotspots->execute([$posterId]);
    return array_map('hotspot_payload', $hotspots->fetchAll());
}

function ar_photo_set(): array
{
    $ids = json_decode(setting('ar_set_ids', '[]'), true);
    $target = setting('ar_set_target');
    if ($target === '') {
        foreach (['assets/ar-targets/penang-set.mind', 'assets/ar-targets/penang-set-20260922025010.mind'] as $candidate) {
            $full = APP_ROOT . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $candidate);
            if (is_file($full)) {
                $target = $candidate;
                break;
            }
        }
    }
    if (!is_array($ids) || !$ids) {
        $rows = db()->query(
            "SELECT id FROM ar_posters
             WHERE status='active'
               AND poster_name IN ('Penang Hill Photo','Kek Lok Si Photo','George Town Street Art Photo')
             ORDER BY id"
        )->fetchAll();
        $ids = array_map(static fn(array $row): int => (int) $row['id'], $rows);
    }
    if (!$ids) {
        return ['ids' => [], 'posters' => [], 'target' => $target];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM ar_posters WHERE id IN ($placeholders) AND status='active'");
    $stmt->execute(array_map('intval', $ids));
    $byId = [];
    foreach ($stmt as $row) {
        $byId[(int) $row['id']] = $row;
    }
    $posters = [];
    foreach ($ids as $id) {
        if (isset($byId[(int) $id])) {
            $posters[] = $byId[(int) $id];
        }
    }
    return [
        'ids' => array_map('intval', $ids),
        'posters' => $posters,
        'target' => $target,
    ];
}

function current_origin(): string
{
    $scheme = is_https() ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return $scheme . '://' . $host;
}

function is_loopback_host(): bool
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $hostname = explode(':', $host)[0];
    return in_array($hostname, ['localhost', '127.0.0.1', '::1'], true);
}

function detect_lan_ip(): ?string
{
    static $resolved = false;
    static $ip = null;
    if ($resolved) {
        return $ip;
    }
    $resolved = true;

    if (function_exists('socket_create')) {
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock) {
            if (@socket_connect($sock, '8.8.8.8', 53)) {
                $addr = '';
                @socket_getsockname($sock, $addr);
                if (is_string($addr) && $addr !== '' && !str_starts_with($addr, '127.') && !str_starts_with($addr, '169.254.')) {
                    $ip = $addr;
                    socket_close($sock);
                    return $ip;
                }
            }
            socket_close($sock);
        }
    }

    if (function_exists('shell_exec')) {
        $out = (string) @shell_exec('ipconfig');
        if (preg_match_all('/IPv4 Address[.\s]*:\s*([0-9.]+)/i', $out, $matches)) {
            foreach ($matches[1] as $candidate) {
                if (!str_starts_with($candidate, '127.') && !str_starts_with($candidate, '169.254.')) {
                    $ip = $candidate;
                    return $ip;
                }
            }
        }
    }

    return null;
}

function phone_origin(): string
{
    if (is_loopback_host()) {
        $lan = detect_lan_ip();
        if ($lan) {
            $port = (string) ($_SERVER['SERVER_PORT'] ?? '');
            $scheme = is_https() ? 'https' : 'http';
            if ($port !== '' && $port !== '80' && $port !== '443') {
                return $scheme . '://' . $lan . ':' . $port;
            }
            return $scheme . '://' . $lan;
        }
    }
    return current_origin();
}

function phone_url(string $path = ''): string
{
    return rtrim(phone_origin(), '/') . url($path);
}

function qr_image_url(string $data, int $size = 280): string
{
    $size = max(120, min(600, $size));
    return url('api/qr.php') . '?size=' . $size . '&data=' . rawurlencode($data);
}

function qr_markup(string $data, string $alt, int $size = 220): string
{
    $size = max(90, min(600, $size));
    return '<div class="qr-target" data-qr="' . e($data) . '" data-size="' . $size . '" role="img" aria-label="' . e($alt) . '"></div>'
        . '<noscript><img class="qr-image" src="' . e(qr_image_url($data, $size)) . '" alt="' . e($alt) . '" width="' . $size . '" height="' . $size . '"></noscript>';
}

function url(string $path = ''): string
{
    $base = BASE_PATH === '' ? '' : BASE_PATH;
    return $base . '/' . ltrim($path, '/');
}

function absolute_url(string $path = ''): string
{
    return rtrim(current_origin(), '/') . url($path);
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/')) . '?v=' . ASSET_VERSION;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): void
{
    $provided = $token;
    if ($provided === null || $provided === '') {
        $provided = (string) ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    }
    if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $provided)) {
        if (str_contains((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/api/')) {
            json_response(['ok' => false, 'error' => 'Invalid security token'], 403);
        }
        http_response_code(403);
        exit('Invalid security token. Please refresh the page and try again.');
    }
}

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            foreach ($rows as $row) {
                $cache[$row['setting_key']] = (string) $row['setting_value'];
            }
        } catch (Throwable) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

function app_title(): string
{
    return setting('site_name', APP_NAME);
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'item-' . bin2hex(random_bytes(3));
}

function unique_slug(string $table, string $slug, ?int $ignoreId = null): string
{
    $base = slugify($slug);
    $candidate = $base;
    $i = 2;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = ?";
        $params = [$candidate];
        if ($ignoreId) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $candidate;
        }
        $candidate = $base . '-' . $i;
        $i++;
    }
}

function youtube_id(?string $url): ?string
{
    if (!$url) {
        return null;
    }
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{11})~', $url, $match)) {
        return $match[1];
    }
    return null;
}

function youtube_embed_url(?string $url): ?string
{
    $id = youtube_id($url);
    return $id ? 'https://www.youtube.com/embed/' . $id : null;
}

function maps_url(array $item): ?string
{
    if (!empty($item['google_maps_url'])) {
        return (string) $item['google_maps_url'];
    }
    if ($item['latitude'] !== null && $item['latitude'] !== '' && $item['longitude'] !== null && $item['longitude'] !== '') {
        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($item['latitude'] . ',' . $item['longitude']);
    }
    return null;
}

function media_url(?string $path): string
{
    if (!$path) {
        return asset('images/placeholder.svg');
    }
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    return url(ltrim($path, '/'));
}

function paginate(int $total, int $page, int $perPage = PER_PAGE): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    return [
        'page' => $page,
        'pages' => $pages,
        'offset' => ($page - 1) * $perPage,
        'per_page' => $perPage,
        'total' => $total,
    ];
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function log_activity(string $action, string $description, ?int $userId = null): void
{
    $stmt = db()->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $userId ?? ($_SESSION['admin_id'] ?? null),
        $action,
        $description,
        client_ip(),
    ]);
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function active_nav(string $file): string
{
    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    return $script === $file ? 'active' : '';
}

function int_param(string $key, int $default = 0): int
{
    return (int) ($_GET[$key] ?? $_POST[$key] ?? $default);
}

function str_param(string $key, string $default = ''): string
{
    return trim((string) ($_GET[$key] ?? $_POST[$key] ?? $default));
}

function status_badge(string $status): string
{
    $class = $status === 'active' || $status === 'READY' ? 'badge-ok' : 'badge-warn';
    return '<span class="badge-status ' . $class . '">' . e($status) . '</span>';
}

function safe_redirect_path(string $path, string $fallback): string
{
    if ($path === '' || str_contains($path, '://') || str_starts_with($path, '//')) {
        return $fallback;
    }
    return $path;
}
