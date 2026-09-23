<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
$pageTitle = 'Categories';
$adminSection = 'categories';
$q = str_param('q');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = str_param('action');
    $id = int_param('id');
    if ($action === 'save') {
        $name = str_param('name');
        if ($name !== '') {
            $slug = unique_slug('categories', str_param('slug') ?: $name, $id ?: null);
            $status = str_param('status') === 'inactive' ? 'inactive' : 'active';
            if ($id) {
                db()->prepare('UPDATE categories SET name=?, slug=?, status=? WHERE id=?')->execute([$name, $slug, $status, $id]);
                log_activity('category.update', 'Admin updated category ' . $name);
            } else {
                db()->prepare('INSERT INTO categories (name, slug, status) VALUES (?,?,?)')->execute([$name, $slug, $status]);
                log_activity('category.create', 'Admin added category ' . $name);
            }
            flash_set('success', 'Category saved.');
        }
    } elseif ($action === 'delete' && $id) {
        db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
        log_activity('category.delete', 'Admin deleted category #' . $id);
        flash_set('success', 'Category deleted.');
    } elseif ($action === 'toggle' && $id) {
        db()->prepare("UPDATE categories SET status = IF(status='active','inactive','active') WHERE id = ?")->execute([$id]);
        log_activity('category.toggle', 'Admin changed category status #' . $id);
    }
    redirect(admin_url('categories/index.php'));
}

$sql = 'SELECT * FROM categories';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE name LIKE ? OR slug LIKE ?';
    $params = ['%' . $q . '%', '%' . $q . '%'];
}
$sql .= ' ORDER BY name';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
$editId = int_param('edit');
$edit = ['id' => 0, 'name' => '', 'slug' => '', 'status' => 'active'];
foreach ($items as $row) {
    if ((int) $row['id'] === $editId) {
        $edit = $row;
    }
}
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="admin-card">
            <h2 class="h5"><?= $edit['id'] ? 'Edit category' : 'Add category' ?></h2>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
                <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="<?= e($edit['name']) ?>" required></div>
                <div class="mb-3"><label class="form-label">Slug</label><input class="form-control" name="slug" value="<?= e($edit['slug']) ?>"></div>
                <div class="mb-3"><label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="active" <?= $edit['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $edit['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <button class="btn btn-success">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="admin-card">
            <form class="row g-2 mb-3" method="get">
                <div class="col-8"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search categories"></div>
                <div class="col-4"><button class="btn btn-outline-success">Search</button></div>
            </form>
            <table class="table">
                <thead><tr><th>Name</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['name']) ?></td>
                        <td><?= status_badge($item['status']) ?></td>
                        <td class="text-end">
                            <a href="?edit=<?= (int) $item['id'] ?>">Edit</a>
                            <form class="d-inline" method="post">
                                <?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button class="btn btn-link p-0">Toggle</button>
                            </form>
                            <form class="d-inline js-confirm" method="post">
                                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button class="btn btn-link text-danger p-0">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
