<?php
/**
 * Admin API guard — JSON bootstrap + strict role check.
 */

require_once __DIR__ . '/api.php';

if (!is_admin()) {
    json_error('Admin access required.', 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
}