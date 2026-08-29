/* Aurora Cyber — floating scroll up / down dock (whole site). */
(function () {
  'use strict';

  function isReduced() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function build() {
    if (document.getElementById('scroll-nav')) return null;

    var dock = document.createElement('div');
    dock.id = 'scroll-nav';
    dock.className = 'scroll-nav';

    var up = document.createElement('button');
    up.type = 'button';
    up.className = 'scroll-nav-btn scroll-up';
    up.setAttribute('aria-label', 'Scroll to top');
    up.title = 'Scroll to top / উপরে যান';
    up.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>';

    var down = document.createElement('button');
    down.type = 'button';
    down.className = 'scroll-nav-btn scroll-down';
    down.setAttribute('aria-label', 'Scroll to bottom');
    down.title = 'Scroll to bottom / নিচে যান';
    down.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>';

    dock.appendChild(up);
    dock.appendChild(down);
    document.body.appendChild(dock);

    up.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: isReduced() ? 'auto' : 'smooth' });
    });
    down.addEventListener('click', function () {
      window.scrollTo({ top: document.documentElement.scrollHeight, behavior: isReduced() ? 'auto' : 'smooth' });
    });

    return dock;
  }

  function boot() {
    var dock = build();
    if (!dock) return;

    var up = dock.querySelector('.scroll-up');
    var down = dock.querySelector('.scroll-down');
    if (!up || !down) return;

    function update() {
      var max = document.documentElement.scrollHeight - window.innerHeight;
      var y = window.pageYOffset || document.documentElement.scrollTop || 0;
      var scrollable = max > 16;

      dock.classList.toggle('scroll-nav-visible', scrollable);

      up.disabled = !scrollable || y < 120;
      down.disabled = !scrollable || y > max - 120;
    }

    var ticking = false;
    function onScroll() {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(function () { update(); ticking = false; });
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    update();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();