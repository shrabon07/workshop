<?php
/** Soft-delete a service (admin) → status 'archived' (restorable). */

require_once __DIR__ . '/../../includes/admin-guard.php';

$id = (int) post('id');
$existing = DB::get('SELECT id, title_en FROM services WHERE id = ? AND status != "archived"', [$id]);
if (!$existing) {
    json_error('Service not found.', 404);
}

DB::update('services', ['status' => 'archived', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);

json_ok(['id' => $id, 'message' => 'Service archived — it can be restored anytime.']);