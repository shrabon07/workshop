<?php
/** Create a service (admin). */

require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../includes/services-save.php';

$p = services_payload();

$err = services_validate($p);
if ($err) {
    json_error($err);
}

// unique slug
if (DB::get('SELECT id FROM services WHERE slug = ?', [$p['slug']])) {
    json_error('This slug is already in use. Please choose another one.');
}

$id = DB::insert('services', array_merge($p, [
    'created_at' => date('Y-m-d H:i:s'),
]));
$p['updated_at'] = date('Y-m-d H:i:s');

json_ok(['id' => $id, 'service' => array_merge(['id' => $id], $p)]);