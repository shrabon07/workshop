<?php
/** Admin — take over / release a chat from the bot. */

require_once __DIR__ . '/../../includes/admin-guard.php';

$chatId = (int) post('chat_id');
$action = (string) post('action'); // 'takeover' | 'release'

if (!$chatId || !in_array($action, ['takeover', 'release'], true)) {
    json_error('chat_id and action (takeover|release) are required.');
}

$chat = DB::get('SELECT * FROM chat_sessions WHERE id = ?', [$chatId]);
if (!$chat) {
    json_error('Chat not found.', 404);
}

if ($action === 'takeover') {
    DB::update('chat_sessions', ['bot_mode' => 0, 'admin_taken' => 1, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$chatId]);
    DB::insert('chat_messages', ['chat_id' => $chatId, 'sender' => 'bot', 'message' => '::chat_taken::']);
} else {
    DB::update('chat_sessions', ['bot_mode' => 1, 'admin_taken' => 0, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$chatId]);
}

json_ok(['action' => $action, 'bot_mode' => $action === 'takeover' ? 0 : 1, 'admin_taken' => $action === 'takeover' ? 1 : 0]);