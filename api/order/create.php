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

// Fire-and-forget branded confirmation email (never blocks the response).
$orderRow = [
    'id'           => $orderId,
    'name'         => $name,
    'email'        => $email,
    'phone'        => $phone,
    'project_type' => $projectType,
    'budget'       => $budget,
    'details'      => $details,
];
$body = '<p>Hi ' . e($name) . ',</p>
<p>Thanks for reaching out to our team! Your project brief is confirmed and is now in our queue.</p>
' . email_order_facts($orderRow) . '

<p><strong>You shared with us:</strong></p>
' . email_brief_block($details) . '

<p style="margin-top:16px;">What happens next:</p>
<ol style="margin:6px 0 0 20px;padding-left:18px;color:#334155;">
  <li>Our team reviews your brief (Mon–Sat).</li>
  <li>You get a personal reply with a plan &amp; quote within <strong>24 hours</strong>.</li>
  <li>We refine the scope together on a quick call or WhatsApp.</li>
</ol>

<p style="margin-top:16px;">In a hurry, or just like chatting?</p>
' . email_button('Message us on WhatsApp', WHATSAPP_LINK);

if (current_user_id()) {
    $body .= '<p style="margin-top:14px;font-size:13px;color:#64748b;">You can track this order anytime from your <a href="' . e(url('account/dashboard.php')) . '" style="color:#0f766e;">customer dashboard</a>.</p>';
}

send_mail(
    $email,
    'Order #' . $orderId . ' received — ' . SITE_NAME,
    email_layout('Order confirmation', $body, ['badge' => 'Order #' . $orderId, 'tagline' => 'Your project is in good hands.']),
    '',
    email_layout_embeds()
);

json_ok([
    'order_id'     => $orderId,
    'message_en'   => 'Your brief was received! We will contact you within 24 hours.',
    'message_bn'   => 'আপনার ব্রিফটি আমরা পেয়েছি! ২৪ ঘণ্টার মধ্যে যোগাযোগ করা হবে।',
    'whatsapp_url' => WHATSAPP_LINK,
    'order_page'   => url('account/login.php?next=' . rawurlencode('account/dashboard.php')),
]);