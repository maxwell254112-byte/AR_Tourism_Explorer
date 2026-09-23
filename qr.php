<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'QR Code · ' . app_title();
$arQrUrl = phone_url('ar.php');
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <p class="hero-kicker">SYSTEM QR</p>
        <h1>扫码进入本系统</h1>
        <p class="no-print">用手机扫这一个二维码，打开 AR 页面。然后对准景点<strong>照片</strong>扫描，识别后播放视频。景点卡片上没有二维码。</p>
        <div class="action-row no-print mb-4">
            <button class="btn btn-gold" type="button" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print QR</button>
            <a class="btn btn-ar" href="<?= e(url('ar.php')) ?>"><i class="fa-solid fa-camera me-2"></i>Start AR Experience</a>
            <a class="btn btn-outline-green" href="<?= e(url('photos.php')) ?>">Print photos</a>
        </div>

        <div class="qr-band">
            <div class="qr-frame">
                <?= qr_markup($arQrUrl, 'Open AR Tourism Explorer', 260) ?>
            </div>
            <div class="qr-meta">
                <p class="hero-kicker mb-1">SCAN TO ENTER</p>
                <h2 class="h3">1. 扫码进入系统</h2>
                <p>2. 允许使用摄像头<br>3. 对准槟城景点照片（不是二维码）看视频</p>
                <p><a href="<?= e($arQrUrl) ?>"><?= e($arQrUrl) ?></a></p>
                <?php if (is_loopback_host()): ?>
                    <p class="qr-note mb-0">手机要和电脑同一 Wi-Fi，并用 <code>php -S 0.0.0.0:8000</code> 启动网站。</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
