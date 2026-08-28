<?php
/** Aurora Cyber — public landing page (Hero · Services · Portfolio · Testimonials · Custom Order). */
require_once __DIR__ . '/config.php';

$services = DB::all(
    'SELECT s.*, c.name_en AS cat_en, c.name_bn AS cat_bn, c.slug AS cat_slug
       FROM services s
       LEFT JOIN service_categories c ON c.id = s.category_id
      WHERE s.status = "active"
      ORDER BY s.sort_order ASC, s.id ASC'
);
$categories = DB::all('SELECT * FROM service_categories ORDER BY sort_order ASC, id ASC');

$PAGE_TITLE = SITE_NAME . ' — Ultra-Fast Web Development in Bangladesh';
$PAGE_DESC  = 'Fast, conversion-focused web development in Bangladesh — e-commerce, SaaS, portfolios & landing pages. Bilingual EN/বাংলা.';
$LOAD_ORDER_JS = true;
require_once __DIR__ . '/includes/public-header.php';

$gridGrads = ['from-cyan-500/25 to-blue-600/20', 'from-violet-500/25 to-fuchsia-600/20', 'from-teal-500/25 to-emerald-600/20', 'from-sky-500/25 to-indigo-600/20', 'from-fuchsia-500/25 to-pink-600/25', 'from-amber-500/20 to-orange-600/20'];
$cardIcons = ['🛒', '🚀', '💼', '🎯', '⚙️', '🏢'];
?>

<!-- ============ HERO ============ -->
<section id="home" class="relative min-h-[92vh] flex items-center overflow-hidden">
  <div class="absolute inset-0 bg-[radial-gradient(1100px_620px_at_18%_-10%,rgba(15,118,110,.35),transparent_60%),radial-gradient(950px_560px_at_85%_10%,rgba(99,102,241,.28),transparent_55%)]"></div>
  <div class="aurora-blob w-[560px] max-w-[85vw] h-[420px] bg-cyan-500/20 top-[8%] -left-40" style="animation-delay:0s"></div>
  <div class="aurora-blob w-[520px] max-w-[80vw] h-[400px] bg-violet-600/20 top-[30%] right-[-160px]" style="animation-delay:-6s"></div>
  <div class="aurora-blob w-[420px] max-w-[70vw] h-[340px] bg-teal-500/20 bottom-[-120px] left-[30%]" style="animation-delay:-12s"></div>
  <div class="absolute inset-0 hero-dot-grid"></div>
  <canvas id="aurora-canvas" aria-hidden="true"></canvas>

  <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 py-24 w-full">
    <div class="max-w-3xl">
      <div class="reveal inline-flex items-center gap-2 glass-chip rounded-full px-4 py-2 text-xs font-bold text-cyan-300">
        <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-400"></span></span>
        <span data-i18n="hero_badge">Bangladesh · Web Development Agency</span>
      </div>

      <h1 class="mt-6 text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white leading-[1.05]">
        <span class="reveal" style="--rd:80ms"><span data-i18n="hero_title_1">Websites that</span> <span class="text-gradient">convert.</span></span><br>
        <span class="reveal" style="--rd:180ms">Built at <span class="animate-gradient-x text-gradient-accent">cyber speed.</span></span>
      </h1>

      <p class="reveal mt-6 text-lg sm:text-xl text-slate-400 max-w-xl" style="--rd:280ms">
        <span data-i18n="hero_sub">We craft blazing-fast e-commerce, SaaS and portfolio websites for Bangladeshi brands — bilingual, beautiful, and built to sell.</span>
      </p>

      <div class="reveal mt-9 flex flex-wrap items-center gap-4" style="--rd:380ms">
        <a href="#order" class="btn-teal !px-8 !py-4 text-base"><span data-i18n="hero_cta_primary">Build my website</span></a>
        <a href="#services" class="btn-ghost !px-8 !py-4 text-base"><span data-i18n="hero_cta_secondary">Explore services</span></a>
      </div>

      <div class="reveal mt-14 grid grid-cols-3 gap-6 max-w-md" style="--rd:480ms">
        <div>
          <div class="text-3xl sm:text-4xl font-extrabold text-white">120<span class="text-gradient">+</span></div>
          <div class="mt-1 text-xs sm:text-sm text-slate-500"><span data-i18n="hero_stat_project">Projects shipped</span></div>
        </div>
        <div>
          <div class="text-3xl sm:text-4xl font-extrabold text-white">95<span class="text-gradient">+</span></div>
          <div class="mt-1 text-xs sm:text-sm text-slate-500"><span data-i18n="hero_stat_client">Happy clients</span></div>
        </div>
        <div>
          <div class="text-3xl sm:text-4xl font-extrabold text-white"><span class="text-gradient">24h</span></div>
          <div class="mt-1 text-xs sm:text-sm text-slate-500"><span data-i18n="hero_stat_support">Response</span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-slate-600 animate-bounce" aria-hidden="true">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
  </div>
