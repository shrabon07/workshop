<?php
/** Admin — Live chat hub (open sessions, conversation, takeover/reply). */
require_once __DIR__ . '/../config.php';
require_admin();

$querySessions = function () {
    return DB::all(
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
};
$sessions = $querySessions();

/* if a session id was passed, preload it */
$activeId = isset($_GET['chat_id']) ? (int) $_GET['chat_id'] : 0;
$activeChat = null;
$messages = [];
if ($activeId) {
    $activeChat = DB::get('SELECT * FROM chat_sessions WHERE id = ?', [$activeId]);
    if ($activeChat) {
        $messages = DB::all('SELECT * FROM chat_messages WHERE chat_id = ? ORDER BY id ASC', [$activeId]);
    }
}

$PAGE_TITLE = 'Live Chats';
$ACTIVE = 'chats';
$LOAD_ADMIN_CHATS = true;
require_once __DIR__ . '/inc/head.php';
?>
<div class="grid gap-6 lg:grid-cols-[320px_1fr] h-[calc(100vh-190px)] min-h-[480px]">

  <!-- session list -->
  <div class="glass-strong rounded-3xl flex flex-col overflow-hidden">
    <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
      <h2 class="font-bold text-white text-sm flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <?= l('Live sessions', 'সক্রিয় চ্যাট') ?>
      </h2>
      <span id="chat-count" class="mini-badge"><?= count($sessions) ?></span>
    </div>
    <div id="chat-sessions" class="overflow-y-auto nice-scroll flex-1">
      <?php foreach ($sessions as $s): ?>
        <?php
        $senderSym = $s['last_sender'] === 'admin' ? '▸' : ($s['last_sender'] === 'bot' ? '●' : '◂');
        $lastMsg   = $s['last_message'] !== null && str_starts_with((string) $s['last_message'], '::') ? '(i18n auto msg)' : $s['last_message'];
        ?>
        <button class="w-full text-left px-4 py-3.5 border-b border-white/5 hover:bg-white/5 transition-colors session-row <?= $s['id'] === $activeId ? 'bg-cyan-400/10' : '' ?>" data-chat-id="<?= (int) $s['id'] ?>">
          <div class="flex items-center justify-between gap-2">
            <span class="font-bold text-slate-100 text-sm truncate"><?= e($s['user_name'] ?: 'Guest') ?></span>
            <span class="text-[10px] text-slate-500 shrink-0"><?= e(date('H:i', strtotime($s['updated_at']))) ?></span>
          </div>
          <div class="text-[11px] text-slate-500 mb-1.5"><?= e((int) $s['bot_mode'] ? '🤖 bot-mode' : ($s['admin_taken'] ? '👨‍💻 live admin' : '•••')) ?> · +<?= e($s['phone'] ?? '—') ?></div>
          <div class="flex justify-between items-center gap-2">
            <span class="truncate text-xs <?= (int) $s['unread'] ? 'text-slate-200 font-semibold' : 'text-slate-500' ?>"><span class="mr-1 animate-pulse"><?= $senderSym ?></span><?= e(mb_strimwidth((string) $lastMsg, 0, 46, '…')) ?></span>
            <?php if ((int) $s['unread']): ?><span class="mini-badge shrink-0"><?= (int) $s['unread'] ?></span><?php endif; ?>
          </div>
        </button>
      <?php endforeach; ?>
      <?php if (!$sessions): ?>
        <div class="py-14 text-center text-slate-500 text-xs px-6"><?= l('No live sessions. Visitors opening the chat will appear here.', 'এখন কোনো সক্রিয় চ্যাট নেই। ভিজিটর চ্যাট খুললে এখানে দেখা যাবে।') ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- conversation -->
  <div class="glass-strong rounded-3xl flex flex-col overflow-hidden">
    <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between gap-3 flex-wrap">
      <div id="chat-head-label" class="font-bold text-white text-sm">
        <?php if ($activeChat): ?>
          <?= e($activeChat['user_name'] ?: 'Guest') ?>
          <span class="text-[11px] text-slate-400 font-normal ml-2">+<?= e($activeChat['phone'] ?? '—') ?> · <?= e((int) $activeChat['bot_mode'] ? 'bot' : 'manual') ?></span>
        <?php else: ?>
          <?= l('Select a conversation', 'একটি কথোপকথন নির্বাচন করুন') ?>
        <?php endif; ?>
      </div>
      <?php if ($activeChat): ?>
      <div class="flex gap-2">
        <button id="takeover-btn" class="btn-ghost !py-2 !px-4 text-xs" data-chat-id="<?= (int) $activeChat['id'] ?>" data-action="<?= (int) $activeChat['admin_taken'] ? 'release' : 'takeover' ?>">
          <?= (int) $activeChat['admin_taken'] ? l('Release to bot', 'বটে ছেড়ে দিন') : l('Take over', 'নিয়ন্ত্রণ নিন') ?>
        </button>
      </div>
      <?php endif; ?>
    </div>

    <div id="chat-messages" class="flex-1 overflow-y-auto nice-scroll px-5 py-4 space-y-3">
      <?php foreach ($messages as $m): ?>
        <?php require __DIR__ . '/inc/message.php'; ?>
      <?php endforeach; ?>
      <?php if (!$activeChat): ?>
        <div class="grid place-items-center h-full text-slate-500 text-sm"><?= l('Pick a session on the left to view the conversation.', 'কথোপকথন দেখতে বাম পাশের সেশন বেছে নিন।') ?></div>
      <?php endif; ?>
    </div>

    <div class="p-4 border-t border-white/10 bg-white/[.03]">
      <form id="reply-form" class="flex gap-2 items-end">
        <textarea id="reply-input" rows="1" class="input !rounded-2xl flex-1 resize-none" placeholder="<?= l('Write a reply… (Send takes over from bot)', 'উত্তর লিখুন… (পাঠান বট থেকে নিয়ন্ত্রণ নেবে)') ?>" <?= $activeChat ? 'data-chat-id="' . (int) $activeChat['id'] . '"' : 'disabled' ?>></textarea>
        <button type="submit" class="btn-teal !py-3.5 !px-5 text-xs shrink-0" <?= $activeChat ? '' : 'disabled' ?>>➤</button>
      </form>
    </div>
  </div>
</div>
<?php
require_once __DIR__ . '/inc/foot.php';
?>