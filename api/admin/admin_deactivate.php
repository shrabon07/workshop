<?php
/**
 * Admin — deactivate / reactivate an admin account.
 * Deactivated admins lose panel access immediately. Super admin only.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

if (!is_super_admin()) {
    json_error('Only the super admin can manage admins.', 403);
}

$adminId = (int) post('admin_id');
$meId    = (int) current_user_id();

if ($adminId === $meId) {
    json_error('You cannot deactivate your own account.');
}

$target = DB::get('SELECT * FROM users WHERE id = ?', [$adminId]);
if (!$target || $target['role'] !== 'admin') {
    json_error('That admin account no longer exists.');
}

$newState = (int) ($target['is_active'] ?? 1) === 1 ? 0 : 1;
DB::update('users', ['is_active' => $newState], 'id = ?', [$adminId]);

json_ok([
    'admin_id'           => $adminId,
    'is_active'          => $newState,
    'deactivated'        => $newState === 0,
]);