</section>

<!-- ============ SERVICES ============ -->
<section id="services" class="relative py-24">
  <div class="mx-auto max-w-7xl px-4 sm:px-6">
    <div class="max-w-2xl">
      <p class="reveal text-xs font-extrabold tracking-[.3em] uppercase text-cyan-400"><?= l('What we do best', 'যা আমরা সবচেয়ে ভালো করি') ?></p>
      <h2 class="reveal mt-3 text-4xl sm:text-5xl font-extrabold text-white" style="--rd:80ms">
        <span data-i18n="sec_services">Our Services</span>
      </h2>
      <p class="reveal mt-4 text-slate-400" style="--rd:160ms"><span data-i18n="sec_services_sub">Conversion-obsessed web development, priced for the Bangladeshi market.</span></p>
    </div>

    <!-- category filter tabs -->
    <div class="reveal mt-10 flex flex-wrap gap-2.5" style="--rd:240ms">
      <button type="button" data-filter="all" class="is-active filter-tab glass-chip rounded-xl px-4 py-2 text-sm font-bold text-slate-200 hover:text-cyan-300 transition-all">
        <span data-i18n="filter_all">All</span>
      </button>
      <?php foreach ($categories as $c): ?>
        <button type="button" data-filter="<?= e($c['slug']) ?>" class="filter-tab glass-chip rounded-xl px-4 py-2 text-sm font-bold text-slate-300 hover:text-cyan-300 transition-all">
          <?= l($c['name_en'], $c['name_bn']) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- services grid -->
    <div id="services-grid" class="stagger mt-12 grid gap-7 md:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($services as $i => $s):
          $featEn = json_decode((string) $s['features_en'], true) ?: [];
          $featBn = json_decode((string) $s['features_bn'], true) ?: [];
          $grad = $gridGrads[$i % count($gridGrads)];
          $icon = $cardIcons[$i % count($cardIcons)];
      ?>
      <article data-reveal="svc-<?= (int) floor($i / 3) ?>" data-category="<?= e($s['cat_slug'] ?? 'custom') ?>"
               class="service-card neon-glow grad-border glass rounded-3xl p-7 flex flex-col transition-all duration-500 hover:-translate-y-2 hover:shadow-neon-teal">
        <?php if ((int) $s['is_featured'] === 1): ?>
          <span class="absolute top-5 right-5 z-10">
            <span class="badge text-emerald-950 bg-gradient-to-r from-emerald-300 to-cyan-300 shadow-[0_0_24px_-6px_rgba(45,212,191,.8)]"><span data-i18n="featured">Popular</span></span>
          </span>
        <?php endif; ?>

        <div class="relative h-40 rounded-2xl overflow-hidden bg-gradient-to-br <?= e($grad) ?> mb-6 flex items-center justify-center">
          <?php if (!empty($s['thumbnail'])): ?>
            <img src="<?= e(uploads_url($s['thumbnail'])) ?>" alt="<?= e($s['title_en']) ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
          <?php else: ?>
            <span class="text-5xl opacity-80"><?= $icon ?></span>
          <?php endif; ?>
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 to-transparent"></div>
          <span class="absolute bottom-3 left-3 glass-chip rounded-full px-3 py-1 text-[11px] font-bold text-cyan-200">
            <?= l($s['cat_en'] ?: 'Custom', $s['cat_bn'] ?: 'কাস্টম') ?>
          </span>
        </div>

        <h3 class="text-xl font-bold text-white leading-snug"><?= l($s['title_en'], $s['title_bn']) ?></h3>
        <p class="mt-2.5 text-sm text-slate-400 leading-relaxed line-clamp-3"><?= l($s['short_desc_en'], $s['short_desc_bn']) ?></p>

        <ul class="mt-5 space-y-2 text-sm text-slate-300">
          <?php foreach (array_slice($featEn, 0, 4) as $k => $f): ?>
            <li class="flex items-start gap-3">
              <svg class="mt-0.5 w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span><span class="e"><?= e($f) ?></span><span class="b"><?= e($featBn[$k] ?? '') ?></span></span>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="mt-6 pt-5 border-t border-white/10 flex items-end justify-between">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500"><?= l($s['price_label'], $s['price_label'] === 'Starts from' ? 'শুরু মাত্র' : ($s['price_label'] === 'Fixed' ? 'সর্বমোট' : $s['price_label'])) ?></div>
            <div class="mt-1 text-2xl font-extrabold text-gradient"><?= e(price_fmt($s['price'])) ?></div>
          </div>
          <a href="#order" class="service-cta inline-flex items-center gap-2 text-sm font-bold text-cyan-300 hover:text-cyan-200 transition-all"
             data-service-id="<?= (int) $s['id'] ?>" data-service-name="<?= e($s['title_en']) ?>" data-service-name-bn="<?= e($s['title_bn']) ?>">
            <span data-i18n="card_cta">Start this service</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/></svg>
          </a>
        </div>
      </article>
      <?php endforeach; ?>

      <div id="services-empty" class="hidden md:col-span-2 lg:col-span-3 text-center glass rounded-3xl p-14 text-slate-400">
        <?= l('No services in this category yet.', 'এই ক্যাটাগরিতে এখনো কোনো সার্ভিস নেই।') ?>
      </div>
    </div>
  </div>
