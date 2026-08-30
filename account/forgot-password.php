<?php
/** Customer password recovery — email OTP flow (multi-step single page). */
require_once __DIR__ . '/../config.php';

if (is_logged_in()) {
    header('Location: ' . url('account/dashboard.php'));
    exit;
}

$errors = [];
$info   = '';
$step   = 1; // 1 = enter email, 2 = enter OTP + new password
$email  = strtolower(trim((string) ($_SESSION['pwd_reset_email'] ?? post('email', ''))));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid security token.';
    } else {
        $action = post('action');
        if ($action === 'request') {
            $email = strtolower(trim(post('email')));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'acc_bad_credentials';
            } else {
                $u = DB::get('SELECT id, name FROM users WHERE email = ? AND role = "customer"', [$email]);
                if (!$u) {
                    $errors[] = 'pwd_no_account';
                } else {
                    $last = DB::get(
                        'SELECT * FROM otp_codes WHERE user_id = ? AND channel = "email" AND purpose = "password_reset" AND used = 0 ORDER BY id DESC LIMIT 1',
                        [$u['id']]
                    );
                    if ($last && (time() - strtotime($last['created_at'])) < OTP_RESEND_SECONDS) {
                        $wait = OTP_RESEND_SECONDS - (time() - strtotime($last['created_at']));
                        $errors[] = 'pwd_wait ' . $wait;
                    } else {
                        DB::run('UPDATE otp_codes SET used = 1 WHERE user_id = ? AND purpose = "password_reset"', [$u['id']]);
                        $code   = random_otp(6);
                        $hash   = hash('sha256', $code);
                        $expiry = date('Y-m-d H:i:s', time() + (OTP_TTL_MINUTES * 60));
                        DB::insert('otp_codes', [
                            'user_id'    => $u['id'],
                            'channel'    => 'email',
                            'purpose'    => 'password_reset',
                            'code_hash'  => $hash,
                            'expires_at' => $expiry,
                            'used'       => 0,
                        ]);
                        send_mail(
                            $email,
                            'Your Aurora Cyber password reset code is ' . $code,
                            '<p>Hi ' . e($u['name']) . ',</p>
                             <p>We received a request to reset your Aurora Cyber password. Your code is:</p>
                             <p style="font-size:30px;letter-spacing:6px;font-weight:800;color:#0f766e">' . $code . '</p>
                             <p>It expires in ' . OTP_TTL_MINUTES . ' minutes. If you did not request this, you can safely ignore this email.</p>'
                        );
                        $_SESSION['pwd_reset_email'] = $email;
                        $step = 2;
                        $info = 'pwd_code_sent';
                    }
                }
            }
        } elseif ($action === 'reset') {
            $email = strtolower(trim(post('email')));
            $code  = preg_replace('/[^0-9]/', '', (string) post('code'));
            $pass  = (string) post('password');
            $pass2 = (string) post('password_confirm');
            $u = DB::get('SELECT * FROM users WHERE email = ? AND role = "customer"', [$email]);
            if (!$u) {
                $errors[] = 'pwd_no_account';
                unset($_SESSION['pwd_reset_email']);
            } elseif (!preg_match('/^\d{6}$/', $code)) {
                $errors[] = 'pwd_bad_code';
            } elseif (mb_strlen($pass) < 6) {
                $errors[] = 'acc_weak_password';
            } elseif ($pass !== $pass2) {
                $errors[] = 'acc_mismatch_password';
            } else {
                $otp = DB::get(
                    'SELECT * FROM otp_codes WHERE user_id = ? AND channel = "email" AND purpose = "password_reset" AND used = 0 AND code_hash = ? ORDER BY id DESC LIMIT 1',
                    [$u['id'], hash('sha256', $code)]
                );
                if (!$otp) {
                    $errors[] = 'pwd_bad_code';
                } elseif (strtotime($otp['expires_at']) < time()) {
                    DB::update('otp_codes', ['used' => 1], 'id = ?', [$otp['id']]);
                    $errors[] = 'pwd_expired_code';
                } else {
                    DB::update('otp_codes', ['used' => 1], 'id = ?', [$otp['id']]);
                    DB::run('UPDATE otp_codes SET used = 1 WHERE user_id = ? AND purpose = "password_reset"', [$u['id']]);
                    DB::update('users', ['password' => password_hash($pass, PASSWORD_DEFAULT)], 'id = ?', [$u['id']]);
                    unset($_SESSION['pwd_reset_email']);
                    login_user($u);
                    header('Location: ' . url('account/dashboard.php'));
                    exit;
                }
            }
        }
    }
}

