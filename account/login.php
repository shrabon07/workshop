<?php
/** Customer sign in (traditional server-side form). Admins land here too. */
require_once __DIR__ . '/../config.php';

if (is_admin()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}
if (is_logged_in()) {
    header('Location: ' . url('account/dashboard.php'));
    exit;
}

$errors = [];
$next = trim((string) ($_GET['next'] ?? ''));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid security token.';
    } else {
        $email = strtolower(trim(post('email')));
        $pass  = (string) post('password');
        if ($email === '' || $pass === '') {
            $errors[] = 'acc_fields_required';
        } else {
            $u = DB::get('SELECT * FROM users WHERE email = ?', [$email]);
            if (!$u || !password_verify($pass, $u['password'])) {
                $errors[] = 'acc_bad_credentials';
            } else {
                login_user($u);
                if ($u['role'] === 'admin') {
                    header('Location: ' . url('admin/dashboard.php'));
                    exit;
                }
                header('Location: ' . url($next && !str_contains($next, '://') ? $next : 'account/dashboard.php'));
                exit;
            }
        }
    }
}

$PAGE_TITLE = 'Sign in — ' . SITE_NAME;
require_once __DIR__ . '/../includes/public-header.php';
?>
<main class="relative min-h-[calc(100vh-72px)] flex items-center justify-center overflow-hidden py-16">
  <div class="aurora-blob w-[420px] h-[380px] bg-cyan-500/15 top-10 -left-40"></div>
  <div class="aurora-blob w-[380px] h-[340px] bg-accent-electric/15 bottom-0 right-[-140px]" style="animation-delay:-8s"></div>

  <div class="relative w-full max-w-md px-4">
    <div class="glass-strong rounded-[2rem] p-8 sm:p-10 neon-glow grad-border">
      <div class="text-center">
        <span class="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-deep to-brand-light grid place-items-center mb-4">
          <img src="<?= e(url('assets/img/logo.svg')) ?>" alt="" class="w-6 h-6">
        </span>
        <h1 class="text-2xl font-extrabold text-white"><span data-i18n="acc_login_title">Welcome back</span></h1>
        <p class="mt-2 text-sm text-slate-400"><span data-i18n="acc_login_sub">Sign in to track orders, chat history and verification.</span></p>
      </div>

      <?php foreach (array_unique($errors) as $e): ?>
        <?php if ($e === 'Invalid security token.'): ?>
          <div class="mt-5 glass-chip border-rose-400/30 text-rose-300 text-sm px-4 py-3 rounded-xl"><?= e($e) ?></div>
        <?php else: ?>
          <div class="mt-5 glass-chip border-rose-400/30 text-rose-300 text-sm px-4 py-3 rounded-xl"><span data-i18n="<?= e($e) ?>"><?= e($e) ?></span></div>
        <?php endif; ?>
      <?php endforeach; ?>

      <form method="post" class="mt-7 space-y-5" novalidate>
        <?= csrf_field() ?>
        <div>
          <label class="label"><span data-i18n="acc_email">Email address</span></label>
          <input class="input" type="email" name="email" required autocomplete="email" value="<?= e(post('email', '')) ?>">
        </div>
        <div>
          <label class="label"><span data-i18n="acc_password">Password</span></label>
          <input class="input" type="password" name="password" required minlength="6" autocomplete="current-password">
        </div>
        <button type="submit" class="btn-teal w-full !py-3.5"><span data-i18n="acc_login_btn">Sign in</span></button>
      </form>

      <p class="mt-4 text-center text-sm">
        <a class="text-slate-400 hover:text-cyan-300 transition-colors" href="<?= e(url('account/forgot-password.php')) ?>"><span data-i18n="pwd_forgot">Forgot password?</span></a>
      </p>

      <p class="mt-6 text-center text-sm text-slate-400">
        <span data-i18n="acc_no_account">New here?</span>
        <a class="text-cyan-300 font-semibold hover:text-cyan-200" href="<?= e(url('account/register.php')) ?>"><span data-i18n="acc_create_one">Create an account</span></a>
      </p>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/public-footer.php'; ?>