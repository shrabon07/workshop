<?php
/**
 * Admin — save MY Gmail sender (regular admins only).
 * Tests the credentials with a real proof-mail using ONLY those creds
 * (no fallback), then stores the app password AES-encrypted.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

if (is_super_admin()) {
    json_error('The super admin always sends from the site account.', 403);
}

$email = strtolower(trim((string) post('smtp_email')));
$pass  = preg_replace('/\s+/', '', (string) post('smtp_pass')); /* Gmail shows app passwords with spaces */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Enter a valid Gmail address.');
}
if ($pass === '') {
    json_error('Enter the Gmail app password (16 letters).');
}
if (strlen($pass) !== 16) {
    json_error('Gmail app passwords are exactly 16 letters.');
}

$tested = smtp_test_auth($email, $pass);

DB::run(
    'INSERT INTO admin_mail_settings (admin_id, smtp_email, smtp_pass, verified, verified_at, updated_at)
     VALUES (?, ?, ?, ?, ?, NOW())
     ON DUPLICATE KEY UPDATE smtp_email = VALUES(smtp_email), smtp_pass = VALUES(smtp_pass),
                             verified = VALUES(verified), verified_at = VALUES(verified_at), updated_at = NOW()',
    [current_user_id(), $email, app_encrypt($pass), $tested ? 1 : 0, $tested ? date('Y-m-d H:i:s') : null]
);

json_ok([
    'verified' => $tested,
    'smtp_email' => $email,
    'message' => $tested
        ? 'Sender verified — a test mail was sent to your Gmail. Your admin emails now come from your own address.'
        : 'Saved but NOT verified. Gmail rejected the app password 3× — turn ON 2-Step Verification for that Gmail and copy a fresh 16-letter app password from myaccount.google.com/apppasswords, then Save again.',
]);