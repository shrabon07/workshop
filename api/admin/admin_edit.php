<?php
/**
 * Admin — edit an admin staff account (name / email / optional new password).
 * Super admin only.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

if (!is_super_admin()) {
    json_error('Only the super admin can edit admins.', 403);
}

$adminId = (int) post('admin_id');
$name    = trim((string) post('name'));
$email   = strtolower(trim((string) post('email')));
$pass    = (string) post('password');

$target = DB::get('SELECT * FROM users WHERE id = ?', [$adminId]);
if (!$target || $target['role'] !== 'admin') {
    json_error('That admin account no longer exists.');
}

if (mb_strlen($name) < 2)                      json_error('Please enter the admin staff&#8217;s full name.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('A valid email address is required.');
if ($pass !== '' && mb_strlen($pass) < 6)      json_error('Password must be at least 6 characters.');

$dupe = DB::get('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $adminId]);
if ($dupe) json_error('Another account already uses that email.');

$fields = ['name' => $name, 'email' => $email];
if ($pass !== '') {
    $fields['password'] = password_hash($pass, PASSWORD_DEFAULT);
}
DB::update('users', $fields, 'id = ?', [$adminId]);

json_ok(['admin_id' => $adminId, 'email' => $email]);