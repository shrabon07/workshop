<?php
/**
 * Admin layout — head + sidebar (glassmorphic Aurora Cyber SaaS chrome).
 * Guard against non-admins on every entry point.
 */
if (!defined('APP_PATH')) {
    require_once __DIR__ . '/../../config.php';
}
require_admin();

$adminTitle = $PAGE_TITLE ?? 'Admin';
$ACTIVE = $ACTIVE ?? 'dashboard';
$v = version_time();

$navItems = [
    'dashboard'  => ['url' => 'dashboard.php',  'icon' => '◈', 'key' => 'a_dashboard'],
    'services'   => ['url' => 'services.php',   'icon' => '▤', 'key' => 'a_services'],
    'categories' => ['url' => 'categories.php', 'icon' => '▦', 'key' => 'a_categories'],
    'orders'     => ['url' => 'orders.php',     'icon' => '◷', 'key' => 'a_orders'],
    'customers'  => ['url' => 'customers.php',  'icon' => '◉', 'key' => 'a_customers'],
    'chats'      => ['url' => 'chats.php',      'icon' => '✉', 'key' => 'a_chats'],
];

$orderCount = (int) DB::value('SELECT COUNT(*) FROM orders WHERE status = "pending"');
$chatCount  = (int) DB::value('SELECT COUNT(*) FROM chat_sessions WHERE status = "open"');
?><!doctype html>
<html lang="en" class="lang-en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<title><?= e($adminTitle) ?> · Aurora Cyber Admin</title>
<link rel="icon" type="image/svg+xml" href="<?= e(url('assets/img/logo.svg')) ?>">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..900&family=Noto+Sans+Bengali:wght@400;500;700;800&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/tailwind.css')) ?>?v=<?= $v ?>">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>?v=<?= $v ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>?v=<?= $v ?>">
</head>
<body class="bg-slate-950 text-slate-300">
<div class="grid lg:grid-cols-[250px_1fr] min-h-screen">

  <!-- ============ SIDEBAR ============ -->
  <aside class="hidden lg:flex flex-col sticky top-0 h-screen glass border-r border-white/10 p-5 z-40">
    <a href="<?= e(url('admin/dashboard.php')) ?>" class="flex items-center gap-2.5 px-1">
      <span class="w-9 h-9 rounded-xl grid place-items-center bg-gradient-to-br from-brand-deep to-brand-light">
        <img src="<?= e(url('assets/img/logo.svg')) ?>" alt="" class="w-5 h-5">
      </span>
      <span class="font-extrabold text-white text-lg">Aurora<span class="text-gradient">Cyber</span></span>
    </a>
    <span class="mt-1 pl-1 text-[11px] uppercase tracking-[.25em] text-slate-500"><span data-i18n="a_admin">Admin Panel</span></span>

    <nav class="mt-8 space-y-1.5 flex-1">
      <?php foreach ($navItems as $key => $item):
          $isActive = $ACTIVE === $key;
          $badge = $key === 'orders' && $orderCount ? $orderCount : ($key === 'chats' && $chatCount ? $chatCount : 0);
      ?>
        <a href="<?= e(url('admin/' . $item['url'])) ?>"
           class="nav-item <?= $isActive ? 'nav-item-active' : '' ?> flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all">
          <span class="text-lg w-6 text-center opacity-80"><?= $item['icon'] ?></span>
          <span class="flex-1" data-i18n="<?= e($item['key']) ?>"><?= e($item['key']) ?></span>
          <?php if ($badge): ?><span class="mini-badge"><?= (int) $badge ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="border-t border-white/10 pt-4 space-y-1.5">
      <a href="<?= e(url('')) ?>" class="nav-item flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all">
        <span class="text-lg w-6 text-center opacity-80">⤴</span><span class="flex-1" data-i18n="a_site">View site</span>
      </a>
      <a href="<?= e(url('admin/logout.php')) ?>" class="nav-item flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all">
        <span class="text-lg w-6 text-center opacity-80">⏻</span><span class="flex-1" data-i18n="a_logout">Sign out</span>
      </a>
    </div>
  </aside>

  <!-- mobile top rows -->
  <div id="admin-topbar" class="lg:hidden sticky top-0 z-50 glass border-b border-white/10 px-4 py-3 flex items-center justify-between">
    <a href="<?= e(url('admin/dashboard.php')) ?>" class="flex items-center gap-2 font-extrabold text-white">
      <img src="<?= e(url('assets/img/logo.svg')) ?>" class="w-6 h-6" alt="">Aurora<span class="text-gradient">Cyber</span>
    </a>
    <div class="flex items-center gap-2">
      <a href="<?= e(url('')) ?>" class="glass-chip rounded-lg p-2 text-xs" aria-label="Site"><span data-i18n="a_site">View site</span></a>
      <a href="<?= e(url('admin/logout.php')) ?>" class="glass-chip rounded-lg p-2 text-xs" aria-label="Logout"><span data-i18n="a_logout">Sign out</span></a>
    </div>
  </div>
  <nav class="lg:hidden sticky top-[57px] z-40 glass border-y border-white/10 px-2 py-1.5 flex gap-1 overflow-x-auto nice-scroll">
    <?php foreach ($navItems as $key => $item): ?>
      <a href="<?= e(url('admin/' . $item['url'])) ?>" class="shrink-0 rounded-xl px-3 py-1.5 text-xs font-bold <?= $ACTIVE === $key ? 'bg-gradient-to-r from-brand-deep to-brand-light text-slate-900' : 'text-slate-300 hover:text-white' ?>">
        <span data-i18n="<?= e($item['key']) ?>"><?= e($item['key']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- ============ MAIN ============ -->
  <main class="relative min-w-0">
    <div class="aurora-blob w-[520px] h-[420px] bg-cyan-500/8 -right-40 top-0"></div>
    <div class="relative px-4 sm:px-8 py-8 max-w-[1400px]">
      <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
          <p class="text-[11px] uppercase tracking-[.3em] text-cyan-400 font-extrabold">Aurora Cyber</p>
          <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-white"><?= e($adminTitle) ?></h1>
        </div>
        <div class="flex items-center gap-3">
          <button type="button" data-lang-toggle class="lang-pill glass-chip rounded-xl flex items-center gap-1 text-xs font-bold text-slate-200 select-none">
            <span class="l-bn">বাং</span><span class="l-en">EN</span>
          </button>
          <span class="hidden sm:inline-flex glass-chip rounded-xl px-3 py-1.5 text-xs font-bold text-slate-400"><?= e(APP_ENV) === 'dev' ? 'DEV' : 'PROD' ?></span>
          <span class="hidden md:inline-flex items-center gap-2 glass-chip rounded-xl px-3 py-1.5 text-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span><?= e(explode(' ', current_user()['name'])[0]) ?>
          </span>
        </div>
      </div>