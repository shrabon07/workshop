/* Aurora Cyber — admin: customer management (override update, edit, email, bulk email, delete) */
(function () {
  'use strict';

  function boot() {
    var A = window.Admin;
    if (!A) { setTimeout(boot, 30); return; } // admin.js loads after this script on some pages

    function esc(s) {
      return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }

    function formRow(label, inner, note) {
      return '<div><label class="block text-xs font-bold text-slate-400 mb-1">' + label + '</label>'
        + inner
        + (note ? '<p class="mt-1 text-xs text-slate-500">' + note + '</p>' : '')
        + '</div>';
    }

    function footerBtns(formId, submitLabel) {
      return '<div class="flex justify-end gap-2 pt-1">'
        + '<button type="button" data-close class="btn-ghost !py-2 !px-4 text-xs">Cancel / বাতিল</button>'
        + '<button type="submit" class="btn-teal !py-2 !px-4 text-xs">' + (submitLabel || 'Save / সংরক্ষণ') + '</button>'
        + '</div>';
    }

    /* ---------- override select -> Update button (explicit apply) ---------- */
    function updateBtnFor(sel) {
      var row = sel.closest('tr');
      return row ? row.querySelector('.ov-update') : null;
    }

    document.querySelectorAll('.override-select').forEach(function (sel) {
      var btn = updateBtnFor(sel);
      if (!btn) return;

      sel.addEventListener('change', function () {
        btn.disabled = sel.value === sel.getAttribute('data-orig'); // enable only when changed
      });

      btn.addEventListener('click', function () {
        btn.disabled = true;
        sel.disabled = true;
        btn.dataset.loading = '1';
        A.post('api/admin/verify_override.php', { user_id: sel.getAttribute('data-id'), override: sel.value })
          .then(function (d) {
            btn.dataset.loading = '';
            if (d.ok) { A.toast('Override applied', 'success'); location.reload(); }
            else { btn.disabled = false; sel.disabled = false; A.toast(d.error || 'Error', 'error'); }
          })
          .catch(function () { btn.dataset.loading = ''; btn.disabled = false; sel.disabled = false; A.toast('Network error', 'error'); });
      });
    });

    /* ---------- edit customer (details + verify bypass) ---------- */
    document.querySelectorAll('.cust-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var d = btn.dataset;
        var m = A.modal(
          '<h3 class="text-lg font-bold text-slate-100">Edit customer / সম্পাদনা</h3>'
          + '<form id="cust-edit-form" class="mt-4 space-y-4">'
          + formRow('Name / নাম', '<input name="name" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" value="' + esc(d.name) + '" maxlength="120" required>')
          + formRow('Email / ইমেইল', '<input name="email" type="email" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" value="' + esc(d.email) + '" maxlength="190" required>')
          + formRow('Phone / ফোন', '<input name="phone" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" value="' + esc(d.phone) + '" maxlength="30">')
          + formRow('Manual verify / ম্যানুয়াল যাচাই',
              '<div class="flex gap-5">'
              + '<label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer select-none"><input type="checkbox" name="email_verified" value="1" ' + (d.emailV === '1' ? 'checked' : '') + ' class="accent-cyan-400 h-4 w-4"> Email verified</label>'
              + '<label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer select-none"><input type="checkbox" name="whatsapp_verified" value="1" ' + (d.whatsV === '1' ? 'checked' : '') + ' class="accent-cyan-400 h-4 w-4"> WhatsApp verified</label>'
              + '</div>',
              'Checking a box bypasses the OTP flow and marks that channel verified immediately. / টিক দিলে OTP ছাড়াই সেই চ্যানেল যাচাই হয়ে যাবে।')
          + footerBtns('cust-edit-form')
          + '</form>'
        );
        m.el.querySelector('[data-close]').addEventListener('click', m.close);
        m.el.querySelector('#cust-edit-form').addEventListener('submit', function (e) {
          e.preventDefault();
          var f = e.target;
          var sb = f.querySelector('[type=submit]');
          var fd = new FormData(f);
          fd.append('user_id', d.id);
          sb.disabled = true;
          sb.dataset.loading = '1';
          A.post('api/admin/customer_save.php', fd)
            .then(function (r) {
              sb.dataset.loading = '';
              if (r.ok) { m.close(); A.toast('Customer saved', 'success'); location.reload(); }
              else { sb.disabled = false; A.toast(r.error || 'Error', 'error'); }
            })
            .catch(function () { sb.dataset.loading = ''; sb.disabled = false; A.toast('Network error', 'error'); });
        });
      });
    });

    /* ---------- add new customer ---------- */
    function randomPass() {
      var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
      var out = '';
      for (var i = 0; i < 10; i++) out += chars[Math.floor(Math.random() * chars.length)];
      return out;
    }

    var addBtn = document.getElementById('btn-add-customer');
    if (addBtn) addBtn.addEventListener('click', function () {
      var m = A.modal(
        '<h3 class="text-lg font-bold text-slate-100">Add new customer / নতুন কাস্টমার</h3>'
        + '<form id="cust-add-form" class="mt-4 space-y-4">'
        + formRow('Name / নাম', '<input name="name" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" maxlength="120" required autofocus>')
        + formRow('Email / ইমেইল', '<input name="email" type="email" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" maxlength="190" required>')
        + formRow('Phone / ফোন', '<input name="phone" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" maxlength="30">')
        + formRow('Password / পাসওয়ার্ড', '<input name="password" type="text" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" minlength="6" value="' + esc(randomPass()) + '" required>',
            'At least 6 characters. Share these credentials with the customer. / কমপক্ষে 6 অক্ষর, কাস্টমারের সাথে শেয়ার করুন।')
        + formRow('Manual verify / ম্যানুয়াল যাচাই',
            '<div class="flex gap-5">'
            + '<label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer select-none"><input type="checkbox" name="email_verified" value="1" class="accent-cyan-400 h-4 w-4"> Email verified</label>'
            + '<label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer select-none"><input type="checkbox" name="whatsapp_verified" value="1" class="accent-cyan-400 h-4 w-4"> WhatsApp verified</label>'
            + '</div>',
            'Optional — checking marks that channel verified immediately. / ঐচ্ছিক — টিক দিলে চ্যানেলটি সাথে সাথে যাচাই হয়ে যাবে।')
        + footerBtns('cust-add-form', 'Create / তৈরি করুন')
        + '</form>'
      );
      m.el.querySelector('[data-close]').addEventListener('click', m.close);
      m.el.querySelector('#cust-add-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var f = e.target;
        var sb = f.querySelector('[type=submit]');
        sb.disabled = true;
        sb.dataset.loading = '1';
        A.post('api/admin/customer_create.php', new FormData(f))
          .then(function (r) {
            sb.dataset.loading = '';
            if (r.ok) { m.close(); A.toast('Customer created', 'success'); location.reload(); }
            else { sb.disabled = false; A.toast(r.error || 'Error', 'error'); }
          })
          .catch(function () { sb.dataset.loading = ''; sb.disabled = false; A.toast('Network error', 'error'); });
      });
    });

    /* ---------- send email (single) / bulk marketing ---------- */
    function openEmailModal(userId, isBulk) {
      var m = A.modal(
        '<h3 class="text-lg font-bold text-slate-100">' + (isBulk ? 'Email all customers / সব কাস্টমারকে ইমেইল' : 'Send email / ইমেইল পাঠান') + '</h3>'
        + '<form id="cust-email-form" class="mt-4 space-y-4">'
        + formRow('Subject / বিষয়', '<input name="subject" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" maxlength="190" placeholder="' + (isBulk ? 'Promo / অফার…' : 'Regarding your project…') + '" required>')
        + formRow('Message / বার্তা', '<textarea name="message" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" rows="6" placeholder="Write the message text…" required></textarea>')
        + formRow('Note / নোট', '<p class="text-xs text-slate-500">' + (isBulk ? 'Sends to every customer who has an email address (admins are skipped).' : 'Sends to this customer&#8217;s inbox.') + '</p>')
        + '<div class="flex justify-end gap-2 pt-1">'
        + '<button type="button" data-close class="btn-ghost !py-2 !px-4 text-xs">Cancel / বাতিল</button>'
        + '<button type="submit" class="btn-teal !py-2 !px-4 text-xs">Send / পাঠান</button>'
        + '</div>'
        + '</form>'
      );
      m.el.querySelector('[data-close]').addEventListener('click', m.close);
      m.el.querySelector('#cust-email-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var f = e.target;
        var sb = f.querySelector('[type=submit]');
        var fd = new FormData(f);
        fd.append('user_id', userId);
        sb.disabled = true;
        sb.dataset.loading = '1';
        A.post('api/admin/customer_email.php', fd)
          .then(function (r) {
            sb.dataset.loading = '';
            if (r.ok) {
              m.close();
              if (isBulk && r.failed) A.toast('Sent to ' + r.sent + ' customers (' + r.failed + ' failed)', 'success');
              else A.toast(isBulk ? 'Sent to ' + r.sent + ' customers' : 'Email sent', 'success');
            } else { sb.disabled = false; A.toast(r.error || 'Error', 'error'); }
          })
          .catch(function () { sb.dataset.loading = ''; sb.disabled = false; A.toast('Network error', 'error'); });
      });
    }

    var bulkBtn = document.getElementById('btn-bulk-email');
    if (bulkBtn) bulkBtn.addEventListener('click', function () { openEmailModal(0, true); });

    document.querySelectorAll('.cust-email').forEach(function (btn) {
      btn.addEventListener('click', function () { openEmailModal(btn.dataset.id, false); });
    });

    /* ---------- delete customer ---------- */
    document.querySelectorAll('.cust-del').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var name = btn.closest('tr') ? btn.closest('tr').querySelector('.font-bold') : null;
        var label = name ? name.textContent.trim() : 'this customer';
        if (!confirm('Delete ' + label + '? Their orders stay in history. / এই কাস্টমারটি মুছবেন? অর্ডার ইতিহাস থাকবে।')) return;
        btn.disabled = true;
        btn.dataset.loading = '1';
        A.post('api/admin/customer_delete.php', { user_id: btn.dataset.id })
          .then(function (r) {
            btn.dataset.loading = '';
            if (r.ok) { A.toast('Customer deleted', 'success'); location.reload(); }
            else { btn.disabled = false; A.toast(r.error || 'Error', 'error'); }
          })
          .catch(function () { btn.dataset.loading = ''; btn.disabled = false; A.toast('Network error', 'error'); });
      });
    });
  }

  boot();
})();