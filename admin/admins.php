<?php
/** Admin — Admins: staff accounts. Super admin only can create / edit / deactivate / delete admins. */
require_once __DIR__ . '/../config.php';
require_admin();

$me = current_user();
$isSuper = is_super_admin();
$admins = DB::all('SELECT id, name, email, is_active, is_super_admin, created_at FROM users WHERE role = "admin" ORDER BY id ASC');

$PAGE_TITLE = 'Admins';
$ACTIVE = 'admins';
require_once __DIR__ . '/inc/head.php';
?>
<div class="glass-strong rounded-3xl p-6">
  <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
    <p class="text-sm text-slate-400">
      <?php if ($isSuper): ?>
        <?= l('Super admin: you can create, edit, deactivate and delete admin accounts.', 'সুপার অ্যাডমিন: আপনি অ্যাডমিন অ্যাকাউন্ট তৈরি, সম্পাদনা, নিষ্ক্রিয় ও মুছতে পারেন।') ?>
      <?php else: ?>
        <?= l('Only the super admin can create, edit, deactivate or delete admins.', 'শুধু সুপার অ্যাডমিন অ্যাডমিন তৈরি, সম্পাদনা, নিষ্ক্রিয় বা মুছতে পারেন।') ?>
      <?php endif; ?>
    </p>
    <?php if ($isSuper): ?>
    <button type="button" id="btn-add-admin" class="btn-teal !py-2.5 !px-4 text-xs">
      <span class="e">+ Add admin</span><span class="b">+ অ্যাডমিন যোগ করুন</span>
    </button>
    <?php endif; ?>
  </div>

  <div class="mt-5 overflow-x-auto nice-scroll rounded-2xl border border-white/10">
    <table class="admin-table min-w-[820px]">
      <thead>
        <tr>
          <th><span class="e">Admin</span><span class="b">অ্যাডমিন</span></th>
          <th><span class="e">Email</span><span class="b">ইমেইল</span></th>
          <th><span class="e">Status</span><span class="b">অবস্থা</span></th>
          <th><span class="e">Since</span><span class="b">হিসেবে</span></th>
          <?php if ($isSuper): ?>
          <th class="text-right"><span class="e">Actions</span><span class="b">অ্যাকশন</span></th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($admins as $a):
          $isMe    = (int) $a['id'] === (int) $me['id'];
          $active  = (int) ($a['is_active'] ?? 1) === 1;
          $isSuperRow = (int) ($a['is_super_admin'] ?? 0) === 1;
        ?>
        <tr class="<?= $active ? '' : 'opacity-50' ?>">
          <td>
            <div class="font-bold text-slate-100 text-sm flex items-center gap-2">
              <span class="w-9 h-9 rounded-full grid place-items-center text-xs font-extrabold bg-cyan-400/10 text-cyan-300 border border-cyan-400/20"><?= e(strtoupper(mb_substr($a['name'] ?: '?', 0, 1))) ?></span>
              <?= e($a['name']) ?>
              <?php if ($isSuperRow): ?><span class="mini-badge"><?= l('SUPER ADMIN', 'সুপার অ্যাডমিন') ?></span><?php endif; ?>
              <?php if ($isMe): ?><span class="mini-badge"><?= l('YOU', 'আপনি') ?></span><?php endif; ?>
            </div>
          </td>
          <td class="text-xs text-slate-400"><?= e($a['email']) ?></td>
          <td>
            <?php if ($active): ?>
              <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-300"><span class="w-2 h-2 rounded-full bg-emerald-400"></span><?= l('Active', 'সক্রিয়') ?></span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-300"><span class="w-2 h-2 rounded-full bg-rose-400"></span><?= l('Deactivated', 'নিষ্ক্রিয়') ?></span>
            <?php endif; ?>
          </td>
          <td class="text-xs text-slate-500"><?= e(date('M j, Y', strtotime($a['created_at']))) ?></td>
          <?php if ($isSuper): ?>
          <td>
            <div class="flex items-center gap-1.5 justify-end">
              <button type="button" class="adm-edit btn-ghost !py-1.5 !px-2.5 !text-xs shrink-0" data-id="<?= (int) $a['id'] ?>"
                      data-name="<?= e($a['name']) ?>" data-email="<?= e($a['email']) ?>"
                      title="<?= e(l_attr('Edit', 'সম্পাদনা')) ?>">✎</button>
              <?php if (!$isMe): ?>
              <button type="button" class="adm-toggle btn-ghost !py-1.5 !px-2.5 !text-xs shrink-0 <?= $active ? '!text-amber-300' : '!text-emerald-300' ?>" data-id="<?= (int) $a['id'] ?>"
                      data-name="<?= e($a['name']) ?>" data-active="<?= $active ? '1' : '0' ?>"
                      title="<?= e(l_attr($active ? 'Deactivate' : 'Reactivate', $active ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন')) ?>"><?= $active ? '⏻' : '⏻' ?></button>
              <button type="button" class="adm-del btn-ghost !py-1.5 !px-2.5 !text-xs shrink-0 !border-rose-400/20 hover:!border-rose-400/50 hover:!text-rose-300" data-id="<?= (int) $a['id'] ?>"
                      data-name="<?= e($a['name']) ?>"
                      title="<?= e(l_attr('Delete', 'মুছুন')) ?>">🗑</button>
              <?php endif; ?>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php if (!$admins): ?>
        <tr><td colspan="<?= $isSuper ? 5 : 4 ?>" class="text-center py-12 text-slate-500 text-sm"><?= l('No admin accounts found.', 'কোনো অ্যাডমিন অ্যাকাউন্ট পাওয়া যায়নি।') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($isSuper): ?>
  <div class="mt-5 rounded-2xl border border-cyan-400/15 bg-cyan-400/5 px-5 py-4 text-xs text-slate-400 leading-relaxed">
    <b class="text-cyan-300"><?= l('Security note', 'নিরাপত্তা নোট') ?>:</b>
    <?= l('Deactivating an admin locks them out of the panel immediately. You cannot deactivate or delete your own super admin account.', 'অ্যাডমিন নিষ্ক্রিয় করলে সাথে সাথে প্যানেল অ্যাক্সেস বন্ধ হয়ে যায়। আপনি নিজের সুপার অ্যাডমিন অ্যাকাউন্ট নিষ্ক্রিয় বা মুছতে পারবেন না।') ?>
  </div>
  <?php endif; ?>
</div>
<script defer src="<?= e(asset('js/admin-admins.js')) ?>?v=<?= $v ?>"></script>
<?php
require_once __DIR__ . '/inc/foot.php';
?>