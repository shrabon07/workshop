<?php
/** Admin — Compose Mail: mail-pad composer (To + up to 10 recipients, subject, body). */
require_once __DIR__ . '/../config.php';
require_admin();
$isSuper  = is_super_admin();
$canSend  = admin_mail_ready();

$PAGE_TITLE = 'Compose Mail';
$ACTIVE = 'compose';
require_once __DIR__ . '/inc/head.php';
?>
<div class="max-w-3xl">
  <div class="glass-strong rounded-3xl p-6">
    <h2 class="font-bold text-white flex items-center gap-2">
      <span class="w-8 h-8 rounded-lg grid place-items-center text-sm bg-gradient-to-br from-brand-deep to-brand-light">✍</span>
      <span class="e">Compose mail</span><span class="b">মেইল রচনা</span>
    </h2>
    <p class="mt-1 text-sm text-slate-400">
      <span class="e">Write an email, add up to
        <strong class="text-cyan-300">10 recipients</strong> with the <strong class="text-cyan-300">+</strong> button, then send. Every mail is saved to the
        <strong class="text-cyan-300">Mail List</strong>.</span>
      <span class="b">একটি ইমেইল লিখুন, <strong class="text-cyan-300">+</strong> বোতাম দিয়ে সর্বোচ্চ
        <strong class="text-cyan-300">১০ জন প্রাপক</strong> যোগ করুন, তারপর পাঠান। প্রতিটি মেইল <strong class="text-cyan-300">মেইল তালিকা</strong>-তে সংরক্ষিত হয়।</span>
    </p>

    <?php if (!$canSend): ?>
      <div class="mt-4 rounded-2xl border border-amber-400/25 bg-amber-400/10 p-4 text-sm text-amber-200">
        <span class="e">You need a <strong>verified mail sender</strong> to compose custom mail. Connect your Gmail app password in
          <a class="underline text-cyan-300 font-bold" href="<?= e(url('admin/mail-settings.php')) ?>">My mail sender</a> first.</span>
        <span class="b">কাস্টম মেইল পাঠাতে আপনার একটি <strong>যাচাইকৃত মেইল সেন্ডার</strong> প্রয়োজন। আগে
          <a class="underline text-cyan-300 font-bold" href="<?= e(url('admin/mail-settings.php')) ?>">আমার মেইল সেন্ডার</a>-এ আপনার Gmail অ্যাপ পাসওয়ার্ড যুক্ত করুন।</span>
      </div>
    <?php endif; ?>

    <form id="compose-form" class="mt-5 grid gap-4">
      <!-- To rows (add via +) -->
      <div>
        <label class="block text-xs font-bold text-slate-400 mb-1.5"><?= l('To (recipients)', 'প্রাপক') ?></label>
        <div id="to-list" class="grid gap-2">
          <div class="flex items-center gap-2 to-row">
            <input type="email" name="recipients[]" class="input w-full !py-2.5" placeholder="you@gmail.com" required>
            <button type="button" class="to-add btn-teal !py-2.5 !px-3 !text-base shrink-0" title="<?= e(l_attr('Add another recipient', 'আরেকজন প্রাপক যোগ করুন')) ?>">+</button>
          </div>
        </div>
        <p class="mt-1.5 text-xs text-slate-500"><?= l('Use + to add more addresses (up to 10). Each mail is saved to the Mail List.', '+ দিয়ে আরো ঠিকানা যোগ করুন (সর্বোচ্চ ১০)। প্রতিটি মেইল মেইল তালিকায় সংরক্ষিত হয়।') ?></p>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-400 mb-1.5"><?= l('Subject', 'বিষয়') ?></label>
        <input name="subject" class="input w-full !py-2.5" maxlength="190" placeholder="<?= e(l_attr('Project update…', 'প্রজেক্ট আপডেট…')) ?>" required>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-400 mb-1.5"><?= l('Message', 'বার্তা') ?></label>
        <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-3">
          <div class="flex items-center gap-2 pb-3 border-b border-white/10 mb-3 flex-wrap">
            <button type="button" class="pad-tool" data-tag="<b></b>"><b>B</b></button>
            <button type="button" class="pad-tool" data-tag="<i></i>"><i>I</i></button>
            <button type="button" class="pad-tool" data-tag="<u></u>"><u>U</u></button>
            <span class="text-slate-700">|</span>
            <button type="button" class="pad-tool text-xs" data-tag="\n\n">¶</button>
            <span class="hidden sm:inline text-xs text-slate-500"><?= l('Formatting toolbar (plain text OK too)', 'ফরম্যাটিং টুলবার (সাধারণ টেক্সটও চলবে)') ?></span>
          </div>
          <textarea name="message" id="compose-body" class="w-full bg-transparent text-slate-100 text-sm focus:outline-none resize-y min-h-[160px]" rows="8" placeholder="<?= e(l_attr('Write your message here…', 'এখানে আপনার বার্তা লিখুন…')) ?>" required></textarea>
        </div>
        <p class="mt-1.5 text-xs text-slate-500"><?= l('Line breaks are preserved. Wrap text with a selection, then tap B / I / U to style it.', 'লাইন ব্রেক সংরক্ষিত থাকে। টেক্সট সিলেক্ট করে B / I / U দিয়ে স্টাইল করুন।') ?></p>
      </div>

      <div class="flex items-center justify-between gap-3 flex-wrap">
        <span id="compose-count" class="text-xs text-slate-500"><?= l('1 recipient', '১ জন প্রাপক') ?></span>
        <button type="submit" id="compose-send" class="btn-accent !py-2.5 !px-6 text-xs" <?= $canSend ? '' : 'disabled' ?>>
          <span class="e">Send mail</span><span class="b">মেইল পাঠান</span>
        </button>
      </div>
      <span id="compose-status" class="text-xs"></span>
    </form>
  </div>
</div>
<script>window.COMPOSE_MAX = 10;</script>
<script defer src="<?= e(asset('js/admin-compose.js')) ?>?v=<?= $v ?>"></script>
<?php
require_once __DIR__ . '/inc/foot.php';
?>
