<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/functions.php';
$pageTitle = 'Settings';
$adminSection = 'settings';
$keys = ['site_name', 'site_tagline', 'about_text', 'contact_email', 'contact_phone', 'contact_address'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($keys as $key) {
        $stmt->execute([$key, str_param($key)]);
    }
    log_activity('settings.update', 'Admin updated system settings');
    flash_set('success', 'Settings saved.');
    redirect(admin_url('settings/index.php'));
}
require __DIR__ . '/../../includes/admin-header.php';
?>
<div class="admin-card">
    <form method="post">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Site name</label><input class="form-control" name="site_name" value="<?= e(setting('site_name', APP_NAME)) ?>"></div>
        <div class="mb-3"><label class="form-label">Tagline</label><input class="form-control" name="site_tagline" value="<?= e(setting('site_tagline')) ?>"></div>
        <div class="mb-3"><label class="form-label">About text</label><textarea class="form-control" name="about_text" rows="6"><?= e(setting('about_text')) ?></textarea></div>
        <div class="mb-3"><label class="form-label">Contact email</label><input class="form-control" name="contact_email" value="<?= e(setting('contact_email')) ?>"></div>
        <div class="mb-3"><label class="form-label">Contact phone</label><input class="form-control" name="contact_phone" value="<?= e(setting('contact_phone')) ?>"></div>
        <div class="mb-3"><label class="form-label">Address</label><input class="form-control" name="contact_address" value="<?= e(setting('contact_address')) ?>"></div>
        <button class="btn btn-success">Save settings</button>
    </form>
</div>
<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
