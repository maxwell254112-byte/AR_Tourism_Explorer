<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
$pageTitle = 'Activity Logs';
$adminSection = 'logs';
$q = str_param('q');
$page = max(1, int_param('page', 1));
$where = 'WHERE 1=1';
$params = [];
if ($q !== '') {
    $where .= ' AND (l.action LIKE ? OR l.description LIKE ? OR u.username LIKE ?)';
    $params = ['%' . $q . '%', '%' . $q . '%', '%' . $q . '%'];
}
$total = db()->prepare("SELECT COUNT(*) FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id $where");
$total->execute($params);
$pager = paginate((int) $total->fetchColumn(), $page);
$stmt = db()->prepare("SELECT l.*, u.username FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id $where ORDER BY l.id DESC LIMIT {$pager['per_page']} OFFSET {$pager['offset']}");
$stmt->execute($params);
$items = $stmt->fetchAll();
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-8"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search activity"></div>
        <div class="col-md-4"><button class="btn btn-success">Search</button></div>
    </form>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['created_at']) ?></td>
                    <td><?= e($item['username'] ?? 'system') ?></td>
                    <td><?= e($item['action']) ?></td>
                    <td><?= e($item['description']) ?><br><small><?= e($item['ip_address']) ?></small></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pager['pages'] > 1): for ($i = 1; $i <= $pager['pages']; $i++): ?>
        <a class="btn btn-sm <?= $i === $pager['page'] ? 'btn-success' : 'btn-outline-success' ?>" href="?q=<?= urlencode($q) ?>&page=<?= $i ?>"><?= $i ?></a>
    <?php endfor; endif; ?>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
