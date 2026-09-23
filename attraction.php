<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$id = int_param('id');
$stmt = db()->prepare(
    "SELECT a.*, d.name AS destination_name, d.id AS dest_id, c.name AS category_name
     FROM attractions a
     JOIN destinations d ON d.id = a.destination_id
     LEFT JOIN categories c ON c.id = a.category_id
     WHERE a.id = ? AND a.status = 'active' AND d.status = 'active'"
);
$stmt->execute([$id]);
$attraction = $stmt->fetch();
if (!$attraction) {
    http_response_code(404);
    $pageTitle = 'Attraction not found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><h1>Attraction not found</h1></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$gallery = db()->prepare('SELECT * FROM attraction_images WHERE attraction_id = ? ORDER BY display_order, id');
$gallery->execute([$id]);
$images = $gallery->fetchAll();
$map = maps_url($attraction);
$embed = youtube_embed_url($attraction['youtube_url'] ?? null);
$pageTitle = $attraction['name'] . ' · ' . app_title();
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <p><a href="<?= e(url('destination.php?id=' . (int) $attraction['dest_id'])) ?>"><?= e($attraction['destination_name']) ?></a></p>
        <div class="detail-hero mb-4">
            <img src="<?= e(media_url($attraction['main_image'])) ?>" alt="<?= e($attraction['name']) ?>">
        </div>
        <?php if ($attraction['category_name']): ?><span class="place-tag"><i class="fa-solid fa-landmark me-2"></i><?= e($attraction['category_name']) ?></span><?php endif; ?>
        <h1 class="mt-3"><?= e($attraction['name']) ?></h1>
        <p class="lead"><?= e($attraction['short_description']) ?></p>
        <p><?= nl2br(e((string) $attraction['full_description'])) ?></p>
        <p><strong>Opening hours:</strong> <?= e($attraction['opening_hours'] ?: 'Not specified') ?></p>
        <p><strong>Entry information:</strong> <?= e($attraction['entry_information'] ?: 'Not specified') ?></p>
        <div class="action-row my-4">
            <?php if ($attraction['youtube_url']): ?>
                <a class="btn btn-ar" href="<?= e($attraction['youtube_url']) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-youtube me-2"></i>Watch Video</a>
            <?php endif; ?>
            <?php if ($map): ?>
                <a class="btn btn-gold" href="<?= e($map) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-map me-2"></i>View on Google Maps</a>
            <?php endif; ?>
            <?php if ($attraction['website_url']): ?>
                <a class="btn btn-outline-green" href="<?= e($attraction['website_url']) ?>" target="_blank" rel="noopener">Official website</a>
            <?php endif; ?>
            <a class="btn btn-outline-green" href="<?= e(url('ar.php')) ?>">Open AR Experience</a>
        </div>
        <?php if ($embed): ?>
            <div class="mb-4">
                <iframe class="video-frame" src="<?= e($embed) ?>" title="<?= e($attraction['name']) ?> video" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        <?php endif; ?>
        <?php if ($images): ?>
            <h2>Gallery</h2>
            <div class="gallery-grid mt-3">
                <?php foreach ($images as $image): ?>
                    <a href="<?= e(media_url($image['image_path'])) ?>" data-lightbox>
                        <img src="<?= e(media_url($image['image_path'])) ?>" alt="<?= e($image['alt_text'] ?: $attraction['name']) ?>" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
