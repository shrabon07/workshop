<?php
/**
 * Bootstraps sessions, the database, and the shared helper layer.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mailer.php';

if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0775, true);
    @file_put_contents(UPLOAD_DIR . '/.gitkeep', '');
}