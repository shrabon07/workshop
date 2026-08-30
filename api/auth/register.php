<?php
/**
 * Register new customers (E-mail + password). Creates the matching
 * verification_status row automatically (starts red ❌).
 */

require_once __DIR__ . '/../../includes/api.php';
require_once __DIR__ . '/../../includes/countries.php';

csrf_require();

$name     = trim((string) post('name'));
$email    = trim((string) post('email'));
$phone    = trim((string) post('phone'));
$country  = trim((string) post('country'));
$consent  = (string) post('consent');
$password = (string) post('password');

if (mb_strlen($name) < 2) {
    json_error('Please provide your full name.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Please provide a valid email address.');
}
if ($country === '') {
    json_error('Please select your country.');
}
if ($consent !== '1') {
    json_error('Please accept the Terms & Privacy and Payment Methods to continue.');
}
if (mb_strlen($password) < 6) {
    json_error('Password must be at least 6 characters.');
}
if (DB::get('SELECT id FROM users WHERE email = ?', [$email])) {
    json_error('An account with this email already exists.', 409);
}

$id = DB::insert('users', [
    'name'     => $name,
    'email'    => strtolower($email),
    'phone'    => $phone ?: null,
    'country'  => in_array($country, country_list(), true) ? $country : 'Bangladesh',
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'role'     => 'customer',
]);

DB::insert('verification_status', [
    'user_id'           => $id,
    'email_verified'    => 0,
    'whatsapp_verified' => 0,
    'admin_override'    => 'none',
]);

login_user(['id' => $id, 'role' => 'customer']);

json_ok(['user_id' => $id, 'redirect' => url('account/verify.php')]);