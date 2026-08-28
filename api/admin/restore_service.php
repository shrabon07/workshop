<?php
/** Restore an archived service (admin) → active. */

require_once __DIR__ . '/../../includes/admin-guard.php';

$id = (int) post('id');
$existing = DB::get('SELECT id FROM services WHERE id = ? AND status = "archived"', [$id]);
if (!$existing) {
    json_error('No archived service with that id.', 404);
}

DB::update('services', ['status' => 'active', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);

json_ok(['id' => $id, 'message' => 'Service restored and visible on the public site.']);