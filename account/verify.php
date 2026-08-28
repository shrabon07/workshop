<?php
/** Customer verification center — Email OTP + WhatsApp click-to-chat flow. */
require_once __DIR__ . '/../config.php';

require_login();
$user = current_user();
$v    = verification_record((int) $user['id']);
$tick = verification_tick($user);

$tickStyles = [
    'red'   => ['ring' => 'border-rose-500/40', 'bg' => 'bg-rose-500', 'text' => 'text-rose-300', 'glow' => 'shadow-[0_0_50px_-12px_rgba(244,63,94,.6)]'],
    'grey'  => ['ring' => 'border-slate-400/40', 'bg' => 'bg-slate-400', 'text' => 'text-slate-200', 'glow' => 'shadow-[0_0_50px_-16px_rgba(148,163,184,.5)]'],
    'green' => ['ring' => 'border-emerald-400/40', 'bg' => 'bg-emerald-400', 'text' => 'text-emerald-300', 'glow' => 'shadow-[0_0_50px_-12px_rgba(52,211,153,.65)]'],
];

$PAGE_TITLE = 'Verify your account — ' . SITE_NAME;
require_once __DIR__ . '/../includes/public-header.php';
?>
<main class="relative py-16 min-h-screen">
  <div class="aurora-blob w-[440px] h-[400px] bg-cyan-500/15 -left-40 top-20"></div>
  <div class="mx-auto max-w-3xl px-4 sm:px-6 relative">

    <div class="text-center reveal">
      <h1 class="text-3xl sm:text-4xl font-extrabold text-white"><span data-i18n="verify_title">Verify Your Account</span></h1>
      <p class="mt-3 text-slate-400 max-w-md mx-auto"><span data-i18n="verify_sub">Verified customers get priority replies and order tracking.</span></p>
    </div>

    <!-- status emblem -->
    <div id="tick-panel" class="reveal mt-10 glass-strong rounded-[2rem] p-8 text-center neon-glow grad-border" data-tick="<?= e($tick['key']) ?>" data-override="<?= e($v['admin_override']) ?>">
      <div id="tick-icon" class="mx-auto w-24 h-24 rounded-full grid place-items-center text-4xl font-black border-2 <?= e($tickStyles[$tick['key']]['ring'] . ' ' . $tickStyles[$tick['key']]['glow']) ?>" style="background:linear-gradient(140deg,#0f172a,#0b1120)">
        <span class="<?= e($tickStyles[$tick['key']]['text']) ?>"><?= e($tick['icon']) ?></span>
      </div>
      <div id="tick-label" class="mt-4 text-lg font-bold text-white"><?= l($tick['label_en'], $tick['label_bn']) ?></div>
      <?php if ($v['admin_override'] !== 'none'): ?>
        <p class="mt-1 text-xs text-accent-electric font-semibold"><?= l('Status set manually by the admin team.', 'অ্যাডমিন টিম দ্বারা ম্যানুয়ালি সেট করা হয়েছে।') ?></p>
      <?php endif; ?>

      <div id="verify-message" class="hidden mt-5 glass-chip border rounded-xl px-4 py-3 text-sm"></div>
    </div>

    <!-- two channels -->
    <div class="mt-8 grid gap-6 md:grid-cols-2">

      <!-- EMAIL -->
      <div class="reveal glass rounded-3xl p-6 grad-border" data-reveal="ver-0">
        <div class="flex items-center gap-3">
          <span class="w-11 h-11 rounded-2xl grid place-items-center bg-gradient-to-br from-brand-deep to-brand-light text-slate-900 font-black">✉</span>
          <div class="flex-1">
            <div class="font-bold text-white"><?= l('Email verification', 'ইমেইল যাচাইকরণ') ?></div>
            <div class="text-xs text-slate-400"><?= e($user['email']) ?></div>
          </div>
          <span id="email-done" class="badge <?= $v['email_verified'] ? 'text-emerald-950 bg-emerald-400' : 'hidden text-slate-400 glass-chip' ?>">✓ <?= l('Verified', 'যাচাইকৃত') ?></span>
        </div>
        <div class="mt-5 <?= $v['email_verified'] ? 'opacity-40 pointer-events-none' : '' ?>">
          <button type="button" data-verify-channel="email" data-csrf="<?= e(csrf_token()) ?>"
                  class="btn-teal w-full !py-3" <?= $v['email_verified'] ? 'disabled' : '' ?>>
            <span data-i18n="verify_email_btn">Send email code</span>
          </button>
          <div data-otp-wrap="email" class="hidden mt-4">
            <input data-otp-input="email" class="input text-center tracking-[.5em] font-black" maxlength="6" inputmode="numeric" placeholder="· · · · · ·">
            <button type="button" data-otp-confirm="email" data-csrf="<?= e(csrf_token()) ?>"
                    class="btn-accent w-full mt-3 !py-3">
              <span data-i18n="verify_confirm_btn">Confirm code</span>
            </button>
            <p data-otp-dev="email" class="hidden mt-3 text-xs text-cyan-300 text-center"><span data-i18n="verify_dev_hint">Dev mode: your code is</span> <b data-otp-code="email"></b></p>
          </div>
        </div>
      </div>

      <!-- WHATSAPP -->
      <div class="reveal glass rounded-3xl p-6 grad-border" data-reveal="ver-1" style="--rd:120ms">
        <div class="flex items-center gap-3">
          <span class="w-11 h-11 rounded-2xl grid place-items-center bg-gradient-to-br from-accent-neon to-accent-electric text-white font-black">💬</span>
          <div class="flex-1">
            <div class="font-bold text-white"><?= l('WhatsApp verification', 'হোয়াটসঅ্যাপ যাচাইকরণ') ?></div>
            <div class="text-xs text-slate-400"><?= l('Click-to-chat code flow', 'ক্লিক-টু-চ্যাট কোড ফ্লো') ?></div>
          </div>
          <span id="wa-done" class="badge <?= $v['whatsapp_verified'] ? 'text-emerald-950 bg-emerald-400' : 'hidden text-slate-400 glass-chip' ?>">✓ <?= l('Verified', 'যাচাইকৃত') ?></span>
        </div>
        <div class="mt-5 <?= $v['whatsapp_verified'] ? 'opacity-40 pointer-events-none' : '' ?>">
          <a data-verify-whatsapp data-csrf="<?= e(csrf_token()) ?>" href="#" class="btn w-full !py-3 text-white bg-gradient-to-r from-emerald-500 to-green-500 hover:shadow-[0_8px_40px_-10px_rgba(16,185,129,.6)] hover:-translate-y-0.5 transition-all <?= $v['whatsapp_verified'] ? 'pointer-events-none' : '' ?>">
            <span data-i18n="verify_whatsapp_link">Send code on WhatsApp</span>
          </a>
          <div data-otp-wrap="whatsapp" class="hidden mt-4">
            <input data-otp-input="whatsapp" class="input text-center tracking-[.5em] font-black" maxlength="6" inputmode="numeric" placeholder="· · · · · ·">
            <button type="button" data-otp-confirm="whatsapp" data-csrf="<?= e(csrf_token()) ?>"
                    class="btn-accent w-full mt-3 !py-3">
              <span data-i18n="verify_confirm_btn">Confirm code</span>
            </button>
            <p data-otp-dev="whatsapp" class="hidden mt-3 text-xs text-cyan-300 text-center"><span data-i18n="verify_dev_hint">Dev mode: your code is</span> <b data-otp-code="whatsapp"></b></p>
          </div>
        </div>
      </div>
    </div>

    <!-- legend -->
    <div class="reveal mt-8 glass rounded-3xl p-6" data-reveal="ver-2">
      <div class="text-sm font-bold text-white mb-4"><?= l('How the ticks work', 'টিক কীভাবে কাজ করে') ?></div>
      <div class="grid gap-4 sm:grid-cols-3 text-sm">
        <div class="flex items-center gap-3 text-slate-300">
          <span class="w-8 h-8 rounded-full grid place-items-center text-white bg-rose-500/90 text-sm">❌</span>
          <span><span class="e">No channel verified</span><span class="b">কোনো চ্যানেল যাচাই হয়নি</span></span>
        </div>
        <div class="flex items-center gap-3 text-slate-300">
          <span class="w-8 h-8 rounded-full grid place-items-center bg-slate-400 text-slate-900 text-sm">✓</span>
          <span><span class="e">Email verified only</span><span class="b">শুধু ইমেইল যাচাইকৃত</span></span>
        </div>
        <div class="flex items-center gap-3 text-slate-300">
          <span class="w-8 h-8 rounded-full grid place-items-center bg-emerald-400 text-slate-900 text-sm">✔</span>
          <span><span class="e">Email + WhatsApp verified</span><span class="b">ইমেইল + হোয়াটসঅ্যাপ যাচাইকৃত</span></span>
        </div>
      </div>
    </div>

  </div>
</main>
<script defer src="<?= e(asset('js/verify.js')) ?>?v=<?= version_time() ?>"></script>
<?php require_once __DIR__ . '/../includes/public-footer.php'; ?>