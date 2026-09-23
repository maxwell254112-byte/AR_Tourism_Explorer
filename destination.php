<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$id = int_param('id');
$stmt = db()->prepare(
    "SELECT d.*, c.name AS category_name
     FROM destinations d
     LEFT JOIN categories c ON c.id = d.category_id
     WHERE d.id = ? AND d.status = 'active'"
);
$stmt->execute([$id]);
$destination = $stmt->fetch();
if (!$destination) {
    http_response_code(404);
    $pageTitle = 'Destination not found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><h1>Destination not found</h1><p>The destination is unavailable or unpublished.</p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$attractions = db()->prepare(
    "SELECT * FROM attractions WHERE destination_id = ? AND status = 'active' ORDER BY display_order, name"
);
$attractions->execute([$id]);
$items = $attractions->fetchAll();
$map = maps_url($destination);
$pageTitle = $destination['name'] . ' · ' . app_title();
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="detail-hero mb-4">
            <img src="<?= e(media_url($destination['cover_image'])) ?>" alt="<?= e($destination['name']) ?>">
        </div>
        <?php if ($destination['category_name']): ?><span class="place-tag"><?= e($destination['category_name']) ?></span><?php endif; ?>
        <h1 class="mt-3"><?= e($destination['name']) ?></h1>
        <p class="lead"><?= e($destination['short_description']) ?></p>
        <p><?= nl2br(e((string) $destination['full_description'])) ?></p>
        <div class="action-row my-4">
            <a class="btn btn-ar" href="<?= e(url('ar.php')) ?>"><i class="fa-solid fa-vr-cardboard me-2"></i>Start AR Experience</a>
            <?php if ($map): ?><a class="btn btn-gold" href="<?= e($map) ?>" target="_blank" rel="noopener">View on Google Maps</a><?php endif; ?>
        </div>
        <h2>Attractions</h2>
        <div class="row g-4 mt-1">
            <?php foreach ($items as $item): ?>
                <div class="col-md-6 col-xl-4">
                    <article class="tour-card">
                        <img src="<?= e(media_url($item['main_image'])) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                        <div class="card-body">
                            <h3 class="h5"><?= e($item['name']) ?></h3>
                            <p><?= e($item['short_description']) ?></p>
                            <a href="<?= e(url('attraction.php?id=' . (int) $item['id'])) ?>">Details</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
