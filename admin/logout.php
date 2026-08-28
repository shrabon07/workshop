<?php
/** Admin — sign out. */
require_once __DIR__ . '/../config.php';
logout_user();
header('Location: ' . url('admin/login.php'));
exit;