</section>

<!-- ============ PORTFOLIO ============ -->
<section id="portfolio" class="relative py-24 bg-slate-900/40">
  <div class="mx-auto max-w-7xl px-4 sm:px-6">
    <div class="max-w-2xl">
      <p class="reveal text-xs font-extrabold tracking-[.3em] uppercase text-cyan-400"><span data-i18n="sec_portfolio_sub">A taste of the products we shipped for our clients.</span></p>
      <h2 class="reveal mt-3 text-4xl sm:text-5xl font-extrabold text-white" style="--rd:80ms"><span data-i18n="sec_portfolio">Selected Work</span></h2>
    </div>

    <div class="stagger mt-12 grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
      <?php
      $pf = [
        ['t' => 'Disha Mango Export',  'b' => 'দিশা আম এক্সপোর্ট',  'c' => 'E-Commerce', 'cb' => 'ই-কমার্স', 'g' => 'from-amber-500/30 to-orange-600/25', 'i' => '🥭'],
        ['t' => 'ScholarBox EdTech SaaS','b' => 'স্কলারবক্স এডটেক সাস','c' => 'SaaS', 'cb' => 'সাস', 'g' => 'from-violet-500/30 to-fuchsia-600/25', 'i' => '🎓'],
        ['t' => 'FitGram Coach Portfolio','b' => 'ফিটগ্রাম কোচ পোর্টফোলিও','c' => 'Portfolio', 'cb' => 'পোর্টফোলিও', 'g' => 'from-cyan-500/30 to-sky-600/25', 'i' => '🏋️'],
        ['t' => 'Nirman Real-Estate Landing','b' => 'নির্মাণ রিয়েল-এস্টেট ল্যান্ডিং','c' => 'Landing', 'cb' => 'ল্যান্ডিং', 'g' => 'from-teal-500/30 to-emerald-600/25', 'i' => '🏙️'],
        ['t' => 'Medicare Appointment System','b' => 'মেডিকেয়ার অ্যাপয়েন্টমেন্ট সিস্টেম','c' => 'Custom App', 'cb' => 'কাস্টম অ্যাপ', 'g' => 'from-sky-500/30 to-indigo-600/25', 'i' => '🩺'],
        ['t' => 'Tant & Thread Handloom Store','b' => 'তাঁত এন্ড থ্রেড হ্যান্ডলুম স্টোর','c' => 'E-Commerce', 'cb' => 'ই-কমার্স', 'g' => 'from-fuchsia-500/30 to-pink-600/25', 'i' => '🧶'],
      ];
      foreach ($pf as $i => $p): ?>
      <div data-reveal="pf-<?= (int) floor($i / 3) ?>" class="group relative overflow-hidden rounded-3xl grad-border glass aspect-[4/3] cursor-pointer">
        <div class="absolute inset-0 bg-gradient-to-br <?= e($p['g']) ?> flex items-center justify-center transition-transform duration-700 group-hover:scale-110">
          <span class="text-6xl drop-shadow-2xl"><?= $p['i'] ?></span>
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent"></div>
        <div class="absolute bottom-0 inset-x-0 p-6 translate-y-1 transition-transform duration-500 group-hover:translate-y-0">
          <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-300"><?= l($p['c'], $p['cb']) ?></span>
          <h3 class="mt-1 text-lg font-bold text-white"><?= l($p['t'], $p['b']) ?></h3>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ TESTIMONIALS ============ -->
