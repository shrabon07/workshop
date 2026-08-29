/* ==================================================================
   AURORA CYBER — floating glass live-chat widget
   Bot auto-reply → admin takeover via api/chat.php & admin panel.
   ================================================================== */
(function () {
  'use strict';

  var TOKEN_KEY = 'wc_chat_token';
  var polling = null, lastId = 0, open = false, botThinking = false;
  var state = { bot_mode: 1, admin_taken: 0, suggestions: [], need_name: 1, guest_name: '' };
  var rendered = []; // rendered message ids

  function $id(id) { return document.getElementById(id); }
  function uuid() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = Math.random() * 16 | 0; var v = c === 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  }
  function token() {
    var t = localStorage.getItem(TOKEN_KEY);
    if (!t) { t = uuid(); localStorage.setItem(TOKEN_KEY, t); }
    return t;
  }
  function lang() { return window.I18N ? I18N.lang : 'en'; }
  function l10n(en, bn) { return '<span class="e">' + en + '</span><span class="b">' + bn + '</span>'; }

  function msgText(m) {
    var s = (m || '').trim();
    if (s.indexOf('::') === 0 && s.lastIndexOf('::') === s.length - 2) {
      var key = s.slice(2, -2);
      if (window.I18N && I18N.t(key)) return I18N.t(key);
    }
    return s;
  }

  function bubbleHTML(m) {
    var isGuest = m.sender === 'guest';
    var cls = isGuest
      ? 'bg-gradient-to-br from-brand-deep to-brand-light text-slate-900 self-end rounded-2xl rounded-br-md'
      : 'glass-chip text-slate-200 self-start rounded-2xl rounded-bl-md';
    var who = isGuest ? (I18N ? I18N.t('chat_you') : 'You') : (M.senderBadge(m.sender));
    return '<div class="flex flex-col max-w-[85%] ' + (isGuest ? 'items-end self-end' : 'items-start self-start') + '">' +
      '<span class="text-[10px] font-bold uppercase tracking-wide text-slate-500 mb-1">' + who + '</span>' +
      '<div class="px-4 py-2.5 text-sm whitespace-pre-line leading-relaxed ' + cls + '">' + escapeHTML(msgText(m.message)) + '</div>' +
      '</div>';
  }
  function escapeHTML(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  var M = {
    senderBadge: function (sender) {
      if (sender === 'admin') return I18N ? I18N.t('chat_agent') : 'Agent';
      if (sender === 'bot') return I18N ? I18N.t('chat_bot') : 'Bot';
      return I18N ? I18N.t('chat_you') : 'You';
    }
  };

  function ensureDOM() {
    if ($id('aurora-chat')) return;
    var div = document.createElement('div');
    div.id = 'aurora-chat';
    div.innerHTML =
      '<div id="chat-fab" class="fixed bottom-5 right-5 z-[60]">' +
      '  <button id="chat-open" class="relative w-16 h-16 rounded-full grid place-items-center bg-gradient-to-br from-brand-deep to-brand-light shadow-neon-teal transition-transform hover:scale-105 active:scale-95" aria-label="Chat">' +
      '    <span id="chat-ping" class="absolute inset-0 rounded-full bg-cyan-400/40" style="animation:pulse-ring 2.2s ease-out infinite"></span>' +
      '    <svg id="chat-fab-open" class="w-7 h-7 text-slate-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5m-6.3 5.6L4 20l.8-2.9A8.5 8.5 0 1 1 10.5 20h1.9l3.2 2.7"/></svg>' +
      '    <svg id="chat-fab-close" class="w-7 h-7 text-slate-900 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>' +
      '  </button>' +
      '</div>' +
      '<div id="chat-panel" class="fixed right-5 bottom-24 z-[60] w-[min(92vw,380px)] origin-bottom-right transition-all duration-300 scale-95 opacity-0 pointer-events-none translate-y-2">' +
      '  <div class="glass-strong rounded-3xl overflow-hidden flex flex-col max-h-[70vh]" style="min-height:420px">' +
      '    <div class="relative px-5 py-4 border-b border-white/10 bg-gradient-to-r from-slate-900/80 to-slate-900/60" id="chat-head">' +
      '      <div class="flex items-center gap-3">' +
      '        <span class="relative w-9 h-9 rounded-2xl grid place-items-center bg-gradient-to-br from-accent-neon to-accent-electric text-white text-xs font-extrabold shrink-0">A' +
      '          <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-400 border-2 border-slate-900"></span>' +
      '        </span>' +
      '        <div class="flex-1 min-w-0">' +
      '          <div class="font-bold text-white text-sm leading-tight" data-i18n="chat_title">Aurora Assistant</div>' +
      '          <div class="text-[11px] text-slate-400" data-i18n="chat_subtitle">Fast replies, Mon–Sat</div>' +
      '        </div>' +
      '        <button id="chat-min" class="glass-chip rounded-lg p-1.5 text-slate-400 hover:text-white transition-colors" aria-label="Minimize">' +
      '          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14"/></svg>' +
      '        </button>' +
      '      </div>' +
      '    </div>' +
      '    <div id="chat-messages" class="flex-1 overflow-y-auto nice-scroll px-4 py-4 flex flex-col gap-2.5 min-h-[240px]"></div>' +
      '    <div id="chat-gate" class="hidden px-4 pb-3 min-h-[150px]">' +
      '      <div class="glass-strong rounded-2xl p-4 flex flex-col gap-3">' +
      '        <div class="text-sm font-bold text-white" data-i18n="chat_name_label">What should we call you?</div>' +
      '        <div class="flex gap-2">' +
      '          <input id="chat-name-input" class="input !rounded-xl !py-2.5 flex-1" placeholder="Your name" data-i18n-placeholder="chat_name_placeholder" autocomplete="off" maxlength="60">' +
      '          <button id="chat-name-go" class="btn-teal !py-2.5 !px-4 rounded-xl shrink-0 whitespace-nowrap text-xs font-bold" type="button" data-i18n="chat_name_start">Start chat</button>' +
      '        </div>' +
      '        <div id="chat-name-err" class="hidden text-[11px] text-rose-300"></div>' +
      '      </div>' +
      '    </div>' +
      '    <div id="chat-typing" class="hidden px-4 pb-2 flex gap-1.5">' +
      '      <span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>' +
      '    </div>' +
      '    <div id="chat-suggestions" class="px-4 pb-3 flex flex-wrap gap-2"></div>' +
      '    <div id="chat-composer" class="border-t border-white/10 p-3 flex items-center gap-2 bg-slate-950/40">' +
      '      <input id="chat-input" class="input !rounded-xl !py-2.5" placeholder="Type your message…" data-i18n-placeholder="chat_placeholder" autocomplete="off">' +
      '      <button id="chat-send" class="btn-teal !p-3 !rounded-xl shrink-0" aria-label="Send">' +
      '        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>' +
      '      </button>' +
      '    </div>' +
      '  </div>' +
      '</div>';
    document.body.appendChild(div);
  }

  function renderAll() {
    var box = $id('chat-messages');
    if (!box) return;
    box.innerHTML = '';
    rendered.forEach(function (m) { box.insertAdjacentHTML('beforeend', bubbleHTML(m)); });
    box.scrollTop = box.scrollHeight;
    rerenderSuggestions();
    // hydrate i18n inside panel
    if (window.I18N) I18N.apply(false);
  }

  function append(msgs) {
    var box = $id('chat-messages');
    var added = 0;
    (msgs || []).forEach(function (m) {
      if (rendered.indexOf(m.id) !== -1) return;
      rendered.push(m.id);
      if (m.id > lastId) lastId = m.id;
      if (box) box.insertAdjacentHTML('beforeend', bubbleHTML(m));
      added++;
    });
    if (added && box) { box.scrollTop = box.scrollHeight; }
  }

  function rerenderSuggestions() {
    var box = $id('chat-suggestions');
    if (!box) return;
    box.innerHTML = '';
    (state.suggestions || []).forEach(function (s) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'glass-chip rounded-full px-3.5 py-1.5 text-xs font-semibold text-cyan-300 hover:bg-cyan-400/10 transition-colors';
      b.innerHTML = s.label_en + ' <span class="text-slate-500">/</span> ' + s.label_bn;
      b.addEventListener('click', function () { send(s.label_en); box.innerHTML = ''; });
      box.appendChild(b);
    });
  }

  function gateShow(show) {
    var gate = $id('chat-gate');
    var comp = $id('chat-composer');
    var msgs = $id('chat-messages');
    state.need_name = show ? 1 : 0;
    if (gate) gate.classList.toggle('hidden', !show);
    if (comp) comp.classList.toggle('hidden', show);
    if (msgs) msgs.classList.toggle('hidden', show);
    if (show && $id('chat-name-input')) {
      setTimeout(function () { $id('chat-name-input').focus(); }, 250);
    }
  }

  function apiSetName(name) {
    var err = $id('chat-name-err');
    if (err) err.classList.add('hidden');
    name = (name || '').trim();
    if (name.length < 2 || name.length > 60) {
      if (err) {
        err.textContent = I18N ? I18N.t('chat_name_error') : 'Please enter your name (2–60 characters).';
        err.classList.remove('hidden');
      }
      return;
    }
    api('set_name', { name: name }).then(function (d) {
      if (!d.ok) {
        if (err) {
          err.textContent = I18N ? I18N.t('chat_name_error') : 'Please enter your name (2–60 characters).';
          err.classList.remove('hidden');
        }
        return;
      }
      state.guest_name = d.guest_name || name;
      state.bot_mode = d.bot_mode; state.admin_taken = d.admin_taken;
      lastId = 0; rendered = [];
      append(d.messages);
      gateShow(false);
      var input = $id('chat-input');
      if (input) setTimeout(function () { input.focus(); }, 200);
      if (!polling) poll();
    }).catch(function () {});
  }

  function updateGate(d) {
    if (!d || d.need_name === undefined) return;
    if (d.need_name) {
      state.need_name = 1;
      state.guest_name = d.guest_name || '';
      gateShow(!state.guest_name);
    } else {
      state.need_name = 0;
      state.guest_name = d.guest_name || '';
      gateShow(false);
    }
  }

  function typing(show) {
    var t = $id('chat-typing');
    if (t) t.classList.toggle('hidden', !show);
    botThinking = show;
  }

  function api(action, payload) {
    payload = payload || {};
    payload.action = action;
    payload.token = token();
    var fd = new FormData();
    Object.keys(payload).forEach(function (k) { fd.append(k, payload[k]); });
    return fetch((window.AURORA_BASE || '') + '/api/chat.php', { method: 'POST', body: fd }).then(function (r) { return r.json(); });
  }

  function openChat() {
    typing(true);
    api('open').then(function (d) {
      typing(false);
      if (!d.ok) return;
      state.bot_mode = d.bot_mode; state.admin_taken = d.admin_taken;
      lastId = 0; rendered = [];
      updateGate(d);
      if (!state.need_name) append(d.messages);
      var box = $id('chat-messages');
      if (box) box.scrollTop = box.scrollHeight;
    }).catch(function () {
      typing(false);
      var box = $id('chat-messages');
      if (box) box.insertAdjacentHTML('beforeend',
        '<div class="glass-chip text-rose-300 text-xs px-3 py-2 self-center">' +
        '<span class="e">Offline — chat unavailable right now.</span><span class="b">অফলাইন — এখন চ্যাট পাওয়া যাচ্ছে না।</span></div>');
    });
  }

  function poll() {
    if (polling || !open) return;
    polling = true;
    api('read', { after: lastId })
      .then(function (d) {
        if (d.ok) {
          updateGate(d);
          if (!state.need_name) append(d.messages);
          state.bot_mode = d.bot_mode;
          state.admin_taken = d.admin_taken;
          if (d.admin_taken) rerenderSuggestions();
        }
      })
      .catch(function () {})
      .finally(function () { polling = false; });
  }

  function send(text) {
    text = (text || '').trim();
    if (!text || state.need_name) return;
    var input = $id('chat-input');
    if (input) input.value = '';
    api('send', { message: text }).then(function (d) {
      typing(false);
      if (!d.ok) return;
      updateGate(d);
      if (state.need_name) return;
      append(d.messages);
      state.bot_mode = d.bot_mode;
      state.admin_taken = d.admin_taken;
      state.suggestions = d.suggestions || [];
      rerenderSuggestions();
    }).catch(function () { typing(false); });
  }

  function togglePanel(force) {
    var panel = $id('chat-panel');
    var fabO = $id('chat-fab-open');
    var fabC = $id('chat-fab-close');
    open = force !== undefined ? force : !open;
    panel.classList.toggle('scale-95', !open);
    panel.classList.toggle('opacity-0', !open);
    panel.classList.toggle('pointer-events-none', !open);
    panel.classList.toggle('translate-y-2', !open);
    if (open) {
      fabO.classList.add('hidden'); fabC.classList.remove('hidden');
      $id('chat-ping') && ($id('chat-ping').style.display = 'none');
      if (!rendered.length) openChat();
      var input = $id('chat-input');
      if (input) setTimeout(function () { input.focus(); }, 200);
      if (!polling) poll();
      if (!polling) { /* already */ }
    } else {
      fabO.classList.remove('hidden'); fabC.classList.add('hidden');
      if ($id('chat-ping')) $id('chat-ping').style.display = '';
    }
  }

  function bind() {
    ensureDOM();
    $id('chat-open').addEventListener('click', function () { togglePanel(); });
    $id('chat-min').addEventListener('click', function () { togglePanel(false); });
    $id('chat-send').addEventListener('click', function () { send($id('chat-input').value); });
    $id('chat-input').addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(e.target.value); }
    });
    $id('chat-name-go').addEventListener('click', function () { apiSetName($id('chat-name-input').value); });
    $id('chat-name-input').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); apiSetName(e.target.value); }
    });
    setInterval(poll, 2500);
    // re-render text keys when the user flips EN/BANGLA
    document.addEventListener('langchange', function () { renderAll(); });
  }

  function start() { bind(); }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();