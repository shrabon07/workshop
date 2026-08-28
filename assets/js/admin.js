/* Aurora Cyber — admin core (toasts, modals, helpers) */
(function () {
  'use strict';

  var CSRF = (function () {
    var meta = document.querySelector('meta[name=csrf]');
    return meta ? meta.getAttribute('content') : '';
  })();

  function toast(msg, type) {
    var mount = document.getElementById('toast-mount');
    if (!mount) return;
    var el = document.createElement('div');
    var cls = type === 'error' ? 'border-rose-400/40 text-rose-200' : type === 'success' ? 'border-emerald-400/40 text-emerald-200' : 'border-cyan-400/30 text-cyan-100';
    el.className = 'glass-strong pointer-events-auto rounded-2xl px-5 py-3 text-sm font-semibold border ' + cls + ' fade-swap';
    el.textContent = msg;
    mount.appendChild(el);
    setTimeout(function () {
      el.style.opacity = '0';
      el.style.transition = 'opacity .3s ease';
      setTimeout(function () { el.remove(); }, 350);
    }, 3200);
  }

  function modal(html) {
    var b = document.createElement('div');
    b.className = 'modal-backdrop';
    b.innerHTML = '<div class="modal-card glass-strong rounded-3xl p-7 max-h-[88vh] overflow-y-auto nice-scroll">' + html + '</div>';
    b.addEventListener('click', function (e) { if (e.target === b) close(); });
    document.body.appendChild(b);
    document.body.style.overflow = 'hidden';
    function close() { b.remove(); document.body.style.overflow = ''; }
    return { el: b, close: close };
  }

  function post(url, data) {
    var fd = data instanceof FormData ? data : new FormData();
    if (!(data instanceof FormData)) {
      Object.keys(data || {}).forEach(function (k) { fd.append(k, data[k]); });
    }
    fd.append('csrf_token', CSRF);
    return fetch(url, { method: 'POST', body: fd }).then(function (r) { return r.json(); });
  }

  window.Admin = { toast: toast, modal: modal, post: post, csrf: CSRF };

  /* mobile sidebar toggle fallback (link-based, no JS needed) */
  document.addEventListener('DOMContentLoaded', function () {
    if (window.I18N) I18N.apply(false);
  });
  if (document.readyState === 'interactive' || document.readyState === 'complete') {
    if (window.I18N) I18N.apply(false);
  }
})();