<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
$pageTitle = 'Attractions';
$adminSection = 'attractions';
$q = str_param('q');
$destinationId = int_param('destination_id');
$categoryId = int_param('category_id');
$page = max(1, int_param('page', 1));
$where = 'WHERE 1=1';
$params = [];
if ($q !== '') {
    $where .= ' AND (a.name LIKE ? OR a.short_description LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($destinationId) {
    $where .= ' AND a.destination_id = ?';
    $params[] = $destinationId;
}
if ($categoryId) {
    $where .= ' AND a.category_id = ?';
    $params[] = $categoryId;
}
$total = db()->prepare("SELECT COUNT(*) FROM attractions a $where");
$total->execute($params);
$pager = paginate((int) $total->fetchColumn(), $page);
$stmt = db()->prepare("SELECT a.*, d.name AS destination_name, c.name AS category_name FROM attractions a JOIN destinations d ON d.id=a.destination_id LEFT JOIN categories c ON c.id=a.category_id $where ORDER BY a.updated_at DESC LIMIT {$pager['per_page']} OFFSET {$pager['offset']}");
$stmt->execute($params);
$items = $stmt->fetchAll();
$destinations = db()->query('SELECT id, name FROM destinations ORDER BY name')->fetchAll();
$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-3"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search attractions"></div>
        <div class="col-md-3">
            <select class="form-select" name="destination_id">
                <option value="">All destinations</option>
                <?php foreach ($destinations as $destination): ?>
                    <option value="<?= (int) $destination['id'] ?>" <?= $destinationId === (int) $destination['id'] ? 'selected' : '' ?>><?= e($destination['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="category_id">
                <option value="">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-success">Filter</button>
            <a class="btn btn-outline-success" href="<?= e(admin_url('attractions/form.php')) ?>">Add</a>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Attraction</th><th>Destination</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['name']) ?></td>
                    <td><?= e($item['destination_name']) ?></td>
                    <td><?= status_badge($item['status']) ?></td>
                    <td class="text-end">
                        <a href="<?= e(admin_url('attractions/form.php?id=' . (int) $item['id'])) ?>">Edit</a>
                        <form class="d-inline js-confirm" method="post" action="<?= e(admin_url('attractions/delete.php')) ?>">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <button class="btn btn-link text-danger p-0">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pager['pages'] > 1): for ($i = 1; $i <= $pager['pages']; $i++): ?>
        <a class="btn btn-sm <?= $i === $pager['page'] ? 'btn-success' : 'btn-outline-success' ?>" href="?q=<?= urlencode($q) ?>&destination_id=<?= $destinationId ?>&category_id=<?= $categoryId ?>&page=<?= $i ?>"><?= $i ?></a>
    <?php endfor; endif; ?>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
