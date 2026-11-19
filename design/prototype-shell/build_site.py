"""Builds one self-contained HTML file with every v2 page of the Amour site."""
import base64, json, os, re, sys
S = '/tmp/claude-0/-home-claude/43f3c1c4-21ec-59cb-94ba-ae08e0d42e56/scratchpad/'
P = S + 'canvas/project/'
A = S + 'site/assets/'
OUT = sys.argv[1] if len(sys.argv) > 1 else S + 'site/Amour-site-prototype.html'
PAGES = {
  'home': ('Amour Relax Hotel · Almyrida, Crete', ['Main-v2.dc.html', 'Main-v2-b.dc.html'], ['Main.dc.html', 'Home-mobile.dc.html']),
  'rooms': ('Rooms · Amour Relax Hotel', ['Rooms.dc.html'], []),
  'standard': ('Standard Queen Room · Amour Relax Hotel', ['Room-standard.dc.html'], []),
  'deluxe': ('Deluxe Queen Room · Amour Relax Hotel', ['Room-deluxe.dc.html'], []),
  'superior': ('Superior Queen Room · Amour Relax Hotel', ['Room-superior.dc.html'], []),
  'villa': ('Villa Eudora · Almyrida, Crete', ['Villa-v2.dc.html', 'Villa-v2-b.dc.html'], ['Villa.dc.html']),
  'court-and-pool': ('Court and pool · Amour Relax Hotel', ['Estate.dc.html'], ['Map-3.dc.html']),
  'guide': ('Almyrida and Crete guide · Amour Relax Hotel', ['Guide.dc.html'], ['Map-1.dc.html', 'Map-2.dc.html']),
  'faq': ('Questions and house rules · Amour Relax Hotel', ['FAQ.dc.html'], []),
  'contact': ('Contact · Amour Relax Hotel', ['Contact.dc.html'], []),
  'book': ('Book your stay · Amour Relax Hotel', ['Book.dc.html'], []),
  'about': ('About us · Amour Relax Hotel', ['About.dc.html'], []),
  'legal': ('Privacy, cookies and booking terms · Amour Relax Hotel', ['Legal.dc.html'], []),
  'thanks': ('Thank you · Amour Relax Hotel', ['Thanks.dc.html'], []),
  'not-found': ('Page not found · Amour Relax Hotel', ['NotFound.dc.html'], []),
  'menu': ('Menu · Amour Relax Hotel', ['Menu-list.dc.html'], ['Menu-desktop.dc.html'])}
pages = {}
used = set()
for key, (title, parts, aliases) in PAGES.items():
    srcs = [open(P + f).read() for f in parts]
    for s in srcs: used.update(re.findall(r'/_blob/([0-9a-f]{32})', s))
    pages[key] = {'title': title, 'parts': srcs, 'files': parts + aliases, 'theme': 'eudora' if key == 'villa' else 'amour'}
css = ''
for f in ['ds/amour/tokens.css', 'ds/amour/components/bundle.css', 'amour-soft.css', 'amour-motion.css']:
    src = P + f
    css += '\n/* ' + f + ' */\n' + open(src).read()
for font, mime in [('TenorSans-Regular.woff2', 'font/woff2'), ('InstrumentSans-latin.woff2', 'font/woff2'), ('InstrumentSans-latin-ext.woff2', 'font/woff2')]:
    fp = P + 'ds/amour/fonts/' + font
    b = base64.b64encode(open(fp if os.path.exists(fp) else S + 'ds/project/fonts/' + font, 'rb').read()).decode()
    css = css.replace("url('fonts/" + font + "')", "url(data:" + mime + ";base64," + b + ")")
used.update(re.findall(r'/_blob/([0-9a-f]{32})', css))
used.update(['c3018ad0a4f2dc69bd0b3842bf8ebf21', '2901163e35c220801b42aced9684d164', '06242d39e4b8ecf2e8c54e8445ff632b', 'eb8582559f2d00dae3e431652377f8c0'])
files = {f.split('.')[0]: f for f in os.listdir(A)}
assets, missing = {}, []
MIME = {'webp': 'image/webp', 'jpg': 'image/jpeg', 'png': 'image/png', 'svg': 'image/svg+xml', 'mp4': 'video/mp4'}
for i in sorted(used):
    if i not in files: missing.append(i); continue
    ext = files[i].rsplit('.', 1)[1]
    raw = open(A + files[i], 'rb').read()
    assets[i] = {'t': MIME[ext], 's': raw.decode('utf-8')} if ext == 'svg' else {'t': MIME[ext], 'd': base64.b64encode(raw).decode()}
