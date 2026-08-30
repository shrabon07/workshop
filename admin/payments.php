<?php
/** Admin — send custom payment requests to customers, track & mark them paid. */
require_once __DIR__ . '/../config.php';
require_admin();
$isSuper = is_super_admin();
$mailReady = admin_mail_ready();

$customers = DB::all('SELECT id, name, email FROM users WHERE role = "customer" ORDER BY name');

$payData = [];
foreach ($customers as $c) {
  $orders = payable_orders((int) $c['id']);
  $payData[(int) $c['id']] = [
    'name'   => $c['name'],
    'orders' => array_map(function ($o) {
      return [
        'id'         => (int) $o['id'],
        'label'      => '#' . (int) $o['id'] . ' — ' . ($o['project_type'] ?: 'Custom'),
        'budget'     => (float) ($o['budget'] ?: 0),
        'has_unpaid' => (int) $o['has_unpaid'],
      ];
    }, $orders),
  ];
}

$reqs = all_payment_requests(200);

$PAGE_TITLE = 'Payments';
$ACTIVE = 'payments';
require_once __DIR__ . '/inc/head.php';
?>
<div class="space-y-6">
  <!-- ====== COMPOSER ====== -->
  <div class="glass-strong rounded-3xl p-6">
    <h2 class="font-bold text-white flex items-center gap-2">
      <span class="w-8 h-8 rounded-lg grid place-items-center text-sm bg-gradient-to-br from-brand-deep to-brand-light">💳</span>
      <span class="e">Send payment request</span><span class="b">পেমেন্ট রিকোয়েস্ট পাঠান</span>
    </h2>
    <p class="mt-1 text-sm text-slate-400"><?= l('Pick a customer — their active orders appear automatically. Override the amount, add a note if needed, then send.', 'গ্রাহক বাছুন — সক্রিয় অর্ডারগুলো অটো দেখা যাবে। প্রয়োজন হলে পরিমাণ বদলান, নোট লিখুন, পাঠান।') ?></p>

    <?php if (!$mailReady): ?>
      <div class="mt-4 rounded-2xl border border-amber-400/25 bg-amber-400/10 p-4 text-sm text-amber-200">
        <span class="e">Sending payment requests is locked until you connect a <strong>verified mail sender</strong> —
          <a class="underline text-cyan-300 font-bold" href="<?= e(url('admin/mail-settings.php')) ?>">My mail sender</a>. (Seen by the customer via email + notification.)</span>
        <span class="b">পেমেন্ট রিকোয়েস্ট পাঠানো একটি <strong>যাচাইকৃত মেইল সেন্ডার</strong> যুক্ত না করা পর্যন্ত বন্ধ —
          <a class="underline text-cyan-300 font-bold" href="<?= e(url('admin/mail-settings.php')) ?>">আমার মেইল সেন্ডার</a>।</span>
      </div>
    <?php endif; ?>

    <form id="pay-request-form" class="mt-5 grid gap-4" <?= $mailReady ? '' : 'data-locked="1"' ?>>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-bold text-slate-400 mb-1.5"><?= l('Customer', 'গ্রাহক') ?></label>
          <select id="pay-customer" class="input w-full !py-2.5" <?= $mailReady ? '' : 'disabled' ?>>
            <option value="">— <?= l('Select customer', 'গ্রাহক বাছুন') ?> —</option>
            <?php foreach ($payData as $cid => $cd): ?>
              <option value="<?= (int) $cid ?>"><?= e($cd['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-400 mb-1.5"><?= l('Active order', 'সক্রিয় অর্ডার') ?></label>
          <select id="pay-order" class="input w-full !py-2.5" disabled>
            <option value="">— <?= l('Select customer first', 'আগে গ্রাহক বাছুন') ?> —</option>
          </select>
        </div>
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-bold text-slate-400 mb-1.5"><?= l('Amount (override)', 'পরিমাণ (ওভাররাইড)') ?></label>
          <input id="pay-amount" type="number" step="0.01" min="1" class="input w-full !py-2.5" placeholder="0.00" required <?= $mailReady ? '' : 'disabled' ?>>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-400 mb-1.5"><?= l('Note for customer (optional)', 'গ্রাহকের জন্য নোট (ঐচ্ছিক)') ?></label>
          <input id="pay-note" type="text" maxlength="1000" class="input w-full !py-2.5" placeholder="<?= e(l_attr('e.g. 50% advance before we start…', 'যেমন: শুরু করার আগে ৫০% অগ্রিম…')) ?>" <?= $mailReady ? '' : 'disabled' ?>>
        </div>
      </div>
      <div class="flex items-center justify-between gap-3 flex-wrap">
        <p id="pay-hint" class="text-xs text-slate-500"><?= l('Orders that already have an unpaid request are hidden.', 'যে অর্ডারে আগেই অপরিশোধিত রিকোয়েস্ট আছে তা লুকানো থাকে।') ?></p>
        <button type="submit" id="pay-send" class="btn-accent !py-2.5 !px-5 text-xs" disabled>
          <span class="e">Send payment request</span><span class="b">পেমেন্ট রিকোয়েস্ট পাঠান</span>
        </button>
      </div>
    </form>
  </div>

  <!-- ====== REQUESTS LIST ====== -->
  <div class="glass-strong rounded-3xl p-6">
    <h2 class="font-bold text-white flex items-center gap-2">
      <span class="w-8 h-8 rounded-lg grid place-items-center text-sm bg-gradient-to-br from-accent-neon to-accent-electric">🧾</span>
      <span class="e">Payment requests</span><span class="b">পেমেন্টের তালিকা</span>
      <?php if ($reqs): ?><span class="mini-badge"><?= count($reqs) ?></span><?php endif; ?>
    </h2>

    <div class="mt-5 overflow-x-auto nice-scroll rounded-2xl border border-white/10">
      <table class="admin-table min-w-[860px]">
        <thead>
          <tr>
            <th><span class="e">Customer</span><span class="b">কাস্টমার</span></th>
            <th><span class="e">Order</span><span class="b">অর্ডার</span></th>
            <th><span class="e">Amount</span><span class="b">পরিমাণ</span></th>
            <th><span class="e">Note</span><span class="b">নোট</span></th>
            <th><span class="e">Created</span><span class="b">প্রেরিত</span></th>
            <th><span class="e">Status</span><span class="b">অবস্থা</span></th>
            <th class="text-right"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reqs as $r): ?>
          <tr data-req="<?= (int) $r['id'] ?>">
            <td class="font-bold text-slate-100 text-sm"><?= e($r['customer_name']) ?></td>
            <td>
              <div class="font-bold text-slate-200">#<?= (int) $r['order_id'] ?></div>
              <div class="text-xs text-slate-500"><?= e($r['project_type'] ?: 'Custom') ?></div>
            </td>
            <td class="font-extrabold text-cyan-300"><?= e(price_fmt((float) $r['amount'])) ?></td>
            <td class="text-xs text-slate-400 max-w-[260px]"><?= $r['note'] ? e(mb_strimwidth($r['note'], 0, 80, '…')) : '<span class="text-slate-600">—</span>' ?></td>
            <td class="text-xs text-slate-400"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
            <td>
              <?php if ($r['status'] === 'paid'): ?>
                <span class="pay-badge pay-paid">✔ <?= l('Paid', 'পরিশোধিত') ?>
                  <span class="block text-[10px] opacity-80"><?= $r['paid_at'] ? e(date('M j, Y', strtotime($r['paid_at']))) : '' ?></span>
                </span>
              <?php else: ?>
                <span class="pay-badge pay-unpaid">⚡ <?= l('Unpaid', 'অপরিশোধিত') ?></span>
              <?php endif; ?>
            </td>
            <td class="text-right">
              <?php if ($r['status'] !== 'paid'): ?>
                <?php if ($isSuper): ?>
                <button type="button" class="pay-paid-btn btn-teal !py-1.5 !px-3 !text-xs shrink-0" data-id="<?= (int) $r['id'] ?>">
                  <span class="e">Mark as paid</span><span class="b">পরিশোধিত করুন</span>
                </button>
                <?php else: ?>
                <span class="text-slate-500 text-xs" title="<?= e(l_attr('Only the super admin can approve payment status.', 'কেবল সুপার অ্যাডমিন পেমেন্ট অনুমোদন করতে পারেন।')) ?>">🔒</span>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-emerald-400/80 text-xs font-bold">✓</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$reqs): ?>
          <tr><td colspan="7" class="text-center py-12 text-slate-500 text-sm"><?= l('No payment requests yet — send one from above.', 'এখনো কোনো পেমেন্ট রিকোয়েস্ট নেই — উপরে থেকে পাঠান।') ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>window.PAY_DATA = <?= json_encode($payData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script defer src="<?= e(asset('js/admin-payments.js')) ?>?v=<?= $v ?>"></script>
<?php
require_once __DIR__ . '/inc/foot.php';
?>