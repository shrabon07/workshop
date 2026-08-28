/* Aurora Cyber — admin: order status quick-update */
(function () {
  'use strict';
  var A = window.Admin;
  if (!A) return;
  document.querySelectorAll('.order-status').forEach(function (sel) {
    sel.addEventListener('change', function () {
      var self = sel;
      self.disabled = true;
      A.post('api/admin/order_status.php', { id: self.getAttribute('data-id'), status: self.value })
        .then(function (d) {
          self.disabled = false;
          if (d.ok) { A.toast('Status updated', 'success'); location.reload(); }
          else A.toast(d.error || 'Error', 'error');
        })
        .catch(function () { self.disabled = false; A.toast('Network error', 'error'); });
    });
  });
})();