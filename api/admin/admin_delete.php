<?php
/**
 * Admin — delete an admin account permanently.
 * Super admin only; the super admin can never delete themselves.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

if (!is_super_admin()) {
    json_error('Only the super admin can manage admins.', 403);
}

$adminId = (int) post('admin_id');
$meId    = (int) current_user_id();

if ($adminId === $meId) {
    json_error('You cannot delete your own account.');
}

$target = DB::get('SELECT * FROM users WHERE id = ?', [$adminId]);
if (!$target || $target['role'] !== 'admin') {
    json_error('That admin account no longer exists.');
}

DB::run('DELETE FROM users WHERE id = ?', [$adminId]);

json_ok(['admin_id' => $adminId, 'email' => $target['email']]);