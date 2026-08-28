<?php
/** Toggle a service active ↔ inactive (admin). */

require_once __DIR__ . '/../../includes/admin-guard.php';

$id = (int) post('id');
$existing = DB::get('SELECT id, status FROM services WHERE id = ? AND status != "archived"', [$id]);
if (!$existing) {
    json_error('Service not found.', 404);
}

$next = $existing['status'] === 'active' ? 'inactive' : 'active';
DB::update('services', ['status' => $next, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);

json_ok(['id' => $id, 'status' => $next]);