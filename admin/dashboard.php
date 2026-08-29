<?php
/** Admin — dashboard (KPIs + latest orders/chats + top services). */
require_once __DIR__ . '/../config.php';
require_admin();

$stats = [
    'orders'        => (int) DB::value('SELECT COUNT(*) FROM orders'),
    'orders_pending'=> (int) DB::value('SELECT COUNT(*) FROM orders WHERE status = "pending"'),
    'revenue'       => (float) DB::value('SELECT COALESCE(SUM(budget),0) FROM orders WHERE status IN ("delivered")'),
    'customers'     => (int) DB::value('SELECT COUNT(*) FROM users WHERE role = "customer"'),
    'verified_full' => (int) DB::value('SELECT COUNT(*) FROM verification_status WHERE whatsapp_verified = 1 AND email_verified = 1'),
    'open_chats'    => (int) DB::value('SELECT COUNT(*) FROM chat_sessions WHERE status = "open"'),
    'live_services' => (int) DB::value('SELECT COUNT(*) FROM services WHERE status = "active"'),
];

$recentOrders = DB::all(
    'SELECT o.*, COALESCE(u.name, o.name) AS client_name, s.title_en AS svc_en
       FROM orders o
       LEFT JOIN users u ON u.id = o.user_id
       LEFT JOIN services s ON s.id = o.service_id
      ORDER BY o.created_at DESC LIMIT 6'
);
$recentChats = DB::all(
    'SELECT s.*, u.name AS user_name, u.email AS user_email
       FROM chat_sessions s
       LEFT JOIN users u ON u.id = s.user_id
      WHERE s.status = "open"
      ORDER BY s.updated_at DESC LIMIT 5'
);
$topServices = DB::all(
    'SELECT s.title_en, s.price, COUNT(o.id) AS cnt
       FROM services s
       LEFT JOIN orders o ON o.service_id = s.id
      WHERE s.status = "active"
      GROUP BY s.id ORDER BY cnt DESC, s.sort_order ASC LIMIT 5'
);

$statuses = ['pending' => 'st-inactive', 'in_progress' => 'st-inactive', 'delivered' => 'st-active', 'cancelled' => 'st-archived'];

$cards = [
    ['label_en' => 'Total orders', 'label_bn' => 'মোট অর্ডার', 'value' => number_format($stats['orders']), 'icon' => '◷', 'glow' => 'from-cyan-500/15 to-teal-500/5 border-cyan-400/20', 'text' => 'text-cyan-300'],
    ['label_en' => 'Pending now', 'label_bn' => 'অপেক্ষমাণ', 'value' => number_format($stats['orders_pending']), 'icon' => '◷', 'glow' => 'from-amber-500/15 to-orange-500/5 border-amber-400/20', 'text' => 'text-amber-300'],
    ['label_en' => 'Customers', 'label_bn' => 'কাস্টমার', 'value' => number_format($stats['customers']), 'icon' => '◉', 'glow' => 'from-indigo-500/15 to-violet-500/5 border-indigo-400/20', 'text' => 'text-indigo-300'],
    ['label_en' => 'Fully verified', 'label_bn' => 'সম্পূর্ণ যাচাই', 'value' => number_format($stats['verified_full']) . ' ✓', 'icon' => '✔', 'glow' => 'from-emerald-500/15 to-green-500/5 border-emerald-400/20', 'text' => 'text-emerald-300'],
    ['label_en' => 'Live services', 'label_bn' => 'সক্রিয় সার্ভিস', 'value' => number_format($stats['live_services']), 'icon' => '▤', 'glow' => 'from-teal-500/15 to-cyan-500/5 border-teal-400/20', 'text' => 'text-teal-300'],
    ['label_en' => 'Open chats', 'label_bn' => 'সক্রিয় চ্যাট', 'value' => number_format($stats['open_chats']), 'icon' => '✉', 'glow' => 'from-fuchsia-500/15 to-pink-500/5 border-fuchsia-400/20', 'text' => 'text-fuchsia-300'],
];

$PAGE_TITLE = 'Dashboard';
$ACTIVE = 'dashboard';
require_once __DIR__ . '/inc/head.php';
?>

