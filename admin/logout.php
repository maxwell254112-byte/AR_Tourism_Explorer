<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
if (current_admin()) {
    log_activity('admin.logout', 'Admin logged out');
}
admin_logout();
redirect(admin_url('login.php'));
