<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Contact · ' . app_title();
$sent = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = str_param('name');
    $email = str_param('email');
    $message = str_param('message');
    if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter your name, a valid email address and a message.';
    } else {
        log_activity('contact.message', $name . ' <' . $email . '>: ' . $message);
        $sent = true;
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <p class="hero-kicker">CONTACT</p>
                <h1>Talk with the tourism team</h1>
                <p><i class="fa-solid fa-envelope me-2"></i><?= e(setting('contact_email')) ?></p>
                <p><i class="fa-solid fa-phone me-2"></i><?= e(setting('contact_phone')) ?></p>
                <p><i class="fa-solid fa-location-dot me-2"></i><?= e(setting('contact_address')) ?></p>
            </div>
            <div class="col-lg-7">
                <div class="tour-card">
                    <div class="card-body">
                        <?php if ($sent): ?><div class="alert alert-success">Thank you. Your message has been recorded.</div><?php endif; ?>
                        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label" for="name">Name</label>
                                <input class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control" id="email" type="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="message">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button class="btn btn-ar" type="submit">Send message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
