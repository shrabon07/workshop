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

/* In-app notification for the customer (dashboard bell) + branded email. */
notify_order_status($order, $status);

if ($order['email']) {
    $sb = admin_identity() ?: [];
    [$subject, $heading, $tagline, $body, $badge] = email_order_status_message($order, $status);
    send_mail(
        $order['email'],
        $subject,
        email_layout($heading, $body, ['badge' => $badge, 'tagline' => $tagline, 'sent_by' => $sb]),
        'Your order #' . (int) $order['id'] . ' status: ' . e(order_status_meta($status)['label_en']) . '. Reply to this email or message us on WhatsApp.',
        [],
        $sb
    );
}

json_ok(['id' => $id, 'status' => $status]);