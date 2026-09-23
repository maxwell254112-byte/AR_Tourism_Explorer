<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About · ' . app_title();
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container col-lg-8">
        <p class="hero-kicker">ABOUT</p>
        <h1>A web-based AR tourism platform</h1>
        <p class="lead"><?= nl2br(e(setting('about_text'))) ?></p>
        <h2 class="h4 mt-4">How a visit works</h2>
        <ol>
            <li>Scan the system QR code with your phone camera. It opens this website’s AR page.</li>
            <li>Tap <strong>START AR</strong> and allow the camera.</li>
            <li>Point the camera at a Penang attraction <strong>photo</strong>. The photo is not a QR code.</li>
            <li>When the photo is recognized, the YouTube video starts.</li>
        </ol>
        <p>Attraction cards show photos only. The QR code is the door into the system. Camera access generally requires HTTPS. Local development on localhost is allowed.</p>
        <div class="action-row">
            <a class="btn btn-ar" href="<?= e(url('qr.php')) ?>"><i class="fa-solid fa-qrcode me-2"></i>Open system QR</a>
            <a class="btn btn-gold" href="<?= e(url('ar.php')) ?>">Start AR Experience</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
