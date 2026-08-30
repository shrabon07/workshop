<?php
/** Aurora Cyber — Terms of Service & Privacy Policy. */
require_once __DIR__ . '/config.php';

$PAGE_TITLE = 'Terms & Privacy · ' . SITE_NAME;
$PAGE_DESC  = 'Terms of service and privacy policy for Aurora Cyber — web development services, payments, data handling and your rights.';
require_once __DIR__ . '/includes/public-header.php';
?>

<section class="relative overflow-hidden pt-32 pb-20">
  <div class="absolute inset-0 bg-[radial-gradient(900px_480px_at_20%_-10%,rgba(15,118,110,.25),transparent_60%),radial-gradient(800px_420px_at_90%_0%,rgba(99,102,241,.22),transparent_55%)]"></div>
  <div class="relative mx-auto max-w-4xl px-4 sm:px-6">
    <p class="text-[11px] uppercase tracking-[.3em] font-extrabold text-cyan-400">Aurora Cyber</p>
    <h1 class="mt-2 text-3xl sm:text-5xl font-extrabold tracking-tight text-white">
      <span class="e">Terms of Service & Privacy Policy</span><span class="b">সেবার শর্তাবলী ও গোপনীয়তা নীতি</span>
    </h1>
    <p class="mt-4 text-slate-400 text-sm sm:text-base">
      <span class="e">Last updated: August 2026 · By using aurora-cyber.infy.click (&#8220;the Site&#8221;), you agree to these terms. Please read them carefully before ordering any service.</span>
      <span class="b">সর্বশেষ হালনাগাদ: আগস্ট ২০২৬ · aurora-cyber.infy.click (&#8220;সাইট&#8221;) ব্যবহার করে আপনি এই শর্তাবলীতে সম্মত হচ্ছেন। কোনো সার্ভিস অর্ডারের আগে অনুগ্রহ করে সাবধানে পড়ুন।</span>
    </p>

    <div class="mt-10 space-y-6">
      <article class="glass-strong rounded-3xl p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-white"><span class="e">1. Agreement</span><span class="b">১. চুক্তি</span></h2>
        <p class="mt-3 text-sm sm:text-base text-slate-400 leading-relaxed">
          <span class="e">Aurora Cyber is a web development agency based in Mirpur, Dhaka-1215, Bangladesh. Placing an order on the Site, or contacting us by email or WhatsApp about a project, forms a business agreement governed by these terms and the laws of Bangladesh.</span>
          <span class="b">অরোরা সাইবার বাংলাদেশের মিরপুর, ঢাকা-১২১৫-এ অবস্থিত একটি ওয়েব ডেভেলপমেন্ট এজেন্সি। সাইটে অর্ডার দেওয়া বা ইমেইল/হোয়াটসঅ্যাপে প্রজেক্ট নিয়ে যোগাযোগ করা এই শর্তাবলী ও বাংলাদেশের আইন অনুযায়ী একটি ব্যবসায়িক চুক্তি গঠন করে।</span>
        </p>
      </article>

      <article class="glass-strong rounded-3xl p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-white"><span class="e">2. Services & Orders</span><span class="b">২. সার্ভিস ও অর্ডার</span></h2>
        <p class="mt-3 text-sm sm:text-base text-slate-400 leading-relaxed">
          <span class="e">Every project starts from our service packages or a custom order form. After you submit an order we reply by email/WhatsApp with a quote, scope, timeline and payment plan. Work begins after you accept the quote and pay the agreed advance. Scope changes during development may be quoted separately.</span>
          <span class="b">প্রতিটি প্রজেক্ট আমাদের সার্ভিস প্যাকেজ বা কাস্টম অর্ডার ফর্ম থেকে শুরু হয়। অর্ডার দেওয়ার পর আমরা ইমেইল/হোয়াটসঅ্যাপে কোটা, সুযোগ-সুবিধা, সময়সীমা ও পেমেন্ট প্ল্যান নিয়ে জানাই। কোটা গ্রহণ ও সম্মত অগ্রিম পরিশোধের পর কাজ শুরু হয়। উন্নয়নের সময় সুযোগ-সুবিধা বদল করলে আলাদাভাবে মূল্য ধরা হতে পারে।</span>
        </p>
      </article>

      <article class="glass-strong rounded-3xl p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-white"><span class="e">3. Payments</span><span class="b">৩. পেমেন্ট</span></h2>
        <p class="mt-3 text-sm sm:text-base text-slate-400 leading-relaxed">
          <span class="e">We accept bKash, Nagad, Rocket, bank transfer, card (via SSLCommerz) and USDT for overseas clients. See our <a class="text-cyan-300 hover:underline" href="<?= e(url('payment-methods.php')) ?>">Payment Methods</a> page for details. Payment is due according to the agreed plan; delivered works remain property of Aurora Cyber until the final payment is cleared.</span>
          <span class="b">আমরা বিকাশ, নগদ, রকেট, ব্যাংক ট্রান্সফার, কার্ড (এসএসএলকমার্জের মাধ্যমে) এবং বিদেশি ক্লায়েন্টদের জন্য ইউএসডিটি নিই। বিস্তারিত জানতে আমাদের <a class="text-cyan-300 hover:underline" href="<?= e(url('payment-methods.php')) ?>">পেমেন্ট মেথড</a> পৃষ্ঠা দেখুন। চুক্তিকৃত প্ল্যান অনুযায়ী পেমেন্ট দেয়া লাগবে; চূড়ান্ত পেমেন্ট পরিষ্কার না হওয়া পর্যন্ত তৈরি কাজ অরোরা সাইবারের সম্পত্তি থাকে।</span>
        </p>
      </article>

      <article class="glass-strong rounded-3xl p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-white"><span class="e">4. Intellectual Property</span><span class="b">৪. মেধাস্বত্ব</span></h2>
        <p class="mt-3 text-sm sm:text-base text-slate-400 leading-relaxed">
          <span class="e">Once fully paid, the completed website and its source files are transferred to you for commercial use. Aurora Cyber retains the right to display the project in our portfolio unless a non-disclosure agreement is signed in advance.</span>
          <span class="b">সম্পূর্ণ পরিশোধের পর তৈরি ওয়েবসাইট ও তার সোর্স ফাইল বাণিজ্যিক ব্যবহারের জন্য আপনার কাছে হস্তান্তর হয়। পূর্বে স্বাক্ষরিত এনডিএ না থাকলে অরোরা সাইবার আমাদের পোর্টফোলিওতে প্রজেক্টটি দেখানোর অধিকার রাখে।</span>
        </p>
      </article>

      <article class="glass-strong rounded-3xl p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-white"><span class="e">5. Client Responsibilities</span><span class="b">৫. ক্লায়েন্টের দায়িত্ব</span></h2>
        <p class="mt-3 text-sm sm:text-base text-slate-400 leading-relaxed">
          <span class="e">You agree to provide accurate contact information, respond to queries within reasonable time, supply required content/logins for the project, and refrain from uploading illegal or infringing material.</span>
          <span class="b">আপনি সঠিক যোগাযোগ তথ্য দিতে, সময়মতো প্রশ্নের উত্তর দিতে, প্রজেক্টের জন্য প্রয়োজনীয় কনটেন্ট/লগইন সরবরাহ করতে এবং অবৈধ বা কপিরাইট-লঙ্ঘনকারী সামগ্রী আপলোড না করতে সম্মত হচ্ছেন।</span>
        </p>
      </article>

      <article class="glass-strong rounded-3xl p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-white"><span class="e">6. Refunds & Cancellation</span><span class="b">৬. রিফান্ড ও বাতিলকরণ</span></h2>
        <p class="mt-3 text-sm sm:text-base text-slate-400 leading-relaxed">
          <span class="e">You may cancel before development begins and receive a full refund of the advance. Once work has started, the advance is non-refundable but adjustments are made based on completed milestones. Bug fixes after delivery within the agreed support window are free; new features are quoted separately.</span>
          <span class="b">উন্নয়ন শুরুর আগে বাতিল করলে অগ্রিম সম্পূর্ণ ফেরত পাবেন। কাজ শুরু হয়ে গেলে অগ্রিম অফেরতযোগ্য, তবে সম্পন্ন মাইলফলক অনুযায়ী সমন্বয় করা হয়। সম্মত সাপোর্ট সময়ের মধ্যে ডেলিভারির পর বাগ ফিক্স ফ্রি; নতুন ফিচারের আলাদা মূল্য ধরা হয়।</span>
        </p>
      </article>

      <article class="glass-strong rounded-3xl p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-white"><span class="e">7. Privacy & Data Handling</span><span class="b">৭. গোপনীয়তা ও তথ্য নিরাপত্তা</span></h2>
        <ul class="mt-3 space-y-3 text-sm sm:text-base text-slate-400 leading-relaxed list-disc list-inside">
          <li><span class="e">We collect only what we need to serve you: name, email, phone and your project details.</span><span class="b">আমরা শুধু প্রয়োজনীয় তথ্য সংগ্রহ করি: নাম, ইমেইল, ফোন এবং আপনার প্রজেক্টের বিবরণ।</span></li>
          <li><span class="e">Your data is used to respond to orders, send project updates, and contact you about our services. We never sell your personal data.</span><span class="b">আপনার তথ্য অর্ডার সাড়া দিতে, প্রজেক্ট আপডেট পাঠাতে এবং আমাদের সার্ভিস সম্পর্কে যোগাযোগ করতে ব্যবহৃত হয়। আমরা আপনার ব্যক্তিগত তথ্য কখনো বিক্রি করি না।</span></li>
          <li><span class="e">Contact details are stored securely and retained as long as needed for the business relationship.</span><span class="b">যোগাযোগের তথ্য নিরাপদে রাখা হয় এবং ব্যবসায়িক সম্পর্কের জন্য যতদিন প্রয়োজন ততদিন রাখা হয়।</span></li>
          <li><span class="e">We may send service emails (quotes, receipts, credentials, support) and occasional updates. You can opt out of marketing messages anytime by replying to any email.</span><span class="b">আমরা সার্ভিস ইমেইল (কোটা, রসিদ, লগইন তথ্য, সাপোর্ট) এবং মাঝে মাঝে আপডেট পাঠাতে পারি। যেকোনো ইমেইলের জবাবে বললে মার্কেটিং বার্তা বন্ধ হবে।</span></li>
          <li><span class="e">Cookies/localStorage are only used to remember your language preference and session.</span><span class="b">কুকি/লোকালস্টোরেজ শুধু ভাষা পছন্দ ও সেশন মনে রাখতে ব্যবহৃত হয়।</span></li>
        </ul>
      </article>

      <article class="glass-strong rounded-3xl p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-white"><span class="e">8. Your Rights & Contact</span><span class="b">৮. আপনার অধিকার ও যোগাযোগ</span></h2>
        <p class="mt-3 text-sm sm:text-base text-slate-400 leading-relaxed">
          <span class="e">You may request a copy or deletion of your personal data at any time by emailing us. We also change these terms as needed; the &#8220;last updated&#8221; date above reflects the current version. Questions? Reach us at <?= e(SITE_EMAIL) ?> or WhatsApp (+880&nbsp;1977&nbsp;665421).

Address: Mirpur, Dhaka-1215, Bangladesh.</span>
          <span class="b">যেকোনো সময় ইমেইল করে আপনার ব্যক্তিগত তথ্যের কপি বা মুছে ফেলার অনুরোধ করতে পারেন। প্রয়োজনে আমরা এই শর্তাবলী বদলাই; উপরের &#8220;সর্বশেষ হালনাগাদ&#8221; তারিখ বর্তমান সংস্করণ দেখায়। প্রশ্ন? <?= e(SITE_EMAIL) ?> বা হোয়াটসঅ্যাপে (+880&nbsp;1977&nbsp;665421) যোগাযোগ করুন।

ঠিকানা: মিরপুর, ঢাকা-১২১৫, বাংলাদেশ।</span>
        </p>
      </article>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/public-footer.php'; ?>