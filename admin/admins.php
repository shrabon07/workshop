<?php
/** Admin — Admins: manage staff accounts. Only admins can create other admins. */
require_once __DIR__ . '/../config.php';
require_admin();

$me = current_user();
$admins = DB::all('SELECT id, name, email, created_at FROM users WHERE role = "admin" ORDER BY id ASC');

$PAGE_TITLE = 'Admins';
$ACTIVE = 'admins';
require_once __DIR__ . '/inc/head.php';
?>
<div class="glass-strong rounded-3xl p-6">
  <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
    <p class="text-sm text-slate-400"><?= l('Staff accounts with full admin access. Only an admin can create another admin.', 'সম্পূর্ণ অ্যাডমিন অ্যাক্সেসসহ স্টাফ অ্যাকাউন্ট। শুধু একজন অ্যাডমিন অন্য অ্যাডমিন তৈরি করতে পারেন।') ?></p>
    <button type="button" id="btn-add-admin" class="btn-teal !py-2.5 !px-4 text-xs">
      <span class="e">+ Add admin</span><span class="b">+ অ্যাডমিন যোগ করুন</span>
    </button>
  </div>

  <div class="mt-5 overflow-x-auto nice-scroll rounded-2xl border border-white/10">
    <table class="admin-table min-w-[640px]">
      <thead>
        <tr>
          <th><span class="e">Admin</span><span class="b">অ্যাডমিন</span></th>
          <th><span class="e">Email</span><span class="b">ইমেইল</span></th>
          <th><span class="e">Since</span><span class="b">হিসেবে</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($admins as $a): $isMe = (int) $a['id'] === (int) $me['id']; ?>
        <tr>
          <td>
            <div class="font-bold text-slate-100 text-sm flex items-center gap-2">
              <span class="w-9 h-9 rounded-full grid place-items-center text-xs font-extrabold bg-cyan-400/10 text-cyan-300 border border-cyan-400/20"><?= e(strtoupper(mb_substr($a['name'] ?: '?', 0, 1))) ?></span>
              <?= e($a['name']) ?>
              <?php if ($isMe): ?><span class="mini-badge"><?= l('YOU', 'আপনি') ?></span><?php endif; ?>
            </div>
          </td>
          <td class="text-xs text-slate-400"><?= e($a['email']) ?></td>
          <td class="text-xs text-slate-500"><?= e(date('M j, Y', strtotime($a['created_at']))) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$admins): ?>
        <tr><td colspan="3" class="text-center py-12 text-slate-500 text-sm"><?= l('No admin accounts found.', 'কোনো অ্যাডমিন অ্যাকাউন্ট পাওয়া যায়নি।') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-5 rounded-2xl border border-cyan-400/15 bg-cyan-400/5 px-5 py-4 text-xs text-slate-400 leading-relaxed">
    <b class="text-cyan-300"><?= l('Security note', 'নিরাপত্তা নোট') ?>:</b>
    <?= l('New admins get full access to orders, payments, customers and staff accounts. Only share admin access with your own team.', 'নতুন অ্যাডমিন অর্ডার, পেমেন্ট, কাস্টমার ও স্টাফ অ্যাকাউন্টের সম্পূর্ণ অ্যাক্সেস পায়। শুধু নিজের টিমকে অ্যাডমিন অ্যাক্সেস দিন।') ?>
  </div>
</div>
<script defer src="<?= e(asset('js/admin-admins.js')) ?>?v=<?= $v ?>"></script>
<?php
require_once __DIR__ . '/inc/foot.php';
?>