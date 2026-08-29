/* Aurora Cyber — admin: services list CRUD, service form, categories */
(function () {
  'use strict';
  var A = window.Admin;
  if (!A) return;

  var esc = function (s) {
    return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  };
  function lang(sel) { var el = document.querySelector(sel); return el ? el.value : ''; }

  /* ============================ SERVICES LIST ============================ */
  function initList() {
    var tbody = document.getElementById('sv-tbody');
    if (!tbody) return;

    var q = document.getElementById('sv-search');
    var catSel = document.getElementById('sv-cat');
    var empty = document.getElementById('sv-empty');

    function rowHTML(s) {
      var st = s.status;
      var stBadge = st === 'active' ? 'st-active' : (st === 'inactive' ? 'st-inactive' : 'st-archived');
      var label = st === 'active' ? 'Active / সক্রিয়' : (st === 'inactive' ? 'Inactive / নিষ্ক্রিয়' : 'Archived / আর্কাইভড');
      var sym = st === 'active' ? '●' : (st === 'archived' ? '✕' : '○');
      var th = s.thumbnail ? '<img src="' + esc(s.thumbnail_url) + '" alt="" class="w-full h-full object-cover" loading="lazy">' : '<span class="text-sm">▤</span>';
      var actions;
      if (st === 'archived') {
        actions = '<button type="button" class="restore-btn glass-chip rounded-lg px-3 py-1.5 text-xs font-bold text-emerald-300 hover:bg-emerald-400/10 transition-colors" data-id="' + s.id + '">Restore / পুনরুদ্ধার</button>';
      } else {
        actions =
          '<a href="service-form.php?id=' + s.id + '" class="glass-chip rounded-lg px-3 py-1.5 text-xs font-bold text-slate-200 hover:text-cyan-300 transition-colors">Edit / সম্পাদনা</a>' +
          '<button type="button" class="toggle-btn glass-chip rounded-lg px-3 py-1.5 text-xs font-bold ' + (st === 'active' ? 'text-slate-400 hover:text-amber-300' : 'text-emerald-300 hover:bg-emerald-400/10') + '" data-id="' + s.id + '">' + (st === 'active' ? 'Deactivate / নিষ্ক্রিয়' : 'Activate / সক্রিয়') + '</button>' +
          '<button type="button" class="delete-btn rounded-lg px-3 py-1.5 text-xs font-bold text-rose-300 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 transition-colors" data-id="' + s.id + '" data-title="' + esc(s.title_en) + '">Delete / মুছুন</button>';
      }
      return '<tr data-service-id="' + s.id + '">' +
        '<td><div class="flex items-center gap-3">' +
          '<span class="w-14 h-10 rounded-lg overflow-hidden border border-white/10 bg-white/5 grid place-items-center shrink-0">' + th + '</span>' +
          '<div class="min-w-0"><div class="font-bold text-slate-100 text-sm truncate max-w-[200px]">' + esc(s.title_en) + '</div>' +
          '<div class="text-xs text-slate-500 truncate max-w-[200px]">' + esc(s.title_bn) + ' · <code>' + esc(s.slug) + '</code></div></div></div></td>' +
        '<td><span class="text-slate-300 text-xs font-semibold">' + esc(s.cat_en || '—') + '</span></td>' +
        '<td><span class="font-extrabold text-slate-100">৳ ' + Number(s.price).toLocaleString() + '</span><div class="text-[11px] text-slate-500">' + esc(s.price_label) + '</div></td>' +
        '<td><span class="st-badge ' + stBadge + '">' + sym + ' ' + label + '</span></td>' +
        '<td><span class="' + (Number(s.is_featured) ? 'text-amber-300' : 'text-slate-700') + ' text-sm">★</span></td>' +
        '<td><div class="flex items-center justify-end gap-2">' + actions + '</div></td>' +
        '</tr>';
    }

    function load(status, qv, cat) {
      var params = new URLSearchParams();
      if (status) params.set('status', status);
      if (qv) params.set('q', qv);
      if (cat) params.set('category_id', cat);
      fetch((window.AURORA_BASE || '') + '/api/admin/get_services.php?' + params.toString()).then(function (r) { return r.json(); }).then(function (d) {
        if (!d.ok) return;
        try {
          var rows = d.services.map(function (s) {
            var c = Object.assign({}, s);
            c.thumbnail_url = s.thumbnail ? ('uploads/' + s.thumbnail) : '';
            return rowHTML(c);
          });
          tbody.innerHTML = rows.join('');
          empty.classList.toggle('hidden', rows.length > 0);
        } catch (err) { /* keep prior rows */ }
      }).catch(function () {});
    }

    var debounce = null;
    function refresh() {
      clearTimeout(debounce);
      debounce = setTimeout(function () {
        var activeTab = document.querySelector('#status-tabs .st-tab-on');
        load(activeTab ? activeTab.getAttribute('data-status') : 'all', (q ? q.value : '').trim(), catSel ? catSel.value : '');
      }, 220);
    }

    document.querySelectorAll('#status-tabs .st-tab').forEach(function (b) {
      b.addEventListener('click', function () {
        document.querySelectorAll('#status-tabs .st-tab').forEach(function (x) { x.classList.remove('st-tab-on'); x.classList.add('text-slate-400'); });
        b.classList.add('st-tab-on');
        b.classList.remove('text-slate-400');
        refresh();
      });
    });
    if (q) q.addEventListener('input', refresh);
    if (catSel) catSel.addEventListener('change', refresh);

    // actions
    tbody.addEventListener('click', function (e) {
      var del = e.target.closest('.delete-btn');
      var tog = e.target.closest('.toggle-btn');
      var res = e.target.closest('.restore-btn');
      if (del) { openDelete(del); }
      else if (tog) { toggle(tog); }
      else if (res) { restore(res); }
    });

    function doPost(fn, btn, okMsg) {
      btn.disabled = true;
      A.post('api/admin/' + fn, { id: btn.getAttribute('data-id') }).then(function (d) {
        btn.disabled = false;
        if (d.ok) { A.toast(okMsg, 'success'); refresh(); }
        else A.toast(d.error || 'Error', 'error');
      }).catch(function () { btn.disabled = false; A.toast('Network error', 'error'); });
    }

    function toggle(btn) { doPost('toggle_status.php', btn, 'Status updated'); }
    function restore(btn) { doPost('restore_service.php', btn, 'Service restored & live'); }

    function openDelete(btn) {
      var modal = document.getElementById('delete-modal');
      document.getElementById('delete-target').textContent = btn.getAttribute('data-title');
      modal.classList.remove('hidden');
      modal.querySelector('#delete-cancel').onclick = function () { modal.classList.add('hidden'); };
      modal.querySelector('#delete-confirm').onclick = function () {
        var c = modal.querySelector('#delete-confirm');
        c.disabled = true;
        A.post('api/admin/delete_service.php', { id: btn.getAttribute('data-id') }).then(function (d) {
          c.disabled = false;
          modal.classList.add('hidden');
          if (d.ok) { A.toast(d.message || 'Service archived', 'success'); refresh(); }
          else A.toast(d.error || 'Error', 'error');
        });
      };
    }
  }

  /* ============================ SERVICE FORM ============================ */
  function initForm() {
    var form = document.getElementById('service-form');
    if (!form) return;

    var csrfInput = document.querySelector('input[name=csrf_token]');
    var slugInput = document.getElementById('fv-slug');
    var titleEn = document.getElementById('fv-title-en');
    var slugAuto = document.getElementById('fv-slug-auto');

    function slugify(s) {
      return s.toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s_]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    }
    titleEn.addEventListener('input', function () {
      if (slugAuto && slugAuto.value === '1') {
        slugInput.value = slugify(titleEn.value);
      }
    });
    slugInput.addEventListener('input', function () { if (slugAuto) slugAuto.value = '0'; });

    // status radio → hidden field
    form.querySelectorAll('input[name=status-vis]').forEach(function (r) {
      r.addEventListener('change', function () {
        document.getElementById('fv-status').value = r.value;
      });
    });

    // ---- features rows ----
    var wrap = document.getElementById('fv-features');
    function addFeature(en, bn) {
      var row = document.createElement('div');
      row.className = 'feat-row';
      row.innerHTML =
        '<input class="input !py-2.5 feat-en" placeholder="English feature" value="' + esc(en || '') + '">' +
        '<input class="input !py-2.5 feat-bn" placeholder="বাংলা ফিচার" value="' + esc(bn || '') + '">' +
        '<button type="button" class="feat-del glass-chip rounded-xl px-3 text-rose-300 hover:bg-rose-500/10 transition-colors" aria-label="Remove">✕</button>';
      row.querySelector('.feat-del').addEventListener('click', function () { row.remove(); });
      wrap.appendChild(row);
      row.querySelector('.feat-en').focus();
    }
    document.getElementById('fv-feat-add').addEventListener('click', function () { addFeature(); });
    wrap.querySelectorAll('.feat-row').forEach(function (r) {
      r.querySelector('.feat-del').addEventListener('click', function () { r.remove(); });
    });

    // ---- thumbnail upload ----
    var thumbFile = document.getElementById('fv-thumb-file');
    if (thumbFile) {
      thumbFile.addEventListener('change', function () {
        var file = thumbFile.files[0];
        if (!file) return;
        var preview = document.getElementById('fv-thumb-preview');
        var reader = new FileReader();
        reader.onload = function (e) {
          preview.src = e.target.result;
          preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
        upload(file, function (d) {
          if (d.ok) {
            document.getElementById('fv-thumbnail').value = d.path;
            document.getElementById('fv-thumb-preview').src = d.url;
          } else {
            A.toast(d.error || 'Upload failed', 'error');
          }
        });
      });
    }

    // ---- gallery ----
    var galleryFile = document.getElementById('fv-gallery-file');
    if (galleryFile) {
      galleryFile.addEventListener('change', function () {
        Array.prototype.forEach.call(galleryFile.files, function (file) {
          upload(file, function (d) {
            if (d.ok) {
              var hid = document.getElementById('fv-gallery');
              var cur = hid.value ? hid.value.split(',') : [];
              cur.push(d.path);
              hid.value = cur.join(',');
              var cont = document.getElementById('fv-gallery-thumbs');
              var im = document.createElement('img');
              im.src = d.url;
              im.className = 'w-20 h-14 object-cover rounded-lg border border-white/10';
              cont.appendChild(im);
              cont.parentElement.classList.remove('hidden');
            } else {
              A.toast(d.error || 'Upload failed', 'error');
            }
          });
        });
      });
    }

    function upload(file, cb) {
      var fd = new FormData();
      fd.append('file', file);
      fd.append('csrf_token', A.csrf);
      fetch((window.AURORA_BASE || '') + '/api/admin/upload.php', { method: 'POST', body: fd }).then(function (r) { return r.json(); }).then(cb).catch(function () { A.toast('Upload error', 'error'); });
    }

    // ---- save ----
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var id = form.getAttribute('data-id') || '';
      var fd = new FormData();
      fd.append('id', id);
      fd.append('csrf_token', csrfInput.value);
      fd.append('title_en', lang('#fv-title-en'));
      fd.append('title_bn', lang('#fv-title-bn'));
      fd.append('slug', slugInput.value);
      fd.append('category_id', lang('#fv-category'));
      fd.append('short_desc_en', lang('#fv-short-en'));
      fd.append('short_desc_bn', lang('#fv-short-bn'));
      fd.append('full_desc_en', lang('#fv-full-en'));
      fd.append('full_desc_bn', lang('#fv-full-bn'));
      fd.append('price', lang('#fv-price'));
      fd.append('price_label', lang('#fv-price-label'));
      fd.append('thumbnail', lang('#fv-thumbnail'));
      fd.append('gallery', lang('#fv-gallery'));
      fd.append('is_featured', document.getElementById('fv-featured').checked ? '1' : '0');
      fd.append('sort_order', lang('#fv-sort'));
      fd.append('status', lang('#fv-status'));
      wrap.querySelectorAll('.feat-row').forEach(function (r) {
        fd.append('features_en[]', r.querySelector('.feat-en').value);
        fd.append('features_bn[]', r.querySelector('.feat-bn').value);
      });

      var btn = form.querySelector('[type=submit]');
      btn.disabled = true;
      btn.textContent = 'Saving…';
      fetch((window.AURORA_BASE || '') + '/api/admin/' + (id ? 'update_service.php' : 'create_service.php'), { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          btn.disabled = false; btn.textContent = 'Save / সংরক্ষণ';
          if (d.ok) {
            A.toast(d.id ? (id ? 'Service updated — live now' : 'Service created — live now') : 'Saved', 'success');
            setTimeout(function () { window.location.href = 'services.php'; }, 600);
          } else {
            A.toast(d.error || 'Could not save', 'error');
          }
        })
        .catch(function () { btn.disabled = false; btn.textContent = 'Save / সংরক্ষণ'; A.toast('Network error', 'error'); });
    });
  }

  /* ============================ CATEGORIES ============================ */
  function initCategories() {
    var wrap = document.getElementById('cat-wrap');
    if (!wrap) return;

    var modal = null;
    function openCatModal(cat) {
      modal = A.modal(
        '<h3 class="font-bold text-white text-lg mb-1">' + (cat ? 'Edit category / ক্যাটাগরি সম্পাদনা' : 'Add category / ক্যাটাগরি যোগ') + '</h3>' +
        '<form id="cat-modal-form" class="mt-4 space-y-4">' +
          '<input type="hidden" name="id" value="' + (cat ? cat.id : '') + '">' +
          '<div><label class="label">English name</label><input class="input" name="name_en" required value="' + esc(cat ? cat.name_en : '') + '"></div>' +
          '<div><label class="label">বাংলা নাম</label><input class="input" name="name_bn" required value="' + esc(cat ? cat.name_bn : '') + '"></div>' +
          '<div><label class="label">Slug</label><input class="input" name="slug" required value="' + esc(cat ? cat.slug : '') + '"></div>' +
          '<div><label class="label">Sort order / ক্রম</label><input class="input" type="number" name="sort_order" value="' + (cat ? cat.sort_order : 0) + '"></div>' +
          '<div class="flex justify-end gap-3 pt-2"><button type="button" data-close class="btn-ghost !py-2 !px-4 text-xs">Cancel</button><button type="submit" class="btn-teal !py-2 !px-4 text-xs">Save / সংরক্ষণ</button></div>' +
        '</form>'
      );
      modal.el.querySelector('[data-close]').addEventListener('click', modal.close);
      modal.el.querySelector('#cat-modal-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(e.target);
        A.post('api/admin/category_save.php', fd).then(function (d) {
          if (d.ok) { modal.close(); A.toast('Category saved', 'success'); location.reload(); }
          else A.toast(d.error || 'Error', 'error');
        });
      });
    }

    wrap.addEventListener('click', function (e) {
      var add = e.target.closest('[data-cat-add]');
      var edit = e.target.closest('[data-cat-edit]');
      var del = e.target.closest('[data-cat-del]');
      if (add) { openCatModal(null); }
      else if (edit) { openCatModal({ id: edit.getAttribute('data-id'), name_en: edit.getAttribute('data-en'), name_bn: edit.getAttribute('data-bn'), slug: edit.getAttribute('data-slug'), sort_order: edit.getAttribute('data-sort') }); }
      else if (del) {
        if (confirm('Delete this category? Services must be reassigned first.')) {
          A.post('api/admin/category_delete.php', { id: del.getAttribute('data-id') }).then(function (d) {
            if (d.ok) { A.toast('Category deleted', 'success'); location.reload(); }
            else A.toast(d.error || 'Reassign services first', 'error');
          });
        }
      }
    });
  }

  function bootAdminServices() { initList(); initForm(); initCategories(); }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAdminServices);
  } else {
    bootAdminServices();
  }
})();