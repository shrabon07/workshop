/* ==================================================================
   AURORA CYBER — main app runtime
   Staggered reveals (IntersectionObserver), aurora particle field,
   category filter, glass navbar, and featherweight page transitions.
   ================================================================== */
(function () {
  'use strict';

  var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------------- navbar: elevate once scrolled ---------------- */
  function navbarScroll() {
    var nav = document.getElementById('site-nav');
    if (!nav) return;
    var onScroll = function () {
      nav.classList.toggle('nav-scrolled', window.scrollY > 24);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------------- staggered reveal observer ---------------- */
  function revealInit() {
    var items = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));
    if (!items.length || !('IntersectionObserver' in window)) {
      items.forEach(function (el) { el.classList.add('is-in'); });
      return;
    }
    var groups = {};
    var ready = 0;
    items.forEach(function (el) {
      var g = el.getAttribute('data-reveal') || 'd';
      (groups[g] = groups[g] || []).push(el);
      ready++;
    });
    var gi = 0;
    for (var g in groups) {
      groups[g].forEach(function (el, i) {
        el.style.setProperty('--rd', (i * 80) + 'ms');
      });
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          io.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    items.forEach(function (el) { io.observe(el); });
  }

  /* ---------------- services category filter ---------------- */
  function serviceFilterInit() {
    var grid = document.getElementById('services-grid');
    if (!grid) return;
    var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-filter]'));
    var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-category]'));

    function show(cat) {
      cards.forEach(function (card) {
        var match = cat === 'all' || card.getAttribute('data-category') === cat;
        card.classList.toggle('is-hidden', !match);
        if (match) {
          card.classList.add('is-in'); // force-reveal so it never looks blank
          setTimeout(function () { card.classList.add('fade-swap'); }, 30);
        } else {
          card.classList.remove('fade-swap');
        }
      });
      var empty = document.getElementById('services-empty');
      if (empty) empty.classList.toggle('hidden', cards.some(function (c) { return !c.classList.contains('is-hidden'); }));
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.classList.remove('is-active'); });
        tab.classList.add('is-active');
        show(tab.getAttribute('data-filter'));
      });
    });
  }

  /* ---------------- featherweight page transitions ---------------- */
  function pageTransitions() {
    document.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('a[data-js-nav]') : null;
      if (!a) return;
      var href = a.getAttribute('href');
      if (!href || href.charAt(0) === '#' || href.indexOf('logout') !== -1 || href.indexOf('javascript:') === 0) return;
      if (e.metaKey || e.ctrlKey || e.shiftKey) return;
      e.preventDefault();
      document.body.classList.add('page-leave');
      setTimeout(function () { window.location.href = href; }, 250);
    });
  }

  /* ---------------- aurora particle field (lightweight canvas) ---------------- */
  function heroCanvas() {
    var canvas = document.getElementById('aurora-canvas');
    if (!canvas || reduced) return;
    var ctx = canvas.getContext('2d');
    var W, H, dots = [], raf = null, running = false;
    var DPR = Math.min(window.devicePixelRatio || 1, 2);

    function resize() {
      var rect = canvas.parentElement.getBoundingClientRect();
      W = rect.width; H = rect.height;
      canvas.width = W * DPR; canvas.height = H * DPR;
      ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
      build();
    }

    function build() {
      var n = Math.max(24, Math.min(70, Math.floor((W * H) / 26000)));
      dots = [];
      for (var i = 0; i < n; i++) {
        dots.push({
          x: Math.random() * W, y: Math.random() * H,
          vx: (Math.random() - .5) * .45, vy: (Math.random() - .5) * .45,
          r: Math.random() * 1.6 + .6,
          c: Math.random() > .5 ? '6,182,212' : '168,85,247'
        });
      }
    }

    function frame() {
      ctx.clearRect(0, 0, W, H);
      var i, j;
      for (i = 0; i < dots.length; i++) {
        var d = dots[i];
        d.x += d.vx; d.y += d.vy;
        if (d.x < 0 || d.x > W) d.vx *= -1;
        if (d.y < 0 || d.y > H) d.vy *= -1;
        for (j = i + 1; j < dots.length; j++) {
          var o = dots[j];
          var dx = d.x - o.x, dy = d.y - o.y;
          var dist = dx * dx + dy * dy;
          if (dist < 14500) {
            var a = (1 - dist / 14500) * .35;
            ctx.strokeStyle = 'rgba(' + d.c + ',' + a.toFixed(3) + ')';
            ctx.lineWidth = .7;
            ctx.beginPath(); ctx.moveTo(d.x, d.y); ctx.lineTo(o.x, o.y); ctx.stroke();
          }
        }
        ctx.fillStyle = 'rgba(' + d.c + ',.9)';
        ctx.beginPath(); ctx.arc(d.x, d.y, d.r, 0, 6.2832); ctx.fill();
      }
      raf = requestAnimationFrame(frame);
    }

    function start() { if (!running) { running = true; frame(); } }
    function stop() { running = false; if (raf) cancelAnimationFrame(raf); }

    var vis = 'visible';
    function visTo(x) { vis = x; vis === 'visible' ? start() : stop(); }
    document.addEventListener('visibilitychange', function () {
      visTo(document.visibilityState);
    });

    var hero = canvas.parentElement;
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (en) { visTo(en[0].isIntersecting ? 'visible' : 'hidden'); });
      io.observe(hero);
    }
    window.addEventListener('resize', resize, { passive: true });
    resize();
    start();
  }

  /* ---------------- mobile nav ---------------- */
  function mobileNav() {
    var burger = document.getElementById('nav-burger');
    var panel = document.getElementById('nav-mobile');
    var backdrop = document.getElementById('nav-mobile-backdrop');
    if (!burger || !panel) return;
    function show(open) {
      panel.classList.toggle('hidden', !open);
      panel.classList.toggle('fade-swap', open);
      backdrop.classList.toggle('hidden', !open);
      document.body.style.overflow = open ? 'hidden' : '';
    }
    burger.addEventListener('click', function () { show(panel.classList.contains('hidden')); });
    backdrop.addEventListener('click', function () { show(false); });
    panel.querySelectorAll('.mobile-link').forEach(function (a) {
      a.addEventListener('click', function () { show(false); });
    });
  }

  /* ---------------- init ---------------- */
  function init() {
    navbarScroll();
    mobileNav();
    revealInit();
    serviceFilterInit();
    pageTransitions();
    heroCanvas();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();