/* Aurora Cyber — custom order form (AJAX to api/order/create.php) */
(function () {
  'use strict';

  function $(sel, scope) { return (scope || document).querySelector(sel); }

  /* service cards → prefill + scroll to order form */
  function wireServiceCards() {
    var form = $('#order-form');
    if (!form) return;
    document.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('[data-service-id]') : null;
      if (!a) return;
      var sel = $('#order-service');
      var id = a.getAttribute('data-service-id');
      if (sel && id) {
        var opt = Array.prototype.slice.call(sel.options).filter(function (o) { return o.value === id; })[0];
        if (opt) {
          sel.value = id;
          opt.textContent = opt.textContent;
        } else {
          // service list in form is a cached subset — fall back to project type
          var type = a.getAttribute('data-service-name');
          var t = $('#order-form [name=project_type]');
          if (t) t.value = type;
        }
      }
      var section = $('#order');
      if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  /* submission */
  function wireForm() {
    var form = $('#order-form');
    if (!form) return;
    var btn = form.querySelector('[type=submit]');
    var result = $('#order-result');
    var langSel = function (en, bn) { return '<span class="e">' + en + '</span><span class="b">' + bn + '</span>'; };

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }

      btn.disabled = true;
      var original = btn.innerHTML;
      btn.innerHTML = '<span class="inline-flex items-center gap-2"><span class="w-4 h-4 rounded-full border-2 border-slate-900/30 border-t-slate-900 animate-spin"></span> ' + langSel('Sending…', 'পাঠানো হচ্ছে…') + '</span>';

      fetch((window.AURORA_BASE || '') + '/api/order/create.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'fetch' },
        body: new FormData(form)
      })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
          result.classList.remove('hidden');
          if (res.ok && res.data.ok) {
            result.className = 'mt-5 rounded-2xl border border-emerald-400/30 bg-emerald-400/10 p-5';
            result.innerHTML =
              '<div class="font-extrabold text-emerald-300 text-lg">✓ ' + langSel('Brief received!', 'ব্রিফ পেয়েছি!') + '</div>' +
              '<p class="mt-2 text-sm text-slate-300">' + langSel(res.data.message_en, res.data.message_bn) + '</p>' +
              '<div class="mt-4 flex flex-wrap gap-3">' +
              '<a class="btn-teal !py-2.5 text-xs" target="_blank" rel="noopener" href="' + res.data.whatsapp_url + '">' + langSel('Continue on WhatsApp', 'হোয়াটসঅ্যাপে চালিয়ে যান') + '</a>' +
              '<a class="btn-ghost !py-2.5 text-xs" href="account/login.php?next=' + encodeURIComponent('account/dashboard.php') + '">' + langSel('Track my order →', 'অর্ডার ট্র্যাক করুন →') + '</a>' +
              '</div>';
            form.reset();
            // re-prefill if a returning user is signed in
            var n = $('#order-form [name=email]');
            if (n && !res.data.guest) { /* keep empty for next guest */ }
          } else {
            result.className = 'mt-5 rounded-2xl border border-rose-400/30 bg-rose-400/10 p-5';
            result.innerHTML = '<div class="font-extrabold text-rose-300 text-sm"></div>' +
              '<p class="text-sm text-slate-300">' + langSel(res.data.error || 'Something went wrong. Please try again.', res.data.error || 'কিছু একটা সমস্যা হয়েছে। আবার চেষ্টা করুন।') + '</p>';
          }
        })
        .catch(function () {
          result.classList.remove('hidden');
          result.className = 'mt-5 rounded-2xl border border-rose-400/30 bg-rose-400/10 p-5';
          result.innerHTML = '<p class="text-sm text-slate-300">' + langSel('Network error — please try again or message us on WhatsApp.', 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন বা হোয়াটসঅ্যাপে জানান।') + '</p>';
        })
        .finally(function () {
          btn.disabled = false;
          btn.innerHTML = original;
        });
    });
  }

  /* Wire exactly once — this script is deferred (runs at 'interactive'), so the
     immediate call already runs; the DOMContentLoaded fallback covers a
     non-deferred include in <head>. Without the guard both would fire on every
     submit and create a duplicate order. */
  var wired = false;
  function wire() {
    if (wired) return;
    wired = true;
    wireServiceCards();
    wireForm();
  }
  document.addEventListener('DOMContentLoaded', wire);
  if (document.readyState !== 'loading') wire();
})();