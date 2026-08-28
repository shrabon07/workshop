<?php
/** Update a service (admin). */

require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../includes/services-save.php';

$id = (int) post('id');
$existing = DB::get('SELECT * FROM services WHERE id = ?', [$id]);
if (!$existing) {
    json_error('Service not found.', 404);
}

$p = services_payload();

$err = services_validate($p);
if ($err) {
    json_error($err);
}

$slugConflict = DB::get('SELECT id FROM services WHERE slug = ? AND id != ?', [$p['slug'], $id]);
if ($slugConflict) {
    json_error('This slug is already in use by another service.');
}

DB::update('services', array_merge($p, ['updated_at' => date('Y-m-d H:i:s')]), 'id = ?', [$id]);

$p['updated_at'] = date('Y-m-d H:i:s');
json_ok(['id' => $id, 'service' => array_merge(['id' => $id], $p)]);