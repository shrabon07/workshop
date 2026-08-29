<?php
/** Admin — Customers (users list with verification ticks + manual override). */
require_once __DIR__ . '/../config.php';
require_admin();

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

$ticks = ['none' => ['System', 'অটো'], 'red' => ['Red', 'লাল'], 'grey' => ['Grey', 'ধূসর'], 'green' => ['Green', 'সবুজ']];

function tick_dot($color) {
  $map = ['red' => 'tick-red', 'grey' => 'tick-grey', 'green' => 'tick-green'];
  $sym = ['red' => '✕', 'grey' => '✓', 'green' => '✔'];
  $cls = $map[$color] ?? 'tick-grey';
  return '<span class="tick-dot ' . $cls . '">' . ($sym[$color] ?? '?') . '</span>';
}

$PAGE_TITLE = 'Customers';
$ACTIVE = 'customers';
$LOAD_ADMIN_SERVICES = true;
require_once __DIR__ . '/inc/head.php';
?>
<div class="glass-strong rounded-3xl p-6">
  <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
    <p class="text-sm text-slate-400"><?= l('Override wins over the automatic verification result. Grey = ✓ (both verified).', 'ম্যানুয়াল ওভাররাইড অটো ফলাফলের উপর প্রাধান্য পায়। সবুজ = ✔ (উভয় ভেরিফাইড)।') ?></p>
    <form method="get" class="flex gap-2 w-full lg:w-auto">
      <input name="q" class="input !py-2.5 lg:w-64" value="<?= e($q) ?>" placeholder="<?= e(l_attr('Search name / email / phone…', 'নাম / ইমেইল / ফোন খুঁজুন…')) ?>">
      <button class="btn-ghost !py-2.5 !px-4 text-xs shrink-0">Go</button>
    </form>
  </div>

  <div class="mt-5 overflow-x-auto nice-scroll rounded-2xl border border-white/10">
    <table class="admin-table min-w-[760px]">
      <thead>
        <tr>
          <th><span class="e">Customer</span><span class="b">কাস্টমার</span></th>
          <th><span class="e">Email / Phone</span><span class="b">ইমেইল / ফোন</span></th>
          <th><span class="e">Tick</span></th>
          <th><span class="e">Email</span></th>
          <th><span class="e">Phone</span></th>
          <th><span class="e">Override</span><span class="b">ওভাররাইড</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u):
          $em  = $u['email_verified'] ? 'green' : 'red';
          $ph  = $u['whatsapp_verified'] ? 'green' : 'red';
          $tick = verification_tick($u);
          $tickCls = ['red' => 'tick-red', 'grey' => 'tick-grey', 'green' => 'tick-green'][$tick['key']];
        ?>
        <tr>
          <td>
            <div class="font-bold text-slate-100 text-sm flex items-center gap-2">
              <span class="w-9 h-9 rounded-full grid place-items-center text-xs font-extrabold bg-cyan-400/10 text-cyan-300 border border-cyan-400/20"><?= e(strtoupper(mb_substr($u['name'] ?: '?', 0, 1))) ?></span>
              <?= e($u['name']) ?>
              <?php if ($u['role'] === 'admin'): ?><span class="mini-badge"><?= l('ADMIN', 'এডমিন') ?></span><?php endif; ?>
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
            <select class="override-select input !py-1.5 !px-2 text-xs w-32" data-id="<?= (int) $u['id'] ?>">
              <?php foreach ($ticks as $tk => $lb): ?>
                <option value="<?= e($tk) ?>" <?= $u['admin_override'] === $tk ? 'selected' : '' ?>><?= e($lb[0]) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$users): ?>
        <tr><td colspan="6" class="text-center py-12 text-slate-500 text-sm"><?= l('No customers found.', 'কোনো কাস্টমার পাওয়া যায়নি।') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script defer src="<?= e(asset('js/admin-customers.js')) ?>?v=<?= $v ?>"></script>
<?php
require_once __DIR__ . '/inc/foot.php';
?>