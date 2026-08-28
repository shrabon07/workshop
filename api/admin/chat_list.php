<?php
/** Admin chat hub — sessions + last message + unread counts. */

require_once __DIR__ . '/../../includes/admin-guard.php';

$rows = DB::all(
    'SELECT s.*, u.name AS user_name, u.email AS user_email,
            (SELECT m.message FROM chat_messages m WHERE m.chat_id = s.id ORDER BY m.id DESC LIMIT 1) AS last_message,
            (SELECT m.sender FROM chat_messages m WHERE m.chat_id = s.id ORDER BY m.id DESC LIMIT 1) AS last_sender,
            (SELECT COUNT(*) FROM chat_messages m WHERE m.chat_id = s.id AND m.sender != "admin" AND m.id > COALESCE((SELECT MAX(m2.id) FROM chat_messages m2 WHERE m2.chat_id = s.id AND m2.sender = "admin"), 0)) AS unread
       FROM chat_sessions s
       LEFT JOIN users u ON u.id = s.user_id
      WHERE s.status = "open"
      ORDER BY s.updated_at DESC
      LIMIT 150'
);

json_ok(['chats' => array_map(function ($r) {
    $r['last_message'] = $r['last_message'] !== null && str_starts_with((string) $r['last_message'], '::') ? '(localized automated message)' : $r['last_message'];
    return $r;
}, $rows)]);