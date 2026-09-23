<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
$pageTitle = 'Destinations';
$adminSection = 'destinations';
$q = str_param('q');
$page = max(1, int_param('page', 1));
$where = 'WHERE 1=1';
$params = [];
if ($q !== '') {
    $where .= ' AND (d.name LIKE ? OR d.location LIKE ?)';
    $params = ['%' . $q . '%', '%' . $q . '%'];
}
$total = db()->prepare("SELECT COUNT(*) FROM destinations d $where");
$total->execute($params);
$pager = paginate((int) $total->fetchColumn(), $page);
$sql = "SELECT d.*, c.name AS category_name FROM destinations d LEFT JOIN categories c ON c.id = d.category_id $where ORDER BY d.updated_at DESC LIMIT {$pager['per_page']} OFFSET {$pager['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-6"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search destinations"></div>
        <div class="col-md-3"><button class="btn btn-success">Search</button></div>
        <div class="col-md-3 text-md-end"><a class="btn btn-outline-success" href="<?= e(admin_url('destinations/form.php')) ?>">Add destination</a></div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Name</th><th>Category</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['name']) ?></td>
                    <td><?= e($item['category_name'] ?? '—') ?></td>
                    <td><?= status_badge($item['status']) ?></td>
                    <td class="text-end">
                        <a href="<?= e(admin_url('destinations/form.php?id=' . (int) $item['id'])) ?>">Edit</a>
                        ·
                        <form class="d-inline js-confirm" method="post" action="<?= e(admin_url('destinations/delete.php')) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <button class="btn btn-link text-danger p-0" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pager['pages'] > 1): ?>
        <nav><?php for ($i = 1; $i <= $pager['pages']; $i++): ?>
            <a class="btn btn-sm <?= $i === $pager['page'] ? 'btn-success' : 'btn-outline-success' ?>" href="?q=<?= urlencode($q) ?>&page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?></nav>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
