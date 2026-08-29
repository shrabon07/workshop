<?php
/**
 * Customer notifications API — list, unread count, mark read.
 */
require_once __DIR__ . '/../includes/api.php';

$uid = current_user_id();
if ($uid === null) {
    json_error('Sign in required.', 401);
}

$action = (string) get('action');

switch ($action) {
    case 'list':
    case 'count':
        json_ok(['unread' => user_unread_count($uid)]);
        break;

    case 'read':
        $notif = DB::get('SELECT id FROM app_notifications WHERE id = ? AND user_id = ?', [(int) get('id'), $uid]);
        if ($notif) {
            DB::update('app_notifications', ['is_read' => 1], 'id = ?', [$notif['id']]);
        }
        json_ok(['id' => (int) get('id'), 'unread' => user_unread_count($uid)]);
        break;

    case 'read_all':
        DB::update('app_notifications', ['is_read' => 1], 'user_id = ?', [$uid]);
        json_ok(['unread' => 0]);
        break;

    default:
        json_error('Unknown action.');
}