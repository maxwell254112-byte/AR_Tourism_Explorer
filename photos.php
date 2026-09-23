<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Scan these Penang photos · ' . app_title();
$penang = db()->query(
    "SELECT a.*
     FROM attractions a
     JOIN destinations d ON d.id = a.destination_id
     WHERE d.slug = 'penang-malaysia' AND a.status = 'active'
     ORDER BY a.display_order, a.id"
)->fetchAll();
$arQrUrl = phone_url('ar.php');
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <p class="hero-kicker">PRINT OR DISPLAY</p>
        <h1>槟城景点照片 · Scan to watch YouTube</h1>
        <p class="no-print">先扫上面的系统二维码进入 AR，再把手机摄像头对准这些<strong>景点照片</strong>看视频。照片上没有二维码。</p>
        <div class="action-row no-print mb-4">
            <a class="btn btn-ar" href="<?= e(url('ar.php')) ?>"><i class="fa-solid fa-camera me-2"></i>Start AR Experience</a>
            <a class="btn btn-outline-green" href="<?= e(url('qr.php')) ?>"><i class="fa-solid fa-qrcode me-2"></i>QR codes</a>
            <button class="btn btn-gold" type="button" onclick="window.print()">Print photos</button>
        </div>
        <div class="qr-band mb-4">
            <div class="qr-frame">
                <?= qr_markup($arQrUrl, 'Start AR QR', 200) ?>
            </div>
            <div>
                <p class="hero-kicker mb-1">SCAN TO START AR</p>
                <h2 class="h4">扫这个码打开 AR 页面</h2>
                <p class="mb-0"><a href="<?= e($arQrUrl) ?>"><?= e($arQrUrl) ?></a></p>
            </div>
        </div>
        <div class="print-sheet">
            <?php foreach ($penang as $place): ?>
                <article class="print-card">
                    <img src="<?= e(media_url($place['main_image'])) ?>" alt="<?= e($place['name']) ?>">
                    <div class="place-body">
                        <span class="place-tag">SCAN THIS PHOTO</span>
                        <h2 class="h3 mt-2"><?= e($place['name']) ?></h2>
                        <p><?= e($place['short_description']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
