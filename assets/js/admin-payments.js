/* Aurora Cyber — admin: payments (send request, mark paid) */
(function () {
  'use strict';

  function boot() {
    var A = window.Admin;
    if (!A) { setTimeout(boot, 30); return; } // admin.js loads after this script on some pages

    var data = window.PAY_DATA || {};

    var custSel  = document.getElementById('pay-customer');
    var orderSel = document.getElementById('pay-order');
    var amountIn = document.getElementById('pay-amount');
    var hint     = document.getElementById('pay-hint');
    if (!custSel || !orderSel || !amountIn) return;

    var sendBtn = document.getElementById('pay-send');

    function refreshSend() {
      sendBtn.disabled = !(custSel.value && orderSel.value && parseFloat(amountIn.value) > 0);
    }

    amountIn.addEventListener('input', refreshSend);
    orderSel.addEventListener('change', function () {
      var cust = data[custSel.value];
      if (!cust) return;
      for (var i = 0; i < cust.orders.length; i++) {
        if (String(cust.orders[i].id) === orderSel.value) {
          amountIn.value = cust.orders[i].budget > 0 ? cust.orders[i].budget : '';
          break;
        }
      }
      refreshSend();
    });

    custSel.addEventListener('change', function () {
      var cust = data[custSel.value];
      orderSel.innerHTML = '<option value="">— Select order —</option>';
      amountIn.value = '';
      orderSel.disabled = !cust || !cust.orders.length;
      hint.textContent = '';
      if (cust) {
        cust.orders.forEach(function (o) {
          var opt = document.createElement('option');
          opt.value = o.id;
          opt.textContent = o.label;
          orderSel.appendChild(opt);
        });
        if (!cust.orders.length) {
          hint.textContent = 'No active orders for this customer.';
        }
      }
      refreshSend();
    });

    document.getElementById('pay-request-form').addEventListener('submit', function (e) {
      e.preventDefault();
      if (sendBtn.disabled) return;
      sendBtn.disabled = true;
      sendBtn.dataset.loading = '1';
      A.post('api/admin/payment_request_create.php', {
        user_id: custSel.value,
        order_id: orderSel.value,
        amount: amountIn.value,
        note: document.getElementById('pay-note').value
      }).then(function (r) {
        sendBtn.dataset.loading = '';
        if (r.ok) { A.toast('Payment request sent — notification + email delivered', 'success'); location.reload(); }
        else { sendBtn.disabled = false; A.toast(r.error || 'Error', 'error'); }
      }).catch(function () { sendBtn.dataset.loading = ''; sendBtn.disabled = false; A.toast('Network error', 'error'); });
    });

    document.querySelectorAll('.pay-paid-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm('Mark this payment as PAID? The customer gets a notification + email. / এই পেমেন্ট কি পরিশোধিত হিসেবে চিহ্নিত করবেন? গ্রাহক নোটিফিকেশন + ইমেইল পাবে।')) return;
        btn.disabled = true;
        btn.dataset.loading = '1';
        A.post('api/admin/payment_request_paid.php', { id: btn.dataset.id })
          .then(function (r) {
            btn.dataset.loading = '';
            if (r.ok) { A.toast('Payment marked as paid — notification + email sent', 'success'); location.reload(); }
            else { btn.disabled = false; A.toast(r.error || 'Error', 'error'); }
          })
          .catch(function () { btn.dataset.loading = ''; btn.disabled = false; A.toast('Network error', 'error'); });
      });
    });
  }

  boot();
})();