<section id="testimonials" class="py-24">
  <div class="mx-auto max-w-7xl px-4 sm:px-6">
    <div class="max-w-2xl">
      <p class="reveal text-xs font-extrabold tracking-[.3em] uppercase text-cyan-400"><span data-i18n="sec_testimonials_sub">Real words from founders and freelancers across Bangladesh.</span></p>
      <h2 class="reveal mt-3 text-4xl sm:text-5xl font-extrabold text-white" style="--rd:80ms"><span data-i18n="sec_testimonials">Client Love</span></h2>
    </div>

    <div class="stagger mt-12 grid gap-7 md:grid-cols-3">
      <?php
      $tm = [
        ['q' => 'Our online store revenue doubled within three months of launch. The team thinks like a business partner, not a vendor.', 'qb' => 'লঞ্চের তিন মাসের মধ্যে আমাদের অনলাইন স্টোরের আয় দ্বিগুণ হয়েছে। টিমটা বিক্রেতা নয়, ব্যবসার অংশীদারের মতো আচরণ করে।', 'n' => 'Rahim Karim', 'nb' => 'রহিম করিম', 'r' => 'Disha Mango Export', 'rb' => 'দিশা আম এক্সপোর্ট', 'i' => 'RK'],
        ['q' => 'The SaaS dashboard we ordered is fast, clean and bilingual. Support answered every question on WhatsApp almost instantly.', 'qb' => 'আমাদের অর্ডার করা সাস ড্যাশবোর্ড দ্রুত, পরিষ্কার এবং দ্বিভাষিক। প্রায় সব প্রশ্নের উত্তর হোয়াটসঅ্যাপে তৎক্ষণাৎ পেয়েছি।', 'n' => 'Tamanna Rahman', 'nb' => 'তামান্না রহমান', 'r' => 'ScholarBox', 'rb' => 'স্কলারবক্স', 'i' => 'TR'],
        ['q' => 'Clean code, beautiful design and honest pricing. My portfolio now ranks on the first page of Google in Dhaka.', 'qb' => 'পরিষ্কার কোড, সুন্দর ডিজাইন ও সৎ মূল্য। আমার পোর্টফোলিও এখন ঢাকায় গুগলের প্রথম পাতায়।', 'n' => 'Shafiq Ahmed', 'nb' => 'শফিক আহমেদ', 'r' => 'Freelance Designer', 'rb' => 'ফ্রিল্যান্স ডিজাইনার', 'i' => 'SA'],
      ];
      foreach ($tm as $i => $t): ?>
      <figure data-reveal="tm-<?= (int) floor($i / 3) ?>" class="glass rounded-3xl p-7 flex flex-col relative overflow-hidden">
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-cyan-400/0 via-cyan-400/40 to-accent-electric/0"></div>
        <svg class="w-8 h-8 text-cyan-400/70 mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9.6 5C6 6.6 3.6 9.6 3.6 13.6c0 3 1.9 5.4 4.7 5.4 2.4 0 4.2-1.8 4.2-4.2 0-2.3-1.6-4-3.8-4.1.3-2 1.8-3.9 3.8-5L9.6 5zm10 0c-3.6 1.6-6 4.6-6 8.6 0 3 1.9 5.4 4.7 5.4 2.4 0 4.2-1.8 4.2-4.2 0-2.3-1.6-4-3.8-4.1.3-2 1.8-3.9 3.8-5L19.6 5z"/></svg>
        <blockquote class="text-slate-300 text-[15px] leading-relaxed flex-1"><?= l($t['q'], $t['qb']) ?></blockquote>
        <figcaption class="mt-6 flex items-center gap-3">
          <span class="w-11 h-11 rounded-2xl grid place-items-center font-extrabold text-slate-900 bg-gradient-to-br from-cyan-300 to-violet-400"><?= $t['i'] ?></span>
          <div>
            <div class="font-bold text-white text-sm"><?= l($t['n'], $t['nb']) ?></div>
            <div class="text-xs text-slate-500"><?= l($t['r'], $t['rb']) ?></div>
          </div>
          <span class="ml-auto flex gap-0.5 text-amber-300 text-xs" aria-label="5 stars">★★★★★</span>
        </figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ CUSTOM ORDER ============ -->
