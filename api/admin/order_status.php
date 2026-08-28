<?php
/** Update order status (admin). */

require_once __DIR__ . '/../../includes/admin-guard.php';

$id     = (int) post('id');
$status = (string) post('status');

if (!in_array($status, ['pending', 'in_progress', 'delivered', 'cancelled'], true)) {
    json_error('Invalid status.');
}

$order = DB::get('SELECT * FROM orders WHERE id = ?', [$id]);
if (!$order) {
    json_error('Order not found.', 404);
}

DB::update('orders', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);

if ($order['email'] && $status === 'delivered') {
    send_mail(
        $order['email'],
        'Your project has been delivered 🎉 — Aurora Cyber',
        '<p>Hi ' . e($order['name']) . ',</p>
         <p>Great news — your project (order #' . $order['id'] . ') is <strong>delivered</strong>!</p>
         <p>Reply to this email or ping us on WhatsApp if you need anything.</p>'
    );
}

json_ok(['id' => $id, 'status' => $status]);