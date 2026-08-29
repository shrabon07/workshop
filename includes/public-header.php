<?php
/* Shared <head> — runs before any markup. Requires config to be loaded. */
if (!defined('APP_PATH')) {
    require_once __DIR__ . '/../config.php';
}
$PAGE_TITLE = $PAGE_TITLE ?? (SITE_NAME . ' — Web Development Agency');
$PAGE_DESC  = $PAGE_DESC ?? 'Fast, modern, conversion-focused websites for Bangladeshi businesses — e-commerce, SaaS, portfolios. Bilingual EN/বাংলা.';
$ver = version_time();
?><!doctype html>
<html lang="en" class="lang-en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<meta name="description" content="<?= e($PAGE_DESC) ?>">
<title><?= e($PAGE_TITLE) ?></title>

<link rel="icon" type="image/svg+xml" href="<?= e(url('assets/img/logo.svg')) ?>">
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..900&family=Noto+Sans+Bengali:wght@400;500;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..900&family=Noto+Sans+Bengali:wght@400;500;700;800&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/tailwind.css')) ?>?v=<?= $ver ?>">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>?v=<?= $ver ?>">
</head>
<body class="bg-slate-950 text-slate-300 min-h-screen">
<div id="site-nav" class="fixed top-0 inset-x-0 z-50 transition-all duration-500 border-b border-transparent">
  <div class="mx-auto max-w-7xl px-4 sm:px-6">
    <nav class="flex items-center justify-between h-[72px]" aria-label="Main">
      <a href="<?= e(url('')) ?>" class="flex items-center gap-2.5 shrink-0" data-js-nav>
        <span class="w-9 h-9 rounded-xl grid place-items-center bg-gradient-to-br from-brand-deep to-brand-light shadow-neon-teal">
          <img src="<?= e(url('assets/img/logo.svg')) ?>" alt="<?= e(SITE_NAME) ?>" class="w-5 h-5">
        </span>
        <span class="font-extrabold text-lg tracking-tight text-white leading-none">
          Aurora<span class="text-gradient">Cyber</span>
        </span>
      </a>

      <div class="hidden lg:flex items-center gap-8 text-sm font-medium text-slate-300">
        <a class="hover:text-cyan-300 transition-colors" href="<?= e(url('') . '#home') ?>"><span data-i18n="nav_home">Home</span></a>
        <a class="hover:text-cyan-300 transition-colors" href="<?= e(url('') . '#services') ?>"><span data-i18n="nav_services">Services</span></a>
        <a class="hover:text-cyan-300 transition-colors" href="<?= e(url('') . '#portfolio') ?>"><span data-i18n="nav_portfolio">Portfolio</span></a>
        <a class="hover:text-cyan-300 transition-colors" href="<?= e(url('') . '#testimonials') ?>"><span data-i18n="nav_testimonials">Testimonials</span></a>
      </div>

      <div class="flex items-center gap-2.5">
        <button type="button" data-lang-toggle class="lang-pill glass-chip rounded-xl flex items-center gap-1 text-xs font-bold text-slate-200 select-none" aria-label="Switch language">
          <span class="l-bn">বাং</span>
          <span class="l-en">EN</span>
        </button>

        <?php if (is_admin()): ?>
          <a href="<?= e(url('admin/dashboard.php')) ?>" class="hidden sm:inline-flex btn-accent !py-2.5 !px-4 text-xs" data-js-nav>
            Admin
          </a>
        <?php elseif (is_logged_in()): ?>
          <a href="<?= e(url('account/dashboard.php')) ?>" class="hidden sm:inline-flex btn-teal !py-2.5 !px-4 text-xs" data-js-nav>
            Dashboard
          </a>
        <?php else: ?>
          <a href="<?= e(url('account/login.php')) ?>" class="hidden sm:inline-flex btn-ghost !py-2.5 !px-4 text-xs" data-js-nav>
            <span data-i18n="nav_login">Sign in</span>
          </a>
        <?php endif; ?>

        <a href="<?= e(url('') . '#order') ?>" class="hidden md:inline-flex btn-teal !py-2.5 !px-5 text-xs">
          <span data-i18n="nav_order">Start a project</span>
        </a>

        <button type="button" id="nav-burger" class="lg:hidden glass-chip rounded-xl p-2.5" aria-label="Menu">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
      </div>
    </nav>
  </div>
</div>

<!-- mobile slide-down -->
<div id="nav-mobile" class="fixed top-[72px] inset-x-0 z-40 px-4 hidden">
  <div class="glass-strong rounded-3xl p-5 flex flex-col gap-1 text-sm font-semibold text-slate-200">
    <a href="<?= e(url('') . '#home') ?>" class="mobile-link px-3 py-2.5 rounded-xl hover:bg-white/5"><span data-i18n="nav_home">Home</span></a>
    <a href="<?= e(url('') . '#services') ?>" class="mobile-link px-3 py-2.5 rounded-xl hover:bg-white/5"><span data-i18n="nav_services">Services</span></a>
    <a href="<?= e(url('') . '#portfolio') ?>" class="mobile-link px-3 py-2.5 rounded-xl hover:bg-white/5"><span data-i18n="nav_portfolio">Portfolio</span></a>
    <a href="<?= e(url('') . '#testimonials') ?>" class="mobile-link px-3 py-2.5 rounded-xl hover:bg-white/5"><span data-i18n="nav_testimonials">Testimonials</span></a>
    <?php if (!is_logged_in()): ?>
      <a href="<?= e(url('account/login.php')) ?>" class="mobile-link mt-1 text-cyan-300"><span data-i18n="nav_login">Sign in</span></a>
    <?php endif; ?>
    <a href="<?= e(url('') . '#order') ?>" class="btn-teal mt-2"><span data-i18n="nav_order">Start a project</span></a>
  </div>
</div>
<div id="nav-mobile-backdrop" class="fixed inset-0 z-30 bg-slate-950/70 backdrop-blur-sm hidden"></div>
<div class="pt-[72px]"></div>