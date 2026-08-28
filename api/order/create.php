<?php
/**
 * Custom Order Form — accepts orders from guests AND logged-in customers.
 */

require_once __DIR__ . '/../../includes/api.php';

csrf_require();

$name        = trim((string) post('name'));
$email       = trim((string) post('email'));
$phone       = trim((string) post('phone'));
$projectType = trim((string) post('project_type'));
$serviceId   = post('service_id') !== null && post('service_id') !== '' ? (int) post('service_id') : null;
$budget      = post('budget') !== null && post('budget') !== '' ? (float) post('budget') : null;
$details     = trim((string) post('details'));

if (mb_strlen($name) < 2) {
    json_error('Please provide your full name.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Please provide a valid email address.');
}
if (mb_strlen($details) < 5) {
    json_error('Please tell us a little about your project.');
}

if ($serviceId) {
    $svc = DB::get('SELECT id, title_en FROM services WHERE id = ? AND status != "archived"', [$serviceId]);
    if (!$svc) {
        $serviceId = null;
    }
}

$orderId = DB::insert('orders', [
    'user_id'      => current_user_id(),
    'service_id'   => $serviceId,
    'name'         => $name,
    'email'        => $email,
    'phone'        => $phone ?: null,
    'project_type' => $projectType ?: null,
    'budget'       => $budget !== null && $budget > 0 ? $budget : null,
    'details'      => $details,
    'status'       => 'pending',
]);

// Fire-and-forget ack email (never blocks the response).
send_mail(
    $email,
    'We received your project brief — Aurora Cyber',
    '<h2 style="color:#0f766e">Thanks ' . e($name) . ' 🙌</h2>
     <p>We received your brief for <strong>' . e($projectType ?: 'your project') . '</strong>.</p>
     <p>Our team will review it and reply within <strong>24 hours</strong> (Mon–Sat).</p>
     <p>You can chat with us anytime: <a href="' . WHATSAPP_LINK . '">WhatsApp</a></p>'
);

json_ok([
    'order_id'     => $orderId,
    'message_en'   => 'Your brief was received! We will contact you within 24 hours.',
    'message_bn'   => 'আপনার ব্রিফটি আমরা পেয়েছি! ২৪ ঘণ্টার মধ্যে যোগাযোগ করা হবে।',
    'whatsapp_url' => WHATSAPP_LINK,
    'order_page'   => url('account/login.php?next=' . rawurlencode('account/dashboard.php')),
]);