<?php
/** Customer registration (traditional server-side form). */
require_once __DIR__ . '/../config.php';

if (is_logged_in()) {
    header('Location: ' . url('account/dashboard.php'));
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid security token.';
    } else {
        $name  = trim(post('name'));
        $email = strtolower(trim(post('email')));
        $phone = trim(post('phone'));
        $pass  = (string) post('password');

        if (mb_strlen($name) < 2) $errors[] = 'acc_fields_required';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'acc_fields_required';
        if (mb_strlen($pass) < 6) $errors[] = 'acc_weak_password';
        if (DB::get('SELECT id FROM users WHERE email = ?', [$email])) $errors[] = 'acc_email_taken';

        if (!$errors) {
            $id = DB::insert('users', [
                'name'     => $name,
                'email'    => $email,
                'phone'    => $phone ?: null,
                'password' => password_hash($pass, PASSWORD_DEFAULT),
                'role'     => 'customer',
            ]);
            DB::insert('verification_status', [
                'user_id'           => $id,
                'email_verified'    => 0,
                'whatsapp_verified' => 0,
                'admin_override'    => 'none',
            ]);
            login_user(['id' => $id, 'role' => 'customer']);
            flash('Your account is ready! Let is verify your email for faster replies.', 'success');
            header('Location: ' . url('account/verify.php'));
            exit;
        }
    }
}

$PAGE_TITLE = 'Create account — ' . SITE_NAME;
require_once __DIR__ . '/../includes/public-header.php';
?>
<main class="relative min-h-[calc(100vh-72px)] flex items-center justify-center overflow-hidden py-16">
  <div class="aurora-blob w-[420px] h-[380px] bg-accent-electric/15 top-10 -left-40" style="animation-delay:-4s"></div>

  <div class="relative w-full max-w-md px-4">
    <div class="glass-strong rounded-[2rem] p-8 sm:p-10 neon-glow grad-border">
      <div class="text-center">
        <h1 class="text-2xl font-extrabold text-white"><span data-i18n="acc_register_title">Join Aurora Cyber</span></h1>
        <p class="mt-2 text-sm text-slate-400"><span data-i18n="acc_register_sub">Track your projects and get verified for priority support.</span></p>
      </div>

      <?php foreach (array_unique($errors) as $e): ?>
        <div class="mt-5 glass-chip border-rose-400/30 text-rose-300 text-sm px-4 py-3 rounded-xl"><span data-i18n="<?= e($e) ?>"><?= e($e) ?></span></div>
      <?php endforeach; ?>

      <form method="post" class="mt-7 space-y-5" novalidate>
        <?= csrf_field() ?>
        <div>
          <label class="label"><span data-i18n="acc_name">Full name</span></label>
          <input class="input" name="name" required minlength="2" autocomplete="name" value="<?= e(post('name', '')) ?>">
        </div>
        <div>
          <label class="label"><span data-i18n="acc_email">Email address</span></label>
          <input class="input" type="email" name="email" required autocomplete="email" value="<?= e(post('email', '')) ?>">
        </div>
        <div>
          <label class="label"><span data-i18n="acc_phone">Phone / WhatsApp</span></label>
          <input class="input" name="phone" placeholder="01XXXXXXXXX" autocomplete="tel" value="<?= e(post('phone', '')) ?>">
        </div>
        <div>
          <label class="label"><span data-i18n="acc_password">Password</span></label>
          <input class="input" type="password" name="password" required minlength="6" autocomplete="new-password">
          <p class="mt-1.5 text-xs text-slate-500"><span data-i18n="acc_password_hint">At least 6 characters.</span></p>
        </div>
        <button type="submit" class="btn-accent w-full !py-3.5"><span data-i18n="acc_register_btn">Create account</span></button>
      </form>

      <p class="mt-6 text-center text-sm text-slate-400">
        <span data-i18n="acc_have_account">Already have an account?</span>
        <a class="text-cyan-300 font-semibold hover:text-cyan-200" href="<?= e(url('account/login.php')) ?>"><span data-i18n="acc_sign_in">Sign in</span></a>
      </p>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/public-footer.php'; ?>