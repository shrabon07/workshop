<?php
/**
 * Admin — delete a row from the custom-mail log. Super admin only.
 * Hard-deletes the row from the database.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

if (!is_super_admin()) {
    json_error('Only the super admin can delete emails from the list.', 403);
}

$id = (int) post('log_id');
if ($id <= 0) {
    json_error('Invalid record.');
}

if (!custom_mail_log_delete($id)) {
    json_error('That email record no longer exists.', 404);
}

json_ok(['log_id' => $id]);
