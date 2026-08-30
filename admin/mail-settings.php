<?php
/**
 * Admin — "My mail sender": a regular admin connects their own Gmail
 * (app password) so customer emails come from THEIR address. The super
 * admin always sends from the site account and never sees this form.
 */
require_once __DIR__ . '/../config.php';
require_admin();
$isSuper = is_super_admin();
$profile = $isSuper ? null : DB::get('SELECT smtp_email, smtp_pass, verified, verified_at FROM admin_mail_settings WHERE admin_id = ?', [current_user_id()]);
if ($profile) {
    $profile['smtp_email_masked'] = preg_replace('/^(.).*(@.*)$/', '$1***$2', $profile['smtp_email']);
}

$PAGE_TITLE = 'My mail sender';
$ACTIVE = 'mail';
require_once __DIR__ . '/inc/head.php';
?>
<div class="max-w-2xl">
  <div class="glass-strong rounded-3xl p-6">
    <h2 class="font-bold text-white flex items-center gap-2">
      <span class="w-8 h-8 rounded-lg grid place-items-center text-sm bg-gradient-to-br from-brand-deep to-brand-light">📤</span>
      <span class="e">My mail sender</span><span class="b">আমার মেইল সেন্ডার</span>
    </h2>

    <?php if ($isSuper): ?>
      <p class="mt-4 text-sm text-slate-400">
        <span class="e">You are the super admin — every email goes permanently from the site account
        <strong class="text-cyan-300"><?= e(SITE_EMAIL) ?></strong> (OTP verification, requests, invoices, announcements). Regular admins connect their own Gmail here so their customer emails come from their own address.</span>
        <span class="b">আপনি সুপার অ্যাডমিন — সব ইমেইল স্থায়ীভাবে সাইট অ্যাকাউন্ট
        <strong class="text-cyan-300"><?= e(SITE_EMAIL) ?></strong> থেকে পাঠানো হয় (OTP যাচাই, রিকোয়েস্ট, ইনভয়েস, ঘোষণা)। সাধারণ অ্যাডমিনরা এখানে নিজের Gmail যোগ করে যাতে তাদের গ্রাহক-ইমেইল নিজের ঠিকানা থেকে যায়।</span>
      </p>
    <?php else: ?>

      <p class="mt-4 text-sm text-slate-400">
        <span class="e">Connect your Gmail with a 16-letter <strong>app password</strong>. Customer emails you send (payment requests, order updates, one-off or bulk mail) will then arrive <strong class="text-cyan-300">from your own Gmail address</strong>. Until you connect one, they are sent from the site account and stamped "Sent by you".</span>
        <span class="b">আপনার Gmail একটি ১৬ অক্ষরের <strong>অ্যাপ পাসওয়ার্ড</strong> দিয়ে যুক্ত করুন। আপনি যে গ্রাহক-ইমেইল পাঠান (পেমেন্ট রিকোয়েস্ট, অর্ডার আপডেট, একক বা বাল্ক মেইল) তা তখন <strong class="text-cyan-300">আপনার নিজের Gmail ঠিকানা থেকে</strong> যাবে। ততক্ষণ পর্যন্ত সাইট অ্যাকাউন্ট থেকে পাঠানো হয় এবং "আপনার পাঠানো" হিসেবে চিহ্নিত থাকে।</span>
      </p>

      <?php if ($profile && (int) $profile['verified'] === 1): ?>
        <div class="mt-5 flex items-start gap-3 rounded-2xl border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-200">
          <span>✓</span>
          <span>
            <span class="e"><strong>Verified.</strong> Sending as <strong><?= e($profile['smtp_email_masked']) ?></strong><?= $profile['verified_at'] ? ' since ' . e(date('M j, Y', strtotime($profile['verified_at']))) : '' ?>. Customer emails you send now come from this address. If Gmail ever revokes the password, we automatically fall back to the site account — just reconnect here.</span>
            <span class="b"><strong>যাচাইকৃত।</strong> <strong><?= e($profile['smtp_email_masked']) ?></strong> হিসেবে পাঠাচ্ছে। আপনি পাঠানো গ্রাহক-ইমেইল এখন এই ঠিকানা থেকে যায়। পাসওয়ার্ড বাতিল হলে স্বয়ংক্রিয়ভাবে সাইট অ্যাকাউন্টে ফিরে যায় — আবার এখানে যুক্ত করুন।</span>
          </span>
        </div>
      <?php elseif ($profile && (int) $profile['verified'] !== 1): ?>
        <div class="mt-5 rounded-2xl border border-amber-400/25 bg-amber-400/10 p-4 text-sm text-amber-200">
          <span class="e">Saved but <strong>not verified</strong> — the proof-mail failed. Fix it below and Save again.</span>
          <span class="b">সংরক্ষিত কিন্তু <strong>যাচাই হয়নি</strong> — টেস্ট মেইল ব্যর্থ। নিচে ঠিক করে আবার সংরক্ষণ করুন।</span>
        </div>
      <?php endif; ?>

      <p class="mt-6 text-xs text-slate-500">
        <span class="e">How to get an app password: open <span class="text-cyan-300">myaccount.google.com/apppasswords</span> → enter 2-Step Verification (must be ON) → app name "Aurora Cyber" → copy the 16 letters. The password is stored AES-encrypted and is only used to authenticate sends from your account.</span>
        <span class="b">অ্যাপ পাসওয়ার্ড পেতে: <span class="text-cyan-300">myaccount.google.com/apppasswords</span> খুলুন → 2-ধাপ যাচাই (চালু থাকতে হবে) → অ্যাপের নাম "Aurora Cyber" → ১৬ অক্ষর কপি করুন। পাসওয়ার্ড AES-এনক্রিপ্ট হয়ে থাকে এবং শুধু আপনার অ্যাকাউন্ট থেকে পাঠানোর জন্য ব্যবহৃত হয়।</span>
      </p>

      <form id="mail-settings-form" class="mt-4 grid gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-400 mb-1.5"><?= l('Your Gmail address', 'আপনার Gmail ঠিকানা') ?></label>
          <input id="ms-email" type="email" class="input w-full !py-2.5" required value="<?= e($profile ? $profile['smtp_email'] : '') ?>" placeholder="you@gmail.com">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-400 mb-1.5"><?= l('Gmail app password (16 letters)', 'Gmail অ্যাপ পাসওয়ার্ড (১৬ অক্ষর)') ?></label>
          <input id="ms-pass" type="password" class="input w-full !py-2.5" required maxlength="30" placeholder="abcd efgh ijkl mnop" spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="new-password">
          <p id="ms-pass-hint" class="text-[11px] text-slate-500 mt-1"></p>
          <p class="mt-1.5 text-xs text-slate-500"><?= l('Saving sends a quick proof-mail to this address and marks it verified.', 'সংরক্ষণে এই ঠিকানায় একটি যাচাই-মেইল যায় এবং যাচাইকৃত হয়।') ?></p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
          <button type="submit" id="ms-save" class="btn-teal !py-2.5 !px-5 text-xs">
            <span class="e">Verify &amp; save</span><span class="b">যাচাই ও সংরক্ষণ করুন</span>
          </button>
          <span id="ms-status" class="text-xs"></span>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
<script defer src="<?= e(asset('js/admin-mail-settings.js')) ?>?v=<?= $v ?>"></script>
<?php
require_once __DIR__ . '/inc/foot.php';
?>