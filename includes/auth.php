<?php
/**
 * Authentication helpers — customers and admins share the `users` table.
 */

declare(strict_types=1);

function current_user_id(): ?int
{
    return isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null;
}

function current_user(): ?array
{
    static $user = false;
    $uid = current_user_id();
    if ($uid === null) {
        return null;
    }
    if ($user === false) {
        $user = DB::get('SELECT * FROM users WHERE id = ?', [$uid]);
        if (!$user) {
            unset($_SESSION['uid']);
            $user = null;
        }
    }
    return $user;
}

function is_admin(): bool
{
    $u = current_user();
    return $u !== null && $u['role'] === 'admin' && (int) ($u['is_active'] ?? 1) === 1;
}

function is_super_admin(): bool
{
    $u = current_user();
    return $u !== null
        && $u['role'] === 'admin'
        && (int) ($u['is_super_admin'] ?? 0) === 1
        && (int) ($u['is_active'] ?? 1) === 1;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect_login('Please sign in to continue.');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $user['id'];
    if ($user['role'] === 'admin') {
        $_SESSION['is_admin'] = true;
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function redirect_login(string $message = '', ?string $next = null): void
{
    $next = $next ?? $_SERVER['REQUEST_URI'] ?? '';
    flash($message, 'error');
    header('Location: ' . url('account/login.php?next=' . rawurlencode($next)));
    exit;
}

function redirect_admin_login(): void
{
    header('Location: ' . url('admin/login.php'));
    exit;
}