<section id="order" class="relative py-24">
  <div class="aurora-blob w-[460px] max-w-[80vw] h-[380px] bg-accent-electric/15 bottom-0 right-[-180px]"></div>
  <div class="relative mx-auto max-w-7xl px-4 sm:px-6">
    <div class="grid gap-12 lg:grid-cols-2 items-center">
      <div>
        <p class="reveal text-xs font-extrabold tracking-[.3em] uppercase text-cyan-400"><span data-i18n="sec_order_sub">Reply within 24 hours.</span></p>
        <h2 class="reveal mt-3 text-4xl sm:text-5xl font-extrabold text-white" style="--rd:80ms"><span data-i18n="sec_order">Launch Your Project</span></h2>
        <p class="reveal mt-5 text-slate-400 text-lg max-w-md" style="--rd:160ms"><span data-i18n="sec_order_sub">Tell us what you need — we reply within 24 hours.</span></p>
        <ul class="reveal mt-8 space-y-4 text-sm text-slate-300" style="--rd:240ms">
          <li class="flex items-center gap-3"><span class="w-8 h-8 rounded-xl grid place-items-center bg-gradient-to-br from-brand-deep to-brand-light text-xs text-slate-900 font-extrabold">1</span> <?= l('Free consultation & detailed estimate', 'ফ্রি কনসালটেশন ও বিস্তারিত এস্টিমেট') ?></li>
          <li class="flex items-center gap-3"><span class="w-8 h-8 rounded-xl grid place-items-center bg-gradient-to-br from-brand-deep to-brand-light text-xs text-slate-900 font-extrabold">2</span> <?= l('Fixed timeline with weekly progress updates', 'ফিক্সড টাইমলাইন, সাপ্তাহিক আপডেট') ?></li>
          <li class="flex items-center gap-3"><span class="w-8 h-8 rounded-xl grid place-items-center bg-gradient-to-br from-brand-deep to-brand-light text-xs text-slate-900 font-extrabold">3</span> <?= l('Launch + 30 days free support', 'লঞ্চ + ৩০ দিন ফ্রি সাপোর্ট') ?></li>
        </ul>
        <div class="reveal mt-8 flex items-center gap-4" style="--rd:320ms">
          <a href="<?= e(WHATSAPP_LINK) ?>" target="_blank" rel="noopener" class="btn-ghost">
            <span><?= l('Prefers WhatsApp?', 'হোয়াটসঅ্যাপ পছন্দ করেন?') ?></span>
          </a>
        </div>
      </div>

      <div class="reveal-right glass-strong rounded-[2rem] p-7 sm:p-9" data-reveal="order">
        <form id="order-form" novalidate>
          <?= csrf_field() ?>
          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label class="label"><span data-i18n="order_name">Full name</span></label>
              <input class="input" name="name" required minlength="2" autocomplete="name" value="<?= e(current_user()['name'] ?? '') ?>">
            </div>
            <div>
              <label class="label"><span data-i18n="order_email">Email address</span></label>
              <input class="input" type="email" name="email" required autocomplete="email" value="<?= e(current_user()['email'] ?? '') ?>">
            </div>
            <div>
              <label class="label"><span data-i18n="order_phone">Phone / WhatsApp</span></label>
              <input class="input" name="phone" placeholder="01XXXXXXXXX" autocomplete="tel" value="<?= e(current_user()['phone'] ?? '') ?>">
            </div>
            <div>
              <label class="label"><span data-i18n="order_type">Project type</span></label>
              <select class="input" name="project_type" required>
                <?php foreach ($categories as $c): ?>
                  <option value="<?= e($c['name_en']) ?>"><?= l($c['name_en'], $c['name_bn']) ?></option>
                <?php endforeach; ?>
                <option value="Other"><?= l('Other', 'অন্যান্য') ?></option>
              </select>
            </div>
            <div>
              <label class="label"><span data-i18n="order_service">Choose a service</span></label>
              <select class="input" name="service_id" id="order-service">
                <option value=""><?= l('General / unsure yet', 'সাধারণ / এখনো নিশ্চিত নই') ?></option>
                <?php foreach ($services as $s): ?>
                  <option value="<?= (int) $s['id'] ?>"><?= e($s['title_en']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="label"><span data-i18n="order_budget">Estimated budget (BDT)</span></label>
              <input class="input" type="number" name="budget" min="0" step="500" placeholder="25000">
            </div>
          </div>
          <div class="mt-5">
            <label class="label"><span data-i18n="order_details">Tell us about your project</span></label>
            <textarea class="input min-h-[120px]" name="details" required minlength="5" placeholder="e.g. An online shop for my clothing brand with bKash payment…"></textarea>
          </div>
          <p class="mt-3 text-xs text-slate-500 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/></svg>
            <span data-i18n="order_note">No login needed.</span>
          </p>
          <button type="submit" class="btn-teal w-full mt-5 !py-4">
            <span data-i18n="order_submit">Send project brief →</span>
          </button>

          <div id="order-result" class="hidden mt-5 rounded-2xl border p-5"></div>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/public-footer.php'; ?>