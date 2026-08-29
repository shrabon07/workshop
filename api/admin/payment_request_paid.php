<?php
/**
 * Admin — manually mark a payment request as paid.
 * Flips it unpaid (red) → paid (green) and sends the customer BOTH an
 * in-app dashboard notification and a payment-received email.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

$reqId = (int) post('id');

$req = DB::get(
    'SELECT pr.id, pr.order_id, pr.user_id, pr.amount, pr.note, pr.status AS pay_status, pr.paid_at,
            o.name, o.email, o.phone, o.project_type, o.budget, o.status AS order_status
       FROM payment_requests pr
       JOIN orders o ON o.id = pr.order_id
      WHERE pr.id = ?',
    [$reqId]
);
if (!$req) {
    json_error('Payment request not found.', 404);
}

if ($req['pay_status'] === 'paid') {
    json_error('That payment is already marked as paid.');
}

$order = $req;
$order['status'] = $req['order_status'];

$amount = (float) $req['amount'];
payment_request_mark_paid($reqId);

notify_payment_paid((int) $req['user_id'], $order, $amount, $reqId);

[$subject, $heading, $tagline, $body, $badge] = email_payment_message($order, $amount, '', true);
$ok = send_mail(
    $order['email'],
    $subject,
    email_layout($heading, $body, ['tagline' => $tagline, 'badge' => $badge]),
    'Payment received — order #' . $order['id'] . ': ' . price_fmt($amount)
);

json_ok([
    'request_id' => $reqId,
    'status'     => 'paid',
    'email_sent' => $ok,
]);