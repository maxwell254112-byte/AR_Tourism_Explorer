<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$pageTitle = app_title() . ' · Explore with AR';
$destinations = db()->query(
    "SELECT d.*, c.name AS category_name
     FROM destinations d
     LEFT JOIN categories c ON c.id = d.category_id
     WHERE d.status = 'active'
     ORDER BY (d.slug = 'penang-malaysia') DESC, d.is_featured DESC, d.updated_at DESC
     LIMIT 6"
)->fetchAll();
$attractions = db()->query(
    "SELECT a.*, d.name AS destination_name
     FROM attractions a
     JOIN destinations d ON d.id = a.destination_id
     WHERE a.status = 'active' AND d.status = 'active'
     ORDER BY (d.slug = 'penang-malaysia') DESC, a.is_featured DESC, a.display_order, a.name
     LIMIT 6"
)->fetchAll();
$categories = db()->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
$stats = [
    'destinations' => (int) db()->query("SELECT COUNT(*) FROM destinations WHERE status='active'")->fetchColumn(),
    'attractions' => (int) db()->query("SELECT COUNT(*) FROM attractions WHERE status='active'")->fetchColumn(),
    'posters' => (int) db()->query("SELECT COUNT(*) FROM ar_posters WHERE status='active'")->fetchColumn(),
    'categories' => (int) db()->query("SELECT COUNT(*) FROM categories WHERE status='active'")->fetchColumn(),
];
$q = str_param('q');
$penang = db()->prepare(
    "SELECT a.*, d.name AS destination_name
     FROM attractions a
     JOIN destinations d ON d.id = a.destination_id
     WHERE d.slug = 'penang-malaysia' AND a.status = 'active'
     ORDER BY a.display_order, a.id"
);
$penang->execute();
$penangAttractions = $penang->fetchAll();
$arQrUrl = phone_url('ar.php');
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container">
        <p class="hero-kicker">WEB-BASED AR TOURISM</p>
        <h1>Scan one QR, then scan a photo to watch the video.</h1>
        <p><?= e(setting('site_tagline', 'Scan the system QR to open AR Tourism Explorer, then point your phone camera at a Penang photo to watch YouTube.')) ?></p>
        <div class="d-flex flex-wrap gap-3 mt-4">
            <a class="btn btn-ar" href="<?= e(url('ar.php')) ?>"><i class="fa-solid fa-camera me-2"></i>Start AR Experience</a>
            <a class="btn btn-gold" href="<?= e(url('qr.php')) ?>"><i class="fa-solid fa-qrcode me-2"></i>Scan QR Codes</a>
            <a class="btn btn-gold" href="<?= e(url('destinations.php')) ?>"><i class="fa-solid fa-mountain-sun me-2"></i>Browse Destinations</a>
        </div>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <div class="qr-band">
            <div class="qr-frame">
                <?= qr_markup($arQrUrl, 'Scan to start AR Experience', 220) ?>
            </div>
            <div class="qr-meta">
                <p class="hero-kicker mb-1">SCAN TO START AR EXPERIENCE</p>
                <h2 class="h3">用手机扫这个二维码，打开 AR 页面</h2>
                <p>用手机扫这一个二维码，进入本系统的 AR 页面。然后把摄像头对准下面的<strong>景点照片</strong>（不是二维码），识别后就会播放视频。</p>
                <ol class="mb-3 ps-3">
                    <li>Scan this QR to open the AR page</li>
                    <li>Allow the camera</li>
                    <li>Point at a Penang photo to watch the video</li>
                </ol>
                <p><a href="<?= e($arQrUrl) ?>"><?= e($arQrUrl) ?></a></p>
                <div class="action-row">
                    <a class="btn btn-ar" href="<?= e(url('qr.php')) ?>"><i class="fa-solid fa-print me-2"></i>Print this QR</a>
                    <a class="btn btn-outline-green" href="<?= e(url('photos.php')) ?>">Print photos</a>
                </div>
                <?php if (is_loopback_host()): ?>
                    <p class="qr-note mt-3 mb-0">手机要和电脑同一 Wi-Fi，并用 <code>php -S 0.0.0.0:8000</code> 启动网站，扫码才能打开本系统。</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if ($penangAttractions): ?>
