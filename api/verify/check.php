<?php
/**
 * Confirm an OTP → flips the verification tick (grey when email,
 * green when both email + whatsapp are verified).
 */

require_once __DIR__ . '/../../includes/api.php';

csrf_require();

$user = current_user();
if (!$user) {
    json_error('Please sign in first.', 401);
}

$channel = post('channel');
$code    = preg_replace('/[^0-9]/', '', (string) post('code'));
if (!in_array($channel, ['email', 'whatsapp'], true) || !$code) {
    json_error('Invalid request.');
}

$otp = DB::get(
    'SELECT * FROM otp_codes WHERE user_id = ? AND channel = ? AND used = 0 AND code_hash = ? ORDER BY id DESC LIMIT 1',
    [$user['id'], $channel, hash('sha256', $code)]
);

if (!$otp) {
    json_error('Incorrect or expired code. Please request a new one.', 422);
}
if (strtotime($otp['expires_at']) < time()) {
    DB::update('otp_codes', ['used' => 1], 'id = ?', [$otp['id']]);
    json_error('This code has expired. Please request a new one.', 422);
}

DB::update('otp_codes', ['used' => 1], 'id = ?', [$otp['id']]);

$rec = verification_record((int) $user['id']);
$set = [];
if (($rec['admin_override'] ?? 'none') === 'none') {
    $set['admin_override'] = 'none';
}
if ($channel === 'email') {
    $set['email_verified'] = 1;
    $set['email_verified_at'] = date('Y-m-d H:i:s');
} else {
    $set['whatsapp_verified'] = 1;
    $set['whatsapp_verified_at'] = date('Y-m-d H:i:s');
}
DB::update('verification_status', $set, 'user_id = ?', [(int) $user['id']]);

$tick = verification_tick(DB::get('SELECT * FROM users WHERE id = ?', [$user['id']]));

json_ok([
    'tick'  => $tick['key'],
    'icon'  => $tick['icon'],
    'label' => $tick['label_en'],
    'verified' => 1,
]);