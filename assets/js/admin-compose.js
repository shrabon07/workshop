/* Aurora Cyber — admin: composition pad (mail pad). Recipient '+' rows (≤10), B/I/U toolbar, send. */
(function () {
  'use strict';

  function boot() {
    var A = window.Admin;
    if (!A) { setTimeout(boot, 30); return; } // admin.js loads after this script on some pages
    var MAX = window.COMPOSE_MAX || 10;
    var list = document.getElementById('to-list');
    var count = document.getElementById('compose-count');
    var form = document.getElementById('compose-form');
    if (!list || !form || !count) { setTimeout(boot, 30); return; }

    function esc(s) {
      return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }

    function makeRow(value) {
      var row = document.createElement('div');
      row.className = 'flex items-center gap-2 to-row';
      row.innerHTML =
        '<input type="email" name="recipients[]" class="input w-full !py-2.5" placeholder="you@gmail.com" value="' + esc(value || '') + '">'
        + '<button type="button" class="to-del btn-ghost !py-2.5 !px-3 !text-sm shrink-0 !border-rose-400/20 hover:!border-rose-400/50 hover:!text-rose-300" title="Remove">✕</button>'
        + '<button type="button" class="to-add btn-teal !py-2.5 !px-3 !text-base shrink-0" title="Add another recipient">+</button>';
      return row;
    }

    function updateCount() {
      var n = list.querySelectorAll('.to-row').length;
      count.textContent = n + ' recipient' + (n > 1 ? 's' : '');
    }

    function maybeHide(row) {
      var add = row.querySelector('.to-add');
      if (add) add.style.display = list.querySelectorAll('.to-row').length >= MAX ? 'none' : '';
    }
    function refreshAdds() {
      list.querySelectorAll('.to-row').forEach(maybeHide);
    }

    list.addEventListener('click', function (e) {
      var addBtn = e.target.closest('.to-add');
      if (addBtn && list.querySelectorAll('.to-row').length < MAX) {
        list.appendChild(makeRow(''));
        refreshAdds();
        updateCount();
        var inputs = list.querySelectorAll('input[name="recipients[]"]');
        if (inputs.length) inputs[inputs.length - 1].focus();
        return;
      }
      var delBtn = e.target.closest('.to-del');
      if (delBtn) {
        if (list.querySelectorAll('.to-row').length <= 1) return;
        delBtn.closest('.to-row').remove();
        refreshAdds();
        updateCount();
      }
    });

    /* formatting toolbar on the message pad */
    var body = document.getElementById('compose-body');
    if (body) {
      document.querySelectorAll('.pad-tool').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var tag = btn.getAttribute('data-tag');
          if (tag === '\n\n') { // paragraph break
            var pos = body.selectionStart;
            body.setRangeText(tag, pos, pos, 'end');
            body.focus();
            return;
          }
          var start = body.selectionStart;
          var end = body.selectionEnd;
          var sel = body.value.slice(start, end);
          if (tag.indexOf('</') > -1) { // wrap tag like <b></b>
            var open = tag;
            var close = tag.replace('>', '</').replace('<', '>');
            body.setRangeText(open + sel + close, start, end, 'select');
          } else {
            body.setRangeText(tag, start, end, 'select');
          }
          body.focus();
        });
      });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var sb = document.getElementById('compose-send');
      var st = document.getElementById('compose-status');
      if (!sb || sb.disabled) return;
      sb.disabled = true;
      sb.dataset.loading = '1';
      st.textContent = '';
      var fd = new FormData(form);
      var raw = fd.getAll('recipients[]').filter(function (x) { return String(x).trim() !== ''; });
      if (!raw.length) { sb.disabled = false; sb.dataset.loading = ''; st.textContent = 'Add at least one recipient.'; return; }
      A.post('api/admin/compose_send.php', fd)
        .then(function (r) {
          sb.dataset.loading = '';
          if (r.ok) {
            st.textContent = 'Delivered to ' + r.sent + ' of ' + r.recipients + ' recipient(s) — saved to Mail List.';
            form.reset();
            list.innerHTML = '';
            list.appendChild(makeRow(''));
            refreshAdds();
            updateCount();
            A.toast('Mail sent and saved to the list', 'success');
          } else {
            sb.disabled = false;
            st.textContent = r.error || 'Error';
            A.toast(r.error || 'Error', 'error');
          }
        })
        .catch(function () { sb.dataset.loading = ''; sb.disabled = false; st.textContent = 'Network error'; A.toast('Network error', 'error'); });
    });

    updateCount();
    refreshAdds();
  }

  boot();
})();
