<?php
/** Sign out (any user). */
require_once __DIR__ . '/../config.php';
logout_user();
header('Location: ' . url(''));
exit;