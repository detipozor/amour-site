/* amour-live: page-wide motion that every page shares.
   1. Footer: the olive (or jug) pattern lights up under the cursor; the big wordmark rises as the footer comes in.
   2. Photo frames: the main picture drifts inside its frame while the page scrolls (parallax).
   Nothing runs on the static design boards (very tall viewport) or when the visitor asks for reduced motion. */
(function () {
  if (window.__amourLive) return;
  window.__amourLive = true;
  var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var isBoard = function () { return window.innerHeight > 1600; };
  var clamp = function (v, a, b) { return Math.max(a, Math.min(b, v)); };

  /* 1. Footer lens: CSS reads --mx/--my (see .olive-lens in amour-motion.css) */
  var lensOn = null;
  document.addEventListener('mousemove', function (e) {
    var f = e.target && e.target.closest ? e.target.closest('.footer-live') : null;
    if (lensOn && lensOn !== f) { lensOn.style.setProperty('--mx', '-999px'); lensOn.style.setProperty('--my', '-999px'); }
    lensOn = f;
    if (!f) return;
    var r = f.getBoundingClientRect();
    f.style.setProperty('--mx', Math.round(e.clientX - r.left) + 'px');
    f.style.setProperty('--my', Math.round(e.clientY - r.top) + 'px');
  }, { passive: true });

  /* 2. Parallax: a picture that fills a clipped frame is scaled up a little and shifted against the scroll */
  var frames = [], scanT = 0;
  function eligible(m) {
    // on a phone the room cards on the home page get it too, as at Casa Momo
    var unitPhone = window.innerWidth < 760 && m.closest('.unit-pic') && !m.classList.contains('unit-alt');
    if (m.closest('.footer-live, .menu-ov, #menu-ov')) return false;
    if (m.closest('[data-nopar]') && !unitPhone) return false;
    var cs = getComputedStyle(m);
    if (cs.objectFit !== 'cover' || cs.position === 'fixed') return false;
    var p = m.parentElement; if (!p) return false;
    var ps = getComputedStyle(p);
    if (ps.overflow.indexOf('hidden') < 0 && ps.overflow.indexOf('clip') < 0) return false;
    var r = m.getBoundingClientRect(), pr = p.getBoundingClientRect();
    if (pr.height < 220 || pr.width < 220) return false;
    return r.width >= pr.width - 2 && r.height >= pr.height - 2 && pr.height < window.innerHeight * 1.4;
  }
  function scan() {
    frames.forEach(function (m) { if (!m.isConnected) return; m.style.removeProperty('translate'); m.style.removeProperty('scale'); });
    frames = [];
    if (still || isBoard()) return;
    document.querySelectorAll('img, video').forEach(function (m) { if (eligible(m)) frames.push(m); });
    tick();
  }
  function tick() {
    var vh = window.innerHeight;
    if (!isBoard()) document.querySelectorAll('.footer-live').forEach(function (f) {
      var r = f.getBoundingClientRect();
      f.style.setProperty('--fw', clamp((vh - r.top) / (vh * 0.9), 0, 1).toFixed(3));
    });
    /* 3. The sticky booking capsule steps aside when another button, a heading or the footer would sit under it */
    document.querySelectorAll('.capsule-dock').forEach(function (dock) {
      var pill = dock.firstElementChild; if (!pill) return;
      var r = pill.getBoundingClientRect(), hide = r.top < 110, els = document.querySelectorAll('.am-btn, .footer-live, [data-cta], .other, .feat, h2, button');
      for (var j = 0; j < els.length && !hide; j++) {
        var e = els[j]; if (dock.contains(e) || e.closest('.hdr')) continue;
        var q = e.getBoundingClientRect();
        hide = q.bottom > r.top - 16 && q.top < r.bottom + 16 && q.right > r.left - 16 && q.left < r.right + 16;
      }
      if (dock.classList.contains('dock--away') !== hide) dock.classList.toggle('dock--away', hide);
    });
    for (var i = 0; i < frames.length; i++) {
      var m = frames[i], p = m.parentElement;
      if (!m.isConnected || !p) continue;
      var r = p.getBoundingClientRect();
      if (r.bottom < -40 || r.top > vh + 40) continue;
      var k = clamp((r.top + r.height / 2 - vh / 2) / (vh / 2 + r.height / 2), -1, 1);
      var strong = window.innerWidth < 760 && m.closest('.unit-pic');
      m.style.scale = strong ? '1.2' : '1.14';
      m.style.translate = '0 ' + (-k * (strong ? 8 : 6)).toFixed(2) + '%';
    }
  }
  var queued = false;
  function frame() { if (queued) return; queued = true; requestAnimationFrame(function () { queued = false; tick(); }); }
  window.addEventListener('scroll', frame, { passive: true });
  window.addEventListener('resize', function () { clearTimeout(scanT); scanT = setTimeout(scan, 200); });
  /* Pages render after this script: rescan when the page changes (the prototype fires amour:page) and once things settle */
  window.addEventListener('amour:page', function () { clearTimeout(scanT); scanT = setTimeout(scan, 450); });
  window.addEventListener('load', function () { setTimeout(scan, 600); });
  /* 4. Reveal on entry: [data-reveal] gets .is-in once it is a quarter into view (lines rise, arches open, cards flip) */
  var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
  }, { threshold: 0.25 }) : null;
  function reveals() {
    document.querySelectorAll('[data-reveal]:not([data-rv])').forEach(function (el) {
      el.setAttribute('data-rv', '1');
      if (still || isBoard() || !io) el.classList.add('is-in'); else io.observe(el);
    });
  }
  setInterval(reveals, 400);
  window.addEventListener('amour:page', function () { setTimeout(reveals, 60); });
  window.AmourLive = { scan: scan, tick: tick, reveals: reveals };
})();