<!-- KPI cards -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
  <?php foreach ($cards as $c): ?>
    <div class="glass rounded-3xl p-5 grad-border fade-swap overflow-hidden relative">
      <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-gradient-to-br <?= e($c['glow']) ?> blur-2xl"></div>
      <div class="text-2xl opacity-80"><?= $c['icon'] ?></div>
      <div class="mt-3 text-2xl font-extrabold <?= e($c['text']) ?>"><?= e($c['value']) ?></div>
      <div class="mt-1 text-[11px] uppercase tracking-wider text-slate-500"><?= l($c['label_en'], $c['label_bn']) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">

  <!-- recent orders -->
  <section class="xl:col-span-2 glass-strong rounded-3xl p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-white"><?= l('Latest orders', 'সাম্প্রতিক অর্ডার') ?></h2>
      <a href="orders.php" class="text-xs font-bold text-cyan-300 hover:text-cyan-200 transition-colors"><?= l('View all', 'সব দেখুন') ?> →</a>
    </div>
    <div class="overflow-x-auto nice-scroll rounded-2xl border border-white/10">
      <table class="admin-table min-w-[640px]">
        <thead><tr><th>#</th><th><span class="e">Client</span><span class="b">ক্লায়েন্ট</span></th><th><span class="e">Service</span><span class="b">সার্ভিস</span></th><th class="text-right"><?= l('Budget', 'বাজেট') ?></th><th><span class="e">Status</span><span class="b">অবস্থা</span></th></tr></thead>
        <tbody>
          <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td class="text-slate-500 font-mono text-xs">#<?= str_pad((string) $o['id'], 4, '0', STR_PAD_LEFT) ?></td>
            <td class="text-slate-200 text-xs font-semibold"><?= e($o['client_name'] ?: '—') ?></td>
            <td class="text-slate-400 text-xs"><?= e($o['svc_en'] ?: 'Custom') ?></td>
            <td class="text-right font-extrabold text-slate-100">৳ <?= e(number_format((float) $o['budget'])) ?></td>
            <td><span class="st-badge <?= e($statuses[$o['status']] ?? 'st-inactive') ?>"><?= e(ucwords(str_replace('_', ' ', $o['status']))) ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$recentOrders): ?><tr><td colspan="5" class="text-center py-10 text-slate-500 text-sm"><?= l('No orders yet.', 'এখনো কোনো অর্ডার নেই।') ?></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- side rail -->
  <aside class="space-y-6">
    <section class="glass-strong rounded-3xl p-6">
      <h2 class="font-bold text-white mb-4"><?= l('Live chats', 'লাইভ চ্যাট') ?></h2>
      <?php if ($recentChats): ?>
        <div class="space-y-2">
          <?php foreach ($recentChats as $c): ?>
            <a href="support.php?chat_id=<?= (int) $c['id'] ?>" class="flex items-center gap-3 rounded-2xl glass-chip px-3.5 py-3 hover:bg-white/5 transition-colors">
              <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0 <?= (int) $c['bot_mode'] ? '' : 'animate-pulse' ?>"></span>
              <span class="min-w-0">
                <span class="block text-xs font-bold text-slate-100 truncate"><?= e($c['user_name'] ?? 'Guest') ?></span>
                <span class="block text-[11px] text-slate-500 truncate"><?= e($c['user_email'] ?? ('+' . $c['phone'])) ?><?= (int) $c['bot_mode'] ? ' · 🤖' : '' ?></span>
              </span>
              <span class="ml-auto text-[10px] text-slate-600"><?= e(date('H:i', strtotime($c['updated_at']))) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-xs text-slate-500"><?= l('No live chats right now.', 'এখন কোনো লাইভ চ্যাট নেই।') ?></p>
      <?php endif; ?>
      <a href="support.php" class="mt-4 block text-center btn-ghost !py-2.5 text-xs"><?= l('Open chat hub', 'চ্যাট হাব খুলুন') ?></a>
    </section>

    <section class="glass-strong rounded-3xl p-6">
      <h2 class="font-bold text-white mb-4"><?= l('Top services', 'জনপ্রিয় সার্ভিস') ?></h2>
      <?php if ($topServices): ?>
        <div class="space-y-3">
          <?php foreach ($topServices as $i => $ts): ?>
            <div class="flex items-center gap-3">
              <span class="w-7 h-7 rounded-lg grid place-items-center text-[11px] font-extrabold bg-cyan-400/10 text-cyan-300 border border-cyan-400/20"><?= $i + 1 ?></span>
              <span class="flex-1 text-xs font-bold text-slate-200 truncate"><?= e($ts['title_en']) ?></span>
              <span class="text-xs font-extrabold text-amber-300"><?= (int) $ts['cnt'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-xs text-slate-500"><?= l('No orders to rank yet.', 'র্যাংক করার মতো অর্ডার নেই।') ?></p>
      <?php endif; ?>
    </section>
  </aside>
</div>
<?php
require_once __DIR__ . '/inc/foot.php';
?>