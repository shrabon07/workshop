<?php
/** Admin — Mail List: archive of every custom mail composed & sent.
 *  Super admin reads all + can delete (removes from DB). A regular admin
 *  sees only their own sent mails (read-only). */
require_once __DIR__ . '/../config.php';
require_admin();
$isSuper = is_super_admin();

$logs = custom_mail_log_list(200);

$PAGE_TITLE = 'Mail List';
$ACTIVE = 'mail-log';
require_once __DIR__ . '/inc/head.php';
?>
<div class="glass-strong rounded-3xl p-6">
  <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
    <p class="text-sm text-slate-400">
      <?php if ($isSuper): ?>
        <?= l('Super admin: every custom mail sent across the team, with delete control. Deleting removes the record from the database.', 'সুপার অ্যাডমিন: দলের পাঠানো প্রতিটি কাস্টম মেইল, মুছে ফেলার সুবিধাসহ। মুছলে রেকর্ডটি ডেটাবেজ থেকেও চলে যায়।') ?>
      <?php else: ?>
        <?= l('Here you see only the custom mails you sent. Delete control belongs to the super admin.', 'এখানে আপনি কেবল নিজের পাঠানো কাস্টম মেইল দেখতে পাবেন। মুছে ফেলার সুবিধা সুপার অ্যাডমিনের।') ?>
      <?php endif; ?>
    </p>
  </div>

  <div class="mt-5 overflow-x-auto nice-scroll rounded-2xl border border-white/10">
    <table class="admin-table min-w-[900px]">
      <thead>
        <tr>
          <?php if ($isSuper): ?><th><span class="e">Sent by</span><span class="b">পাঠিয়েছেন</span></th><?php endif; ?>
          <th><span class="e">Recipients</span><span class="b">প্রাপক</span></th>
          <th><span class="e">Subject</span><span class="b">বিষয়</span></th>
          <th><span class="e">Message</span><span class="b">বার্তা</span></th>
          <th><span class="e">Sent</span><span class="b">প্রেরিত</span></th>
          <th><span class="e">Date</span><span class="b">তারিখ</span></th>
          <?php if ($isSuper): ?><th class="text-right"><span class="e">Actions</span><span class="b">অ্যাকশন</span></th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $m): ?>
        <tr data-log="<?= (int) $m['id'] ?>">
          <?php if ($isSuper): ?>
          <td>
            <div class="text-sm font-bold text-slate-100"><?= e($m['admin_name'] ?: 'Admin #' . (int) $m['admin_id']) ?></div>
            <div class="text-xs text-slate-500"><?= e($m['admin_email'] ?: '') ?></div>
          </td>
          <?php endif; ?>
          <td class="text-xs text-slate-400 max-w-[220px]"><?= e($m['recipients']) ?></td>
          <td class="text-sm font-bold text-slate-100 max-w-[200px]"><?= e($m['subject']) ?></td>
          <td class="text-xs text-slate-400 max-w-[260px]"><?= e(mb_strimwidth($m['message'], 0, 120, '…')) ?></td>
          <td><span class="mini-badge"><?= (int) $m['sent_count'] ?></span></td>
          <td class="text-xs text-slate-400"><?= e(date('M j, Y · H:i', strtotime($m['created_at']))) ?></td>
          <?php if ($isSuper): ?>
          <td class="text-right">
            <button type="button" class="log-del btn-ghost !py-1.5 !px-2.5 !text-xs shrink-0 !border-rose-400/20 hover:!border-rose-400/50 hover:!text-rose-300" data-id="<?= (int) $m['id'] ?>"
                    title="<?= e(l_attr('Delete (remove from database)', 'মুছুন (ডেটাবেজ থেকে মুছে ফেলুন)')) ?>">🗑</button>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php if (!$logs): ?>
        <tr><td colspan="<?= $isSuper ? 7 : 5 ?>" class="text-center py-12 text-slate-500 text-sm"><?= l('No mail sent yet — open Compose Mail.', 'এখনো কোনো মেইল পাঠানো হয়নি — মেইল রচনা খুলুন।') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php if ($isSuper): ?>
<script defer src="<?= e(asset('js/admin-mail-log.js')) ?>?v=<?= $v ?>"></script>
<?php endif; ?>
<?php
require_once __DIR__ . '/inc/foot.php';
?>
