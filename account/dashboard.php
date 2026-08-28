<?php
/** Customer dashboard — orders, verification status, chat history. */
require_once __DIR__ . '/../config.php';

require_login();
$user = current_user();
if ($user['role'] === 'admin') {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$orders = DB::all('SELECT o.*, COALESCE(s.title_en, o.project_type) AS svc_en, COALESCE(s.title_bn, o.project_type) AS svc_bn
                     FROM orders o
                     LEFT JOIN services s ON s.id = o.service_id
                    WHERE o.user_id = ?
                    ORDER BY o.id DESC
                    LIMIT 50', [$user['id']]);

$chats = DB::all(
    'SELECT cs.*,
            (SELECT COUNT(*) FROM chat_messages m WHERE m.chat_id = cs.id) AS msg_count,
            (SELECT m.message FROM chat_messages m WHERE m.chat_id = cs.id ORDER BY m.id DESC LIMIT 1) AS last_message
       FROM chat_sessions cs
      WHERE cs.user_id = ?
      ORDER BY cs.updated_at DESC
      LIMIT 10', [$user['id']]);

$tick = verification_tick($user);

$PAGE_TITLE = 'Dashboard — ' . SITE_NAME;
require_once __DIR__ . '/../includes/public-header.php';
?>
<main class="relative py-14 min-h-screen">
  <div class="aurora-blob w-[460px] h-[420px] bg-cyan-500/12 -left-40 top-24"></div>
  <div class="mx-auto max-w-6xl px-4 sm:px-6 relative">

    <div class="flex flex-wrap items-end justify-between gap-6">
      <div>
        <p class="text-xs font-extrabold tracking-[.3em] uppercase text-cyan-400"><span data-i18n="dash_verification">Verification</span></p>
        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold text-white"><span data-i18n="dash_greeting">Hello</span>, <?= e(explode(' ', $user['name'])[0]) ?> 👋</h1>
      </div>
      <div class="flex items-center gap-3">
        <span class="badge text-white border border-white/10 bg-white/5"><?= e($user['email']) ?></span>
        <span id="dash-tick" class="badge border border-white/10 bg-white/5 <?= e($tick['key'] === 'red' ? 'text-rose-400' : ($tick['key'] === 'grey' ? 'text-slate-300' : 'text-emerald-400')) ?>"><?= e($tick['icon']) ?> <?= l($tick['label_en'], $tick['label_bn']) ?></span>
        <a href="logout.php" class="btn-ghost !py-2 !px-4 text-xs"><span data-i18n="nav_logout">Sign out</span></a>
      </div>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-3">

      <!-- ORDERS -->
      <section class="lg:col-span-2 glass rounded-3xl p-6 grad-border">
        <div class="flex items-center justify-between">
          <h2 class="font-bold text-white"><span data-i18n="dash_orders">My Orders</span></h2>
          <a href="<?= e(url('') . '#order') ?>" class="text-sm font-bold text-cyan-300 hover:text-cyan-200"><span data-i18n="dash_go_new">Start a project</span> →</a>
        </div>

        <?php if (!$orders): ?>
          <div class="mt-8 text-center text-slate-400 py-10 glass-chip rounded-2xl" data-i18n="dash_no_orders">No orders yet — start your first project!</div>
        <?php else: ?>
        <div class="mt-5 overflow-x-auto nice-scroll">
          <table class="w-full text-sm min-w-[560px]">
            <thead>
              <tr class="text-left text-xs uppercase tracking-wider text-slate-500 border-b border-white/10">
                <th class="py-3 pr-4"><span data-i18n="dash_order_id">Order</span></th>
                <th class="py-3 pr-4"><span data-i18n="dash_item">Project</span></th>
                <th class="py-3 pr-4"><span data-i18n="dash_date">Date</span></th>
                <th class="py-3"><span data-i18n="dash_status">Status</span></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): $st = order_status_meta($o['status']); ?>
              <tr class="border-b border-white/5 hover:bg-white/[.03] transition-colors">
                <td class="py-3.5 pr-4 font-bold text-slate-200">#<?= (int) $o['id'] ?></td>
                <td class="py-3.5 pr-4"><?= l($o['svc_en'] ?: 'Custom', $o['svc_bn'] ?: 'কাস্টম') ?>
                  <?php if ($o['budget']): ?><div class="text-xs text-slate-500 mt-0.5"><?= e(price_fmt($o['budget'])) ?></div><?php endif; ?>
                </td>
                <td class="py-3.5 pr-4 text-slate-400"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                <td class="py-3.5">
                  <span class="badge <?= $st['cls'] === 'emerald' ? 'text-emerald-950 bg-emerald-400' : ($st['cls'] === 'rose' ? 'text-rose-100 bg-rose-500/80' : ($st['cls'] === 'amber' ? 'text-amber-950 bg-amber-300' : 'text-sky-950 bg-sky-400')) ?>">
                    <?= l($st['label_en'], $st['label_bn']) ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </section>

      <!-- VERIFICATION + CHAT -->
      <section class="space-y-6">
        <div class="glass rounded-3xl p-6 grad-border neon-glow">
          <h2 class="font-bold text-white flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg grid place-items-center text-sm bg-gradient-to-br from-brand-deep to-brand-light">🛡</span>
            <span data-i18n="dash_verification">Verification</span>
          </h2>
          <div class="mt-5 space-y-3 text-sm">
            <div class="flex items-center justify-between glass-chip rounded-xl px-4 py-3">
              <span class="text-slate-300"><?= l('Email', 'ইমেইল') ?></span>
              <span class="badge <?= $tick['record']['email_verified'] ? 'text-emerald-950 bg-emerald-400' : 'text-slate-400 glass-chip' ?>">
                <?= $tick['record']['email_verified'] ? '✔ ' . l('Verified', 'যাচাইকৃত') : '❌ ' . l('Pending', 'বাকি') ?>
              </span>
            </div>
            <div class="flex items-center justify-between glass-chip rounded-xl px-4 py-3">
              <span class="text-slate-300"><?= l('WhatsApp', 'হোয়াটসঅ্যাপ') ?></span>
              <span class="badge <?= $tick['record']['whatsapp_verified'] ? 'text-emerald-950 bg-emerald-400' : 'text-slate-400 glass-chip' ?>">
                <?= $tick['record']['whatsapp_verified'] ? '✔ ' . l('Verified', 'যাচাইকৃত') : '❌ ' . l('Pending', 'বাকি') ?>
              </span>
            </div>
            <a href="<?= e(url('account/verify.php')) ?>" class="btn-accent w-full !py-2.5 text-xs">
              <span data-i18n="dash_verify_cta">Complete verification</span>
            </a>
          </div>
        </div>

        <div class="glass rounded-3xl p-6 grad-border">
          <h2 class="font-bold text-white flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg grid place-items-center text-sm bg-gradient-to-br from-accent-neon to-accent-electric text-white">💬</span>
            <span data-i18n="dash_chat">Chat History</span>
          </h2>
          <?php if (!$chats): ?>
            <p class="mt-5 text-sm text-slate-400" data-i18n="dash_chat_empty">No chat history yet.</p>
          <?php else: ?>
            <div class="mt-4 space-y-2.5">
              <?php foreach ($chats as $c): ?>
                <a href="<?= e(url('') . '#chat-hint') ?>" class="block glass-chip rounded-xl px-4 py-3 hover:bg-white/[.06] transition-colors">
                  <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-200">#<?= (int) $c['id'] ?> · <?= (int) $c['msg_count'] ?> <?= l('messages', 'মেসেজ') ?></span>
                    <span class="text-slate-500"><?= e(ago($c['updated_at'])) ?></span>
                  </div>
                  <p class="mt-1 text-xs text-slate-400 truncate">
                    <?= e(mb_strimwidth(is_string($c['last_message']) && str_starts_with($c['last_message'], '::') ? '…' : (string) $c['last_message'], 0, 60, '…')) ?>
                  </p>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/public-footer.php'; ?>