<?php
/** Admin — Customers: verification ticks, manual override, edit, delete, single + bulk email. */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/countries.php';
require_admin();
$isSuper = is_super_admin();
$mailReady = admin_mail_ready();

$q = trim((string) get('q'));
$sql = 'SELECT u.*, v.email_verified, v.whatsapp_verified, v.admin_override
          FROM users u
          LEFT JOIN verification_status v ON v.user_id = u.id ';
if ($q !== '') {
  $like = "%$q%";
  $sql .= 'WHERE u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? ';
  $users = DB::all($sql . 'ORDER BY u.created_at DESC LIMIT 300', [$like, $like, $like]);
} else {
  $users = DB::all($sql . 'ORDER BY u.created_at DESC LIMIT 300');
}
foreach ($users as &$u) {
  $u['email_verified'] = (int) ($u['email_verified'] ?? 0);
  $u['whatsapp_verified'] = (int) ($u['whatsapp_verified'] ?? 0);
  $u['admin_override'] = $u['admin_override'] ?? 'none';
}
unset($u);

$ticks = ['none' => ['System (auto)', 'অটো'], 'red' => ['Not verified', 'যাচাই হয়নি'], 'grey' => ['Email only', 'শুধু ইমেইল'], 'green' => ['Fully verified', 'সম্পূর্ণ যাচাইকৃত']];

function tick_dot($color) {
  $map = ['red' => 'tick-red', 'grey' => 'tick-grey', 'green' => 'tick-green'];
  $sym = ['red' => '✕', 'grey' => '✓', 'green' => '✔'];
  $cls = $map[$color] ?? 'tick-grey';
  return '<span class="tick-dot ' . $cls . '">' . ($sym[$color] ?? '?') . '</span>';
}

