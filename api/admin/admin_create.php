<?php
/**
 * Admin — create another admin account (name / email / password).
 * Only an already-logged-in admin can reach this endpoint.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

if (!is_super_admin()) {
    json_error('Only the super admin can create admins.', 403);
}

$name  = trim((string) post('name'));
$email = strtolower(trim((string) post('email')));
$pass  = (string) post('password');

if (mb_strlen($name) < 2)                      json_error('Please enter the admin staff&#8217;s full name.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('A valid email address is required.');
if (mb_strlen($pass) < 6)                      json_error('Password must be at least 6 characters.');
if (DB::get('SELECT id FROM users WHERE email = ?', [$email])) {
    json_error('Another account already uses that email.');
}

$id = DB::insert('users', [
    'name'           => $name,
    'email'          => $email,
    'password'       => password_hash($pass, PASSWORD_DEFAULT),
    'role'           => 'admin',
    'is_active'      => 1,
    'is_super_admin' => 0,
    'created_at'     => date('Y-m-d H:i:s'),
]);

$now = date('Y-m-d H:i:s');
DB::insert('verification_status', [
    'user_id'              => $id,
    'email_verified'       => 1,
    'whatsapp_verified'    => 0,
    'email_verified_at'    => $now,
    'whatsapp_verified_at' => null,
    'admin_override'       => 'none',
]);

/* Email the new admin their login credentials. */
$loginUrl = url('admin/login.php');
$subject  = 'You are now an admin at ' . SITE_NAME;
$bodyHtml = '<p>Hi ' . e($name) . ',</p>'
          . '<p>Your <b>' . e(SITE_NAME) . '</b> admin account is ready. Here are your login details:</p>'
          . '<table style="width:100%;max-width:440px;margin:18px 0;border-collapse:collapse;font-size:14px;">'
          . '<tr><td style="padding:10px 12px;background:#0f172a;color:#94a3b8;border:1px solid #1e293b;border-radius:10px 0 0 0;">Email</td>'
          . '<td style="padding:10px 12px;background:#0f172a;color:#f1f5f9;font-weight:700;border:1px solid #1e293b;border-radius:0 10px 0 0;">' . e($email) . '</td></tr>'
          . '<tr><td style="padding:10px 12px;background:#0f172a;color:#94a3b8;border:1px solid #1e293b;">Password</td>'
          . '<td style="padding:10px 12px;background:#0f172a;color:#22d3ee;font-weight:700;border:1px solid #1e293b;">' . e($pass) . '</td></tr>'
          . '<tr><td style="padding:10px 12px;background:#0f172a;color:#94a3b8;border:1px solid #1e293b;border-radius:0 0 10px 10px;">Admin sign in</td>'
          . '<td style="padding:10px 12px;background:#0f172a;border:1px solid #1e293b;border-radius:0 0 10px 10px;"><a href="' . e($loginUrl) . '" style="color:#22d3ee;font-weight:700;">' . e($loginUrl) . '</a></td></tr>'
          . '</table>'
          . '<p style="margin-top:8px;font-size:12px;color:#94a3b8;">Only admins can access the panel. Keep your password safe and change it after signing in.</p>';
$sent = send_mail(
    $email,
    $subject,
    email_layout($subject, $bodyHtml, ['tagline' => 'Admin access granted.']),
    "Hi " . $name . ",\n\nYour " . SITE_NAME . " admin account is ready.\n\nEmail: " . $email . "\nPassword: " . $pass . "\n\nSign in: " . $loginUrl
);

json_ok([
    'admin_id'   => $id,
    'email'      => $email,
    'email_sent' => $sent,
]);