<?php
/** Admin — fetch all messages for a chat session. */

require_once __DIR__ . '/../../includes/admin-guard.php';

$chatId = (int) get('chat_id');
if (!$chatId) {
    json_error('chat_id is required.');
}

$chat = DB::get('SELECT * FROM chat_sessions WHERE id = ?', [$chatId]);
if (!$chat) {
    json_error('Chat not found.', 404);
}

json_ok([
    'chat' => $chat,
    'messages' => DB::all('SELECT id, sender, message, created_at FROM chat_messages WHERE chat_id = ? ORDER BY id ASC', [$chatId]),
]);