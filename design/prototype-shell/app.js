/* Prototype shell: one file, every page; links between pages work like on the real site.
   Adds what lives above the pages: the pinned header trio, the page curtain and the menu overlay. */
(function () {
  var assets = JSON.parse(document.getElementById('assets').textContent);
  var pages = JSON.parse(document.getElementById('pages').textContent);
  var urls = {};
  Object.keys(assets).forEach(function (id) {
    var a = assets[id], blob;
    if (a.s != null) blob = new Blob([a.s], { type: a.t });
    else {
      var bin = atob(a.d), u8 = new Uint8Array(bin.length);
      for (var i = 0; i < bin.length; i++) u8[i] = bin.charCodeAt(i);
      blob = new Blob([u8], { type: a.t });
    }
    urls[id] = URL.createObjectURL(blob);
  });
  var resolve = function (s) { return s.replace(/\/_blob\/([0-9a-f]{32})/g, function (m, id) { return urls[id] || m; }); };
  var FILES = {};
  Object.keys(pages).forEach(function (k) { pages[k].files.forEach(function (f) { FILES[f] = k; }); });
  var cache = {}, root = null, current = null;
  var styleEl = document.getElementById('page-css'), mount = document.getElementById('app');
  var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
  var LOGO = { amour: ['c3018ad0a4f2dc69bd0b3842bf8ebf21', '2901163e35c220801b42aced9684d164'], eudora: ['06242d39e4b8ecf2e8c54e8445ff632b', 'eb8582559f2d00dae3e431652377f8c0'] };
  var themeOf = function (key) { return pages[key] && pages[key].theme === 'eudora' ? 'eudora' : 'amour'; };

  /* Header trio: Menu, logo, EN + Book stay pinned on every page, with no bar under them.
     The logo switches between its dark and light version by what is under it. */
  var hdr = document.getElementById('hdr'), logos = hdr.querySelectorAll('.hdr__logo img'), home = hdr.querySelector('.hdr__logo');
  var curtain = document.getElementById('curtain'), curtainLogo = curtain.querySelector('img');
  function darkUnder(x, y) {
    var els = document.elementsFromPoint(x, y);
    for (var i = 0; i < els.length; i++) {
      var el = els[i];
      if (hdr.contains(el) || el === curtain || el === document.documentElement || el === document.body) continue;
      var tone = el.closest && el.closest('[data-tone]');
      if (tone) return tone.getAttribute('data-tone') === 'dark';
      if (el.tagName === 'IMG' || el.tagName === 'VIDEO') return true;
      var cs = getComputedStyle(el), m = /rgba?\(([^)]+)\)/.exec(cs.backgroundColor);
      if (m) {
        var p = m[1].split(',').map(parseFloat), a = p.length > 3 ? p[3] : 1;
        if (a > 0.5) return (0.2126 * p[0] + 0.7152 * p[1] + 0.0722 * p[2]) / 255 < 0.5;
      }
    }
    return false;
  }
  var hdrQueued = false;
  function adapt() {
    hdrQueued = false;
    /* probe where the logo normally sits: four edges and the middle */
    var cx = window.innerWidth / 2, cy = 42, pts = [[cx - 104, cy], [cx + 104, cy], [cx, cy - 32], [cx, cy + 32]];
    var mid = document.elementsFromPoint(cx, cy).filter(function (e) { return !hdr.contains(e); })[0];
    var split = !!(mid && mid.closest && mid.closest('[data-hdr="split"]'));
    var dark = pts.map(function (p) { return darkUnder(p[0], p[1]); });
    var mixed = dark.some(function (d) { return d !== dark[0]; });
    /* a block split down the middle (photo | text): the logo moves over the photo half */
    hdr.classList.toggle('hdr--shift', split && mixed);
    /* the logo sits on an edge between a light and a dark block: it steps out until the edge has passed */
    hdr.classList.toggle('hdr--hide', mixed && !split);
    hdr.classList.toggle('hdr--light', split && mixed ? darkUnder(window.innerWidth / 4, cy) : dark.filter(Boolean).length >= 3);
    /* a block that belongs to the villa shows the Eudora logo in the header (never two logos on screen) */
    var bEl = mid && mid.closest ? mid.closest('[data-brand]') : null;
    var brand = bEl ? bEl.getAttribute('data-brand') : themeOf(current);
    if (LOGO[brand] && brand !== hdr.getAttribute('data-brand')) {
      hdr.setAttribute('data-brand', brand);
      hdr.classList.add('hdr--swap');
      clearTimeout(adapt.sw);
      adapt.sw = setTimeout(function () { logos[0].src = urls[LOGO[brand][0]] || ''; logos[1].src = urls[LOGO[brand][1]] || ''; hdr.classList.remove('hdr--swap'); }, 260);
    }
  }
  function queueAdapt() { if (!hdrQueued) { hdrQueued = true; requestAnimationFrame(adapt); } }
  window.addEventListener('scroll', queueAdapt, { passive: true });
  /* While reading down the page the logo steps aside (Menu and Book stay); it comes back as soon as you scroll up */
  var lastScroll = 0;
  window.addEventListener('scroll', function () {
    var y = window.scrollY;
    /* on a phone the logo shows only at the top of a page: further down it would sit on the text */
    var phone = window.innerWidth < 760;
    if (y < 240 || (!phone && y < lastScroll - 0.5)) hdr.classList.remove('hdr--up');
    else if (phone || y > lastScroll + 0.5) hdr.classList.add('hdr--up');
    lastScroll = y;
  }, { passive: true });
  window.addEventListener('resize', queueAdapt);
  setInterval(queueAdapt, 700); /* pages animate in and videos load: re-check now and then */

  /* Page curtain: a panel in the house colour covers the screen, the page changes under it, the panel lifts away */
  var covered = true;
  function cover(theme, cb) {
    curtain.setAttribute('data-theme', theme);
    curtainLogo.src = urls[LOGO[theme][1]] || '';
    if (still) { cb(); return; }
    curtain.className = 'curtain in';
    covered = true;
    setTimeout(cb, 680);
  }
  function reveal() {
    if (still) { curtain.className = 'curtain'; covered = false; return; }
    curtain.className = 'curtain in out';
    mount.classList.add('enter');
    setTimeout(function () { curtain.className = 'curtain'; covered = false; }, 950);
    setTimeout(function () { mount.classList.remove('enter'); }, 1300);
  }

  function load(key) {
    if (!cache[key]) {
      var parts = pages[key].parts.map(function (src) { return DCLite.parsePage(resolve(src)); });
      cache[key] = { parts: parts, css: parts.map(function (p) { return p.css; }).join('\n') };
    }
    return cache[key];
  }
  function toast(text) {
    var t = document.getElementById('toast');
    t.textContent = text; t.classList.add('on');
    clearTimeout(toast.tm); toast.tm = setTimeout(function () { t.classList.remove('on'); }, 2200);
  }
  /* Smooth, slightly heavy scroll in the manner of casamomo.at (Lenis-like): the wheel moves a target, the page glides to it */
  var glide = { on: false, cur: 0, target: 0 };
  function glideStop() { glide.on = false; glide.cur = glide.target = window.scrollY; }
  function glideStep() {
    if (!glide.on) return;
    glide.cur += (glide.target - glide.cur) * 0.075;
    if (Math.abs(glide.target - glide.cur) < 0.4) { glide.cur = glide.target; glide.on = false; }
    window.scrollTo(0, glide.cur);
    if (glide.on) requestAnimationFrame(glideStep);
  }
  if (!still) {
    window.addEventListener('wheel', function (e) {
      if (e.ctrlKey || Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;
      if (document.documentElement.classList.contains('menu-open')) return;
      for (var el = e.target; el && el !== document.body && el.nodeType === 1; el = el.parentElement) {
        var oy = getComputedStyle(el).overflowY;
        if ((oy === 'auto' || oy === 'scroll') && el.scrollHeight > el.clientHeight + 1) return;
      }
      e.preventDefault();
      var d = e.deltaY * (e.deltaMode === 1 ? 32 : e.deltaMode === 2 ? window.innerHeight : 1);
      if (!glide.on) { glide.cur = glide.target = window.scrollY; }
      glide.target = Math.max(0, Math.min(document.documentElement.scrollHeight - window.innerHeight, glide.target + d));
      if (!glide.on) { glide.on = true; requestAnimationFrame(glideStep); }
    }, { passive: false });
    /* a scrollbar drag, keys or a jump take over at once */
    window.addEventListener('scroll', function () { if (glide.on && Math.abs(window.scrollY - Math.round(glide.cur)) > 3) glideStop(); }, { passive: true });
    ['keydown', 'mousedown', 'touchstart'].forEach(function (t) { window.addEventListener(t, function () { if (glide.on) glideStop(); }, { passive: true }); });
  }
  function scrollToId(id) {
    glideStop();
    if (!id || id === 'top') { window.scrollTo({ top: 0, behavior: 'smooth' }); return; }
    var el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    else if (id === 'book') go('book');
    else toast('This part comes with the booking engine');
  }
  function playVideos(scope) {
    scope.querySelectorAll('video').forEach(function (v) { v.muted = true; if (still) { v.pause(); return; } var p = v.play && v.play(); if (p && p.catch) p.catch(function () {}); });
  }
  function show(key, anchor) {
    if (!pages[key]) key = 'home';
    var pg = load(key);
    if (current !== key) {
      if (root) root.unmount();
      mount.innerHTML = '';
      styleEl.textContent = pg.css;
      document.title = pages[key].title;
      glideStop(); window.scrollTo(0, 0); glide.cur = glide.target = 0;
      root = ReactDOM.createRoot(mount);
      ReactDOM.flushSync(function () { root.render(React.createElement(DCLite.Page, { id: key, parts: pg.parts })); });
      current = key;
      playVideos(mount);
      var th = themeOf(key);
      hdr.setAttribute('data-theme', th);
      hdr.classList.toggle('hdr--off', key === 'menu');
      logos[0].src = urls[LOGO[th][0]] || ''; logos[1].src = urls[LOGO[th][1]] || ''; hdr.setAttribute('data-brand', th);
      home.setAttribute('href', th === 'eudora' ? 'Villa-v2.dc.html' : 'Main-v2.dc.html');
      window.dispatchEvent(new Event('scroll'));
      window.dispatchEvent(new Event('amour:page'));
      setTimeout(adapt, 50);
    }
    if (anchor) setTimeout(function () { scrollToId(anchor); }, 60);
  }
  function parse() { var m = /^#\/([\w-]+)(?:\/([\w-]+))?/.exec(location.hash); return { key: m ? (pages[m[1]] ? m[1] : 'not-found') : 'home', anchor: m ? m[2] : null }; }
  function route() {
    var r = parse();
    if (current && r.key !== current && !covered) { cover(themeOf(r.key), function () { closeMenu(true); show(r.key, r.anchor); reveal(); }); return; }
    show(r.key, r.anchor);
    if (covered) { if (soft) softReveal(); else reveal(); }
  }
  /* From the menu: the names lift away one after another, the menu dissolves and the new page rises into place under it */
  var soft = false;
  function softGo(target) {
    menuOv.classList.add('leaving');
    covered = true; soft = true;
    setTimeout(function () { location.hash = target; }, 460);
  }
  function softReveal() {
    soft = false;
    mount.classList.add('enter');
    var an = menuOv.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 650, easing: 'cubic-bezier(.4,0,.2,1)', fill: 'forwards' });
    an.onfinish = function () {
      closeMenu(true); menuOv.classList.remove('leaving'); an.cancel(); covered = false;
    };
    setTimeout(function () { mount.classList.remove('enter'); }, 1300);
  }
  function go(key, anchor, from) {
    var target = '#/' + key + (anchor ? '/' + anchor : '');
    if (from && key !== current && !still && Element.prototype.animate) { softGo(target); return; }
    if (key === current) {
      closeMenu();
      if (anchor) scrollToId(anchor); else window.scrollTo({ top: 0, behavior: 'smooth' });
      if (location.hash !== target) history.replaceState(null, '', target);
      return;
    }
    cover(themeOf(key), function () { closeMenu(true); location.hash = target; });
  }

  /* Menu: opens over the page from the top, the links rise one after another */
  var menuOv = document.getElementById('menu-ov'), menuCss = document.getElementById('menu-css'), menuRoot = null;
  function openMenu() {
    if (!menuRoot) {
      var pg = load('menu');
      menuCss.textContent = pg.css;
      menuRoot = ReactDOM.createRoot(menuOv);
      ReactDOM.flushSync(function () { menuRoot.render(React.createElement(DCLite.Page, { id: 'menu-ov', parts: pg.parts })); });
    }
    menuOv.hidden = false;
    void menuOv.offsetWidth;
    menuOv.classList.add('open');
    document.documentElement.classList.add('menu-open');
    setTimeout(function () { var a = menuOv.querySelector('nav a'); if (a) a.focus({ preventScroll: true }); }, 500);
  }
  function closeMenu(instant) {
    if (!menuOv.classList.contains('open')) return;
    menuOv.classList.remove('open');
    document.documentElement.classList.remove('menu-open');
    if (instant) { menuOv.hidden = true; return; }
    setTimeout(function () { if (!menuOv.classList.contains('open')) menuOv.hidden = true; }, 760);
  }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });

  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href');
    var inMenu = menuOv.contains(a);
    if (inMenu && /^\s*close\s*$/i.test(a.textContent || '')) { e.preventDefault(); closeMenu(); return; }
    if (href === 'Menu-list.dc.html') { e.preventDefault(); openMenu(); return; }
    if (a.classList.contains('hdr__book')) {
      e.preventDefault();
      // Book always opens the booking page; from the villa it opens on the villa
      if (current === 'book') scrollToId('top');
      else go('book');
      return;
    }
    if (href === '#lang') { e.preventDefault(); toast('The German version comes with the live site'); return; }
    var m = /^([\w-]+\.dc\.html)(?:#([\w-]+))?$/.exec(href);
    if (m) {
      e.preventDefault();
      var key = FILES[m[1]];
      if (!key) { toast('This page is not in the prototype yet'); return; }
      go(key, m[2], inMenu ? a : null);
      return;
    }
    if (href.charAt(0) === '#' && href.charAt(1) !== '/') { e.preventDefault(); closeMenu(); scrollToId(href.slice(1)); return; }
    if (/^https?:/.test(href)) { a.target = '_blank'; a.rel = 'noopener'; return; }
  }, true);
  document.addEventListener('submit', function (e) { e.preventDefault(); if (current === 'book') { toast('Here the engine shows the prices for your dates'); return; } go('book'); }, true);
  window.addEventListener('hashchange', route);
  route();
})();
