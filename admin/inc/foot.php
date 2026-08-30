<?php
/** Admin layout — footer + scripts. */
$v = version_time();
?>
    </div>
    <footer class="relative px-6 sm:px-8 py-6 border-t border-white/5 text-xs text-slate-600 flex flex-wrap gap-4 items-center justify-between">
      <span>© <?= date('Y') ?> <b class="text-slate-400">Aurora Cyber</b> · <span data-i18n="footer_rights">All rights reserved.</span></span>
      <span class="flex flex-wrap items-center gap-x-4 gap-y-2">
        <a class="hover:text-cyan-400 transition-colors" href="<?= e(url('terms-privacy.php')) ?>" target="_blank" rel="noopener">Term &amp; Privacy</a>
        <a class="hover:text-cyan-400 transition-colors" href="<?= e(url('payment-methods.php')) ?>" target="_blank" rel="noopener">Payment</a>
        <a class="hover:text-cyan-400 transition-colors" href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>
        <a class="hover:text-cyan-400 transition-colors" href="<?= e(APP_BASE_URL) ?>" target="_blank" rel="noopener">aurora-cyber.infy.click</a>
        <span>Mirpur, Dhaka-1215</span>
        <a class="inline-flex text-slate-400 hover:text-cyan-400 transition-colors" href="<?= e(WHATSAPP_LINK) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48a9 9 0 0 1-1.66-2.07c-.17-.3-.02-.46.13-.61.14-.14.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.5 0 1.47 1.07 2.9 1.22 3.1.15.2 2.11 3.22 5.1 4.51.71.3 1.27.49 1.7.63.72.23 1.37.2 1.88.12.58-.09 1.76-.72 2.01-1.42.25-.7.25-1.29.17-1.42-.07-.12-.27-.2-.57-.35zM12.05 21.8h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.44 4.43-9.87 9.9-9.87 2.64 0 5.13 1.03 7 2.9a9.83 9.83 0 0 1 2.9 7c0 5.44-4.44 9.87-9.9 9.87zM20.55 3.5A11.8 11.8 0 0 0 12.05 0C5.5 0 .17 5.32.17 11.88c0 2.1.55 4.14 1.6 5.94L0 24l6.34-1.66a11.9 11.9 0 0 0 5.7 1.45h.01c6.55 0 11.88-5.33 11.88-11.88A11.8 11.8 0 0 0 20.55 3.5z"/></svg>
        </a>
      </span>
    </footer>
  </main>
</div>

<!-- toast mount -->
<div id="toast-mount" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[80] flex flex-col gap-2 items-center pointer-events-none"></div>

<script>window.AURORA_BASE = "<?= e(APP_BASE_URL) ?>";</script>
<script defer src="<?= e(asset('js/i18n.js')) ?>?v=<?= $v ?>"></script>
<script defer src="<?= e(asset('js/admin.js')) ?>?v=<?= $v ?>"></script>
<script defer src="<?= e(asset('js/admin-push.js')) ?>?v=<?= $v ?>"></script>
<script defer src="<?= e(asset('js/scroll-nav.js')) ?>?v=<?= $v ?>"></script>
<?php if (isset($LOAD_ADMIN_SERVICES)): ?>
<script defer src="<?= e(asset('js/admin-services.js')) ?>?v=<?= $v ?>"></script>
<?php endif; ?>
<?php if (isset($LOAD_ADMIN_CHATS)): ?>
<script defer src="<?= e(asset('js/admin-support.js')) ?>?v=<?= $v ?>"></script>
<?php endif; ?>
</body>
</html>