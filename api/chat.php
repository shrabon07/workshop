<?php
/**
 * Public Live Chat API — powers the floating glass widget.
 *   open   → create/resume a session (bot greeting on first visit)
 *   send   → append a guest message + auto bot reply / handoff
 *   read   → poll new messages after an id
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/bot.php';

// Messages that literally start with "::" are dictionary keys the client
// resolves via i18n.js → the bot greets in the visitor's active language.
const CHAT_I18N_MARK = '::';

function chat_token(bool $must = true): string
{
    $t = post('token') ?: get('token');
    if (empty($t) && !empty($_SESSION['chat_token'])) {
        $t = $_SESSION['chat_token'];
    }
    if (empty($t) && $must) {
        json_error('Missing chat token.', 422);
    }
    return (string) $t;
}

/** Guests must give their name before chatting — 1 until the session has one. */
function chat_need_name(array $row): int
{
    return (int) (trim((string) ($row['guest_name'] ?? '')) === '');
}

function chat_session(string $token): array
{
    $row = DB::get('SELECT * FROM chat_sessions WHERE session_token = ?', [$token]);
    $isNew = !$row;
    if (!$row) {
        $id = DB::insert('chat_sessions', ['session_token' => $token, 'bot_mode' => 1, 'admin_taken' => 0, 'status' => 'open']);
        $row = DB::get('SELECT * FROM chat_sessions WHERE id = ?', [$id]);
    }
    // attach / refresh created_at-less metadata each open; logged-in users are
    // identified by their account, so they get a name automatically.
    if (is_logged_in() && empty($row['user_id'])) {
        DB::update('chat_sessions', ['user_id' => current_user_id()], 'id = ?', [$row['id']]);
        $row['user_id'] = current_user_id();
    }
    if (is_logged_in() && chat_need_name($row) && !empty($row['user_id'])) {
        $u = DB::get('SELECT name FROM users WHERE id = ?', [(int) $row['user_id']]);
        if (!empty($u['name'])) {
            DB::update('chat_sessions', ['guest_name' => $u['name']], 'id = ?', [$row['id']]);
            $row['guest_name'] = $u['name'];
        }
    }
    $_SESSION['chat_token'] = $token;
    if ($isNew) {
        DB::insert('chat_messages', ['chat_id' => $row['id'], 'sender' => 'bot', 'message' => CHAT_I18N_MARK . 'chat_bot_hi' . CHAT_I18N_MARK]);
        DB::insert('chat_messages', ['chat_id' => $row['id'], 'sender' => 'bot', 'message' => CHAT_I18N_MARK . 'chat_bot_menu' . CHAT_I18N_MARK]);
    }
    return $row;
}

function chat_messages(int $chatId, int $after = 0): array
{
    return DB::all('SELECT id, sender, message, created_at FROM chat_messages WHERE chat_id = ? AND id > ? ORDER BY id ASC LIMIT 200', [$chatId, $after]);
}

function action_open(): void
{
    $token = chat_token(true);
    if (strlen($token) < 8) {
        json_error('Invalid token.', 422);
    }
    $s = chat_session($token);
    json_ok([
        'chat_id'  => (int) $s['id'],
        'token'    => $token,
        'need_name' => chat_need_name($s),
        'guest_name' => (string) ($s['guest_name'] ?? ''),
        'bot_mode' => (int) $s['bot_mode'],
        'admin_taken' => (int) $s['admin_taken'],
        'messages' => chat_messages((int) $s['id']),
    ]);
}

function action_set_name(): void
{
    $token = chat_token(true);
    $name  = trim((string) post('name'));
    $name  = trim(preg_replace('/[\x00-\x1F\x7F]|<[^>]*>/', '', $name));
    $len   = mb_strlen($name);
    if ($len < 2 || $len > 60) {
        json_error('Please enter your name (2–60 characters).', 422);
    }
    $s = DB::get('SELECT * FROM chat_sessions WHERE session_token = ?', [$token]);
    if (!$s) {
        $s = chat_session($token);
    }
    DB::update('chat_sessions', ['guest_name' => $name], 'id = ?', [(int) $s['id']]);
    $s['guest_name'] = $name;
    json_ok([
        'need_name'  => 0,
        'guest_name' => $name,
        'bot_mode'   => (int) $s['bot_mode'],
        'admin_taken' => (int) $s['admin_taken'],
        'messages'   => chat_messages((int) $s['id']),
    ]);
}

function action_send(): void
{
    $token  = chat_token(true);
    $msg    = trim((string) post('message'));
    if ($msg === '') {
        json_error('Message is empty.', 422);
    }
    if (mb_strlen($msg) > 1500) {
        json_error('Message too long.', 422);
    }
    $s = chat_session($token);
    if (chat_need_name($s)) {
        json_ok([
            'need_name'  => 1,
            'bot_mode'   => (int) $s['bot_mode'],
            'admin_taken' => (int) $s['admin_taken'],
            'messages'   => chat_messages((int) $s['id']),
            'last_id'    => 0,
        ]);
        return;
    }
    DB::insert('chat_messages', ['chat_id' => $s['id'], 'sender' => 'guest', 'message' => $msg]);

    $out = ['bot_mode' => (int) $s['bot_mode'], 'admin_taken' => (int) $s['admin_taken'], 'suggestions' => []];

    if ($s['bot_mode']) {
        $ctx = ['user_id' => !empty($s['user_id']) ? (int) $s['user_id'] : null];
        $res = bot_reply($msg, $ctx);
        DB::insert('chat_messages', ['chat_id' => $s['id'], 'sender' => 'bot', 'message' => $res['reply']]);
        $out['suggestions'] = $res['suggestions'] ?? [];
        if (!empty($res['handoff'])) {
            DB::update('chat_sessions', ['bot_mode' => 0, 'admin_taken' => 1], 'id = ?', [$s['id']]);
            DB::insert('chat_messages', ['chat_id' => $s['id'], 'sender' => 'bot', 'message' => CHAT_I18N_MARK . 'chat_taken' . CHAT_I18N_MARK]);
            $out['bot_mode'] = 0;
            $out['admin_taken'] = 1;
        }
    }

    json_ok(array_merge($out, ['messages' => chat_messages((int) $s['id'])], ['last_id' => (int) ($s['id'] ?? 0)]));
}

function action_read(): void
{
    $token = chat_token(true);
    $after = max(0, (int) (get('after') ?: post('after')));
    $s = DB::get('SELECT * FROM chat_sessions WHERE session_token = ?', [$token]);
    if (!$s) {
        json_ok(['messages' => [], 'bot_mode' => 1, 'admin_taken' => 0, 'last_id' => 0]);
    }
    $msgs = chat_messages((int) $s['id'], $after);
    $last = count($msgs) ? (int) end($msgs)['id'] : $after;
    json_ok([
        'messages' => $msgs,
        'need_name' => chat_need_name($s),
        'guest_name' => (string) ($s['guest_name'] ?? ''),
        'bot_mode' => (int) $s['bot_mode'],
        'admin_taken' => (int) $s['admin_taken'],
        'last_id'  => $last,
    ]);
}

switch (get('action') ?: post('action')) {
    case 'open': action_open(); break;
    case 'set_name': action_set_name(); break;
    case 'send': action_send(); break;
    case 'read': action_read(); break;
    default: json_error('Unknown action.', 404);
}