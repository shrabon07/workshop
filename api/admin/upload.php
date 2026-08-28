<?php
/**
 * Admin image upload for services (thumbnail + gallery).
 * Stores inside /uploads/service/… — served directly by Apache.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    json_error('No file received or upload failed.', 400);
}

$f = $_FILES['file'];
$allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml'];
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));

if (!isset($allowed[$ext])) {
    json_error('Only JPG, PNG, GIF, WEBP or SVG images are allowed.');
}
$finfo = function_exists('finfo_open') ? (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) : ($allowed[$ext] ?? '');
if (function_exists('finfo_open') && !in_array($finfo, array_values($allowed), true)) {
    json_error('The file content does not look like a valid image.');
}
if ($f['size'] > 4 * 1024 * 1024) {
    json_error('Image must be under 4 MB.');
}

$dir = UPLOAD_DIR . '/service';
if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
}

$name = date('Ymd') . '-' . substr(bin2hex(random_bytes(6)), 0, 10) . '.' . $ext;
if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
    json_error('Could not store the file.');
}

json_ok(['path' => 'service/' . $name, 'url' => uploads_url('service/' . $name)]);