$PAGE_TITLE = 'Forgot password — ' . SITE_NAME;
require_once __DIR__ . '/../includes/public-header.php';
?>
<main class="relative min-h-[calc(100vh-72px)] flex items-center justify-center overflow-hidden py-16">
  <div class="aurora-blob w-[420px] h-[380px] bg-accent-electric/15 top-10 -left-40" style="animation-delay:-4s"></div>

  <div class="relative w-full max-w-md px-4">
    <div class="glass-strong rounded-[2rem] p-8 sm:p-10 neon-glow grad-border">
      <div class="text-center">
        <span class="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-br from-accent-neon to-accent-electric grid place-items-center mb-4 text-white text-xl">🔑</span>
        <h1 class="text-2xl font-extrabold text-white"><span data-i18n="pwd_title">Reset your password</span></h1>
        <p class="mt-2 text-sm text-slate-400"><span data-i18n="pwd_sub">We&#8217;ll email you a one-time code to verify it&#8217;s you.</span></p>
      </div>

      <?php foreach (array_unique($errors) as $e): ?>
        <?php if (str_starts_with($e, 'pwd_wait ')): ?>
          <div class="mt-5 glass-chip border-rose-400/30 text-rose-300 text-sm px-4 py-3 rounded-xl"><?= e(l('Please wait ' . trim(substr($e, 9)) . 's before requesting another code.', 'আবার কোড নিতে অনুগ্রহ করে ' . trim(substr($e, 9)) . ' সেকেন্ড অপেক্ষা করুন।')) ?></div>
        <?php else: ?>
          <div class="mt-5 glass-chip border-rose-400/30 text-rose-300 text-sm px-4 py-3 rounded-xl"><span data-i18n="<?= e($e) ?>"><?= e($e) ?></span></div>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if ($info): ?>
        <div class="mt-5 glass-chip border-emerald-400/30 text-emerald-300 text-sm px-4 py-3 rounded-xl"><span data-i18n="<?= e($info) ?>">A reset code has been sent to your email.</span></div>
      <?php endif; ?>

      <?php if ($step === 1): ?>
        <form method="post" class="mt-7 space-y-5" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="request">
          <div>
            <label class="label"><span data-i18n="acc_email">Email address</span></label>
            <input class="input" type="email" name="email" required autocomplete="email" value="<?= e($email) ?>">
          </div>
          <button type="submit" class="btn-teal w-full !py-3.5"><span data-i18n="pwd_send_btn">Send reset code</span></button>
        </form>
      <?php else: ?>
        <form method="post" class="mt-7 space-y-5" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="reset">
          <input type="hidden" name="email" value="<?= e($email) ?>">
          <div>
            <label class="label"><span data-i18n="pwd_code">6-digit code</span></label>
            <input class="input text-center tracking-[.5em] font-black" name="code" required maxlength="6" inputmode="numeric" placeholder="· · · · · ·">
          </div>
          <div>
            <label class="label"><span data-i18n="acc_password">New password</span></label>
            <input class="input" type="password" name="password" required minlength="6" autocomplete="new-password">
            <p class="mt-1.5 text-xs text-slate-500"><span data-i18n="acc_password_hint">At least 6 characters.</span></p>
          </div>
          <div>
            <label class="label"><span data-i18n="pwd_confirm">Confirm new password</span></label>
            <input class="input" type="password" name="password_confirm" required minlength="6" autocomplete="new-password">
          </div>
          <button type="submit" class="btn-accent w-full !py-3.5"><span data-i18n="pwd_reset_btn">Reset password</span></button>
        </form>
        <p class="mt-4 text-center text-sm text-slate-400">
          <a class="text-cyan-300 font-semibold hover:text-cyan-200" href="<?= e(url('account/forgot-password.php')) ?>"><span data-i18n="pwd_resend">Resend code</span></a>
        </p>
      <?php endif; ?>

      <p class="mt-6 text-center text-sm text-slate-400">
        <span data-i18n="acc_have_account">Already have an account?</span>
        <a class="text-cyan-300 font-semibold hover:text-cyan-200" href="<?= e(url('account/login.php')) ?>"><span data-i18n="acc_sign_in">Sign in</span></a>
      </p>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/public-footer.php'; ?>
