<?php
/** Admin — permanently delete a customer (keeps order history, cascades verification). */

require_once __DIR__ . '/../../includes/admin-guard.php';

$userId = (int) post('user_id');

$user = DB::get('SELECT id, role FROM users WHERE id = ?', [$userId]);
if (!$user) {
    json_error('Customer not found.', 404);
}
if ($user['role'] === 'admin') {
    json_error('Admins cannot be deleted.', 403);
}

DB::update('orders', ['user_id' => null], 'user_id = ?', [$userId]);
DB::run('DELETE FROM app_notifications WHERE user_id = ?', [$userId]);
DB::run('DELETE FROM users WHERE id = ?', [$userId]); // verification_status cascades

json_ok(['user_id' => $userId]);