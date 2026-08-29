<?php
/**
 * Send a verification OTP (email channel → real SMTP / offline fallback;
 * whatsapp channel → click-to-chat flow with the code pre-filled).
 */

require_once __DIR__ . '/../../includes/api.php';

csrf_require();

$user = current_user();
if (!$user) {
    json_error('Please sign in first.', 401);
}

$channel = post('channel');
if (!in_array($channel, ['email', 'whatsapp'], true)) {
    json_error('Unknown channel.');
}

$last = DB::get(
    'SELECT * FROM otp_codes WHERE user_id = ? AND channel = ? AND used = 0 ORDER BY id DESC LIMIT 1',
    [$user['id'], $channel]
);
if ($last && (time() - strtotime($last['created_at'])) < OTP_RESEND_SECONDS) {
    json_error('Please wait before requesting another code.', 429, ['wait' => OTP_RESEND_SECONDS - (time() - strtotime($last['created_at']))]);
}

$code   = random_otp(6);
$hash   = hash('sha256', $code);
$expiry = date('Y-m-d H:i:s', time() + (OTP_TTL_MINUTES * 60));

DB::insert('otp_codes', [
    'user_id'    => $user['id'],
    'channel'    => $channel,
    'purpose'    => 'verify',
    'code_hash'  => $hash,
    'expires_at' => $expiry,
    'used'       => 0,
]);

$data = [
    'expires_in_minutes' => OTP_TTL_MINUTES,
    'dev_reveal'         => DEV_REVEAL_OTP ? $code : null,
];

if ($channel === 'email') {
    send_mail(
        $user['email'],
        'Your Aurora Cyber verification code is ' . $code,
        '<p>Hi ' . e($user['name']) . ',</p>
         <p>Your verification code is:</p>
         <p style="font-size:30px;letter-spacing:6px;font-weight:800;color:#0f766e">' . $code . '</p>
         <p>It expires in ' . OTP_TTL_MINUTES . ' minutes. If you did not request this, ignore this email.</p>'
    );
} else {
    // Click-to-chat flow: pre-fill the intent on WhatsApp.
    $waText = 'Aurora Cyber — my WhatsApp verification code is ' . $code . '. Please verify my account.';
    $data['whatsapp_link'] = wa_link($user['phone'] ?: WHATSAPP_NUMBER, $waText);
}

json_ok($data);