/* Aurora Cyber — admin: staff accounts (create / edit / deactivate / delete). Super admin controls. */
(function () {
  'use strict';

  function boot() {
    var A = window.Admin;
    if (!A) { setTimeout(boot, 30); return; }

    function esc(s) {
      return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }

    function randomPass() {
      var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
      var out = '';
      for (var i = 0; i < 10; i++) out += chars[Math.floor(Math.random() * chars.length)];
      return out;
    }

    function fields(submitLabel) {
      return '<div class="flex justify-end gap-2 pt-1">'
        + '<button type="button" data-close class="btn-ghost !py-2 !px-4 text-xs">Cancel / বাতিল</button>'
        + '<button type="submit" class="btn-teal !py-2 !px-4 text-xs">' + (submitLabel || 'Save / সংরক্ষণ') + '</button>'
        + '</div>';
    }

    function wire(m, formSel, url, onOk) {
      m.el.querySelector('[data-close]').addEventListener('click', m.close);
      m.el.querySelector(formSel).addEventListener('submit', function (e) {
        e.preventDefault();
        var f = e.target;
        var s = f.querySelector('[type=submit]');
        s.disabled = true; s.dataset.loading = '1';
        A.post(url, new FormData(f))
          .then(function (r) {
            s.dataset.loading = '';
            if (r.ok) {
              if (onOk) { onOk(); } else { m.close(); A.toast('Saved', 'success'); location.reload(); }
            } else { s.disabled = false; A.toast(r.error || 'Error', 'error'); }
          })
          .catch(function () { s.dataset.loading = ''; s.disabled = false; A.toast('Network error', 'error'); });
      });
    }

    /* ---------- create admin (super admin only) ---------- */
    var addBtn = document.getElementById('btn-add-admin');
    if (addBtn) addBtn.addEventListener('click', function () {
      var m = A.modal(
        '<h3 class="text-lg font-bold text-slate-100">Add new admin / নতুন অ্যাডমিন যোগ করুন</h3>'
        + '<form id="admin-add-form" class="mt-4 space-y-4">'
        + '<div><label class="block text-xs font-bold text-slate-400 mb-1">Name / নাম</label>'
        + '<input name="name" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" maxlength="120" required autofocus></div>'
        + '<div><label class="block text-xs font-bold text-slate-400 mb-1">Email / ইমেইল</label>'
        + '<input name="email" type="email" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" maxlength="190" required></div>'
        + '<div><label class="block text-xs font-bold text-slate-400 mb-1">Password / পাসওয়ার্ড</label>'
        + '<input name="password" type="text" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" minlength="6" value="' + esc(randomPass()) + '" required>'
        + '<p class="mt-1 text-xs text-slate-500">At least 6 characters. Login details are emailed to the new admin. / কমপক্ষে 6 অক্ষর। লগইন তথ্য নতুন অ্যাডমিনকে ইমেইলে পাঠানো হয়।</p></div>'
        + '<div class="rounded-xl border border-rose-400/20 bg-rose-400/5 px-4 py-3 text-xs text-rose-200">'
        + 'Only the super admin can create admins. / শুধু সুপার অ্যাডমিনই অ্যাডমিন তৈরি করতে পারেন।'
        + '</div>'
        + fields('Create admin / অ্যাডমিন তৈরি করুন')
        + '</form>'
      );
      wire(m, '#admin-add-form', 'api/admin/admin_create.php', function () {
        m.close();
        A.toast('Admin created — login details emailed', 'success');
        location.reload();
      });
    });

    /* ---------- edit admin ---------- */
    document.querySelectorAll('.adm-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var d = btn.dataset;
        var m = A.modal(
          '<h3 class="text-lg font-bold text-slate-100">Edit admin / অ্যাডমিন সম্পাদনা</h3>'
          + '<form id="admin-edit-form" class="mt-4 space-y-4">'
          + '<div><label class="block text-xs font-bold text-slate-400 mb-1">Name / নাম</label>'
          + '<input name="name" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" maxlength="120" value="' + esc(d.name) + '" required></div>'
          + '<div><label class="block text-xs font-bold text-slate-400 mb-1">Email / ইমেইল</label>'
          + '<input name="email" type="email" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" maxlength="190" value="' + esc(d.email) + '" required></div>'
          + '<div><label class="block text-xs font-bold text-slate-400 mb-1">New password / নতুন পাসওয়ার্ড</label>'
          + '<input name="password" type="text" class="input w-full !bg-slate-900/60 !border-white/10 text-slate-100" minlength="6" placeholder="Leave blank to keep current / ফাঁকা রাখলে বর্তমান থাকবে">'
          + '<p class="mt-1 text-xs text-slate-500">Optional — at least 6 characters if set. / ঐচ্ছিক — সেট করলে কমপক্ষে 6 অক্ষর।</p></div>'
          + fields('Save / সংরক্ষণ')
          + '</form>'
        );
        m.el.querySelector('#admin-edit-form').addEventListener('submit', function (e) {
          e.preventDefault();
          var f = e.target;
          var s = f.querySelector('[type=submit]');
          s.disabled = true; s.dataset.loading = '1';
          var data = new FormData(f);
          data.append('admin_id', d.id);
          A.post('api/admin/admin_edit.php', data)
            .then(function (r) {
              s.dataset.loading = '';
              if (r.ok) { m.close(); A.toast('Admin updated', 'success'); location.reload(); }
              else { s.disabled = false; A.toast(r.error || 'Error', 'error'); }
            })
            .catch(function () { s.dataset.loading = ''; s.disabled = false; A.toast('Network error', 'error'); });
        });
      });
    });

    /* ---------- deactivate / reactivate ---------- */
    document.querySelectorAll('.adm-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var d = btn.dataset;
        var deactivating = d.active === '1';
        if (!confirm(deactivating
          ? 'Deactivate ' + d.name + '? They will lose panel access immediately. / ' + d.name + ' কে নিষ্ক্রিয় করবেন? সাথে সাথে প্যানেল অ্যাক্সেস বন্ধ হবে।'
          : 'Reactivate ' + d.name + '? They will regain panel access. / ' + d.name + ' কে সক্রিয় করবেন? প্যানেল অ্যাক্সেস ফিরে পাবে।')) return;
        btn.disabled = true; btn.dataset.loading = '1';
        A.post('api/admin/admin_deactivate.php', { admin_id: d.id })
          .then(function (r) {
            btn.dataset.loading = '';
            if (r.ok) { A.toast(deactivating ? 'Admin deactivated' : 'Admin reactivated', 'success'); location.reload(); }
            else { btn.disabled = false; A.toast(r.error || 'Error', 'error'); }
          })
          .catch(function () { btn.dataset.loading = ''; btn.disabled = false; A.toast('Network error', 'error'); });
      });
    });

    /* ---------- delete ---------- */
    document.querySelectorAll('.adm-del').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var d = btn.dataset;
        if (!confirm('Delete admin ' + d.name + '? This cannot be undone. / অ্যাডমিন ' + d.name + ' কে মুছবেন? এটি ফেরানো যাবে না।')) return;
        btn.disabled = true; btn.dataset.loading = '1';
        A.post('api/admin/admin_delete.php', { admin_id: d.id })
          .then(function (r) {
            btn.dataset.loading = '';
            if (r.ok) { A.toast('Admin deleted', 'success'); location.reload(); }
            else { btn.disabled = false; A.toast(r.error || 'Error', 'error'); }
          })
          .catch(function () { btn.dataset.loading = ''; btn.disabled = false; A.toast('Network error', 'error'); });
      });
    });
  }

  boot();
})();