<?php
/**
 * Admin — create a new customer directly (name / email / phone / password) +
 * optional verify bypass. Their login credentials are emailed to them.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../includes/countries.php';

if (!admin_mail_ready()) {
    json_error('Connect and verify your mail sender (Mail settings → My mail sender) before creating customers.', 403);
}

$name    = trim((string) post('name'));
$email   = strtolower(trim((string) post('email')));
$phone   = trim((string) post('phone'));
$country = trim((string) post('country'));
$consent = (string) post('consent');
$pass    = (string) post('password');
$emailV  = post('email_verified') ? 1 : 0;
$whatsV  = post('whatsapp_verified') ? 1 : 0;

if (mb_strlen($name) < 2)                      json_error('Please enter the customer&#8217;s full name.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('A valid email address is required.');
if ($country === '')                            json_error('Please select the customer&#8217;s country.');
if ($consent !== '1')                           json_error('Please confirm consent to Terms &amp; Privacy and Payment Methods.');
if (mb_strlen($pass) < 6)                      json_error('Password must be at least 6 characters.');
if (DB::get('SELECT id FROM users WHERE email = ?', [$email])) {
    json_error('Another account already uses that email.');
}

$id = DB::insert('users', [
    'name'       => $name,
    'email'      => $email,
    'phone'      => $phone !== '' ? $phone : null,
    'country'    => in_array($country, country_list(), true) ? $country : 'Bangladesh',
    'password'   => password_hash($pass, PASSWORD_DEFAULT),
    'role'       => 'customer',
    'created_at' => date('Y-m-d H:i:s'),
]);

$now = date('Y-m-d H:i:s');
DB::insert('verification_status', [
    'user_id'               => $id,
    'email_verified'        => $emailV,
    'whatsapp_verified'     => $whatsV,
    'email_verified_at'     => $emailV ? $now : null,
    'whatsapp_verified_at'  => $whatsV ? $now : null,
    'admin_override'        => 'none',
]);

/* Email the customer their login credentials. */
$loginUrl = url('account/login.php');
$subject  = 'Welcome to ' . SITE_NAME . ' — your login details';
$bodyHtml = '<p>Hi ' . e($name) . ',</p>'
          . '<p>Your <b>' . e(SITE_NAME) . '</b> account has been created. Here are your login details:</p>'
          . '<table style="width:100%;max-width:440px;margin:18px 0;border-collapse:collapse;font-size:14px;">'
          . '<tr><td style="padding:10px 12px;background:#0f172a;color:#94a3b8;border:1px solid #1e293b;border-radius:10px 0 0 0;">Email</td>'
          . '<td style="padding:10px 12px;background:#0f172a;color:#f1f5f9;font-weight:700;border:1px solid #1e293b;border-radius:0 10px 0 0;">' . e($email) . '</td></tr>'
          . '<tr><td style="padding:10px 12px;background:#0f172a;color:#94a3b8;border:1px solid #1e293b;">Password</td>'
          . '<td style="padding:10px 12px;background:#0f172a;color:#22d3ee;font-weight:700;border:1px solid #1e293b;">' . e($pass) . '</td></tr>'
          . '<tr><td style="padding:10px 12px;background:#0f172a;color:#94a3b8;border:1px solid #1e293b;border-radius:0 0 10px 10px;">Sign in</td>'
          . '<td style="padding:10px 12px;background:#0f172a;border:1px solid #1e293b;border-radius:0 0 10px 10px;"><a href="' . e($loginUrl) . '" style="color:#22d3ee;font-weight:700;">' . e($loginUrl) . '</a></td></tr>'
          . '</table>'
          . '<p style="margin-top:8px;font-size:12px;color:#94a3b8;">Keep this password safe, and change it after signing in. '
          . 'Verifying your email gets you priority support on WhatsApp.</p>';
$sb = admin_identity() ?: [];
$sent = send_mail(
    $email,
    $subject,
    email_layout($subject, $bodyHtml, ['tagline' => 'Your account is ready.', 'sent_by' => $sb]),
    "Hi " . $name . ",\n\nYour " . SITE_NAME . " account is ready.\n\nEmail: " . $email . "\nPassword: " . $pass . "\n\nSign in: " . $loginUrl,
    [],
    $sb
);

json_ok([
    'user_id'    => $id,
    'email'      => $email,
    'email_sent' => $sent,
]);