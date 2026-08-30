<?php
/** Admin — Customer orders (list, filter, status control). */
require_once __DIR__ . '/../config.php';
require_admin();
$isSuper = is_super_admin();
$mailReady = admin_mail_ready();

$status = get('status');
$q      = trim((string) get('q'));
$sql    = 'SELECT o.*,
                  COALESCE(u.name, o.name)  AS client_name,
                  COALESCE(u.email, o.email) AS client_email,
                  COALESCE(u.phone, o.phone) AS client_phone,
                  s.title_en AS svc_en, s.title_bn AS svc_bn
             FROM orders o
             LEFT JOIN users u ON u.id = o.user_id
             LEFT JOIN services s ON s.id = o.service_id ';
$p = [];

if ($status && $status !== 'all') { $sql .= ' WHERE o.status = ?'; $p[] = $status; }
if ($q !== '') {
    $sql .= ($status && $status !== 'all' ? ' AND' : ' WHERE') . ' (u.name LIKE ? OR o.name LIKE ? OR o.email LIKE ? OR s.title_en LIKE ?)';
    $like = "%$q%"; array_push($p, $like, $like, $like, $like);
}
$sql .= ' ORDER BY o.created_at DESC LIMIT 200';

$orders = DB::all($sql, $p);

$statuses = [
  'pending'     => ['Pending', 'অপেক্ষমাণ'],
  'in_progress' => ['In Progress', 'চলমান'],
  'delivered'   => ['Delivered', 'ডেলিভারি সম্পন্ন'],
  'cancelled'   => ['Cancelled', 'বাতিল'],
];

$PAGE_TITLE = 'Orders';
$ACTIVE = 'orders';
require_once __DIR__ . '/inc/head.php';
?>
<div class="glass-strong rounded-3xl p-6">
  <?php if (!$mailReady): ?>
  <div class="mb-5 rounded-2xl border border-amber-400/25 bg-amber-400/10 p-4 text-sm text-amber-200">
    <span class="e">Changing order status is locked until you connect a <strong>verified mail sender</strong> —
      <a class="underline text-cyan-300 font-bold" href="<?= e(url('admin/mail-settings.php')) ?>">My mail sender</a>.</span>
    <span class="b">অর্ডারের অবস্থা পরিবর্তন একটি <strong>যাচাইকৃত মেইল সেন্ডার</strong> যুক্ত না করা পর্যন্ত বন্ধ —
      <a class="underline text-cyan-300 font-bold" href="<?= e(url('admin/mail-settings.php')) ?>">আমার মেইল সেন্ডার</a>。</span>
  </div>
  <?php endif; ?>
  <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
    <div class="flex flex-wrap items-center gap-2">
      <div class="flex flex-wrap items-center gap-2" id="order-tabs">
        <a href="orders.php" class="st-tab rounded-xl px-3.5 py-2 text-xs font-bold <?= !$status || $status === 'all' ? 'st-tab-on' : 'text-slate-400 hover:text-white' ?>"><?= l('All', 'সব') ?></a>
        <?php foreach ($statuses as $st => $lb): ?>
          <a href="orders.php?status=<?= e($st) ?>" class="st-tab rounded-xl px-3.5 py-2 text-xs font-bold <?= $status === $st ? 'st-tab-on' : 'text-slate-400 hover:text-white' ?>"><?= l($lb[0], $lb[1]) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <form method="get" class="flex gap-2 w-full lg:w-auto">
      <input name="q" class="input !py-2.5 lg:w-64" value="<?= e($q) ?>" placeholder="<?= e(l_attr('Search client or service…', 'ক্লায়েন্ট বা সার্ভিস খুঁজুন…')) ?>">
      <button class="btn-ghost !py-2.5 !px-4 text-xs shrink-0">Go</button>
    </form>
  </div>

  <div class="mt-5 overflow-x-auto nice-scroll rounded-2xl border border-white/10">
    <table class="admin-table min-w-[900px]">
      <thead>
        <tr>
          <th>#</th>
          <th><span class="e">Client</span><span class="b">ক্লায়েন্ট</span></th>
          <th><span class="e">Service</span><span class="b">সার্ভিস</span></th>
          <th><span class="e">Budget</span><span class="b">বাজেট</span></th>
          <th><span class="e">Date</span><span class="b">তারিখ</span></th>
          <th><span class="e">Status</span><span class="b">অবস্থা</span></th>
          <th class="text-right"><span class="e">Update</span><span class="b">আপডেট</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td class="text-slate-500 font-mono text-xs">#<?= str_pad((string) $o['id'], 4, '0', STR_PAD_LEFT) ?></td>
          <td>
            <div class="font-bold text-slate-100 text-sm"><?= e($o['client_name'] ?: '—') ?></div>
            <div class="text-xs text-slate-500"><?= e($o['client_email'] ?: '') ?><?= isset($o['client_phone']) && $o['client_phone'] ? ' · ' . e($o['client_phone']) : '' ?></div>
          </td>
          <td><span class="text-xs font-semibold text-slate-300"><?= e($o['svc_en'] ?: 'Custom request') ?></span></td>
          <td class="font-extrabold text-slate-100">৳ <?= e(number_format((float) $o['budget'])) ?></td>
          <td class="text-xs text-slate-400"><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
          <td>
            <span class="st-badge <?= $o['status'] === 'delivered' ? 'st-active' : ($o['status'] === 'cancelled' ? 'st-archived' : 'st-inactive') ?>">
              <?= e($statuses[$o['status']][0] ?? $o['status']) ?>
            </span>
          </td>
          <td>
            <div class="flex items-center gap-2 justify-end">
              <?php if ($isSuper): ?>
              <button type="button" class="order-del btn-ghost !py-1.5 !px-2.5 !text-xs shrink-0 !border-rose-400/20 hover:!border-rose-400/50 hover:!text-rose-300" data-id="<?= (int) $o['id'] ?>"
                      title="<?= e(l_attr('Delete order', 'অর্ডার মুছুন')) ?>">🗑</button>
              <?php endif; ?>
              <select class="order-status input !py-1.5 !px-2 text-xs w-40" data-id="<?= (int) $o['id'] ?>" data-orig="<?= e($o['status']) ?>" <?= ($o['status'] === 'cancelled' || !$mailReady) ? 'disabled' : '' ?>>
                <?php foreach ($statuses as $st => $lb): ?>
                  <option value="<?= e($st) ?>" <?= $o['status'] === $st ? 'selected' : '' ?>><?= e($lb[0]) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="button" class="order-update btn-teal !py-1.5 !px-3 !text-xs shrink-0" data-id="<?= (int) $o['id'] ?>" disabled <?= ($o['status'] === 'cancelled' || !$mailReady) ? 'data-lock="1" disabled' : '' ?>>
                <?= l('Update', 'আপডেট') ?>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?>
        <tr><td colspan="7" class="text-center py-12 text-slate-500 text-sm"><?= l('No orders found.', 'কোনো অর্ডার পাওয়া যায়নি।') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script defer src="<?= e(asset('js/admin-orders.js')) ?>?v=<?= $v ?>"></script>
<?php
require_once __DIR__ . '/inc/foot.php';
?>