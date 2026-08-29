<?php
/** Admin — fetch all messages for a chat session. */

require_once __DIR__ . '/../../includes/admin-guard.php';

$chatId = (int) get('chat_id');
if (!$chatId) {
    json_error('chat_id is required.');
}

$chat = DB::get(
    'SELECT s.*, u.name AS user_name, u.email AS user_email
       FROM chat_sessions s
       LEFT JOIN users u ON u.id = s.user_id
      WHERE s.id = ?',
    [$chatId]
);
if (!$chat) {
    json_error('Chat not found.', 404);
}

json_ok([
    'chat' => $chat,
    'messages' => DB::all('SELECT id, sender, message, created_at FROM chat_messages WHERE chat_id = ? ORDER BY id ASC', [$chatId]),
]);