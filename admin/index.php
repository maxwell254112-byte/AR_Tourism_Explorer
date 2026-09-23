<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Dashboard';
$adminSection = 'dashboard';
$counts = [];
foreach (['destinations', 'attractions', 'ar_posters', 'ar_hotspots', 'categories'] as $table) {
    $counts[$table] = (int) db()->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}
$recentAttractions = db()->query("SELECT id, name, updated_at FROM attractions ORDER BY updated_at DESC LIMIT 5")->fetchAll();
$recentPosters = db()->query("SELECT id, poster_name, target_status, updated_at FROM ar_posters ORDER BY updated_at DESC LIMIT 5")->fetchAll();
$logs = db()->query("SELECT action, description, created_at FROM activity_logs ORDER BY id DESC LIMIT 8")->fetchAll();
require __DIR__ . '/../includes/admin-header.php';
?>
<div class="row g-3 mb-4">
    <?php foreach ($counts as $label => $value): ?>
        <div class="col-6 col-xl">
            <div class="stat-card">
                <span><?= e(ucwords(str_replace('_', ' ', $label))) ?></span>
                <strong><?= $value ?></strong>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="admin-card">
            <h2 class="h5">Quick actions</h2>
            <div class="d-grid gap-2">
                <a class="btn btn-success" href="<?= e(admin_url('destinations/form.php')) ?>">Add destination</a>
                <a class="btn btn-outline-success" href="<?= e(admin_url('attractions/form.php')) ?>">Add attraction</a>
                <a class="btn btn-outline-success" href="<?= e(admin_url('ar-posters/form.php')) ?>">Add AR poster</a>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card">
            <h2 class="h5">Recent attractions</h2>
            <?php foreach ($recentAttractions as $item): ?>
                <p class="mb-2"><a href="<?= e(admin_url('attractions/form.php?id=' . (int) $item['id'])) ?>"><?= e($item['name']) ?></a><br><small><?= e($item['updated_at']) ?></small></p>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card">
            <h2 class="h5">Recent AR posters</h2>
            <?php foreach ($recentPosters as $item): ?>
                <p class="mb-2"><?= e($item['poster_name']) ?> <?= status_badge($item['target_status']) ?><br><small><?= e($item['updated_at']) ?></small></p>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-12">
        <div class="admin-card">
            <h2 class="h5">System activity</h2>
            <?php foreach ($logs as $log): ?>
                <p class="mb-2"><strong><?= e($log['action']) ?></strong> — <?= e($log['description']) ?><br><small><?= e($log['created_at']) ?></small></p>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
