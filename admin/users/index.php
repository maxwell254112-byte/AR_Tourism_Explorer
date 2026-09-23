<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
$pageTitle = 'Users';
$adminSection = 'users';
$q = str_param('q');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = str_param('action');
    $id = int_param('id');
    if ($action === 'save') {
        $username = str_param('username');
        $email = str_param('email') ?: null;
        $status = str_param('status') === 'inactive' ? 'inactive' : 'active';
        $password = (string) ($_POST['password'] ?? '');
        if ($username === '') {
            flash_set('error', 'Username is required.');
        } elseif ($id) {
            db()->prepare('UPDATE users SET username=?, email=?, status=? WHERE id=?')->execute([$username, $email, $status, $id]);
            if ($password !== '') {
                db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
            }
            log_activity('user.update', 'Admin updated user ' . $username);
            flash_set('success', 'User updated.');
        } else {
            if (strlen($password) < 8) {
                flash_set('error', 'New users need a password of at least 8 characters.');
            } else {
                db()->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES (?,?,?,'admin',?)")
                    ->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $status]);
                log_activity('user.create', 'Admin created user ' . $username);
                flash_set('success', 'User created.');
            }
        }
    } elseif ($action === 'delete' && $id && $id !== (int) ($_SESSION['admin_id'] ?? 0)) {
        db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        log_activity('user.delete', 'Admin deleted user #' . $id);
        flash_set('success', 'User deleted.');
    }
    redirect(admin_url('users/index.php'));
}

$sql = 'SELECT id, username, email, role, status, last_login, created_at FROM users';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE username LIKE ? OR email LIKE ?';
    $params = ['%' . $q . '%', '%' . $q . '%'];
}
$sql .= ' ORDER BY username';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="admin-card">
            <h2 class="h5">Add administrator</h2>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <div class="mb-2"><label class="form-label">Username</label><input class="form-control" name="username" required></div>
                <div class="mb-2"><label class="form-label">Email</label><input class="form-control" type="email" name="email"></div>
                <div class="mb-2"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>
                <div class="mb-2"><label class="form-label">Status</label>
                    <select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                </div>
                <button class="btn btn-success">Create user</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="admin-card">
            <form class="row g-2 mb-3" method="get">
                <div class="col-8"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search users"></div>
                <div class="col-4"><button class="btn btn-outline-success">Search</button></div>
            </form>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>User</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= e($user['username']) ?><br><small><?= e($user['email'] ?? '') ?></small></td>
                            <td><?= status_badge($user['status']) ?></td>
                            <td>
                                <form class="row g-1" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="save">
                                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                    <div class="col-6"><input class="form-control form-control-sm" name="username" value="<?= e($user['username']) ?>"></div>
                                    <div class="col-6"><input class="form-control form-control-sm" name="email" value="<?= e($user['email'] ?? '') ?>"></div>
                                    <div class="col-6"><input class="form-control form-control-sm" type="password" name="password" placeholder="New password"></div>
                                    <div class="col-4">
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-2"><button class="btn btn-sm btn-success">Save</button></div>
                                </form>
                                <?php if ((int) $user['id'] !== (int) ($_SESSION['admin_id'] ?? 0)): ?>
                                    <form class="js-confirm" method="post">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                        <button class="btn btn-link text-danger p-0">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
