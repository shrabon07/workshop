<?php
/** Admin — standalone sign in (staff only; regular customers use account/login). */
require_once __DIR__ . '/../config.php';

if (is_admin()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $email    = strtolower(trim((string) post('email')));
    $password = (string) post('password');
    $user = DB::get('SELECT * FROM users WHERE email = ?', [$email]);
    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] !== 'admin') {
            $error = l('This account is not an admin.', 'এই অ্যাকাউন্টটি এডমিন নয়।');
        } elseif ((int) ($user['is_active'] ?? 1) !== 1) {
            $error = l('This admin account has been deactivated. Contact the super admin.', 'এই অ্যাডমিন অ্যাকাউন্ট নিষ্ক্রিয় করা হয়েছে। সুপার অ্যাডমিনের সাথে যোগাযোগ করুন।');
        } else {
            login_user($user);
            header('Location: ' . url('admin/dashboard.php'));
            exit;
        }
    } else {
        $error = l('Incorrect email or password.', 'ভুল ইমেইল বা পাসওয়ার্ড।');
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = l('Invalid security token. Please refresh and try again.', 'সিকিউরিটি টোকেন অবৈধ।');
}

$flashes = flash_pull();
$v = version_time();
?><!doctype html>
<html lang="en" class="lang-en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<title>Admin Sign in · Aurora Cyber</title>
<link rel="icon" type="image/svg+xml" href="<?= e(url('assets/img/logo.svg')) ?>">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..900&family=Noto+Sans+Bengali:wght@400;500;700;800&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/tailwind.css')) ?>?v=<?= $v ?>">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>?v=<?= $v ?>">
</head>
<body class="bg-slate-950 text-slate-300 min-h-screen grid place-items-center relative">
<div class="absolute inset-0 overflow-hidden">
  <div class="aurora-blob w-[560px] h-[460px] bg-cyan-500/10 -left-32 top-10"></div>
  <div class="aurora-blob w-[480px] h-[400px] bg-indigo-500/10 -right-28 bottom-0"></div>
</div>

<div class="relative z-10 w-full max-w-sm px-5">
  <a href="<?= e(url('')) ?>" class="flex items-center justify-center gap-2.5 mb-8">
    <span class="w-10 h-10 rounded-xl grid place-items-center bg-gradient-to-br from-brand-deep to-brand-light shadow-lg shadow-cyan-500/20">
      <img src="<?= e(url('assets/img/logo.svg')) ?>" alt="" class="w-5 h-5">
    </span>
    <span class="font-extrabold text-white text-2xl">Aurora<span class="text-gradient">Cyber</span></span>
  </a>

  <div class="glass rounded-3xl p-7 grad-border fade-swap">
    <h1 class="font-extrabold text-white text-xl"><?= l('Admin sign in', 'এডমিন সাইন ইন') ?></h1>
    <p class="text-xs text-slate-400 mt-1"><?= l('Staff only — customer accounts won’t work here.', 'শুধু স্টাফ — কাস্টমার অ্যাকাউন্ট এখানে কাজ করবে না।') ?></p>

    <?php foreach ($flashes as $f): ?>
      <div class="mt-4 glass-chip rounded-xl px-4 py-2.5 text-xs font-semibold text-amber-200"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <?php if ($error): ?>
      <div class="mt-4 glass-chip rounded-xl px-4 py-2.5 text-xs font-semibold text-rose-200"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" class="mt-6 space-y-4" novalidate>
      <?= csrf_field() ?>
      <div>
        <label class="label"><?= l('Email', 'ইমেইল') ?></label>
        <input type="email" name="email" class="input" required autocomplete="username" value="<?= e((string) post('email', '')) ?>" placeholder="admin@auroracyber.com">
      </div>
      <div>
        <label class="label"><?= l('Password', 'পাসওয়ার্ড') ?></label>
        <input type="password" name="password" class="input" required autocomplete="current-password" placeholder="••••••••••">
      </div>
      <button type="submit" class="btn-teal w-full !py-3.5"><?= l('Sign in', 'সাইন ইন') ?> →</button>
    </form>

    <div class="mt-5 pt-4 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-500">
      <a href="<?= e(url('')) ?>" class="hover:text-cyan-300 transition-colors"><?= l('Back to site', 'সাইটে ফিরুন') ?></a>
      <span><?= e(strtoupper(APP_ENV)) ?></span>
    </div>
  </div>
</div>

<script>window.AURORA_BASE = "<?= e(APP_BASE_URL) ?>";</script>
<script defer src="<?= e(asset('js/i18n.js')) ?>?v=<?= $v ?>"></script>
</body>
</html>