def js(o): return json.dumps(o, ensure_ascii=False).replace('</', '<\\/')
read = lambda p: open(S + 'site/' + p).read()
html = '''<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#f2eadb">
<title>Amour Relax Hotel · site prototype</title>
<style>''' + css + '''
#toast{position:fixed;left:50%;top:24px;z-index:1000;transform:translate(-50%,-20px);opacity:0;pointer-events:none;padding:12px 20px;border-radius:999px;background:#2a2420;color:#f2eadb;font:500 14px/1.4 var(--font-body);transition:opacity .3s,transform .3s}
#toast.on{opacity:1;transform:translate(-50%,0)}
.skip{position:fixed;left:16px;top:-60px;z-index:1001;padding:10px 16px;border-radius:999px;background:#2a2420;color:#f2eadb;font:500 14px var(--font-body);text-decoration:none;transition:top .2s}
.skip:focus{top:16px}
/* Header trio: pinned on every page, no bar under it */
#app header:has(a[href="Menu-list.dc.html"]){visibility:hidden}
#app header[data-overlay]{position:absolute!important;left:0;right:0;top:0}
.hdr{position:fixed;left:0;right:0;top:0;z-index:900;display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);align-items:center;padding:20px clamp(16px,4vw,56px);pointer-events:none;transition:opacity .35s}
.hdr>*{pointer-events:auto}
.hdr--off,.menu-open .hdr{opacity:0;pointer-events:none}
.hdr--off>*,.menu-open .hdr>*{pointer-events:none}
.hdr__pill{display:inline-flex;align-items:center;gap:12px;min-height:44px;padding:0 18px 0 14px;border-radius:999px;background:rgba(242,234,219,.84);-webkit-backdrop-filter:blur(14px) saturate(1.15);backdrop-filter:blur(14px) saturate(1.15);box-shadow:inset 0 0 0 1px rgba(42,36,32,.1);color:#2a2420;text-decoration:none;font:500 15px/1 var(--font-body);transition:background-color .3s}
.hdr__pill:hover{background:rgba(251,247,239,.96);color:#2a2420}
.hdr__pill svg{display:block;flex:none}
.hdr__lang{padding:0 16px}
.hdr__logo{position:relative;display:block;width:248px;height:44px}
.hdr__logo img{position:absolute;left:50%;top:-16px;height:76px;transform:translateX(-50%);transition:opacity .45s}
.hdr__logo{transition:transform .8s cubic-bezier(.65,0,.35,1),opacity .35s ease}
.hdr--shift .hdr__logo{transform:translateX(max(-25vw, calc(304px - 50vw)))}
.hdr--hide .hdr__logo{opacity:0;transform:translateY(-10px);pointer-events:none}
.hdr--up .hdr__logo{opacity:0!important;pointer-events:none}
.hdr--swap .hdr__logo{opacity:0!important}
.hdr__logo img+img{opacity:0;filter:drop-shadow(0 1px 8px rgba(28,22,18,.16))}
.hdr--light .hdr__logo img{opacity:0}
.hdr--light .hdr__logo img+img{opacity:1}
.hdr__right{justify-self:end;display:flex;align-items:center;gap:16px}
.hdr__menu{justify-self:start}
.hdr__book{min-height:44px!important}
/* Page curtain */
.curtain{position:fixed;inset:0;z-index:960;display:grid;place-items:center;background:#5c1e1c;clip-path:inset(100% 0 0 0);pointer-events:none}
.curtain[data-theme="eudora"]{background:#1f3a47}
.curtain.in{clip-path:inset(0 0 0 0);transition:clip-path .68s cubic-bezier(.76,0,.24,1);pointer-events:auto}
.curtain.in.out{clip-path:inset(0 0 100% 0);transition:clip-path .85s cubic-bezier(.76,0,.24,1) .08s}
.curtain img{height:76px;opacity:0;transform:translateY(14px);transition:opacity .4s .3s,transform .7s cubic-bezier(.2,.7,.2,1) .3s}
.curtain.in img{opacity:1;transform:none}
.curtain.in.out img{opacity:0;transform:translateY(-14px);transition:opacity .3s,transform .5s}
#app.enter{animation:page-in 1.1s cubic-bezier(.2,.7,.2,1) both}
@keyframes page-in{from{transform:translateY(72px)}to{transform:none}}
/* Menu overlay */
#menu-ov{position:fixed;inset:0;z-index:940;overflow:auto;background:#5c1e1c;clip-path:inset(0 0 100% 0);transition:clip-path .72s cubic-bezier(.76,0,.24,1)}
#menu-ov.open{clip-path:inset(0 0 0 0);transition:clip-path .85s cubic-bezier(.76,0,.24,1)}
#menu-ov.open nav li{animation:menu-in .9s cubic-bezier(.2,.7,.2,1) both}
#menu-ov.open nav li:nth-child(1){animation-delay:0.30s}
#menu-ov.open nav li:nth-child(2){animation-delay:0.36s}
#menu-ov.open nav li:nth-child(3){animation-delay:0.42s}
#menu-ov.open nav li:nth-child(4){animation-delay:0.48s}
#menu-ov.open nav li:nth-child(5){animation-delay:0.54s}
#menu-ov.open nav li:nth-child(6){animation-delay:0.60s}
#menu-ov.open nav li:nth-child(7){animation-delay:0.66s}
#menu-ov.open nav li:nth-child(8){animation-delay:0.72s}
#menu-ov.open nav li:nth-child(9){animation-delay:0.78s}
#menu-ov.open nav li:nth-child(10){animation-delay:0.84s}
#menu-ov.open nav li:nth-child(11){animation-delay:0.90s}
#menu-ov.open nav li:nth-child(12){animation-delay:0.96s}
#menu-ov.open header{animation:menu-fade .6s ease .45s both}
#menu-ov.open img{animation:menu-pic 1.6s cubic-bezier(.2,.7,.2,1) .15s both}
@keyframes menu-out{to{opacity:0;transform:translateY(-18px)}}
#menu-ov.leaving nav a{animation:menu-out .42s cubic-bezier(.4,0,.2,1) both}
#menu-ov.leaving nav li:nth-child(2) a{animation-delay:0.03s}
#menu-ov.leaving nav li:nth-child(3) a{animation-delay:0.06s}
#menu-ov.leaving nav li:nth-child(4) a{animation-delay:0.09s}
#menu-ov.leaving nav li:nth-child(5) a{animation-delay:0.12s}
#menu-ov.leaving nav li:nth-child(6) a{animation-delay:0.15s}
#menu-ov.leaving nav li:nth-child(7) a{animation-delay:0.18s}
#menu-ov.leaving nav li:nth-child(8) a{animation-delay:0.21s}
#menu-ov.leaving nav li:nth-child(9) a{animation-delay:0.24s}
#menu-ov.leaving nav li:nth-child(10) a{animation-delay:0.27s}
#menu-ov.leaving nav li:nth-child(11) a{animation-delay:0.30s}
#menu-ov.leaving nav li:nth-child(12) a{animation-delay:0.33s}
#menu-ov.leaving footer,#menu-ov.leaving header,#menu-ov.leaving .menu-arch,#menu-ov.leaving .menu-arch+p{opacity:0;transition:opacity .4s ease}
@keyframes menu-in{from{opacity:0;transform:translateY(56px)}to{opacity:1;transform:none}}
@keyframes menu-fade{from{opacity:0}to{opacity:1}}
@keyframes menu-pic{from{transform:scale(1.18)}to{transform:none}}
.menu-open{overflow:hidden}
@media (prefers-reduced-motion: reduce){#menu-ov,#menu-ov.open{transition:none}#menu-ov.open nav li,#menu-ov.open img,#menu-ov.open header,#app.enter{animation:none}.curtain{display:none}}
/* phone: compact header, the menu keeps only its icon, language moves into the menu */
@media (max-width:760px){.hdr{padding:12px 16px}.hdr__logo{zoom:.5;translate:none}.hdr--shift .hdr__logo,.hdr__logo{transform:none!important}.hdr__menu{font-size:0;letter-spacing:0;gap:0;padding:0 13px}.hdr__lang{display:none}.hdr__right{gap:8px}}
#app:focus{outline:none}
</style>
<style id="page-css"></style>
<style id="menu-css"></style>
</head>
<body>
<a class="skip" href="#app">Skip to content</a>
<header id="hdr" class="hdr" data-theme="amour">
  <a href="Menu-list.dc.html" class="hdr__pill hdr__menu"><svg width="18" height="12" viewBox="0 0 18 12" aria-hidden="true"><path d="M0 1h18M0 6h18M0 11h18" fill="none" stroke="currentColor" stroke-width="2"/></svg>Menu</a>
  <a href="Main-v2.dc.html" class="hdr__logo" aria-label="Amour Relax Hotel, home"><img alt="" src=""><img alt="" src=""></a>
  <div class="hdr__right"><a href="#lang" class="hdr__pill hdr__lang" aria-label="Language: English">EN</a><a href="#book" class="am-btn hdr__book">Book</a></div>
</header>
<main id="app" tabindex="-1"></main>
<div id="menu-ov" role="dialog" aria-modal="true" aria-label="Menu" hidden></div>
<div id="curtain" class="curtain in" aria-hidden="true"><img alt="" src=""></div>
<div id="toast" role="status" aria-live="polite"></div>
<script type="application/json" id="assets">''' + js(assets) + '''</script>
<script type="application/json" id="pages">''' + js(pages) + '''</script>
<script>''' + read('vendor/react.production.min.js') + '''</script>
<script>''' + read('vendor/react-dom.production.min.js') + '''</script>
<script>''' + open(S + 'artifact-files/773b1e4a-bf20-420c-b141-862029ac0032/project/ds/amour/components/bundle.js').read() + '''</script>
<script>''' + open(P + 'amour-live.js').read() + '''</script>
<script>''' + read('dc-lite.js') + '''</script>
<script>''' + read('app.js') + '''</script>
</body>
</html>
'''
open(OUT, 'w').write(html)
print('written', OUT, round(len(html.encode()) / 1e6, 1), 'MB', 'assets', len(assets), 'missing', missing)
