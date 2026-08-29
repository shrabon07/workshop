/* Aurora Cyber — admin: order status update (explicit Update button) */
(function () {
  'use strict';

  function boot() {
    var A = window.Admin;
    if (!A) { setTimeout(boot, 30); return; } // admin.js loads after this script on some pages

    function pairFor(sel) {
      var row = sel && sel.closest('tr');
      return row ? row.querySelector('.order-update') : null;
    }

    document.querySelectorAll('.order-status').forEach(function (sel) {
      var btn = pairFor(sel);
      if (sel.getAttribute('data-lock') || sel.disabled) return; // cancelled orders are locked

      sel.addEventListener('change', function () {
        btn.disabled = sel.value === sel.getAttribute('data-orig'); // enable only when changed
      });

      btn.addEventListener('click', function () {
        btn.disabled = true;
        sel.disabled = true;
        btn.dataset.loading = '1';
        A.post('api/admin/order_status.php', { id: sel.getAttribute('data-id'), status: sel.value })
          .then(function (d) {
            btn.dataset.loading = '';
            if (d.ok) { A.toast('Status updated — email sent to client', 'success'); location.reload(); }
            else { btn.disabled = false; A.toast(d.error || 'Error', 'error'); }
          })
          .catch(function () { btn.dataset.loading = ''; btn.disabled = false; A.toast('Network error', 'error'); });
      });
    });
  }

  boot();
})();