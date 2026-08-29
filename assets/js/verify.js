/* Aurora Cyber — verification center (email OTP + WhatsApp click-to-chat) */
(function () {
  'use strict';

  var state = { email: { pending: false }, whatsapp: { pending: false } };
  var styles = {
    red:   { ring: 'border-rose-500/40', cls: 'text-rose-300', bg: '#fb7185' },
    grey:  { ring: 'border-slate-400/40', cls: 'text-slate-200', bg: '#94a3b8' },
    green: { ring: 'border-emerald-400/40', cls: 'text-emerald-300', bg: '#34d399' }
  };

  function $(s, c) { return (c || document).querySelector(s); }
  function $$(s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); }
  function lang() { return window.I18N ? I18N.lang : 'en'; }

  function flash(msg) {
    var el = $('#verify-message');
    if (!el) return;
    el.classList.remove('hidden');
    el.innerHTML = msg;
    clearTimeout(flash._t);
    flash._t = setTimeout(function () { el.classList.add('hidden'); }, 6000);
  }
  function okFlash(en, bn) { flash('<span class="text-emerald-300 font-semibold">✓ ' + en + ' / ' + bn + '</span>'); }
  function errFlash(msg) { flash('<span class="text-rose-300 font-semibold">' + msg + '</span>'); }

  function updateTick(d) {
    var panel = $('#tick-panel');
    if (!panel || !d || !d.tick) return;
    var t = d.tick;
    var s = styles[t] || styles.red;
    panel.setAttribute('data-tick', t);
    var icon = $('#tick-icon');
    var label = $('#tick-label');
    if (icon) {
      icon.className = 'mx-auto w-24 h-24 rounded-full grid place-items-center text-4xl font-black border-2 border-' + t + ' glow';
      icon.style.borderColor = 'rgba(255,255,255,.14)';
      icon.innerHTML = '<span class="' + s.cls + '">' + (d.icon || (t === 'red' ? '❌' : t === 'grey' ? '✓' : '✔')) + '</span>';
    }
    if (label) label.textContent = d.label_en + ' / ' + d.label_bn;
  }

  function bind() {
    var chButtons = $$('[data-verify-channel]');
    chButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var channel = btn.getAttribute('data-verify-channel');
        requestCode(channel, btn);
      });
    });

    var wa = $('[data-verify-whatsapp]');
    if (wa) wa.addEventListener('click', function (e) {
      e.preventDefault();
      requestCode('whatsapp', wa);
    });

    $$('[data-otp-confirm]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var channel = btn.getAttribute('data-otp-confirm');
        var input = $('[data-otp-input="' + channel + '"]');
        var code = input ? input.value.trim() : '';
        if (!/^\d{6}$/.test(code)) { errFlash('Please enter the 6-digit code.'); return; }
        confirmCode(channel, code, btn.getAttribute('data-csrf'));
      });
    });

    $$('[data-otp-input]').forEach(function (inp) {
      inp.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          var ch = inp.getAttribute('data-otp-input');
          var btn = $('[data-otp-confirm="' + ch + '"]');
          if (btn) btn.click();
        }
      });
    });
  }

  function requestCode(channel, btn) {
    if (state[channel] && state[channel].pending) return;
    state[channel] = { pending: true };

    var fd = new FormData();
    fd.append('channel', channel);
    fd.append('csrf_token', btn.getAttribute('data-csrf'));

    fetch((window.AURORA_BASE || '') + '/api/verify/send-otp.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
      .then(function (res) {
        state[channel].pending = false;
        if (!res.ok || res.data.error) {
          var w = res.data && res.data.wait;
          errFlash((res.data && res.data.error) || 'Something went wrong.' + (w ? ' (wait ' + w + 's)' : ''));
          return;
        }
        // reveal OTP box
        var wrap = $('[data-otp-wrap="' + channel + '"]');
        if (wrap) {
          wrap.classList.remove('hidden');
          wrap.classList.add('fade-swap');
        }
        if (res.data.dev_reveal) {
          var codeEl = $('[data-otp-code="' + channel + '"]');
          if (codeEl) codeEl.textContent = res.data.dev_reveal;
          var devEl = $('[data-otp-dev="' + channel + '"]');
          if (devEl) devEl.classList.remove('hidden'); // server only sends this in dev mode
        }
        if (channel === 'whatsapp' && res.data.whatsapp_link) {
          okFlash('Code sent to WhatsApp — send the pre-filled message once.', 'হোয়াটসঅ্যাপে কোড পাঠানো হয়েছে।');
          window.open(res.data.whatsapp_link, '_blank', 'noopener');
        } else if (channel === 'email') {
          okFlash('OTP sent to your email inbox.', 'আপনার ইমেইলে OTP পাঠানো হয়েছে।');
        }
      })
      .catch(function () {
        state[channel].pending = false;
        errFlash('Network error — please try again.');
      })
      .finally(function () {
        state[channel].pending = false;
      });
  }

  function confirmCode(channel, code, csrf) {
    var fd = new FormData();
    fd.append('channel', channel);
    fd.append('code', code);
    fd.append('csrf_token', csrf);

    fetch((window.AURORA_BASE || '') + '/api/verify/confirm.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
      .then(function (res) {
        if (!res.ok || res.data.error) {
          errFlash(res.data.error || 'Code could not be verified.');
          return;
        }
        okFlash(res.data.label + ' — ' + res.data.icon, res.data.icon + ' ' + res.data.label);
        updateTick(res.data);
        // lock channel
        var pan = $('[data-otp-wrap="' + channel + '"]');
        var parent = pan ? pan.parentElement : null;
        if (parent) parent.classList.add('opacity-40', 'pointer-events-none');
        var done = channel === 'email' ? $('#email-done') : $('#wa-done');
        if (done) { done.classList.remove('hidden'); done.classList.add('text-emerald-950', 'bg-emerald-400'); }
        if (window.I18N) I18N.apply(false);
      })
      .catch(function () { errFlash('Network error — please try again.'); });
  }

  document.addEventListener('DOMContentLoaded', bind);
  if (document.readyState === 'interactive' || document.readyState === 'complete') bind();
})();