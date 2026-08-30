/* Aurora Cyber — admin: connect my Gmail app password as my personal sender */
(function () {
  'use strict';
  function boot() {
    var A = window.Admin;
    if (!A) { setTimeout(boot, 30); return; }
    var form = document.getElementById('mail-settings-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = document.getElementById('ms-save');
      var st = document.getElementById('ms-status');
      var email = document.getElementById('ms-email').value.trim();
      var pass = document.getElementById('ms-pass').value.trim();
      if (!email || !pass || pass.length !== 16) {
        st.textContent = 'Enter your Gmail and the 16-letter app password.';
        st.className = 'text-xs text-amber-300';
        return;
      }
      btn.disabled = true; btn.dataset.loading = '1'; st.textContent = '';
      A.post('api/admin/mail_settings_save.php', { smtp_email: email, smtp_pass: pass })
        .then(function (d) {
          btn.dataset.loading = '';
          if (d.ok) {
            st.textContent = d.verified ? '✓ Verified — sending as ' + email : 'Not verified — check your app password and retry.';
            st.className = 'text-xs ' + (d.verified ? 'text-emerald-300' : 'text-amber-300');
            A.toast(d.message || 'Saved', d.verified ? 'success' : 'error');
            location.reload();
          } else { btn.disabled = false; A.toast(d.error || 'Error', 'error'); }
        })
        .catch(function () { btn.dataset.loading = ''; btn.disabled = false; A.toast('Network error', 'error'); });
    });
  }
  boot();
})();