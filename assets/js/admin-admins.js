/* Aurora Cyber — admin: staff account creation (admins only). */
(function () {
  'use strict';

  function boot() {
    var A = window.Admin;
    if (!A) { setTimeout(boot, 30); return; }

    function randomPass() {
      var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
      var out = '';
      for (var i = 0; i < 10; i++) out += chars[Math.floor(Math.random() * chars.length)];
      return out;
    }

    var addBtn = document.getElementById('btn-add-admin');
    if (!addBtn) return;

    addBtn.addEventListener('click', function () {
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
        + 'Only admins can create admins. New admins get full panel access. / শুধু অ্যাডমিনরাই অ্যাডমিন তৈরি করতে পারেন। নতুন অ্যাডমিন প্যানেলের সম্পূর্ণ অ্যাক্সেস পায়।'
        + '</div>'
        + '<div class="flex justify-end gap-2 pt-1">'
        + '<button type="button" data-close class="btn-ghost !py-2 !px-4 text-xs">Cancel / বাতিল</button>'
        + '<button type="submit" class="btn-teal !py-2 !px-4 text-xs">Create admin / অ্যাডমিন তৈরি করুন</button>'
        + '</div>'
        + '</form>'
      );

      m.el.querySelector('[data-close]').addEventListener('click', m.close);
      m.el.querySelector('#admin-add-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var f = e.target;
        var sb = f.querySelector('[type=submit]');
        sb.disabled = true;
        sb.dataset.loading = '1';
        A.post('api/admin/admin_create.php', new FormData(f))
          .then(function (r) {
            sb.dataset.loading = '';
            if (r.ok) {
              m.close();
              if (r.email_sent) A.toast('Admin created — login details emailed', 'success');
              else A.toast('Admin created, but the login email failed', 'error');
              location.reload();
            }
            else { sb.disabled = false; A.toast(r.error || 'Error', 'error'); }
          })
          .catch(function () { sb.dataset.loading = ''; sb.disabled = false; A.toast('Network error', 'error'); });
      });
    });
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  boot();
})();