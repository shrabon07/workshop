/* Aurora Cyber — admin: connect my Gmail app password as my personal sender */
(function () {
  'use strict';
  function boot() {
    var A = window.Admin;
    if (!A) { setTimeout(boot, 30); return; }
    var form = document.getElementById('mail-settings-form');
    if (!form) return;

    var passInput = document.getElementById('ms-pass');
    var passHint = document.getElementById('ms-pass-hint');

    /* Gmail app passwords are 16 letters, but they are shown with spaces
       (abcd efgh ijkl mnop). We strip any spaces so pasting either form works. */
    function normPass() {
      return (passInput.value || '').replace(/\s+/g, '');
    }
    if (passInput && passHint) {
      passInput.addEventListener('input', function () {
        var n = normPass().length;
        passHint.textContent = n === 0 ? '' : (n === 16 ? '16 letters ✓' : (n + ' / 16 letters'));
      });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = document.getElementById('ms-save');
      var st = document.getElementById('ms-status');
      var email = document.getElementById('ms-email').value.trim();
      var pass = normPass();
      if (!email || !pass) {
        st.textContent = 'Enter your Gmail and the 16-letter app password.';
        st.className = 'text-xs text-amber-300';
        return;
      }
      if (pass.length !== 16) {
        st.textContent = 'Gmail app passwords are exactly 16 letters (spaces are optional).';
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