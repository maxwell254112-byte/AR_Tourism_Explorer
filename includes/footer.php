</main>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h2 class="h5"><?= e(app_title()) ?></h2>
                <p><?= e(setting('site_tagline', 'A web-based AR tourism and cultural heritage information system.')) ?></p>
            </div>
            <div class="col-lg-4">
                <h2 class="h5">Explore</h2>
                <ul class="footer-links">
                    <li><a href="<?= e(url('destinations.php')) ?>">Destinations</a></li>
                    <li><a href="<?= e(url('attractions.php')) ?>">Attractions</a></li>
                    <li><a href="<?= e(url('ar.php')) ?>">AR Experience</a></li>
                    <li><a href="<?= e(url('qr.php')) ?>">QR Codes</a></li>
                    <li><a href="<?= e(url('about.php')) ?>">About</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h2 class="h5">Visit</h2>
                <p><i class="fa-solid fa-envelope me-2"></i><?= e(setting('contact_email', 'hello@artourism.local')) ?></p>
                <p><i class="fa-solid fa-phone me-2"></i><?= e(setting('contact_phone', '+00 000 000 000')) ?></p>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= e(app_title()) ?></span>
            <a href="<?= e(url('admin/login.php')) ?>">Administrator</a>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/qrcode.min.js')) ?>"></script>
<script src="<?= e(asset('js/public.js')) ?>"></script>
</body>
</html>
