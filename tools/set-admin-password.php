<?php
declare(strict_types=1);

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash . PHP_EOL;
echo password_verify($password, $hash) ? "OK\n" : "FAIL\n";

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

$pdo = db();
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE username = 'admin' AND role = 'admin' LIMIT 1");
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    $pdo->prepare('UPDATE users SET password_hash = ?, status = ? WHERE id = ?')
        ->execute([$hash, 'active', (int) $user['id']]);
    echo "Updated local admin id={$user['id']}\n";
} else {
    $pdo->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES ('admin', 'admin@artourism.local', ?, 'admin', 'active')")
        ->execute([$hash]);
    echo "Created local admin\n";
}

file_put_contents(dirname(__DIR__) . '/tools/.admin123.hash', $hash);
