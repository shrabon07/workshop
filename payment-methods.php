<?php
/** Aurora Cyber — Payment Methods. */
require_once __DIR__ . '/config.php';

$PAGE_TITLE = 'Payment Methods · ' . SITE_NAME;
$PAGE_DESC  = 'How to pay Aurora Cyber — bKash, Nagad, Rocket, bank transfer, card (SSLCommerz) and USDT. Simple steps, quick confirmation.';
require_once __DIR__ . '/includes/public-header.php';

$methods = [
  ['bKash', 'bkash-badge', '<span class="e">Send money to our bKash Personal number. We confirm instantly on WhatsApp.</span><span class="b">আমাদের বিকাশ পার্সোনাল নম্বরে সেন্ড মানি করুন। আমরা হোয়াটসঅ্যাপে সাথে সাথে নিশ্চিত করি।</span>'],
  ['Nagad', 'nagad-badge', '<span class="e">Send money through Nagad to our number. Keep the TrxID for your records.</span><span class="b">নগদ দিয়ে আমাদের নম্বরে সেন্ড মানি করুন। রেকর্ডের জন্য TrxID জমা রাখুন।</span>'],
  ['Rocket', 'rocket-badge', '<span class="e">DBBL Rocket money transfers are accepted for both volumes and smaller payments.</span><span class="b">ডাচ্-বাংলা রকেট মানি ট্রান্সফার বড় ছোট সব পেমেন্টে গ্রহণযোগ্য।</span>'],
  ['Bank Transfer', 'bank-badge', '<span class="e">For larger invoices, bank transfer (BEFTN) details are shared after your order.</span><span class="b">বড় ইনভয়েসের জন্য অর্ডারের পর ব্যাংক ট্রান্সফার (BEFTN) তথ্য জানিয়ে দেওয়া হয়।</span>'],
  ['Debit / Credit Card', 'card-badge', '<span class="e">Card payments are processed securely online via SSLCommerz for select services.</span><span class="b">নির্বাচিত সার্ভিসে SSLCommerz-এর মাধ্যমে কার্ড পেমেন্ট অনলাইনে নিরাপদে নেওয়া হয়।</span>'],
  ['USDT (International)', 'usdt-badge', '<span class="e">Overseas clients can pay in USDT (TRC-20). Wallet address is shared on request.</span><span class="b">বিদেশি ক্লায়েন্টরা ইউএসডিটি (TRC-20) দিতে পারেন। চাইলে ওয়ালেট ঠিকানা দেওয়া হয়।</span>'],
];
?>
<section class="relative overflow-hidden pt-32 pb-20">
  <div class="absolute inset-0 bg-[radial-gradient(900px_480px_at_20%_-10%,rgba(15,118,110,.25),transparent_60%),radial-gradient(800px_420px_at_90%_0%,rgba(99,102,241,.22),transparent_55%)]"></div>
  <div class="relative mx-auto max-w-4xl px-4 sm:px-6">
    <p class="text-[11px] uppercase tracking-[.3em] font-extrabold text-cyan-400">Aurora Cyber</p>
    <h1 class="mt-2 text-3xl sm:text-5xl font-extrabold tracking-tight text-white">
      <span class="e">Payment Methods</span><span class="b">পেমেন্ট মেথড</span>
    </h1>
    <p class="mt-4 text-slate-400 text-sm sm:text-base max-w-2xl">
      <span class="e">Simple and secure ways to pay for your project. After you place an order, your payment details are confirmed on email and WhatsApp.</span>
      <span class="b">আপনার প্রজেক্টের পেমেন্টের সহজ ও নিরাপদ উপায়। অর্ডার দেওয়ার পর পেমেন্টের তথ্য ইমেইল ও হোয়াটসঅ্যাপে নিশ্চিত করা হয়।</span>
    </p>

    <div class="mt-10 grid gap-5 sm:grid-cols-2">
      <?php foreach ($methods as $m): ?>
      <article class="glass-strong rounded-3xl p-6">
        <div class="flex items-center gap-3">
          <span class="w-10 h-10 rounded-2xl grid place-items-center bg-cyan-400/10 border border-cyan-400/20 text-lg">💳</span>
          <h2 class="text-lg font-extrabold text-white"><?= e($m[0]) ?></h2>
        </div>
        <p class="mt-3 text-sm text-slate-400 leading-relaxed"><?= $m[2] ?></p>
      </article>
      <?php endforeach; ?>
    </div>

    <article class="mt-8 glass-strong rounded-3xl p-6 sm:p-8">
      <h2 class="text-xl font-extrabold text-white"><span class="e">How payment works</span><span class="b">পেমেন্ট যেভাবে হয়</span></h2>
      <ol class="mt-4 space-y-3 text-sm sm:text-base text-slate-400 leading-relaxed list-decimal list-inside">
        <li><span class="e">Place your order on the Site with your contact details.</span><span class="b">সাইটে আপনার যোগাযোগ তথ্যসহ অর্ডার দিন।</span></li>
        <li><span class="e">We email/WhatsApp your quote, invoice number and the exact payment details.</span><span class="b">আমরা ইমেইল/হোয়াটসঅ্যাপে আপনার কোটা, ইনভয়েস নম্বর ও সঠিক পেমেন্ট তথ্য পাঠাই।</span></li>
        <li><span class="e">Send the payment using any method above and share the TrxID/reference.</span><span class="b">উপরের যেকোনো উপায়ে পেমেন্ট পাঠান এবং TrxID/রেফারেন্স জানান।</span></li>
        <li><span class="e">We verify the payment and confirm — development starts on schedule.</span><span class="b">আমরা পেমেন্ট যাচাই করে নিশ্চিত করি — সময়মতো উন্নয়ন শুরু হয়।</span></li>
      </ol>
      <p class="mt-5 text-sm text-slate-500"><span class="e">Questions about payment?</span> <span class="b">পেমেন্ট নিয়ে প্রশ্ন?</span> <a class="text-cyan-300 hover:underline" href="mailto:<?= e(SITE_EMAIL) ?>"><span class="e">Email us</span><span class="b">ইমেইল করুন</span></a> <span class="e">or WhatsApp +880&nbsp;1977&nbsp;665421.</span><span class="b">বা হোয়াটসঅ্যাপ +880&nbsp;1977&nbsp;665421।</span></p>
    </article>
  </div>
</section>

<?php require_once __DIR__ . '/includes/public-footer.php'; ?>