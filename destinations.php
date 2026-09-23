<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Destinations · ' . app_title();
$q = str_param('q');
$category = str_param('category');
$sql = "SELECT d.*, c.name AS category_name, c.slug AS category_slug
        FROM destinations d
        LEFT JOIN categories c ON c.id = d.category_id
        WHERE d.status = 'active'";
$params = [];
if ($q !== '') {
    $sql .= " AND (d.name LIKE ? OR d.short_description LIKE ? OR d.location LIKE ?)";
    $like = '%' . $q . '%';
    $params = [$like, $like, $like];
}
if ($category !== '') {
    $sql .= " AND c.slug = ?";
    $params[] = $category;
}
$sql .= " ORDER BY d.is_featured DESC, d.name";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <p class="hero-kicker">TOURISM</p>
        <h1>Destinations</h1>
        <form class="search-panel my-4 d-flex flex-column flex-md-row gap-2" method="get">
            <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search destinations">
            <button class="btn btn-ar" type="submit">Search</button>
        </form>
        <div class="row g-4">
            <?php foreach ($items as $item): ?>
                <div class="col-md-6 col-xl-4">
                    <article class="tour-card">
                        <img src="<?= e(media_url($item['cover_image'])) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                        <div class="card-body">
                            <?php if ($item['category_name']): ?><span class="place-tag"><?= e($item['category_name']) ?></span><?php endif; ?>
                            <h2 class="h4 mt-3"><?= e($item['name']) ?></h2>
                            <p><?= e($item['short_description']) ?></p>
                            <a class="btn btn-outline-green" href="<?= e(url('destination.php?id=' . (int) $item['id'])) ?>">View destination</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
            <?php if (!$items): ?><p>No destinations match this search.</p><?php endif; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
