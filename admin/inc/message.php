<?php
/* Single chat message bubble (expects $m with sender/message/created_at). */
$senderCls = $m['sender'] === 'admin' ? 'ab-admin ml-auto text-right'
           : ($m['sender'] === 'bot' ? 'ab-bot' : 'ab-guest');
$senderTag = $m['sender'] === 'admin' ? '🗣' : ($m['sender'] === 'bot' ? '🤖' : '');
$bodyOut = $m['message'];
if ($bodyOut !== null && str_starts_with((string) $bodyOut, '::')) {
    $keys = ['::chat_taken::' => ['Admin took over this chat.', 'এডমিন এই চ্যাট নিয়েছেন।'],
             '::chat_welcome::' => ['Welcome! How can we help?', 'স্বাগতম! কীভাবে সাহায্য করতে পারি?']];
    $bodyOut = $keys[$bodyOut][0] ?? '…';
}
?>
<div class="flex <?= $m['sender'] === 'admin' ? 'justify-end' : 'justify-start' ?>">
  <div class="ab <?= e($senderCls) ?>">
    <div class="text-[10px] uppercase tracking-wider opacity-60 mb-1"><?= e($senderTag ?: strtoupper($m['sender'])) ?> · <?= e(date('H:i', strtotime($m['created_at']))) ?></div>
    <div><?= nl2br(e($bodyOut)) ?></div>
  </div>
</div>