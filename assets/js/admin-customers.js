/* Aurora Cyber — admin: verification override quick-set */
(function () {
  'use strict';
  var A = window.Admin;
  if (!A) return;
  document.querySelectorAll('.override-select').forEach(function (sel) {
    sel.addEventListener('change', function () {
      var self = sel;
      self.disabled = true;
      A.post('api/admin/verify_override.php', { user_id: self.getAttribute('data-id'), override: self.value })
        .then(function (d) {
          self.disabled = false;
          if (d.ok) { A.toast('Override saved', 'success'); }
          else A.toast(d.error || 'Error', 'error');
        })
        .catch(function () { self.disabled = false; A.toast('Network error', 'error'); });
    });
  });
})();