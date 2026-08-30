<?php
/**
 * Admin — send an email to one customer or a bulk marketing email
 * to every customer who has an address (never to admins).
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

$userId = (int) post('user_id');            // 0 => bulk
$bulk   = $userId <= 0;
$subject = trim((string) post('subject'));
$message = trim((string) post('message'));

if ($subject === '' || $message === '') {
    json_error('Subject and message are required.');
}
if (mb_strlen($subject) > 190) {
    json_error('Subject is too long.');
}

$bodyHtml = '<p>' . nl2br(e($message)) . '</p>'
          . '<p style="margin-top:16px;font-size:12px;color:#64748b;">— ' . e(SITE_NAME) . ' · '
          . e(SITE_EMAIL) . '<br>' . ($bulk ? 'You receive these updates because you contacted us. Reply "STOP" to unsubscribe.' : '') . '</p>';

$sb = admin_identity() ?: [];

$html = email_layout($bulk ? 'Updates from ' . SITE_NAME : 'Message from ' . SITE_NAME, $bodyHtml, [
    'tagline' => $bulk ? 'News, offers and project updates.' : 'Sent from the customer team.',
    'sent_by' => $sb,
]);

if ($bulk) {
    $rows = DB::all('SELECT id, name, email FROM users WHERE role = "customer" AND email <> "" ORDER BY id');
    if (!$rows) {
        json_error('No customers with an email address yet.', 404);
    }
    $sent = 0;
    $failed = 0;
    foreach ($rows as $c) {
        $ok = send_mail($c['email'], $subject, $html, $message . ' — ' . SITE_NAME, [], $sb);
        $ok ? $sent++ : $failed++;
    }
    json_ok(['mode' => 'bulk', 'sent' => $sent, 'failed' => $failed]);
}

$cust = DB::get('SELECT id, name, email FROM users WHERE id = ? AND role = "customer"', [$userId]);
if (!$cust) {
    json_error('Customer not found.', 404);
}

$ok = send_mail(
    $cust['email'],
    $subject,
    email_layout('Message from ' . SITE_NAME, $bodyHtml, ['tagline' => 'A note from the ' . SITE_NAME . ' team.', 'sent_by' => $sb]),
    $message . ' — ' . SITE_NAME,
    [],
    $sb
);
if (!$ok) {
    json_error('Email could not be sent.');
}

json_ok(['mode' => 'single', 'user_id' => $cust['id']]);