$PAGE_TITLE = 'Customers';
$ACTIVE = 'customers';
require_once __DIR__ . '/inc/head.php';
?>
<div class="glass-strong rounded-3xl p-6">
  <?php if (!$mailReady): ?>
  <div class="mb-5 rounded-2xl border border-amber-400/25 bg-amber-400/10 p-4 text-sm text-amber-200">
    <span class="e">These customer tools are locked until you connect a <strong>verified mail sender</strong> —
      <a class="underline text-cyan-300 font-bold" href="<?= e(url('admin/mail-settings.php')) ?>">My mail sender</a>.</span>
    <span class="b">গ্রাহক টুলগুলো একটি <strong>যাচাইকৃত মেইল সেন্ডার</strong> যুক্ত না করা পর্যন্ত বন্ধ —
      <a class="underline text-cyan-300 font-bold" href="<?= e(url('admin/mail-settings.php')) ?>">আমার মেইল সেন্ডার</a>।</span>
  </div>
  <?php endif; ?>
  <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
    <p class="text-sm text-slate-400"><?= l('Add a customer, manually verify them, edit details, delete an account, or send one-off / bulk emails.', 'কাস্টমার যোগ করুন, ম্যানুয়ালি যাচাই করুন, তথ্য সম্পাদনা করুন, অ্যাকাউন্ট মুছুন, বা একক / বাল্ক ইমেইল পাঠান।') ?></p>
    <div class="flex flex-wrap gap-2 w-full lg:w-auto justify-start lg:justify-end">
      <button type="button" id="btn-add-customer" class="btn-teal !py-2.5 !px-4 text-xs" <?= $mailReady ? '' : 'disabled' ?>>
        <span class="e">+ Add customer</span><span class="b">+ কাস্টমার যোগ করুন</span>
      </button>
      <button type="button" id="btn-bulk-email" class="btn-accent !py-2.5 !px-4 text-xs" <?= $mailReady ? '' : 'disabled' ?>>
        <span class="e">Email all customers</span><span class="b">সব কাস্টমারে ইমেইল</span>
      </button>
      <form method="get" class="flex gap-2">
        <input name="q" class="input !py-2.5 lg:w-64" value="<?= e($q) ?>" placeholder="<?= e(l_attr('Search name / email / phone…', 'নাম / ইমেইল / ফোন খুঁজুন…')) ?>">
        <button class="btn-ghost !py-2.5 !px-4 text-xs shrink-0">Go</button>
      </form>
    </div>
  </div>

  <div class="mt-5 overflow-x-auto nice-scroll rounded-2xl border border-white/10">
    <table class="admin-table min-w-[980px]">
      <thead>
        <tr>
          <th><span class="e">Customer</span><span class="b">কাস্টমার</span></th>
          <th><span class="e">Email / Phone</span><span class="b">ইমেইল / ফোন</span></th>
          <th><span class="e">Tick</span></th>
          <th><span class="e">Email</span></th>
          <th><span class="e">Phone</span></th>
          <th><span class="e">Override</span><span class="b">ওভাররাইড</span></th>
          <th class="text-right"><span class="e">Actions</span><span class="b">অ্যাকশন</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u):
          $em  = $u['email_verified'] ? 'green' : 'red';
          $ph  = $u['whatsapp_verified'] ? 'green' : 'red';
          $tick = verification_tick($u);
          $tickCls = ['red' => 'tick-red', 'grey' => 'tick-grey', 'green' => 'tick-green'][$tick['key']];
          $isAdmin = $u['role'] === 'admin';
        ?>
        <tr data-user="<?= (int) $u['id'] ?>">
          <td>
            <div class="font-bold text-slate-100 text-sm flex items-center gap-2">
              <span class="w-9 h-9 rounded-full grid place-items-center text-xs font-extrabold bg-cyan-400/10 text-cyan-300 border border-cyan-400/20"><?= e(strtoupper(mb_substr($u['name'] ?: '?', 0, 1))) ?></span>
              <?= e($u['name']) ?>
              <?php if ($isAdmin): ?><span class="mini-badge"><?= l('ADMIN', 'এডমিন') ?></span><?php endif; ?>
            </div>
          </td>
          <td>
            <div class="text-xs text-slate-400"><?= e($u['email']) ?></div>
            <div class="text-xs text-slate-500">+<?= e($u['phone'] ?? '—') ?></div>
          </td>
          <td>
            <span class="tick-dot <?= $tickCls ?>" title="<?= ($tick['record']['admin_override'] ?? 'none') !== 'none' ? 'Manual override / ম্যানুয়াল' : '' ?>"><?= $tick['icon'] ?></span>
          </td>
          <td><?= tick_dot($em) ?></td>
          <td><?= tick_dot($ph) ?></td>
          <td>
            <select class="override-select input !py-1.5 !px-2 text-xs w-36" data-id="<?= (int) $u['id'] ?>" data-orig="<?= e($u['admin_override']) ?>" <?= $mailReady ? '' : 'disabled' ?>>
              <?php foreach ($ticks as $tk => $lb): ?>
                <option value="<?= e($tk) ?>" <?= $u['admin_override'] === $tk ? 'selected' : '' ?>><?= e($lb[0]) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td>
            <div class="flex items-center gap-1.5 justify-end">
              <button type="button" class="ov-update btn-teal !py-1.5 !px-2.5 !text-xs shrink-0" data-id="<?= (int) $u['id'] ?>" disabled data-loading-wrap="1"
                      title="<?= e(l_attr('Apply override', 'ওভাররাইড প্রযোজ্য করুন')) ?>" <?= $mailReady ? '' : 'disabled' ?>><?= l('Update', 'আপডেট') ?></button>
              <button type="button" class="cust-edit btn-ghost !py-1.5 !px-2.5 !text-xs shrink-0" data-id="<?= (int) $u['id'] ?>"
                      data-name="<?= e($u['name']) ?>" data-email="<?= e($u['email']) ?>" data-phone="<?= e($u['phone'] ?? '') ?>"
                      data-email-v="<?= $u['email_verified'] ? '1' : '0' ?>" data-whats-v="<?= $u['whatsapp_verified'] ? '1' : '0' ?>"
                      title="<?= e(l_attr('Edit', 'সম্পাদনা')) ?>" <?= $mailReady ? '' : 'disabled' ?>>✎</button>
              <button type="button" class="cust-email btn-ghost !py-1.5 !px-2.5 !text-xs shrink-0" data-id="<?= (int) $u['id'] ?>"
                      title="<?= e(l_attr('Send email', 'ইমেইল পাঠান')) ?>" <?= $mailReady ? '' : 'disabled' ?>>✉</button>
              <?php if (!$isAdmin && $isSuper): ?>
              <button type="button" class="cust-del btn-ghost !py-1.5 !px-2.5 !text-xs shrink-0 !border-rose-400/20 hover:!border-rose-400/50 hover:!text-rose-300" data-id="<?= (int) $u['id'] ?>"
                      title="<?= e(l_attr('Delete', 'মুছুন')) ?>">🗑</button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$users): ?>
        <tr><td colspan="7" class="text-center py-12 text-slate-500 text-sm"><?= l('No customers found.', 'কোনো কাস্টমার পাওয়া যায়নি।') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script defer src="<?= e(asset('js/admin-customers.js')) ?>?v=<?= $v ?>"></script>
<script>
  window.CUSTOMER_COUNTRIES = <?= json_encode(country_list()) ?>;
  window.CUSTOMER_COUNTRY_DEFAULT = 'Bangladesh';
</script>
<?php
require_once __DIR__ . '/inc/foot.php';
?>