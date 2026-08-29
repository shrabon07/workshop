/* Aurora Cyber — admin: live chat hub (sessions poll, messages poll, reply, takeover) */
(function () {
  'use strict';
  var A = window.Admin;
  if (!A) return;

  var sessionsBox = document.getElementById('chat-sessions');
  var counter = document.getElementById('chat-count');
  var msgBox = document.getElementById('chat-messages');
  var headLabel = document.getElementById('chat-head-label');
  var replyInput = document.getElementById('reply-input');
  var replyForm = document.getElementById('reply-form');
  var takeoverBtn = document.getElementById('takeover-btn');
  if (!sessionsBox || !msgBox || !replyForm) return;

  var activeId = replyInput.getAttribute('data-chat-id') ? Number(replyInput.getAttribute('data-chat-id')) : 0;
  var lastCount = 0;

  function esc(s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
  function timeAgo(stamp) { return String(stamp).slice(11, 16); }

  function whoName(s) { return s.user_name || s.guest_name || 'Guest'; }

  function sessionRow(s) {
    var sym = s.last_sender === 'admin' ? '▸' : (s.last_sender === 'bot' ? '●' : '◂');
    var lm = s.last_message !== null && String(s.last_message).indexOf('::') === 0 ? '(i18n auto msg)' : s.last_message;
    return '<button class="w-full text-left px-4 py-3.5 border-b border-white/5 hover:bg-white/5 transition-colors session-row ' + (s.id === activeId ? 'bg-cyan-400/10' : '') + '" data-chat-id="' + s.id + '">' +
      '<div class="flex items-center justify-between gap-2"><span class="font-bold text-slate-100 text-sm truncate">' + esc(whoName(s)) + '</span>' +
      '<span class="text-[10px] text-slate-500 shrink-0">' + esc(timeAgo(s.updated_at)) + '</span></div>' +
      '<div class="text-[11px] text-slate-500 mb-1.5">' + (Number(s.bot_mode) ? '🤖 bot-mode' : (Number(s.admin_taken) ? '👨\u200d💻 live admin' : '•••')) + ' · +' + esc(s.phone || '—') + '</div>' +
      '<div class="flex justify-between items-center gap-2"><span class="truncate text-xs ' + (Number(s.unread) ? 'text-slate-200 font-semibold' : 'text-slate-500') + '"><span class="mr-1">' + sym + '</span>' + esc((lm || '').slice(0, 46)) + '</span>' +
      (Number(s.unread) ? '<span class="mini-badge shrink-0">' + s.unread + '</span>' : '') + '</div></button>';
  }

  function bubble(m) {
    var isAdmin = m.sender === 'admin';
    var isBot = m.sender === 'bot';
    var cls = isAdmin ? 'ab-admin ml-auto text-right' : (isBot ? 'ab-bot' : 'ab-guest');
    var tag = isAdmin ? '🗣' : (isBot ? '🤖' : m.sender.toUpperCase());
    var body = m.message;
    if (body !== null && String(body).indexOf('::') === 0) {
      var key = String(body).slice(2, -2);
      body = (window.I18N && I18N.t(key)) ? I18N.t(key) : ('(auto: ' + key + ')');
    }
    return '<div class="flex ' + (isAdmin ? 'justify-end' : 'justify-start') + '"><div class="ab ' + cls + '">' +
      '<div class="text-[10px] uppercase tracking-wider opacity-60 mb-1">' + esc(tag) + ' · ' + esc(timeAgo(m.created_at)) + '</div>' +
      '<div>' + esc(body).replace(/\n/g, '<br>') + '</div></div></div>';
  }

  function renderSessions(chats) {
    var html = chats.map(sessionRow).join('');
    sessionsBox.innerHTML = html || '<div class="py-14 text-center text-slate-500 text-xs px-6">No live sessions right now.</div>';
    if (counter) counter.textContent = chats.length;
  }

  function loadSessions() {
    fetch((window.AURORA_BASE || '') + '/api/admin/chat_list.php').then(function (r) { return r.json(); }).then(function (d) {
      if (d.ok) renderSessions(d.chats || []);
    }).catch(function () {});
  }

  function openChat(id, chats) {
    activeId = id;
    replyInput.setAttribute('data-chat-id', id);
    replyInput.disabled = false;
    replyForm.querySelector('[type=submit]').disabled = false;
    document.querySelectorAll('.session-row').forEach(function (b) { b.classList.toggle('bg-cyan-400/10', Number(b.getAttribute('data-chat-id')) === id); });
    fetch((window.AURORA_BASE || '') + '/api/admin/chat_messages.php?chat_id=' + id).then(function (r) { return r.json(); }).then(function (d) {
      if (!d.ok) return;
      var c = d.chat;
      headLabel.innerHTML = esc(whoName(c)) + '<span class="text-[11px] text-slate-400 font-normal ml-2">+' + esc(c.phone || '—') + ' · ' + (Number(c.bot_mode) ? 'bot' : 'manual') + '</span>';
      if (takeoverBtn) {
        takeoverBtn.innerHTML = Number(c.admin_taken) ? 'Release to bot / বটে ছেড়ে দিন' : 'Take over / নিয়ন্ত্রণ নিন';
        takeoverBtn.setAttribute('data-chat-id', id);
        takeoverBtn.setAttribute('data-action', Number(c.admin_taken) ? 'release' : 'takeover');
        takeoverBtn.classList.remove('hidden');
      }
      lastCount = (d.messages || []).length;
      msgBox.innerHTML = (d.messages || []).map(bubble).join('') || '<div class="text-slate-500 text-sm py-8 text-center">No messages yet.</div>';
      msgBox.scrollTop = msgBox.scrollHeight;
    }).catch(function () {});
  }

  function pollActive() {
    if (!activeId) return;
    var reqId = activeId;
    fetch((window.AURORA_BASE || '') + '/api/admin/chat_messages.php?chat_id=' + reqId).then(function (r) { return r.json(); }).then(function (d) {
      if (!d.ok || reqId !== activeId) return;
      var ms = d.messages || [];
      if (ms.length !== lastCount) {
        lastCount = ms.length;
        msgBox.innerHTML = ms.map(bubble).join('');
        msgBox.scrollTop = msgBox.scrollHeight;
      }
    }).catch(function () {});
  }

  sessionsBox.addEventListener('click', function (e) {
    var row = e.target.closest('.session-row');
    if (row) openChat(Number(row.getAttribute('data-chat-id')));
  });

  replyForm.addEventListener('submit', function (e) {
    e.preventDefault();
    var text = replyInput.value.trim();
    var id = Number(replyInput.getAttribute('data-chat-id'));
    if (!id || !text) return;
    var btn = replyForm.querySelector('[type=submit]');
    btn.disabled = true;
    A.post('api/admin/chat_reply.php', { chat_id: id, message: text }).then(function (d) {
      btn.disabled = false;
      if (d.ok) {
        replyInput.value = '';
        lastCount++;
        loadSessions();
        var now = new Date().toISOString().replace('T', ' ').slice(0, 16) + ':00';
        msgBox.insertAdjacentHTML('beforeend', bubble({ sender: 'admin', message: text, created_at: now }));
        msgBox.scrollTop = msgBox.scrollHeight;
      } else {
        A.toast(d.error || 'Send failed', 'error');
      }
    }).catch(function () { btn.disabled = false; A.toast('Network error', 'error'); });
  });

  if (takeoverBtn) takeoverBtn.addEventListener('click', function () {
    var self = takeoverBtn;
    if (!self.getAttribute('data-chat-id')) return;
    self.disabled = true;
    A.post('api/admin/chat_takeover.php', { chat_id: self.getAttribute('data-chat-id'), action: self.getAttribute('data-action') })
      .then(function (d) {
        self.disabled = false;
        if (d.ok) {
          self.setAttribute('data-action', Number(d.admin_taken) ? 'release' : 'takeover');
          self.innerHTML = Number(d.admin_taken) ? 'Release to bot / বটে ছেড়ে দিন' : 'Take over / নিয়ন্ত্রণ নিন';
          openChat(activeId);
          A.toast(d.action === 'takeover' ? 'You took over' : 'Bot resumed', 'success');
        } else A.toast(d.error || 'Action failed', 'error');
      })
      .catch(function () { self.disabled = false; });
  });

  loadSessions();
  setInterval(loadSessions, 5000);
  setInterval(pollActive, 4000);
  if (activeId) { openChat(activeId); }
})();