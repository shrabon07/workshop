<?php
/**
 * Admin — custom mail composer (mail pad): to (up to 10 email addresses,
 * added with the '+' button), subject, body. Any admin can compose; a
 * regular admin must have a verified personal sender first (super admin
 * always sends from the site account). Every send is archived to the
 * custom_mail_log list.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

if (!admin_mail_ready()) {
    json_error('Connect and verify your mail sender (Mail settings → My mail sender) before composing custom mail.', 403);
}

$subject = trim((string) post('subject'));
$message = trim((string) post('message'));
if ($subject === '') {
    json_error('Please enter a subject.');
}
if (mb_strlen($subject) > 190) {
    json_error('Subject is too long.');
}
if ($message === '') {
    json_error('Please write a message.');
}

/* Collect + normalise recipient addresses (up to 10). */
$raw = $_POST['recipients'] ?? [];
if (!is_array($raw)) {
    $raw = [$raw];
}
$seen    = [];
$emails  = [];
foreach ($raw as $entry) {
    $parts = preg_split('/[,;\s]+/', (string) $entry) ?: [];
    foreach ($parts as $p) {
        $addr = strtolower(trim($p));
        if ($addr === '' || isset($seen[$addr])) {
            continue;
        }
        $seen[$addr] = true;
        if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
            $emails[] = $addr;
        }
        if (count($emails) >= 10) {
            break;
        }
    }
    if (count($emails) >= 10) {
        break;
    }
}

if ($emails === []) {
    json_error('Enter at least one valid email address.');
}

/* Branded mail pad layout. */
$bodyHtml = '<p>' . nl2br(e($message)) . '</p>'
          . '<p style="margin-top:16px;font-size:12px;color:#64748b;">— ' . e(SITE_NAME) . ' · '
          . e(SITE_EMAIL) . '</p>';

$sb = admin_identity() ?: [];
$html = email_layout('Message from ' . SITE_NAME, $bodyHtml, [
    'tagline' => 'Sent from the ' . SITE_NAME . ' mail pad.',
    'sent_by' => $sb,
]);

$sent = 0;
foreach ($emails as $to) {
    if (send_mail($to, $subject, $html, $message . ' — ' . SITE_NAME, [], $sb)) {
        $sent++;
    }
}

$logId = custom_mail_log_add($emails, $subject, $message, $sent);

json_ok([
    'log_id'     => $logId,
    'recipients' => count($emails),
    'sent'       => $sent,
    'failed'     => count($emails) - $sent,
]);
