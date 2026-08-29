<?php
/** Admin — reply to a chat (takes it over from the bot automatically). */

require_once __DIR__ . '/../../includes/admin-guard.php';

$chatId = (int) post('chat_id');
$msg    = trim((string) post('message'));

if (!$chatId || $msg === '') {
    json_error('chat_id and message are required.');
}
if (mb_strlen($msg) > 2000) {
    json_error('Message too long.');
}

$chat = DB::get('SELECT id FROM chat_sessions WHERE id = ?', [$chatId]);
if (!$chat) {
    json_error('Chat not found.', 404);
}

DB::insert('chat_messages', ['chat_id' => $chatId, 'sender' => 'admin', 'message' => $msg]);
DB::update('chat_sessions', ['bot_mode' => 0, 'admin_taken' => 1, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$chatId]);

json_ok(['id' => (int) DB::pdo()->lastInsertId()]);