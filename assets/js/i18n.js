/* ==================================================================
   AURORA CYBER — i18n engine (EN | বাংলা)
   Lightweight vanilla dictionary that swaps text via data-i18n keys,
   hides the inactive language on server-rendered .e/.b span pairs via
   CSS, and persists the preference to localStorage.

   HTML contract:
     <span data-i18n="nav_home"></span>        → filled from DICT (+ DB overrides)
     <span class="e">EN</span><span class="b">বাংলা</span> → CSS toggle
     <button data-lang-toggle aria-label="Switch language">…</button>
   ================================================================== */
(function () {
  'use strict';

  var LANG_KEY = 'wclang';

  var DICT = {
    /* -------- navigation -------- */
    nav_home: { en: 'Home', bn: 'হোম' },
    nav_services: { en: 'Services', bn: 'সার্ভিস' },
    nav_portfolio: { en: 'Portfolio', bn: 'পোর্টফোলিও' },
    nav_testimonials: { en: 'Testimonials', bn: 'রিভিউ' },
    nav_order: { en: 'Start a project', bn: 'প্রজেক্ট শুরু করুন' },
    nav_login: { en: 'Sign in', bn: 'লগইন' },
    nav_register: { en: 'Create account', bn: 'নিবন্ধন' },
    nav_dashboard: { en: 'Dashboard', bn: 'ড্যাশবোর্ড' },
    nav_logout: { en: 'Sign out', bn: 'লগআউট' },

    /* -------- hero -------- */
    hero_badge: { en: 'Bangladesh · Web Development Agency', bn: 'বাংলাদেশ · ওয়েব ডেভেলপমেন্ট এজেন্সি' },
    hero_title_1: { en: 'Websites that', bn: 'ওয়েবসাইট যা' },
    hero_title_2: { en: 'convert. Built at', bn: 'কনভার্ট করে। গড়া,' },
    hero_title_3: { en: 'cyber speed.', bn: 'সাইবার গতিতে।' },
    hero_sub: { en: 'We craft blazing-fast e-commerce, SaaS and portfolio websites for Bangladeshi brands — bilingual, beautiful, and built to sell.', bn: 'আমরা বাংলাদেশি ব্র্যান্ডগুলোর জন্য দ্রুতগতির ই-কমার্স, সাস ও পোর্টফোলিও ওয়েবসাইট বানাই — দ্বিভাষিক, সুন্দর ও বিক্রি করার মতো তৈরি।' },
    hero_cta_primary: { en: 'Build my website', bn: 'আমার ওয়েবসাইট বানান' },
    hero_cta_secondary: { en: 'Explore services', bn: 'সার্ভিস দেখুন' },
    hero_stat_project: { en: 'Projects shipped', bn: 'হাতে-কলমে প্রজেক্ট' },
    hero_stat_client: { en: 'Happy clients', bn: 'সন্তুষ্ট ক্লায়েন্ট' },
    hero_stat_support: { en: 'Support', bn: 'সাপোর্ট' },

    /* -------- sections -------- */
    sec_services: { en: 'Our Services', bn: 'আমাদের সার্ভিস' },
    sec_services_sub: { en: 'Conversion-obsessed web development, priced for the Bangladeshi market.', bn: 'বাংলাদেশি বাজারের জন্য ডিজাইনকৃত, কনভার্সন-ফোকাসড ওয়েব ডেভেলপমেন্ট।' },
    sec_portfolio: { en: 'Selected Work', bn: 'নির্বাচিত কাজ' },
    sec_portfolio_sub: { en: 'A taste of the products we shipped for our clients.', bn: 'আমাদের ক্লায়েন্টদের জন্য তৈরি কিছু কাজের নমুনা।' },
    sec_testimonials: { en: 'Client Love', bn: 'ক্লায়েন্টদের মতামত' },
    sec_testimonials_sub: { en: 'Real words from founders and freelancers across Bangladesh.', bn: 'বাংলাদেশজুড়ে ফাউন্ডার ও ফ্রিল্যান্সারদের আসল মতামত।' },
    filter_all: { en: 'All', bn: 'সব' },

    /* -------- service cards -------- */
    card_cta: { en: 'Start this service', bn: 'এই সার্ভিস শুরু করুন' },
    service_from: { en: 'Starts from', bn: 'শুরু মাত্র' },
    featured: { en: 'Popular', bn: 'জনপ্রিয়' },

    /* -------- order form section -------- */
    sec_order: { en: 'Launch Your Project', bn: 'আপনার প্রজেক্ট চালু করুন' },
    sec_order_sub: { en: 'Tell us what you need — we reply within 24 hours.', bn: 'আপনার প্রয়োজন জানান — আমরা ২৪ ঘণ্টার মধ্যে উত্তর দিই।' },
    order_name: { en: 'Full name', bn: 'পুরো নাম' },
    order_email: { en: 'Email address', bn: 'ইমেইল ঠিকানা' },
    order_phone: { en: 'Phone / WhatsApp', bn: 'ফোন / হোয়াটসঅ্যাপ' },
    order_type: { en: 'Project type', bn: 'প্রজেক্টের ধরন' },
    order_service: { en: 'Choose a service (optional)', bn: 'একটি সার্ভিস বাছুন (ঐচ্ছিক)' },
    order_budget: { en: 'Estimated budget (BDT)', bn: 'আনুমানিক বাজেট (টাকা)' },
    order_details: { en: 'Tell us about your project', bn: 'আপনার প্রজেক্ট সম্পর্কে জানান' },
    order_submit: { en: 'Send project brief →', bn: 'প্রজেক্ট ব্রিফ পাঠান →' },
    order_note: { en: 'No login needed.', bn: 'লগইনের প্রয়োজন নেই।' },
    order_success_title: { en: 'Brief received!', bn: 'ব্রিফ পেয়েছি!' },
    order_success_msg: { en: 'Our team will review your project and contact you within 24 hours.', bn: 'আমাদের টিম আপনার প্রজেক্ট রিভিউ করে ২৪ ঘণ্টার মধ্যে যোগাযোগ করবে।' },
    order_wa_continue: { en: 'Continue on WhatsApp', bn: 'হোয়াটসঅ্যাপে চালিয়ে যান' },
    order_wa_status: { en: 'Check order on WhatsApp', bn: 'হোয়াটসঅ্যাপে অর্ডার দেখুন' },

    /* -------- footer -------- */
    footer_tag: { en: 'Web experiences engineered in Bangladesh for the world.', bn: 'বিশ্বের জন্য বাংলাদেশে তৈরি ডিজিটাল অভিজ্ঞতা।' },
    footer_services: { en: 'Services', bn: 'সার্ভিস' },
    footer_company: { en: 'Company', bn: 'কোম্পানি' },
    footer_support: { en: 'Support', bn: 'সাপোর্ট' },
    footer_rights: { en: 'All rights reserved.', bn: 'সর্বস্বত্ব সংরক্ষিত।' },

    /* -------- chat widget -------- */
    chat_title: { en: 'Aurora Assistant', bn: 'অরোরা সহকারী' },
    chat_subtitle: { en: 'Fast replies, Mon–Sat', bn: 'দ্রুত উত্তর, রবি-শুক্র' },
    chat_placeholder: { en: 'Type your message…', bn: 'আপনার মেসেজ লিখুন…' },
    chat_name_label: { en: 'What should we call you?', bn: 'আপনার নাম কী?' },
    chat_name_placeholder: { en: 'Your name', bn: 'আপনার নাম' },
    chat_name_start: { en: 'Start chat', bn: 'চ্যাট শুরু করুন' },
    chat_name_error: { en: 'Please enter your name (2–60 characters).', bn: 'অনুগ্রহ করে আপনার নাম লিখুন (২–৬০ অক্ষর)।' },
    chat_bot_hi: { en: 'Hi there! 👋 Welcome to Aurora Cyber. I am your site assistant.', bn: 'আসসালামু আলাইকুম! 👋 অরোরা সাইবারে স্বাগতম। আমি আপনার সাইট সহকারী।' },
    chat_bot_menu: { en: 'How can I help you?', bn: 'কীভাবে সাহায্য করতে পারি?' },
    chat_opt_services: { en: 'Our services & pricing', bn: 'আমাদের সার্ভিস ও মূল্য' },
    chat_opt_order: { en: 'Track my order', bn: 'অর্ডার ট্র্যাক করুন' },
    chat_opt_human: { en: 'Talk to a human', bn: 'মানুষের সাথে কথা বলুন' },
    chat_taken: { en: 'Our team has taken over this chat.', bn: 'আমাদের টিম এই চ্যাটে জয়েন করেছে।' },
    chat_you: { en: 'You', bn: 'আপনি' },
    chat_bot: { en: 'Bot', bn: 'বট' },
    chat_agent: { en: 'Agent', bn: 'এজেন্ট' },

    /* -------- account / auth -------- */
    acc_login_title: { en: 'Welcome back', bn: 'আবারও স্বাগতম' },
    acc_login_sub: { en: 'Sign in to track orders, chat history and verification.', bn: 'অর্ডার, চ্যাট ইতিহাস ও যাচাইকরণ দেখতে লগইন করুন।' },
    acc_login_btn: { en: 'Sign in', bn: 'লগইন করুন' },
    acc_register_title: { en: 'Join Aurora Cyber', bn: 'অরোরা সাইবারে যোগ দিন' },
    acc_register_sub: { en: 'Track your projects and get verified for priority support.', bn: 'আপনার প্রজেক্ট ট্র্যাক করুন এবং প্রায়োরিটি সাপোর্টের জন্য যাচাইকৃত হোন।' },
    acc_register_btn: { en: 'Create account', bn: 'অ্যাকাউন্ট তৈরি করুন' },
    acc_name: { en: 'Full name', bn: 'পুরো নাম' },
    acc_email: { en: 'Email address', bn: 'ইমেইল ঠিকানা' },
    acc_phone: { en: 'Phone / WhatsApp', bn: 'ফোন / হোয়াটসঅ্যাপ' },
    acc_password: { en: 'Password', bn: 'পাসওয়ার্ড' },
    acc_password_hint: { en: 'At least 6 characters.', bn: 'কমপক্ষে ৬ অক্ষর।' },
    acc_no_account: { en: 'New here?', bn: 'নতুন?' },
    acc_create_one: { en: 'Create an account', bn: 'অ্যাকাউন্ট তৈরি করুন' },
    acc_have_account: { en: 'Already have an account?', bn: 'আগে থেকে অ্যাকাউন্ট আছে?' },
    acc_sign_in: { en: 'Sign in', bn: 'লগইন' },
    acc_fields_required: { en: 'Please fill in all required fields.', bn: 'সব প্রয়োজনীয় ঘর পূরণ করুন।' },
    acc_bad_credentials: { en: 'Incorrect email or password.', bn: 'ইমেইল বা পাসওয়ার্ড ভুল।' },
    acc_email_taken: { en: 'An account with this email already exists.', bn: 'এই ইমেইলে আগেই অ্যাকাউন্ট আছে।' },
    acc_weak_password: { en: 'Password must be at least 6 characters.', bn: 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।' },
    acc_account_created: { en: 'Account created — welcome to Aurora Cyber!', bn: 'অ্যাকাউন্ট তৈরি হয়েছে — অরোরা সাইবারে স্বাগতম!' },

    /* -------- verification page -------- */
    verify_title: { en: 'Verify Your Account', bn: 'আপনার অ্যাকাউন্ট যাচাই করুন' },
    verify_sub: { en: 'Verified customers get priority replies and order tracking.', bn: 'যাচাইকৃত গ্রাহকরা প্রায়োরিটি উত্তর ও অর্ডার ট্র্যাকিং পাবেন।' },
    verify_email_btn: { en: 'Send email code', bn: 'ইমেইল কোড পাঠান' },
    verify_email_ok: { en: 'Code sent to your email.', bn: 'আপনার ইমেইলে কোড পাঠানো হয়েছে।' },
    verify_whatsapp_btn: { en: 'Verify via WhatsApp', bn: 'হোয়াটসঅ্যাপে যাচাই করুন' },
    verify_whatsapp_link: { en: 'Send code on WhatsApp', bn: 'হোয়াটসঅ্যাপে কোড পাঠান' },
    verify_otp_placeholder: { en: 'Enter the 6-digit code', bn: '৬ ডিজিটের কোড লিখুন' },
    verify_confirm_btn: { en: 'Confirm code', bn: 'কোড নিশ্চিত করুন' },
    verify_resend: { en: 'Re-send code', bn: 'আবার কোড পাঠান' },
    verify_dev_hint: { en: 'Dev mode: your code is', bn: 'ডেভ মোড: আপনার কোড হলো' },
    verify_success: { en: 'Successfully verified!', bn: 'সফলভাবে যাচাইকৃত!' },

    /* -------- customer dashboard -------- */
    dash_greeting: { en: 'Hello', bn: 'আসসালামু আলাইকুম' },
    dash_orders: { en: 'My Orders', bn: 'আমার অর্ডার' },
    dash_chat: { en: 'Chat History', bn: 'চ্যাট ইতিহাস' },
    dash_verification: { en: 'Verification', bn: 'যাচাইকরণ' },
    dash_no_orders: { en: 'No orders yet — start your first project!', bn: 'এখনো কোনো অর্ডার নেই — আপনার প্রথম প্রজেক্ট শুরু করুন!' },
    dash_order_id: { en: 'Order', bn: 'অর্ডার' },
    dash_item: { en: 'Project', bn: 'প্রজেক্ট' },
    dash_date: { en: 'Date', bn: 'তারিখ' },
    dash_status: { en: 'Status', bn: 'অবস্থা' },
    dash_go_new: { en: 'Start a project', bn: 'প্রজেক্ট শুরু করুন' },
    dash_chat_empty: { en: 'No chat history yet.', bn: 'এখনো চ্যাট ইতিহাস নেই।' },
    dash_verify_cta: { en: 'Complete verification', bn: 'যাচাইকরণ সম্পন্ন করুন' },

    /* -------- admin sidebar / chrome -------- */
    a_dashboard: { en: 'Dashboard', bn: 'ড্যাশবোর্ড' },
    a_services: { en: 'Services', bn: 'সার্ভিস' },
    a_categories: { en: 'Categories', bn: 'ক্যাটাগরি' },
    a_orders: { en: 'Orders', bn: 'অর্ডার' },
    a_payments: { en: 'Payments', bn: 'পেমেন্ট' },
    a_customers: { en: 'Customers', bn: 'গ্রাহক' },
    a_chats: { en: 'Live Chats', bn: 'লাইভ চ্যাট' },
    a_site: { en: 'View site', bn: 'সাইট দেখুন' },
    a_logout: { en: 'Sign out', bn: 'লগআউট' },
    a_admin: { en: 'Admin Panel', bn: 'অ্যাডমিন প্যানেল' }
  };

  var lang = localStorage.getItem(LANG_KEY);
  if (!lang) {
    lang = (navigator.language || 'en').toLowerCase().indexOf('bn') === 0 ? 'bn' : 'en';
  }
  lang = lang === 'bn' ? 'bn' : 'en';

  function apply(announce) {
    var root = document.documentElement;
    root.classList.remove('lang-en', 'lang-bn');
    root.classList.add('lang-' + lang);
    root.setAttribute('lang', lang === 'bn' ? 'bn' : 'en');
    root.style.setProperty('--lang', JSON.stringify(lang));

    // hydrate data-i18n nodes
    var nodes = document.querySelectorAll('[data-i18n]');
    for (var i = 0; i < nodes.length; i++) {
      var key = nodes[i].getAttribute('data-i18n');
      var entry = (DICT[key] && DICT[key][lang]) || null;
      if (entry !== null) nodes[i].textContent = entry;
    }
    // hydrated placeholders (inputs)
    var ph = document.querySelectorAll('[data-i18n-placeholder]');
    for (var p = 0; p < ph.length; p++) {
      var pkey = ph[p].getAttribute('data-i18n-placeholder');
      var pentry = (DICT[pkey] && DICT[pkey][lang]) || null;
      if (pentry !== null) ph[p].placeholder = pentry;
    }
    if (announce) {
      document.dispatchEvent(new CustomEvent('langchange', { detail: { lang: lang } }));
    }
  }

  function t(key) {
    var entry = DICT[key];
    return entry ? (entry[lang] || entry.en) : key;
  }

  // server-side DB overrides merge first (translations table)
  if (window.fetch) {
    fetch((window.AURORA_BASE || '') + '/api/translations.php' + (location.search && location.search.indexOf('lng') > -1 ? '' : '')).then(function (r) { return r.json(); }).then(function (data) {
      if (data && data.ok && data.list) {
        data.list.forEach(function (row) {
          if (!DICT[row.dict_key]) DICT[row.dict_key] = {};
          DICT[row.dict_key].en = row.en;
          DICT[row.dict_key].bn = row.bn;
        });
        apply(false);
      }
    }).catch(function () {});
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('[data-lang-toggle]') : null;
    if (!btn) return;
    lang = lang === 'bn' ? 'en' : 'bn';
    localStorage.setItem(LANG_KEY, lang);
    apply(true);
  });

  // expose
  window.I18N = { lang: lang, apply: apply, t: t, DICT: DICT };
  document.addEventListener('DOMContentLoaded', function () { apply(false); });
  if (document.readyState === 'interactive' || document.readyState === 'complete') {
    apply(false);
  }
})();