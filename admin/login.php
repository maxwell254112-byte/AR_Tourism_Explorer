<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if (current_admin()) {
    redirect(admin_url('index.php'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!login_allowed()) {
        $error = 'Too many login attempts. Please wait 10 minutes and try again.';
    } else {
        $identity = str_param('username');
        $password = (string) ($_POST['password'] ?? '');
        $remember = isset($_POST['remember']);
        if (attempt_admin_login($identity, $password, $remember)) {
            log_activity('admin.login', 'Admin logged in');
            $next = safe_redirect_path((string) ($_GET['next'] ?? $_POST['next'] ?? ''), admin_url('index.php'));
            redirect($next);
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <style>body{min-height:100vh;display:grid;place-items:center;background:#eef3f0;font-family:Outfit,sans-serif}.login-card{width:min(420px,92vw)}</style>
</head>
<body>
    <form class="admin-card login-card" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e((string) ($_GET['next'] ?? '')) ?>">
        <h1 class="h3 mb-3">AR Tourism Admin</h1>
        <div class="alert alert-info py-2">
            Username: <strong>admin</strong><br>
            Password: <strong>admin123</strong>
        </div>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <div class="mb-3">
            <label class="form-label" for="username">Username or email</label>
            <input class="form-control" id="username" name="username" value="admin" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" type="password" name="password" value="admin123" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <button class="btn btn-success w-100" type="submit">Login</button>
        <p class="mt-3 mb-0"><a href="<?= e(url('index.php')) ?>">Back to website</a></p>
    </form>
</body>
</html>
