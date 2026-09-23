<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
$pageTitle = 'AR Posters';
$adminSection = 'posters';
$q = str_param('q');
$page = max(1, int_param('page', 1));
$where = 'WHERE 1=1';
$params = [];
if ($q !== '') {
    $where .= ' AND poster_name LIKE ?';
    $params[] = '%' . $q . '%';
}
$total = db()->prepare("SELECT COUNT(*) FROM ar_posters $where");
$total->execute($params);
$pager = paginate((int) $total->fetchColumn(), $page);
$stmt = db()->prepare("SELECT p.*, (SELECT COUNT(*) FROM ar_hotspots h WHERE h.poster_id = p.id) AS hotspot_count FROM ar_posters p $where ORDER BY p.updated_at DESC LIMIT {$pager['per_page']} OFFSET {$pager['offset']}");
$stmt->execute($params);
$items = $stmt->fetchAll();
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-6"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search posters"></div>
        <div class="col-md-3"><button class="btn btn-success">Search</button></div>
        <div class="col-md-3 text-md-end"><a class="btn btn-outline-success" href="<?= e(admin_url('ar-posters/form.php')) ?>">Add poster</a></div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Poster</th><th>Hotspots</th><th>Target status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['poster_name']) ?></td>
                    <td><?= (int) $item['hotspot_count'] ?></td>
                    <td><?= status_badge($item['target_status']) ?></td>
                    <td class="text-end">
                        <a href="<?= e(admin_url('ar-posters/form.php?id=' . (int) $item['id'])) ?>">Edit</a> ·
                        <a href="<?= e(admin_url('ar-posters/editor.php?id=' . (int) $item['id'])) ?>">Hotspots</a> ·
                        <a href="<?= e(admin_url('ar-posters/compile.php?id=' . (int) $item['id'])) ?>">Compile</a> ·
                        <a href="<?= e(url('ar.php?poster=' . (int) $item['id'])) ?>">Preview</a>
                        <form class="d-inline js-confirm" method="post" action="<?= e(admin_url('ar-posters/delete.php')) ?>">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <button class="btn btn-link text-danger p-0">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
