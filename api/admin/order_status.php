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
    $body = '<p>Hi ' . e($order['name']) . ',</p>
<p>Great news — your project is <strong style="color:#059669;">delivered</strong> 🎉</p>
' . email_order_facts($order) . '

<p>Please test everything and tell us if you need any tweaks — we are one message away.</p>
' . email_button('Message us on WhatsApp', WHATSAPP_LINK) . '

<p style="margin-top:14px;font-size:13px;color:#64748b;">Thanks for trusting ' . e(SITE_NAME) . ' with your project. Take care, and let`s grow from here!</p>';

    send_mail(
        $order['email'],
        'Your project is delivered — ' . SITE_NAME,
        email_layout('Project delivered', $body, ['badge' => 'Order #' . $order['id'], 'tagline' => 'Your website is live.']),
        'Your project (order #' . $order['id'] . ') has been delivered. Reply to this email or message us on WhatsApp.',
        email_layout_embeds()
    );
}

json_ok(['id' => $id, 'status' => $status]);