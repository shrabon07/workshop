/* Aurora Cyber — admin browser notifications.
   Polls api/admin/push_poll.php every 15s on ANY admin page and fires a
   browser Notification for new orders and new live-chat messages, from any
   device/browser the admin is signed in on. Also powers the 🔔 bell badge. */
(function () {
  'use strict';

  var POLL_MS = 15000;

  function boot() {
    var A = window.Admin;
    if (!A) { setTimeout(boot, 30); return; }

    var lang = localStorage.getItem('wclang') === 'bn' ? 'bn' : 'en';
    var T = {
      en: { order: 'New order', chat: 'Live chat', enable: 'Click the bell to enable browser notifications', denied: 'Notifications are blocked in this browser — allow them in site settings.', on: 'Browser notifications ON', off: 'Browser notifications OFF', bell: 'Browser notifications' },
      bn: { order: 'নতুন অর্ডার', chat: 'লাইভ চ্যাট', enable: 'ব্রাউজার নোটিফিকেশন চালু করতে ঘণ্টায় ক্লিক করুন', denied: 'এই ব্রাউজারে নোটিফিকেশন ব্লক করা — সাইট সেটিংস থেকে অনুমতি দিন।', on: 'ব্রাউজার নোটিফিকেশন চালু', off: 'ব্রাউজার নোটিফিকেশন বন্ধ', bell: 'ব্রাউজার নোটিফিকেশন' }
    }[lang];

    var bell = document.getElementById('push-bell');
    var countEl = document.getElementById('push-count');
    var enabled = localStorage.getItem('acpush') === '1';
    var perm = ('Notification' in window) ? Notification.permission : 'unsupported';
    var lastSince = 0;
    var chatMsgs = {};   // chat_id -> last notified message id

    if (!bell) return;

    function setBadge(n) {
      if (!countEl) return;
      if (n > 0) { countEl.textContent = n > 99 ? '99+' : n; countEl.classList.remove('hidden'); }
      else { countEl.classList.add('hidden'); }
    }

    function renderBell() {
      var on = enabled && perm === 'granted';
      bell.setAttribute('data-on', on ? '1' : '0');
      bell.classList.toggle('opacity-50', !on);
      bell.title = on ? T.bell + ' ✓' : T.enable;
    }

    function beep() {
      try {
        var ctx = new (window.AudioContext || window.webkitAudioContext)();
        var o = ctx.createOscillator(); var g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.frequency.value = 880; g.gain.value = 0.07;
        o.start(); o.stop(ctx.currentTime + 0.18);
      } catch (e) {}
    }

    function fire(title, body, tag, url) {
      if (document.hidden) {
        try { beep(); } catch (e) {}
      }
      try {
        if (enabled && perm === 'granted') {
          new Notification(title, { body: body, tag: tag, icon: window.location.origin + '/assets/img/logo.png' });
        }
      } catch (e) {}
      A.toast(title + ' — ' + body, 'info');
      if (url) { setTimeout(function () { window.location.href = url; }, 4200); }
    }

    function onOrders(orders, since) {
      orders.forEach(function (o) {
        fire(T.order + ' #' + o.id + ' (' + o.status + ')',
             o.client + ' — ' + o.svc + ' — ৳ ' + Number(o.budget).toLocaleString(),
             'order-' + o.id,
             'orders.php');
      });
    }

    function onChats(chats, since) {
      // group burst messages per chat; only notify for new chats / new latest message
      var latest = {};
      chats.forEach(function (c) {
        if (!latest[c.id] || latest[c.id].msg < c.msg) latest[c.id] = c;
      });
      Object.keys(latest).forEach(function (cid) {
        var c = latest[cid];
        if ((chatMsgs[cid] || 0) >= c.msg) return;
        chatMsgs[cid] = c.msg;
        // suppress when the admin is already looking at this chat
        try {
          var q = new URLSearchParams(window.location.search);
          if (/support\.php/.test(window.location.pathname) && q.get('chat_id') === cid) return;
        } catch (e) {}
        var who = c.sender === 'admin' ? '(admin) ' : '';
        fire(T.chat + (c.name ? ' — ' + c.name : ''), who + c.preview, 'chat-' + c.id, 'support.php?chat_id=' + c.id);
      });
    }

    function tick() {
      var since = lastSince;
      A.post('api/admin/push_poll.php', { since: since })
        .then(function (d) {
          if (!d || !d.ok) return;
          if (since === 0) {           // baseline — establish "now", no bursts
            lastSince = d.now;
            (d.counts) && updateBadge(d.counts);
            return;
          }
          lastSince = d.now;
          onOrders(d.orders || [], since);
          onChats(d.chats || [], since);
          if (d.counts) updateBadge(d.counts);
        })
        .catch(function () {});
    }

    function updateBadge(c) {
      setBadge((c.orders_pending || 0) + (c.chats_open || 0));
    }

    // bell click: request permission the first time, then toggle, then open inbox
    var asked = localStorage.getItem('acpushasked') === '1';
    bell.addEventListener('click', function () {
      if (perm === 'default') {
        if ('Notification' in window) {
          Notification.requestPermission().then(function (p) {
            perm = p;
            localStorage.setItem('acpushasked', '1');
            enabled = p === 'granted';
            localStorage.setItem('acpush', enabled ? '1' : '0');
            renderBell();
            A.toast(p === 'granted' ? T.on : T.denied, p === 'granted' ? 'success' : 'error');
            if (enabled) tick();
          });
        } else {
          A.toast('This browser does not support notifications.', 'error');
        }
      } else if (perm === 'denied') {
        A.toast(T.denied, 'error');
      } else {
        enabled = !enabled;
        localStorage.setItem('acpush', enabled ? '1' : '0');
        renderBell();
        A.toast(enabled ? T.on : T.off, enabled ? 'success' : 'info');
      }
    });

    renderBell();
    var initOrders = parseInt(bell.getAttribute('data-orders') || '0', 10);
    var initChats = parseInt(bell.getAttribute('data-chats') || '0', 10);
    setBadge(initOrders + initChats);
    if (perm === 'default' && !asked) {
      A.toast(T.enable, 'info');
    }
    tick();
    setInterval(tick, POLL_MS);
  }

  if (document.readyState !== 'loading') boot(); else document.addEventListener('DOMContentLoaded', boot);
})();