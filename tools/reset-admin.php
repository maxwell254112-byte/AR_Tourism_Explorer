<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$pdo = db();
$pdo->exec('DELETE FROM login_attempts');

$stmt = $pdo->query("SELECT id, username, email, role, status, password_hash FROM users WHERE username = 'admin' LIMIT 1");
$user = $stmt->fetch();

if ($user) {
    $pdo->prepare('UPDATE users SET password_hash = ?, email = ?, role = ?, status = ? WHERE id = ?')
        ->execute([$hash, 'admin@artourism.local', 'admin', 'active', (int) $user['id']]);
    echo "UPDATED id={$user['id']}\n";
} else {
    $pdo->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES ('admin', 'admin@artourism.local', ?, 'admin', 'active')")
        ->execute([$hash]);
    echo "CREATED\n";
}

$row = $pdo->query("SELECT id, username, email, role, status, password_hash FROM users WHERE username='admin'")->fetch();
echo 'verify admin123: ' . (password_verify('admin123', $row['password_hash']) ? 'YES' : 'NO') . "\n";
echo 'HASH=' . $row['password_hash'] . "\n";
file_put_contents(__DIR__ . '/admin-hash.txt', $row['password_hash']);
