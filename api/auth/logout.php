<?php
/** Logout (POST only). */

require_once __DIR__ . '/../../includes/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}
csrf_require();

logout_user();

json_ok(['redirect' => url('')]);