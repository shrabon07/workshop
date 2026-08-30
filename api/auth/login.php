<?php
/**
 * Login — customers and admins. Redirect target depends on role.
 */

require_once __DIR__ . '/../../includes/api.php';

csrf_require();

$email    = strtolower(trim((string) post('email')));
$password = (string) post('password');
$next     = (string) post('next');

if ($email === '' || $password === '') {
    json_error('Email and password are required.');
}

$user = DB::get('SELECT * FROM users WHERE email = ?', [$email]);
if (!$user || !password_verify($password, $user['password'])) {
    json_error('Incorrect email or password.', 401);
}

if ($user['role'] === 'admin' && (int) ($user['is_active'] ?? 1) !== 1) {
    json_error('This admin account has been deactivated. Contact the super admin.', 403);
}

login_user($user);

$fallback = $user['role'] === 'admin' ? url('admin/dashboard.php') : url('account/dashboard.php');
$redirect = $next && str_starts_with($next, 'http') ? $next : url('account/dashboard.php');

json_ok([
    'redirect' => $user['role'] === 'admin' ? url('admin/dashboard.php') : $redirect,
    'role'     => $user['role'],
]);