<?php
/* Shared public footer + script loader. */
$v = version_time();
?>
<footer class="relative mt-24 border-t border-white/10 bg-slate-950/80">
  <div class="absolute inset-x-0 -top-px h-px bg-gradient-to-r from-transparent via-cyan-500/40 to-transparent"></div>
  <div class="mx-auto max-w-7xl px-4 sm:px-6 py-14 grid gap-10 md:grid-cols-4">
    <div class="md:col-span-2">
      <a href="<?= e(url('')) ?>" class="flex items-center gap-2.5">
        <span class="w-9 h-9 rounded-xl grid place-items-center bg-gradient-to-br from-brand-deep to-brand-light">
          <img src="<?= e(url('assets/img/logo.svg')) ?>" alt="" class="w-5 h-5">
        </span>
        <span class="font-extrabold text-lg text-white">Aurora<span class="text-gradient">Cyber</span></span>
      </a>
      <p class="mt-4 text-sm text-slate-400 max-w-sm"><span data-i18n="footer_tag">Web experiences engineered in Bangladesh for the world.</span></p>
      <div class="mt-5 flex gap-3">
        <a class="glass-chip rounded-xl p-2.5 hover:text-cyan-300 transition-colors" href="<?= e(WHATSAPP_LINK) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48a9 9 0 0 1-1.66-2.07c-.17-.3-.02-.46.13-.61.14-.14.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.5 0 1.47 1.07 2.9 1.22 3.1.15.2 2.11 3.22 5.1 4.51.71.3 1.27.49 1.7.63.72.23 1.37.2 1.88.12.58-.09 1.76-.72 2.01-1.42.25-.7.25-1.29.17-1.42-.07-.12-.27-.2-.57-.35zM12.05 21.8h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.44 4.43-9.87 9.9-9.87 2.64 0 5.13 1.03 7 2.9a9.83 9.83 0 0 1 2.9 7c0 5.44-4.44 9.87-9.9 9.87zM20.55 3.5A11.8 11.8 0 0 0 12.05 0C5.5 0 .17 5.32.17 11.88c0 2.1.55 4.14 1.6 5.94L0 24l6.34-1.66a11.9 11.9 0 0 0 5.7 1.45h.01c6.55 0 11.88-5.33 11.88-11.88A11.8 11.8 0 0 0 20.55 3.5z"/></svg>
        </a>
        <a class="glass-chip rounded-xl p-2.5 hover:text-cyan-300 transition-colors" href="mailto:<?= e(SITE_EMAIL) ?>" aria-label="Email">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.9 5.26a2 2 0 0 0 2.2 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/></svg>
        </a>
      </div>
    </div>

    <div>
      <h4 class="font-bold text-white text-sm mb-4"><span data-i18n="footer_services">Services</span></h4>
      <ul class="space-y-2.5 text-sm text-slate-400">
        <?php
        foreach (DB::all('SELECT id, title_en, title_bn FROM services WHERE status = "active" ORDER BY sort_order, id LIMIT 5') as $fs) {
            echo '<li><a class="hover:text-cyan-300 transition-colors" href="' . e(url('') . '#services') . '">' . l($fs['title_en'], $fs['title_bn']) . '</a></li>';
        }
        ?>
      </ul>
    </div>

    <div>
      <h4 class="font-bold text-white text-sm mb-4"><span data-i18n="footer_company">Company</span></h4>
      <ul class="space-y-2.5 text-sm text-slate-400">
        <li><a class="hover:text-cyan-300 transition-colors" href="<?= e(url('') . '#portfolio') ?>"><span data-i18n="nav_portfolio">Portfolio</span></a></li>
        <li><a class="hover:text-cyan-300 transition-colors" href="<?= e(url('') . '#order') ?>"><span data-i18n="nav_order">Start a project</span></a></li>
        <li><a class="hover:text-cyan-300 transition-colors" href="<?= e(url('account/register.php')) ?>"><span data-i18n="nav_register">Create account</span></a></li>
        <li><a class="hover:text-cyan-300 transition-colors" href="mailto:<?= e(SITE_EMAIL) ?>">hello@auroracyber.com</a></li>
      </ul>
    </div>
  </div>

  <div class="border-t border-white/5">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
      <span>© <?= date('Y') ?> <b class="text-slate-300"><?= e(SITE_NAME) ?></b> — <span data-i18n="footer_rights">All rights reserved.</span></span>
      <span class="text-slate-600">EN <span class="text-slate-500">|</span> বাং · Made in 🇧🇩 Bangladesh</span>
    </div>
  </div>
</footer>

<script>window.AURORA_BASE = "<?= e(APP_BASE_URL) ?>";</script>
<script defer src="<?= e(asset('js/i18n.js')) ?>?v=<?= $v ?>"></script>
<script defer src="<?= e(asset('js/app.js')) ?>?v=<?= $v ?>"></script>
<?php if (isset($LOAD_ORDER_JS)): ?>
<script defer src="<?= e(asset('js/order.js')) ?>?v=<?= $v ?>"></script>
<?php endif; ?>
<script defer src="<?= e(asset('js/chat.js')) ?>?v=<?= $v ?>"></script>
</body>
</html>