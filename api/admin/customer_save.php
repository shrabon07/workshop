<?php
/** Admin — edit a customer (name / email / phone) + direct verify bypass. */

require_once __DIR__ . '/../../includes/admin-guard.php';

if (!admin_mail_ready()) {
    json_error('Connect and verify your mail sender (Mail settings → My mail sender) before editing customers.', 403);
}

$id        = (int) post('user_id');
$name      = trim((string) post('name'));
$email     = trim((string) post('email'));
$phone     = trim((string) post('phone'));
$emailV    = post('email_verified') ? 1 : 0;
$whatsV    = post('whatsapp_verified') ? 1 : 0;
$resetOverride = (bool) post('reset_override');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Valid name and email are required.');
}

$user = DB::get('SELECT id, role FROM users WHERE id = ?', [$id]);
if (!$user || $user['role'] === 'admin') {
    json_error('Customer not found.', 404);
}

$dup = DB::value('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $id]);
if ($dup) {
    json_error('Another customer already uses that email.');
}

DB::update('users', [
    'name'       => $name,
    'email'      => $email,
    'phone'      => $phone !== '' ? $phone : null,
    'updated_at' => date('Y-m-d H:i:s'),
], 'id = ?', [$id]);

$rec = verification_record($id);
$now = date('Y-m-d H:i:s');
$flagsChanged = $emailV !== (int) $rec['email_verified'] || $whatsV !== (int) $rec['whatsapp_verified'];
DB::update('verification_status', [
    'email_verified'       => $emailV,
    'whatsapp_verified'    => $whatsV,
    'email_verified_at'    => $emailV ? ($rec['email_verified'] ? $rec['email_verified_at'] : $now) : null,
    'whatsapp_verified_at' => $whatsV ? ($rec['whatsapp_verified'] ? $rec['whatsapp_verified_at'] : $now) : null,
    'admin_override'       => ($flagsChanged || $resetOverride) ? 'none' : $rec['admin_override'],
], 'user_id = ?', [$id]);

json_ok([
    'user_id' => $id,
    'tick'    => verification_tick(['id' => $id, 'role' => 'customer']),
]);