<section class="section pt-0">
    <div class="container">
        <div class="section-title d-flex justify-content-between align-items-end">
            <div>
                <p class="hero-kicker mb-1">PENANG · 槟城景点</p>
                <h2>Scan a photo to watch the video</h2>
                <p class="mb-0">这些是给 AR 扫描的照片，卡片上没有二维码。先扫上面的系统二维码，再对准这些照片看视频。</p>
            </div>
            <a href="<?= e(url('photos.php')) ?>">Print photos</a>
        </div>
        <div class="penang-grid">
            <?php foreach ($penangAttractions as $place): ?>
                <article class="tour-card penang-card">
                    <a href="<?= e($place['youtube_url']) ?>" target="_blank" rel="noopener">
                        <img class="tour-photo" src="<?= e(media_url($place['main_image'])) ?>" alt="<?= e($place['name']) ?>">
                    </a>
                    <div class="place-body">
                        <span class="place-tag">PENANG · 槟城</span>
                        <h3 class="h4 mt-2"><?= e($place['name']) ?></h3>
                        <h4><?= e($place['short_description']) ?></h4>
                        <div class="action-row">
                            <a class="btn btn-ar" href="<?= e($place['youtube_url']) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-youtube me-2"></i>Watch Video</a>
                            <a class="btn btn-gold" href="<?= e(url('ar.php')) ?>"><i class="fa-solid fa-camera me-2"></i>Scan AR</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section pt-0">
    <div class="container">
        <form class="search-panel d-flex flex-column flex-md-row gap-2" action="<?= e(url('attractions.php')) ?>" method="get" role="search">
            <label class="visually-hidden" for="homeSearch">Search destinations or attractions</label>
            <input class="form-control form-control-lg" id="homeSearch" name="q" value="<?= e($q) ?>" placeholder="Search destinations, attractions or categories">
            <button class="btn btn-ar" type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
        </form>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <div class="section-title d-flex justify-content-between align-items-end">
            <div>
                <p class="hero-kicker mb-1">FEATURED DESTINATIONS</p>
                <h2>Places ready for a tourism campaign</h2>
            </div>
            <a href="<?= e(url('destinations.php')) ?>">View all</a>
        </div>
        <div class="row g-4">
            <?php foreach ($destinations as $destination): ?>
                <div class="col-md-6 col-xl-4">
                    <article class="tour-card">
                        <img src="<?= e(media_url($destination['cover_image'])) ?>" alt="<?= e($destination['name']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= e(asset('images/placeholder.svg')) ?>'">
                        <div class="card-body">
                            <?php if ($destination['category_name']): ?><span class="place-tag"><?= e($destination['category_name']) ?></span><?php endif; ?>
                            <h3 class="h4 mt-3"><?= e($destination['name']) ?></h3>
                            <p><?= e($destination['short_description']) ?></p>
                            <a class="btn btn-outline-green" href="<?= e(url('destination.php?id=' . (int) $destination['id'])) ?>">Explore</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <div class="section-title">
            <p class="hero-kicker mb-1">CATEGORIES</p>
            <h2>Travel by interest</h2>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($categories as $category): ?>
                <a class="category-chip" href="<?= e(url('attractions.php?category=' . urlencode($category['slug']))) ?>">
                    <i class="fa-solid fa-tag"></i><?= e($category['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section pt-0" id="stories">
    <div class="container">
        <div class="section-title d-flex justify-content-between align-items-end">
            <div>
                <p class="hero-kicker mb-1">POPULAR ATTRACTIONS</p>
                <h2>Stories, maps and videos</h2>
            </div>
            <a href="<?= e(url('attractions.php')) ?>">View all</a>
        </div>
        <div class="row g-4">
            <?php foreach ($attractions as $attraction): ?>
                <div class="col-md-6 col-xl-4">
                    <article class="tour-card">
                        <img src="<?= e(media_url($attraction['main_image'])) ?>" alt="<?= e($attraction['name']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= e(asset('images/placeholder.svg')) ?>'">
                        <div class="card-body">
                            <small class="text-muted"><?= e($attraction['destination_name']) ?></small>
                            <h3 class="h4"><?= e($attraction['name']) ?></h3>
                            <p><?= e($attraction['short_description']) ?></p>
                            <a class="btn btn-outline-green" href="<?= e(url('attraction.php?id=' . (int) $attraction['id'])) ?>">More information</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <div class="stats-band">
            <div class="row text-center g-4">
                <div class="col-6 col-md-3"><div class="stat-number"><?= $stats['destinations'] ?></div><div>Destinations</div></div>
                <div class="col-6 col-md-3"><div class="stat-number"><?= $stats['attractions'] ?></div><div>Attractions</div></div>
                <div class="col-6 col-md-3"><div class="stat-number"><?= $stats['posters'] ?></div><div>AR Posters</div></div>
                <div class="col-6 col-md-3"><div class="stat-number"><?= $stats['categories'] ?></div><div>Categories</div></div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
