<?php
/** Admin — create a new customer directly (name / email / phone / password) + optional verify bypass. */

require_once __DIR__ . '/../../includes/admin-guard.php';

$name   = trim((string) post('name'));
$email  = strtolower(trim((string) post('email')));
$phone  = trim((string) post('phone'));
$pass   = (string) post('password');
$emailV = post('email_verified') ? 1 : 0;
$whatsV = post('whatsapp_verified') ? 1 : 0;

if (mb_strlen($name) < 2)                      json_error('Please enter the customer&#8217;s full name.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('A valid email address is required.');
if (mb_strlen($pass) < 6)                      json_error('Password must be at least 6 characters.');
if (DB::get('SELECT id FROM users WHERE email = ?', [$email])) {
    json_error('Another account already uses that email.');
}

$id = DB::insert('users', [
    'name'       => $name,
    'email'      => $email,
    'phone'      => $phone !== '' ? $phone : null,
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
    'admin_override'        => ($emailV || $whatsV) ? 'none' : 'none',
]);

json_ok([
    'user_id' => $id,
    'email'   => $email,
]);