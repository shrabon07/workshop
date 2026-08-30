<?php
/**
 * Admin — send a custom payment request to a customer.
 * Validates the customer/active order, then creates the request and
 * delivers BOTH an in-app dashboard notification and an email.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

$userId    = (int) post('user_id');
$orderId   = (int) post('order_id');
$amountRaw = (string) post('amount');
$note      = mb_substr(trim((string) post('note')), 0, 1000);

if ($userId <= 0 || $orderId <= 0) {
    json_error('Choose a customer and an order first.');
}

$amount = (float) str_replace(',', '', $amountRaw);
if ($amount <= 0 || !is_finite($amount)) {
    json_error('Enter a valid amount greater than zero.');
}

$customer = DB::get('SELECT id, name FROM users WHERE id = ? AND role = "customer"', [$userId]);
if (!$customer) {
    json_error('Customer not found.', 404);
}

$order = DB::get('SELECT * FROM orders WHERE id = ? AND user_id = ?', [$orderId, $userId]);
if (!$order) {
    json_error('Order not found for that customer.', 404);
}
if ($order['status'] === 'cancelled') {
    json_error('Cancelled orders cannot receive payment requests.');
}

$existing = DB::value('SELECT COUNT(*) FROM payment_requests WHERE order_id = ? AND status = "unpaid"', [$orderId]);
if ($existing > 0) {
    json_error('That order already has an unpaid payment request.');
}

$reqId = payment_request_create($userId, $orderId, $amount, $note);

notify_payment_request($userId, $order, $amount, $reqId);

[$subject, $heading, $tagline, $body, $badge] = email_payment_message($order, $amount, $note, false);
$sb = admin_identity() ?: [];
$ok = send_mail(
    $order['email'],
    $subject,
    email_layout($heading, $body, ['tagline' => $tagline, 'badge' => $badge, 'sent_by' => $sb]),
    'Payment request — order #' . $order['id'] . ': ' . price_fmt($amount),
    [],
    $sb
);

json_ok([
    'request_id' => $reqId,
    'email_sent' => $ok,
]);