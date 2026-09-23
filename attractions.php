<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Attractions · ' . app_title();
$q = str_param('q');
$category = str_param('category');
$sql = "SELECT a.*, d.name AS destination_name, c.name AS category_name, c.slug AS category_slug
        FROM attractions a
        JOIN destinations d ON d.id = a.destination_id
        LEFT JOIN categories c ON c.id = a.category_id
        WHERE a.status = 'active' AND d.status = 'active'";
$params = [];
if ($q !== '') {
    $sql .= " AND (a.name LIKE ? OR a.short_description LIKE ? OR d.name LIKE ? OR c.name LIKE ?)";
    $like = '%' . $q . '%';
    $params = [$like, $like, $like, $like];
}
if ($category !== '') {
    $sql .= " AND c.slug = ?";
    $params[] = $category;
}
$sql .= " ORDER BY a.is_featured DESC, a.display_order, a.name";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <p class="hero-kicker">THINGS TO SEE</p>
        <h1>Attractions</h1>
        <form class="search-panel my-4 d-flex flex-column flex-md-row gap-2" method="get">
            <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search attractions, destinations or categories">
            <?php if ($category !== ''): ?><input type="hidden" name="category" value="<?= e($category) ?>"><?php endif; ?>
            <button class="btn btn-ar" type="submit">Search</button>
        </form>
        <div class="row g-4">
            <?php foreach ($items as $item): ?>
                <div class="col-md-6 col-xl-4">
                    <article class="tour-card">
                        <img src="<?= e(media_url($item['main_image'])) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                        <div class="card-body">
                            <small class="text-muted"><?= e($item['destination_name']) ?><?= $item['category_name'] ? ' · ' . e($item['category_name']) : '' ?></small>
                            <h2 class="h4"><?= e($item['name']) ?></h2>
                            <p><?= e($item['short_description']) ?></p>
                            <a class="btn btn-outline-green" href="<?= e(url('attraction.php?id=' . (int) $item['id'])) ?>">More information</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
            <?php if (!$items): ?><p>No attractions match this search.</p><?php endif; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
