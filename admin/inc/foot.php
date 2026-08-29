<?php
/** Admin layout — footer + scripts. */
$v = version_time();
?>
    </div>
    <footer class="relative px-6 sm:px-8 py-6 border-t border-white/5 text-xs text-slate-600 flex flex-wrap gap-4 items-center justify-between">
      <span>© <?= date('Y') ?> <b class="text-slate-400">Aurora Cyber</b> · <span data-i18n="footer_rights">All rights reserved.</span></span>
      <span>PHP <?= PHP_VERSION ?> · MariaDB · Outfit / Noto Sans Bengali</span>
    </footer>
  </main>
</div>

<!-- toast mount -->
<div id="toast-mount" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[80] flex flex-col gap-2 items-center pointer-events-none"></div>

<script>window.AURORA_BASE = "<?= e(APP_BASE_URL) ?>";</script>
<script defer src="<?= e(asset('js/i18n.js')) ?>?v=<?= $v ?>"></script>
<script defer src="<?= e(asset('js/admin.js')) ?>?v=<?= $v ?>"></script>
<script defer src="<?= e(asset('js/scroll-nav.js')) ?>?v=<?= $v ?>"></script>
<?php if (isset($LOAD_ADMIN_SERVICES)): ?>
<script defer src="<?= e(asset('js/admin-services.js')) ?>?v=<?= $v ?>"></script>
<?php endif; ?>
<?php if (isset($LOAD_ADMIN_CHATS)): ?>
<script defer src="<?= e(asset('js/admin-support.js')) ?>?v=<?= $v ?>"></script>
<?php endif; ?>
</body>
</html>