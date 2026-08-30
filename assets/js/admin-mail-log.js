/* Aurora Cyber — admin: mail list delete (super admin only) */
(function () {
  'use strict';
  function boot() {
    var A = window.Admin;
    if (!A) { setTimeout(boot, 30); return; } // admin.js loads after this script
    document.querySelectorAll('.log-del').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm('Delete this mail from the list (removed from the database)? / এই মেইলটি তালিকা থেকে মুছবেন (ডেটাবেজ থেকে মুছে ফেলা হবে)?')) return;
        btn.disabled = true;
        btn.dataset.loading = '1';
        A.post('api/admin/mail_log_delete.php', { log_id: btn.dataset.id })
          .then(function (r) {
            btn.dataset.loading = '';
            if (r.ok) { A.toast('Mail deleted from the list', 'success'); location.reload(); }
            else { btn.disabled = false; A.toast(r.error || 'Error', 'error'); }
          })
          .catch(function () { btn.dataset.loading = ''; btn.disabled = false; A.toast('Network error', 'error'); });
      });
    });
  }
  boot();
})();
