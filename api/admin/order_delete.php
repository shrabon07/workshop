<?php
/**
 * Admin — permanently delete an order (its payment requests cascade).
 * Super admin only.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

if (!is_super_admin()) {
    json_error('Only the super admin can delete orders.', 403);
}

$orderId = (int) post('order_id');
if ($orderId <= 0) json_error('Invalid order.');

$order = DB::get('SELECT id FROM orders WHERE id = ?', [$orderId]);
if (!$order) {
    json_error('That order no longer exists.', 404);
}

DB::run('DELETE FROM orders WHERE id = ?', [$orderId]); // payment_requests cascade via FK

json_ok(['order_id' => $orderId]);