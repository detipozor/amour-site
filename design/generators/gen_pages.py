"""Service pages of the Amour site: Book, About us, Legal (privacy, cookies, booking terms), Thank you, 404.
Writes .dc.html files next to the other canvas pages. Square brackets mark facts still to come from the owners."""
import json

P = '/tmp/claude-0/-home-claude/43f3c1c4-21ec-59cb-94ba-ae08e0d42e56/scratchpad/canvas/project/'

LOGO = '/_blob/c3018ad0a4f2dc69bd0b3842bf8ebf21'
OLIVE = '/_blob/136a761c27b957778f5fa1fdbbe7982f'

LBL = 'margin: 0; font-size: 13px; font-weight: 400; letter-spacing: 0.2em; text-transform: uppercase; color: var(--accent); font-family: var(--font-label);'
H1 = 'margin: 0; font-family: var(--font-head); font-weight: 400; font-size: 72px; line-height: 1.05; letter-spacing: -0.03em;'
H2 = 'margin: 0; font-family: var(--font-head); font-weight: 400; font-size: 44px; line-height: 1.1; letter-spacing: -0.025em;'
H3 = 'margin: 0; font-family: var(--font-head); font-weight: 500; font-size: 24px; line-height: 1.2; letter-spacing: -0.01em;'


def head(title):
    return f'''<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{title}</title>
<script src="./support.js"></script>
<link rel="stylesheet" href="ds/amour/tokens.css">
<link rel="stylesheet" href="ds/amour/components/bundle.css">
<link rel="stylesheet" href="amour-soft.css">
<link rel="stylesheet" href="amour-motion.css">
<script src="ds/amour/components/bundle.js"></script>
<script src="amour-live.js"></script>
</head>
<body>
<x-dc>
<helmet>
<style>
body{{margin:0;background:#f2eadb}}
a{{color:#a8482a}}a:hover{{color:#9b4227}}
</style>
</helmet>
<div class="v2" data-theme="amour" style="background: var(--bg); color: var(--text); font-family: var(--font-body); font-size: 18px; line-height: 1.6;">

<header style="position: sticky; top: 0; z-index: 10; display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); align-items: center; padding: 20px var(--gutter); background: var(--bg);">
  <div><a href="Menu-list.dc.html" style="display: inline-flex; align-items: center; gap: 12px; min-height: 44px; color: var(--text); text-decoration: none; font-size: 15px; font-weight: 500;"><x-import component-from-global-scope="Amour.Icon" name="menu" size="{{{{icon}}}}"></x-import>Menu</a></div>
  <a href="Main-v2.dc.html" aria-label="Amour Relax Hotel, home" style="display: block; height: 44px;"><img src="{LOGO}" alt="Amour Relax Hotel" style="display: block; height: 76px; margin: -16px 0;"></a>
  <div style="display: flex; justify-content: flex-end; align-items: center; gap: 16px;">
    <a href="#top" style="display: inline-flex; align-items: center; min-height: 44px; padding: 0 12px; font-size: 14px; font-weight: 500; letter-spacing: 0.2em; color: var(--text); text-decoration: none;">EN</a>
    <x-import component-from-global-scope="Amour.Button" variant="primary" href="Book.dc.html">Book</x-import>
  </div>
</header>
'''


FOOT_HTML = f'''
<div class="footer-live">
  <div class="olive-field" aria-hidden="true">
    <sc-for list="{{{{olives}}}}" as="o" hint-placeholder-count="96"><img src="{OLIVE}" alt="" style="animation-delay: {{{{o.delay}}}};"></sc-for>
  </div>
  <div class="olive-field olive-lens" aria-hidden="true">
    <sc-for list="{{{{olives}}}}" as="o" hint-placeholder-count="96"><img src="{OLIVE}" alt=""></sc-for>
  </div>
  <x-import component-from-global-scope="Amour.Footer" logo="/_blob/d363bf89f0c7b41d1d2e94590a1f7092" columns="{{{{footerCols}}}}" social="{{{{social}}}}" legal="{{{{legal}}}}" legal-links="{{{{legalLinks}}}}"></x-import>
  <img class="footer-word" src="/_blob/2ff4e86eb203fa11650a8bf02bd78349" alt="" aria-hidden="true">
</div>

</div>
</x-dc>
'''

FOOT_VALS = '''      icon: 22,
      olives: Array.from({ length: 96 }, (_, i) => ({ delay: (((i % 16) + Math.floor(i / 16) * 1.4) * 0.12).toFixed(2) + 's' })),
      footerCols: [
        { title: 'Stay', links: [{ label: 'Rooms', href: 'Rooms.dc.html' }, { label: 'Villa Eudora', href: 'Villa-v2.dc.html' }, { label: 'Court and pool', href: 'Estate.dc.html' }] },
        { title: 'Discover', links: [{ label: 'Crete guide', href: 'Guide.dc.html' }, { label: 'About us', href: 'About.dc.html' }, { label: 'FAQ', href: 'FAQ.dc.html' }] },
        { title: 'Contact', links: [{ label: '[Phone]' }, { label: '[Email]' }, { label: 'Almyrida, Crete', href: 'Contact.dc.html' }] }
      ],
      social: [{ label: 'Instagram', href: '#top' }, { label: 'Facebook', href: '#top' }, { label: 'TikTok', href: '#top' }],
      legal: ['[Company name] · [Address]', 'MHTE [MHTE number]'],
      legalLinks: [{ label: 'Privacy', href: 'Legal.dc.html#privacy' }, { label: 'Cookies', href: 'Legal.dc.html#cookies' }, { label: 'Terms', href: 'Legal.dc.html#terms' }]'''


def script(height, body):
    return f'''<script type="text/x-dc" data-dc-script data-props='{{"$preview":{{"width":1440,"height":{height}}}}}'>
{body}
</script>
</body>
</html>
'''


def crumb(name):
    return f'<nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--text-2);"><a href="Main-v2.dc.html" style="color: var(--text-2);">Home</a> · {name}</nav>'


# ---------------------------------------------------------------- Book
# As at Casa Momo: dates and guests first, then only what is free with the price for the whole stay,
# then the guest's details and payment. All four units, the villa included, in one list.
UNITS = [
    {'name': 'Standard Queen Room', 'pills': ['20 m²', '2 guests'], 'desc': 'A queen bed, a kitchenette and a quiet start to the day.', 'src': '/_blob/2b59c90b2750bdb3ac2476355da8d974', 'alt': 'The queen bed with fresh linen', 'cap': 2, 'add': 0, 'villa': False},
    {'name': 'Deluxe Queen Room', 'pills': ['32 m²', '2 guests'], 'desc': 'More room, a full kitchen and a view over the pool.', 'src': '/_blob/1c8b6c14e093b7b1da7fc5f2887bcb7c', 'alt': 'Queen bed, sitting corner and the door to the terrace', 'cap': 2, 'add': 20, 'villa': False},
    {'name': 'Superior Queen Room', 'pills': ['Sea view', '2 guests'], 'desc': 'A terrace of your own with the sea in front of it.', 'src': '/_blob/c52dc0b422d93e5648121425daaa19a4', 'alt': 'The terrace of a Superior Room looking out to the sea', 'cap': 2, 'add': 45, 'villa': False},
    {'name': 'Villa Eudora', 'pills': ['5 bedrooms', 'Private pool'], 'desc': 'A stone house for family and friends, four minutes from the hotel.', 'src': '/_blob/5a3bd934ace28fc571185ce354dcf308', 'alt': 'A long table set on the villa terrace above the bay', 'cap': 11, 'add': 0, 'villa': True},
]
FIELD = 'display: flex; flex-direction: column; justify-content: center; gap: 4px; min-height: 76px; margin: 0; padding: 12px 20px; border: 1px solid var(--line); border-radius: 16px; background: var(--surface); color: var(--text); text-align: left; font: inherit; cursor: pointer;'
SMALL = 'font-size: 13px; font-weight: 400; letter-spacing: 0.16em; text-transform: uppercase; color: var(--text-2); font-family: var(--font-label);'
INPUT = 'font-family: var(--font-body); font-size: 17px; color: var(--text); background: transparent; border: 0; border-bottom: 1px solid var(--control-border); padding: 10px 0;'
LABEL = 'display: flex; flex-direction: column; gap: 4px; font-size: 14px; color: var(--text-2);'
STEPBTN = 'width: 40px; height: 40px; border: 1px solid var(--line); border-radius: 50%; background: transparent; color: var(--text); font: 400 20px/1 var(--font-body); cursor: pointer;'

book = head('Book your stay · Amour Relax Hotel') + f"""
<section id="top" style="padding: 40px var(--gutter) 32px;">
  <div style="max-width: var(--container); margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
    {crumb('Book')}
    <h1 style="{H1}">Book your stay</h1>
    <p style="margin: 0; color: var(--text-2);">Same price as on Booking.com, paid securely by card.</p>
  </div>
</section>

<section id="book" ref="{{{{setTop}}}}" style="padding: 0 var(--gutter) var(--sec-l); scroll-margin-top: 96px;">
  <div style="max-width: var(--container); margin: 0 auto; display: flex; flex-direction: column; gap: 40px;">
    <ol aria-label="Steps" style="list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 8px 28px; font-size: 13px; font-weight: 400; letter-spacing: 0.16em; text-transform: uppercase; font-family: var(--font-label);">
      <sc-for list="{{{{steps}}}}" as="st" hint-placeholder-count="4">
        <li aria-current="{{{{st.cur}}}}" style="display: flex; align-items: center; gap: 10px; color: {{{{st.fg}}}};"><span style="display: inline-block; width: 24px; height: 1px; background: {{{{st.fg}}}};"></span>{{{{st.label}}}}</li>
      </sc-for>
    </ol>

    <div style="display: {{{{s1D}}}}; flex-direction: column; gap: 48px;">
      <div class="bk-search" style="position: relative; display: grid; grid-template-columns: 1fr 1fr 1.2fr auto; gap: 12px; align-items: stretch;">
        <button type="button" onClick="{{{{openArr}}}}" aria-expanded="{{{{arrExp}}}}" style="{FIELD} border-color: {{{{arrBd}}}};"><span style="{SMALL}">Arrive</span><span style="font-size: 18px;">{{{{arrTxt}}}}</span></button>
        <button type="button" onClick="{{{{openDep}}}}" aria-expanded="{{{{depExp}}}}" style="{FIELD} border-color: {{{{depBd}}}};"><span style="{SMALL}">Leave</span><span style="font-size: 18px;">{{{{depTxt}}}}</span></button>
        <div class="bk-guests" style="{FIELD} flex-direction: row; align-items: center; justify-content: space-between; cursor: default;">
          <span style="display: flex; flex-direction: column; gap: 4px;"><span style="{SMALL}">Guests</span><span style="font-size: 18px;">{{{{guestsTxt}}}}</span></span>
          <span style="display: flex; gap: 8px;"><button type="button" aria-label="Fewer guests" onClick="{{{{minus}}}}" style="{STEPBTN}">−</button><button type="button" aria-label="More guests" onClick="{{{{plus}}}}" style="{STEPBTN}">+</button></span>
        </div>
        <button type="button" class="am-btn bk-go" onClick="{{{{search}}}}" style="min-height: 76px; padding: 0 36px; border-radius: 16px;">Search</button>
        <div role="dialog" aria-label="Choose your dates" style="display: {{{{calD}}}}; position: absolute; z-index: 6; left: {{{{calL}}}}; top: calc(100% + 12px); width: min(100%, 400px); box-sizing: border-box; padding: 20px; border-radius: 20px; background: var(--surface); box-shadow: 0 18px 48px rgba(28, 22, 18, 0.16);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <button type="button" aria-label="Previous month" onClick="{{{{prevM}}}}" style="{STEPBTN} border: 0;">←</button>
            <p style="margin: 0; font-weight: 500;">{{{{monthTxt}}}}</p>
            <button type="button" aria-label="Next month" onClick="{{{{nextM}}}}" style="{STEPBTN} border: 0;">→</button>
          </div>
          <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); text-align: center; font-size: 12px; color: var(--text-2);"><sc-for list="{{{{wk}}}}" as="w" hint-placeholder-count="7"><span style="padding: 6px 0;">{{{{w.t}}}}</span></sc-for></div>
          <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); row-gap: 2px;">
            <sc-for list="{{{{days}}}}" as="d" hint-placeholder-count="35"><button type="button" aria-label="{{{{d.aria}}}}" aria-disabled="{{{{d.dis}}}}" aria-pressed="{{{{d.on}}}}" onClick="{{{{d.pick}}}}" style="height: 44px; margin: 0; padding: 0; border: 0; border-radius: {{{{d.rad}}}}; background: {{{{d.bg}}}}; color: {{{{d.fg}}}}; font: 400 15px/1 var(--font-body); visibility: {{{{d.vis}}}}; cursor: {{{{d.cur}}}}; text-decoration: {{{{d.td}}}};">{{{{d.label}}}}</button></sc-for>
          </div>
          <p style="margin: 12px 0 0; font-size: 13px; color: var(--text-2);">{{{{calHint}}}}</p>
        </div>
      </div>

      <p style="display: {{{{hintD}}}}; margin: 0; max-width: 520px; color: var(--text-2);">Choose your dates: we show only what is free, with the price for your whole stay.</p>
      <p style="display: {{{{noteD}}}}; margin: 0; color: var(--text-2);">{{{{resNote}}}}</p>
      <ul style="display: {{{{resD}}}}; list-style: none; margin: 0; padding: 0; flex-direction: column;">
        <sc-for list="{{{{units}}}}" as="u" hint-placeholder-count="4">
          <li class="bk-unit" style="display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 32px; align-items: center; padding: 40px 0; border-top: 1px solid var(--line);">
            <span style="grid-column: 1 / span 5; display: block; aspect-ratio: 3 / 2; border-radius: 20px; overflow: hidden; background: var(--line);"><img src="{{{{u.src}}}}" alt="{{{{u.alt}}}}" style="display: block; width: 100%; height: 100%; object-fit: cover;"></span>
            <div style="grid-column: 7 / span 6; display: flex; flex-direction: column; gap: 14px;">
              <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px;">
                <h2 class="bk-name" style="{H3} font-size: 30px;">{{{{u.name}}}}</h2>
                <sc-for list="{{{{u.pills}}}}" as="pl" hint-placeholder-count="2"><span style="padding: 6px 12px; border-radius: 999px; background: var(--surface); font-size: 14px;">{{{{pl.t}}}}</span></sc-for>
              </div>
              <p style="margin: 0; color: var(--text-2);">{{{{u.desc}}}}</p>
              <p style="margin: 8px 0 0; font-size: 14px; color: var(--text-2);">{{{{u.stay}}}}</p>
              <p style="margin: 0; font-family: var(--font-head); font-size: 40px; line-height: 1; letter-spacing: -0.025em;">{{{{u.total}}}}</p>
              <p style="margin: 0; font-size: 14px; color: var(--text-2);">Free cancellation until [14] days before arrival · climate fee paid at the hotel</p>
              <div style="padding-top: 8px;"><button type="button" class="am-btn" onClick="{{{{u.pick}}}}">Select</button></div>
            </div>
          </li>
        </sc-for>
      </ul>
    </div>

    <div class="bk-two" style="display: {{{{s23D}}}}; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 32px; align-items: start;">
      <div style="grid-column: 1 / span 7; display: flex; flex-direction: column; gap: 28px;">
        <div ref="{{{{setDet}}}}" style="display: {{{{s2D}}}}; flex-direction: column; gap: 24px;">
          <h2 class="bk-name" style="{H3} font-size: 30px;">Your details</h2>
          <p style="margin: -12px 0 0; font-size: 15px; color: var(--text-2);">All fields are needed unless marked optional.</p>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <label style="{LABEL}">First name<input type="text" autocomplete="given-name" data-req="first" aria-invalid="{{{{aFirst}}}}" aria-describedby="e-first" style="{INPUT}"><span id="e-first" style="display: {{{{eFirst}}}}; font-size: 14px; line-height: 1.4; color: var(--accent);">Please add your first name.</span></label>
            <label style="{LABEL}">Last name<input type="text" autocomplete="family-name" data-req="last" aria-invalid="{{{{aLast}}}}" aria-describedby="e-last" style="{INPUT}"><span id="e-last" style="display: {{{{eLast}}}}; font-size: 14px; line-height: 1.4; color: var(--accent);">Please add your last name.</span></label>
            <label style="{LABEL}">Email<input type="email" autocomplete="email" data-req="email" aria-invalid="{{{{aEmail}}}}" aria-describedby="e-email" style="{INPUT}"><span id="e-email" style="display: {{{{eEmail}}}}; font-size: 14px; line-height: 1.4; color: var(--accent);">Please add an email like name@example.com; the confirmation goes there.</span></label>
            <label style="{LABEL}">Phone, optional<input type="tel" autocomplete="tel" style="{INPUT}"></label>
            <label style="grid-column: 1 / -1; {LABEL}">Anything we should know?<textarea rows="3" style="{INPUT} resize: vertical;"></textarea></label>
          </div>
          <div style="display: flex; flex-direction: column; gap: 6px;"><label style="display: flex; gap: 12px; align-items: flex-start; font-size: 15px;"><input type="checkbox" data-req="terms" aria-invalid="{{{{aTerms}}}}" aria-describedby="e-terms" style="width: 20px; height: 20px; margin: 2px 0 0; accent-color: var(--accent);"><span>I accept the <a href="Legal.dc.html#terms">booking terms</a> and the cancellation rule of this rate.</span></label><span id="e-terms" style="display: {{{{eTerms}}}}; font-size: 14px; line-height: 1.4; color: var(--accent); padding-left: 32px;">Please accept the booking terms to continue.</span></div>
          <label style="display: flex; gap: 12px; align-items: flex-start; font-size: 15px; color: var(--text-2);"><input type="checkbox" style="width: 20px; height: 20px; margin: 2px 0 0; accent-color: var(--accent);"><span>Send me news and offers now and then.</span></label>
          <div style="display: flex; flex-wrap: wrap; gap: 12px;"><button type="button" class="am-btn" onClick="{{{{toPay}}}}">Continue to payment</button><button type="button" class="am-btn am-btn--ghost" onClick="{{{{back}}}}">Back</button></div>
        </div>
        <div style="display: {{{{s3D}}}}; flex-direction: column; gap: 24px;">
          <h2 class="bk-name" style="{H3} font-size: 30px;">Payment</h2>
          <p style="margin: 0;">You pay {{{{totalTxt}}}} now by card. The payment page is secure; we never see your card number.</p>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <label style="grid-column: 1 / -1; {LABEL}">Card number<input type="text" inputmode="numeric" autocomplete="cc-number" placeholder="1234 5678 9012 3456" style="{INPUT}"></label>
            <label style="{LABEL}">Expiry<input type="text" inputmode="numeric" autocomplete="cc-exp" placeholder="MM / YY" style="{INPUT}"></label>
            <label style="{LABEL}">Security code<input type="text" inputmode="numeric" autocomplete="cc-csc" placeholder="CVC" style="{INPUT}"></label>
            <label style="grid-column: 1 / -1; {LABEL}">Name on card<input type="text" autocomplete="cc-name" style="{INPUT}"></label>
          </div>
          <div style="display: flex; flex-wrap: wrap; gap: 12px;"><a class="am-btn" href="Thanks.dc.html" onClick="{{{{save}}}}">Pay {{{{totalTxt}}}}</a><button type="button" class="am-btn am-btn--ghost" onClick="{{{{back}}}}">Back</button></div>
        </div>
      </div>
      <aside class="bk-sum" style="grid-column: 9 / span 4; position: sticky; top: 112px; border-radius: 24px; overflow: hidden; background: var(--surface);">
        <span style="display: block; aspect-ratio: 3 / 2; overflow: hidden;"><img src="{{{{sel.src}}}}" alt="{{{{sel.alt}}}}" style="display: block; width: 100%; height: 100%; object-fit: cover;"></span>
        <div style="padding: 24px 28px 28px; display: flex; flex-direction: column; gap: 6px;">
          <p style="{LBL}">Your stay</p>
          <p style="margin: 0; font-family: var(--font-head); font-size: 24px; letter-spacing: -0.01em; font-weight: 500;">{{{{sel.name}}}}</p>
          <p style="margin: 6px 0 0; font-size: 15px;">{{{{rangeTxt}}}}</p>
          <p style="margin: 0; font-size: 15px; color: var(--text-2);">{{{{stayTxt}}}}</p>
          <p style="margin: 14px 0 0; padding-top: 14px; border-top: 1px solid var(--line); display: flex; justify-content: space-between; align-items: baseline;"><span>Total</span><span style="font-family: var(--font-head); font-size: 28px; letter-spacing: -0.02em; font-weight: 500;">{{{{totalTxt}}}}</span></p>
          <p style="margin: 0; font-size: 14px; color: var(--text-2);">Free cancellation until [14] days before arrival · climate fee paid at the hotel</p>
          <button type="button" onClick="{{{{change}}}}" style="align-self: flex-start; margin: 10px 0 0; padding: 0; border: 0; background: none; color: var(--accent); font: 500 15px/1.4 var(--font-body); text-decoration: underline; cursor: pointer;">Change dates or room</button>
        </div>
      </aside>
    </div>

    <p style="margin: 0; font-size: 15px; color: var(--text-2);">Questions? <a href="#top">Write on WhatsApp</a> · <a href="Legal.dc.html#terms">Booking terms</a></p>
  </div>
</section>
""" + FOOT_HTML + script(2600, """class Component extends DCLogic {
  constructor(props) {
    super(props);
    // the season we sell: first night 1 March, last night 31 October
    this.OPEN = Date.UTC(2027, 2, 1);
    this.LAST = Date.UTC(2027, 9, 31);
    // arriving from the search on the home page: the calendar is already open
    let ask = null;
    try { ask = window.sessionStorage.getItem('amour-cal'); window.sessionStorage.removeItem('amour-cal'); } catch (e) {}
    // on the design board the page shows a finished search, so the free rooms and the villa are visible
    const board = (window.innerHeight || 0) > 1600;
    this.state = { step: 1, arr: board ? Date.UTC(2027, 4, 15) : null, dep: board ? Date.UTC(2027, 4, 22) : null, guests: 2, open: ask === 'arr' ? 'arr' : null, vy: 2027, vm: 4, searched: board, sel: 0, errs: {} };
    this.top = null;
    this.setTop = (el) => { this.top = el; };
    this.goTop = () => { if (this.top && this.top.scrollIntoView) this.top.scrollIntoView({ behavior: 'smooth', block: 'start' }); };
    this.openArr = () => this.setState({ open: this.state.open === 'arr' ? null : 'arr' });
    this.openDep = () => this.setState({ open: this.state.open === 'dep' ? null : 'dep' });
    this.prevM = () => { const s = this.state; const m = s.vm === 0 ? 11 : s.vm - 1; this.setState({ vm: m, vy: s.vm === 0 ? s.vy - 1 : s.vy }); };
    this.nextM = () => { const s = this.state; const m = s.vm === 11 ? 0 : s.vm + 1; this.setState({ vm: m, vy: s.vm === 11 ? s.vy + 1 : s.vy }); };
    this.minus = () => this.setState({ guests: Math.max(1, this.state.guests - 1) });
    this.plus = () => this.setState({ guests: Math.min(11, this.state.guests + 1) });
    this.search = () => { const s = this.state; if (!s.arr) { this.setState({ open: 'arr' }); return; } if (!s.dep) { this.setState({ open: 'dep' }); return; } this.setState({ searched: true, open: null }); };
    this.pickDay = (t) => {
      const s = this.state;
      if (t < this.OPEN || t > this.LAST + 86400000) return;
      if (s.open === 'arr' || !s.arr || (s.arr && s.dep && s.open !== 'dep') || t <= s.arr) { this.setState({ arr: t, dep: null, open: 'dep', searched: false }); return; }
      this.setState({ dep: t, open: null, searched: true });
    };
    this.pickUnit = (i) => { this.setState({ sel: i, step: 2 }); this.goTop(); };
    this.setDet = (el) => { this.det = el; };
    // the details are checked before payment: what is missing is said under the field, and the first one gets the focus
    this.toPay = () => {
      const box = this.det, f = (k) => (box ? box.querySelector('[data-req="' + k + '"]') : null), errs = {};
      ['first', 'last'].forEach((k) => { const el = f(k); if (el && !el.value.trim()) errs[k] = 1; });
      const em = f('email'); if (em && !/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(em.value.trim())) errs.email = 1;
      const tm = f('terms'); if (tm && !tm.checked) errs.terms = 1;
      const keys = Object.keys(errs);
      if (box && keys.length) { this.setState({ errs }); const el = f(keys[0]); if (el) el.focus(); return; }
      this.setState({ step: 3, errs: {} }); this.goTop();
    };
    this.back = () => { this.setState({ step: this.state.step - 1 }); this.goTop(); };
    this.change = () => { this.setState({ step: 1 }); this.goTop(); };
  }
  renderVals() {
    const s = this.state;
    const DAY = 86400000;
    const MON = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const WD = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    // English dates written out by hand, so the browser language never changes them
    const fmt = (t) => { const d = new Date(t); return WD[d.getUTCDay()] + ' ' + d.getUTCDate() + ' ' + MON[d.getUTCMonth()].slice(0, 3); };
    const eur = (v) => '€' + Math.round(v).toLocaleString('en-GB');
    const UNITS = """ + json.dumps(UNITS) + """;
    // example prices from the 2027 price list in the plan: hotel €110–200 and villa €450–1,000 a night by month
    // example prices from the 2027 price proposal (file 01); on the live site they come from the booking engine
    const HOTEL = { 2: 110, 3: 130, 4: 150, 5: 170, 6: 190, 7: 200, 8: 170, 9: 130 };
    const VILLA = { 2: 450, 3: 450, 4: 550, 5: 800, 6: 950, 7: 1000, 8: 700, 9: 500 };
    const nights = s.arr && s.dep ? Math.round((s.dep - s.arr) / DAY) : 0;
    const price = (u) => { let sum = 0; for (let i = 0; i < nights; i++) { const m = new Date(s.arr + i * DAY).getUTCMonth(); sum += u.villa ? (VILLA[m] || 550) : (HOTEL[m] || 120) + u.add; } return sum; };
    const gTxt = s.guests + (s.guests === 1 ? ' adult' : ' adults');
    const stay = nights + (nights === 1 ? ' night' : ' nights') + ' · ' + gTxt;
    const fit = UNITS.map((u, i) => Object.assign({}, u, { i: i })).filter((u) => u.cap >= s.guests);
    const units = fit.map((u) => ({ name: u.name, desc: u.desc, src: u.src, alt: u.alt, pills: u.pills.map((t) => ({ t: t })), stay: stay, total: eur(price(u)), pick: () => this.pickUnit(u.i) }));
    // the month grid, Monday first
    const first = Date.UTC(s.vy, s.vm, 1), lead = (new Date(first).getUTCDay() + 6) % 7, dim = new Date(Date.UTC(s.vy, s.vm + 1, 0)).getUTCDate();
    const days = [];
    for (let i = 0; i < lead; i++) days.push({ label: '', aria: '', dis: 'true', on: 'false', vis: 'hidden', bg: 'transparent', fg: 'var(--text)', rad: '0', cur: 'default', td: 'none', pick: () => {} });
    for (let d = 1; d <= dim; d++) {
      const t = Date.UTC(s.vy, s.vm, d);
      const closed = t < this.OPEN || t > this.LAST + DAY;
      const end = t === s.arr || t === s.dep, mid = s.arr && s.dep && t > s.arr && t < s.dep;
      days.push({ label: String(d), aria: fmt(t) + (closed ? ', closed' : ''), dis: closed ? 'true' : 'false', on: end ? 'true' : 'false', vis: 'visible',
        bg: end ? 'var(--accent)' : mid ? 'rgba(168, 72, 42, 0.12)' : 'transparent', fg: end ? 'var(--c-limestone)' : closed ? 'rgba(42, 34, 30, 0.28)' : 'var(--text)',
        rad: end ? '999px' : mid ? '0' : '999px', cur: closed ? 'default' : 'pointer', td: closed ? 'line-through' : 'none', pick: () => this.pickDay(t) });
    }
    const sel = UNITS[s.sel];
    const total = eur(price(sel));
    const labels = ['Dates', 'Room', 'Details', 'Payment'];
    const cur = s.step === 1 ? (s.searched ? 1 : 0) : s.step;
    return {
      setTop: this.setTop,
      steps: labels.map((l, i) => ({ label: (i + 1) + ' ' + l, cur: i === cur ? 'step' : 'false', fg: i === cur ? 'var(--text)' : i < cur ? 'var(--accent)' : 'var(--text-muted)' })),
      s1D: s.step === 1 ? 'flex' : 'none', s23D: s.step > 1 ? 'grid' : 'none', s2D: s.step === 2 ? 'flex' : 'none', s3D: s.step === 3 ? 'flex' : 'none',
      openArr: this.openArr, openDep: this.openDep, minus: this.minus, plus: this.plus, search: this.search, prevM: this.prevM, nextM: this.nextM,
      arrTxt: s.arr ? fmt(s.arr) : 'Add date', depTxt: s.dep ? fmt(s.dep) : 'Add date', arrExp: s.open === 'arr' ? 'true' : 'false', depExp: s.open === 'dep' ? 'true' : 'false',
      arrBd: s.open === 'arr' ? 'var(--text)' : 'var(--line)', depBd: s.open === 'dep' ? 'var(--text)' : 'var(--line)',
      guestsTxt: gTxt, calD: s.open ? 'block' : 'none', calL: s.open === 'dep' ? 'min(25%, calc(100% - 400px))' : '0px', monthTxt: MON[s.vm] + ' ' + s.vy,
      wk: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'].map((t) => ({ t: t })), days: days,
      calHint: s.open === 'dep' ? 'Now the day you leave.' : 'We are open from 1 March to 31 October 2027.',
      hintD: s.searched ? 'none' : 'block', resD: s.searched && units.length ? 'flex' : 'none',
      noteD: s.searched ? 'block' : 'none',
      resNote: !units.length ? 'Nothing free for these dates. Try other dates or write to us.' : s.guests > 2 ? 'For more than two guests: the villa, or several rooms booked one by one.' : 'Free for ' + fmt(s.arr) + ' – ' + fmt(s.dep) + ', ' + stay + '.',
      units: units,
      sel: sel, totalTxt: total, rangeTxt: s.arr && s.dep ? fmt(s.arr) + ' – ' + fmt(s.dep) : '', stayTxt: stay,
      toPay: this.toPay, back: this.back, change: this.change, setDet: this.setDet,
      eFirst: s.errs.first ? 'block' : 'none', aFirst: s.errs.first ? 'true' : 'false',
      eLast: s.errs.last ? 'block' : 'none', aLast: s.errs.last ? 'true' : 'false',
      eEmail: s.errs.email ? 'block' : 'none', aEmail: s.errs.email ? 'true' : 'false',
      eTerms: s.errs.terms ? 'block' : 'none', aTerms: s.errs.terms ? 'true' : 'false',
      // the thank-you page shows this booking in the prototype
      save: () => { try { window.sessionStorage.setItem('amour-booking', JSON.stringify({ room: sel.name, stay: (s.arr && s.dep ? fmt(s.arr) + ' – ' + fmt(s.dep) + ' · ' : '') + nights + (nights === 1 ? ' night' : ' nights'), guests: gTxt, paid: total + ' by card' })); } catch (e) {} },
""" + FOOT_VALS + """
    };
  }
}""")

# ---------------------------------------------------------------- About
PHIL = 'We believe a place to stay should feel like a home, not a hotel corridor. Your own kitchen, your own terrace, stone and olive trees around you, nothing that shouts. A quiet base to live by your own rhythm and to set out to see Crete.'
GAL = [
    {'src': '/_blob/deb2647ed7b0e40eb233a905c0be42ac', 'alt': 'A serve into the low sun on the hotel court', 'cap': 'Our court at sunset', 'ar': '3 / 4', 'mt': '0px'},
    {'src': '/_blob/94833f4f7f37f38c5ee6ed7578db4a52', 'alt': 'Stone stairs with amphorae at golden hour', 'cap': 'The stairs to the pool', 'ar': '4 / 5', 'mt': '120px'},
    {'src': '/_blob/1c8b6c14e093b7b1da7fc5f2887bcb7c', 'alt': 'Queen bed, sitting corner and the door to the terrace', 'cap': 'One of the nine rooms', 'ar': '3 / 4', 'mt': '40px'},
]
CARE = [
    {'n': '01', 'h': 'Room to live', 't': 'Every room has a kitchen and a terrace. Cook, eat outside, keep your own hours.'},
    {'n': '02', 'h': 'A base for Crete', 't': 'Honest drive times and our own guide to beaches, gorges and villages.'},
    {'n': '03', 'h': 'Stone, olives, quiet', 't': 'Terraces in an olive grove, a pool and our own tennis court, free for guests.'},
]

about = head('About us · Amour Relax Hotel') + f'''
<section id="top" style="padding: 40px var(--gutter) var(--sec-m);">
  <div style="max-width: var(--container); margin: 0 auto; display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 32px; align-items: end;">
    <div style="grid-column: 1 / span 6; display: flex; flex-direction: column; gap: 28px; padding-bottom: 32px;">
      {crumb('About us')}
      <h1 style="{H1} font-size: 96px;">About Amour</h1>
      <p style="margin: 0; max-width: 520px; font-size: 20px;">A small hotel on stone terraces above Almyrida bay, and a villa four minutes away. Built around one idea: stay like you live here.</p>
    </div>
    <div data-reveal="rise" style="grid-column: 8 / span 5; aspect-ratio: 4 / 5; border-radius: 999px 999px 24px 24px; overflow: hidden; background: var(--c-dark);"><img src="/_blob/16e92c844577adb7ca466250469281b0" alt="Late sun and the shadow of an olive tree on a limestone wall by a wooden door" style="display: block; width: 100%; height: 100%; object-fit: cover;"></div>
  </div>
</section>

<section ref="{{{{setPhil}}}}" style="padding: var(--sec-m) var(--gutter);">
  <div style="max-width: 1120px; margin: 0 auto; display: flex; flex-direction: column; gap: 32px;">
    <p style="{LBL}">Our philosophy</p>
    <p class="ab-phil stmt" style="margin: 0; font-family: var(--font-head); font-size: clamp(28px, 3.7vw, 54px); line-height: 1.22; letter-spacing: -0.025em;"><sc-for list="{{{{words}}}}" as="w" hint-placeholder-count="40"><span style="opacity: {{{{w.op}}}}; transition: opacity .4s;">{{{{w.text}}}} </span></sc-for></p>
  </div>
</section>

<section style="padding: var(--sec-s) var(--gutter) var(--sec-m);">
  <div class="gal" style="max-width: var(--container); margin: 0 auto; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 32px; align-items: start;">
    <sc-for list="{{{{gallery}}}}" as="g" hint-placeholder-count="3">
      <figure data-reveal="rise" style="margin: {{{{g.mt}}}} 0 0; display: flex; flex-direction: column; gap: 10px;">
        <span style="display: block; aspect-ratio: {{{{g.ar}}}}; border-radius: 24px; overflow: hidden; background: var(--line);"><img src="{{{{g.src}}}}" alt="{{{{g.alt}}}}" style="display: block; width: 100%; height: 100%; object-fit: cover;"></span>
        <figcaption style="display: flex; justify-content: space-between; font-size: 14px; color: var(--text-2);"><span>{{{{g.cap}}}}</span><span>{{{{g.n}}}}</span></figcaption>
      </figure>
    </sc-for>
  </div>
</section>

<section style="padding: var(--sec-m) var(--gutter) var(--sec-l);">
  <div class="ab-cta" style="max-width: var(--container); margin: 0 auto; padding: 96px 56px; border-radius: 24px; background: var(--deep); color: var(--on-deep); display: flex; flex-direction: column; align-items: center; gap: 28px; text-align: center;">
    <h2 style="{H2} font-size: 56px; color: var(--on-deep);">Stay like you live here</h2>
    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 12px;">
      <x-import component-from-global-scope="Amour.Button" variant="primary" href="Book.dc.html">Book your stay</x-import>
      <x-import component-from-global-scope="Amour.Button" variant="on-photo" href="Rooms.dc.html">See the rooms</x-import>
    </div>
  </div>
</section>
''' + FOOT_HTML + script(4600, f'''class Component extends DCLogic {{
  constructor(props) {{
    super(props);
    this.state = {{ top: null, vh: 900 }};
    this.raf = null;
    this.el = null;
    this.reduce = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    this.setPhil = (el) => {{ this.el = el; }};
    this.measure = () => {{
      this.raf = null;
      const vh = window.innerHeight || 900;
      this.setState({{ vh: vh, top: this.el ? this.el.getBoundingClientRect().top : null, isStatic: vh > 1600 }});
    }};
    this.onScroll = () => {{ if (!this.raf) this.raf = window.requestAnimationFrame(this.measure); }};
  }}
  componentDidMount() {{
    window.addEventListener('scroll', this.onScroll, {{ passive: true }});
    window.addEventListener('resize', this.onScroll);
    this.onScroll();
  }}
  componentWillUnmount() {{
    window.removeEventListener('scroll', this.onScroll);
    window.removeEventListener('resize', this.onScroll);
    if (this.raf) window.cancelAnimationFrame(this.raf);
  }}
  renderVals() {{
    const s = this.state;
    const clamp = (v) => Math.max(0, Math.min(1, v));
    // the philosophy lights up word by word as it rises through the screen, as at Casa Momo
    const live = s.top !== null && !s.isStatic && !this.reduce;
    const q = live ? clamp((s.vh * 0.85 - s.top) / (s.vh * 0.7)) : 1;
    const words = {json.dumps(PHIL)}.split(' ');
    const GAL = {json.dumps(GAL)};
    return {{
      setPhil: this.setPhil,
      words: words.map((w, i) => ({{ text: w, op: (0.16 + 0.84 * clamp(q * words.length - i)).toFixed(3) }})),
      gallery: GAL.map((g, i) => Object.assign({{}}, g, {{ n: (i + 1) + ' / ' + GAL.length }})),
      facts: [{{ v: '1.2 km', k: 'to the beach, 15 min on foot' }}, {{ v: '40 min', k: 'by car to Chania' }}, {{ v: '4 min', k: 'by car to the villa' }}],
      care: {json.dumps(CARE)},
{FOOT_VALS}
    }};
  }}
}}''')

# ---------------------------------------------------------------- Legal: one template, three documents
def sec(i, h, ps, rows=None):
    return {'id': f'sec-{i}', 'h': h, 'ps': [{'t': p} for p in ps], 'rows': rows or [], 'rowsD': 'grid' if rows else 'none'}

DOCS = {
    'privacy': {
        'title': 'Privacy policy',
        'lead': 'How we collect, use and protect the data of our guests and of visitors to this site.',
        'sections': [
            sec(1, 'Who we are', ['[Company name], [legal form], [address], Greece. General Commercial Registry (ΓΕΜΗ) [number], tax number (ΑΦΜ) [number]. We run Amour Relax Hotel (MHTE [number]) and Villa Eudora ([MHTE or AMA number]).', 'For anything about your data, write to [privacy email].']),
            sec(2, 'What we collect', ['When you book: your name, email, phone, dates, number of guests and any requests. Card details go straight to the secure payment page of our booking engine; we never see or keep the full card number.', 'When you write to us: your name, email, dates and message. When you visit the site: cookies and similar data, only with your consent (see the cookie policy).']),
            sec(3, 'Why we use it', ['To take and run your booking (a contract with you). To keep accounting records and register guests as Greek law requires (a legal duty). To answer your questions. To send news and offers only if you ask for them (your consent, which you can withdraw at any time). To measure and improve the site, only with your consent.']),
            sec(4, 'Who receives it', ['Our booking engine (BookOnlineNow), channel manager (HotelAvailabilities) and hotel management system (CloudHotel); the bank that processes card payments; [newsletter service]; Google and Meta for analytics and ads, only with your consent; our accountant; public authorities when the law requires it. Each works under a data processing agreement with us.', 'Some providers, such as Google and Meta, may process data outside the EU. They do so under the safeguards of EU data protection law, such as standard contractual clauses.']),
            sec(5, 'How long we keep it', ['Booking and accounting records: as long as Greek tax law requires [… years]. Messages: [… months] after our last contact. Newsletter: until you unsubscribe. Cookies: see the cookie policy.']),
            sec(6, 'Your rights', ['You can ask to see, correct or delete your data, to limit or object to its use, to take it with you, and to withdraw your consent at any time. Write to [privacy email]; we answer within one month.', 'You can also complain to the Hellenic Data Protection Authority (www.dpa.gr).']),
            sec(7, 'Changes', ['We update this policy when our services change. The date at the top shows the latest version.']),
        ]},
    'cookies': {
        'title': 'Cookie policy',
        'lead': 'Which cookies this site uses, why, and how to change your choice.',
        'sections': [
            sec(1, 'What cookies are', ['Small files a site saves in your browser, so it can remember your choices or count visits.']),
            sec(2, 'Your choice', ['On your first visit you choose: accept all or reject. Both buttons are equal. Analytics, ads, maps and videos load only after you accept. You can change your choice at any time with "Cookie settings" at the bottom of every page.']),
            sec(3, 'Cookies we use', ['[The list is completed when the site, the booking engine and analytics are set up.]'], rows=[
                {'n': 'Name', 'p': 'Set by', 'u': 'Purpose', 'd': 'Kept for', 'hd': '500', 'bg': 'var(--surface)'},
                {'n': '[cookie_consent]', 'p': 'This site', 'u': 'Remembers your cookie choice', 'd': '[12 months]', 'hd': '400', 'bg': 'transparent'},
                {'n': '[name]', 'p': 'BookOnlineNow', 'u': 'Keeps your booking in progress', 'd': '[session]', 'hd': '400', 'bg': 'transparent'},
                {'n': '_ga, _ga_*', 'p': 'Google Analytics', 'u': 'Counts visits, after consent', 'd': '[up to 2 years]', 'hd': '400', 'bg': 'transparent'},
                {'n': '_fbp', 'p': 'Meta', 'u': 'Measures ads, after consent', 'd': '[3 months]', 'hd': '400', 'bg': 'transparent'},
                {'n': '[name]', 'p': 'Google Maps', 'u': 'Shows the map, after consent', 'd': '[…]', 'hd': '400', 'bg': 'transparent'},
            ]),
            sec(4, 'How to delete cookies', ['In your browser settings you can see and delete cookies at any time. If you block all cookies, the booking engine may not work.']),
            sec(5, 'Changes', ['We update this list every time we add or remove a service.']),
        ]},
    'terms': {
        'title': 'Booking terms',
        'lead': 'The conditions of a booking made on this site.',
        'sections': [
            sec(1, 'Who you book with', ['[Company name], [address], Greece, tax number (ΑΦΜ) [number], which runs Amour Relax Hotel (MHTE [number]) and Villa Eudora ([MHTE or AMA number]).']),
            sec(2, 'Booking and payment', ['You book on this site through our booking engine. The full amount is charged when you book, by card, on its secure payment page. Prices include VAT. You must be 18 or over to book.']),
            sec(3, 'Climate resilience fee', ['Greece charges a climate resilience fee per room per night. It is not part of the price and is paid at the hotel: [€… per room per night].']),
            sec(4, 'Cancellation and changes', ['[Standard rate: free cancellation up to … days before arrival; after that …]. [Non-refundable rate, if offered: the full amount is charged at booking and not refunded.] The rule of each rate is shown next to its price and in your confirmation.', 'To change your dates, write to us; we help if the room is free. No-show: [ … ].']),
            sec(5, 'Groups and celebrations', ['Bookings of three rooms or more, of the whole hotel, or for a celebration at the villa follow separate written conditions: deposit and payment dates agreed with us.']),
            sec(6, 'Arrival and departure', ['Check-in from 14:00, check-out until 11:00. Please tell us your arrival time. [Online check-in before arrival.]']),
            sec(7, 'House rules', ['Children are welcome; our rooms suit guests from 12 years best, the villa suits any age. Pets are welcome [conditions]. Celebrations only at the villa, agreed in advance. [Quiet hours, smoking.]']),
            sec(8, 'Liability and complaints', ['[Liability for belongings and damage, deposit for the villa: written with the lawyer.] If something is wrong, tell us on the spot and we put it right. Greek law applies to these terms.']),
        ]},
}

legal = head('Legal · Amour Relax Hotel') + f'''
<span id="privacy" aria-hidden="true"></span><span id="cookies" aria-hidden="true"></span><span id="terms" aria-hidden="true"></span>
<section id="top" style="padding: 40px var(--gutter) 56px;">
  <div style="max-width: var(--container); margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">
    {crumb('{{doc.title}}')}
    <h1 style="{H1}">{{{{doc.title}}}}</h1>
    <p style="margin: 0; max-width: 620px;">{{{{doc.lead}}}}</p>
    <p style="margin: 0; font-size: 14px; color: var(--text-2);">Last updated [date] · [Draft: the lawyer checks it before launch]</p>
    <div role="tablist" aria-label="Documents" style="display: flex; flex-wrap: wrap; gap: 8px; padding-top: 8px;">
      <sc-for list="{{{{tabs}}}}" as="t" hint-placeholder-count="3">
        <button type="button" role="tab" aria-selected="{{{{t.sel}}}}" onClick="{{{{t.pick}}}}" style="min-height: 44px; padding: 0 20px; border: 1px solid {{{{t.bd}}}}; border-radius: 999px; background: {{{{t.bg}}}}; color: {{{{t.fg}}}}; font: 500 15px/1 var(--font-body); cursor: pointer; transition: background-color .3s, color .3s;">{{{{t.label}}}}</button>
      </sc-for>
    </div>
  </div>
</section>

<section style="padding: 0 var(--gutter) var(--sec-l);">
  <div style="max-width: var(--container); margin: 0 auto; display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 32px; align-items: start;">
    <nav class="legal-toc" aria-label="On this page" style="grid-column: 1 / span 3; position: sticky; top: 112px; display: flex; flex-direction: column; gap: 4px;">
      <sc-for list="{{{{doc.sections}}}}" as="x" hint-placeholder-count="7">
        <a href="#{{{{x.id}}}}" style="display: block; padding: 8px 0; font-size: 15px; color: var(--text-2); text-decoration: none;">{{{{x.h}}}}</a>
      </sc-for>
    </nav>
    <article style="grid-column: 5 / span 8; max-width: 760px; display: flex; flex-direction: column; gap: 48px;">
      <sc-for list="{{{{doc.sections}}}}" as="x" hint-placeholder-count="7">
        <section id="{{{{x.id}}}}" style="display: flex; flex-direction: column; gap: 14px; scroll-margin-top: 96px;">
          <h2 class="legal-h" style="{H3} font-size: 28px;">{{{{x.h}}}}</h2>
          <sc-for list="{{{{x.ps}}}}" as="p" hint-placeholder-count="2"><p style="margin: 0;">{{{{p.t}}}}</p></sc-for>
          <div style="display: {{{{x.rowsD}}}}; flex-direction: column; margin-top: 8px; border-radius: 16px; overflow: hidden; border: 1px solid var(--line);">
            <sc-for list="{{{{x.rows}}}}" as="r" hint-placeholder-count="6">
              <div class="ck-row" style="display: grid; grid-template-columns: 1.1fr 1fr 1.6fr 0.9fr; gap: 16px; padding: 14px 18px; background: {{{{r.bg}}}}; border-bottom: 1px solid var(--line); font-size: 15px; font-weight: {{{{r.hd}}}};"><span>{{{{r.n}}}}</span><span>{{{{r.p}}}}</span><span>{{{{r.u}}}}</span><span>{{{{r.d}}}}</span></div>
            </sc-for>
          </div>
        </section>
      </sc-for>
      <p style="margin: 0; padding: 24px 28px; border-radius: 20px; background: var(--surface); font-size: 16px;">Questions about this page? Write to [privacy email] or <a href="Contact.dc.html">contact us</a>.</p>
    </article>
  </div>
</section>
''' + FOOT_HTML + script(3400, f'''class Component extends DCLogic {{
  constructor(props) {{
    super(props);
    this.keys = ['privacy', 'cookies', 'terms'];
    this.state = {{ doc: this.fromHash() }};
    this.picks = this.keys.map((k) => () => this.setState({{ doc: k }}));
    this.onHash = () => this.setState({{ doc: this.fromHash() }});
  }}
  // the footer links open the right document: #/legal/cookies in the prototype, Legal.dc.html#cookies on the site
  fromHash() {{
    const m = /(privacy|cookies|terms)/.exec((window.location && window.location.hash) || '');
    return m ? m[1] : 'privacy';
  }}
  componentDidMount() {{ window.addEventListener('hashchange', this.onHash); }}
  componentWillUnmount() {{ window.removeEventListener('hashchange', this.onHash); }}
  renderVals() {{
    const DOCS = {json.dumps(DOCS)};
    const d = this.state.doc;
    return {{
      doc: DOCS[d],
      tabs: this.keys.map((k, i) => ({{ label: DOCS[k].title, sel: k === d ? 'true' : 'false', pick: this.picks[i],
        bg: k === d ? 'var(--text)' : 'transparent', fg: k === d ? 'var(--c-limestone)' : 'var(--text)', bd: k === d ? 'var(--text)' : 'var(--line)' }})),
{FOOT_VALS}
    }};
  }}
}}''')

# ---------------------------------------------------------------- Thank you
STEPS = [
    {'n': '01', 'h': 'Before you travel', 't': '[… days] before arrival we send a link for online check-in, so you skip the paperwork at the desk.'},
    {'n': '02', 'h': 'Getting here', 't': 'Chania airport is [… min] by car. Want a hire car waiting when you land? Tell us and our partner arranges it.'},
    {'n': '03', 'h': 'Arrival', 't': 'Check-in from 14:00. Send us your arrival time on WhatsApp and we meet you at the hotel.'},
]
thanks = head('Thank you · Amour Relax Hotel') + f'''
<section id="top" style="padding: 40px var(--gutter) var(--sec-m);">
  <div style="max-width: var(--container); margin: 0 auto; display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 32px; align-items: center;">
    <div style="grid-column: 1 / span 6; display: flex; flex-direction: column; gap: 24px;">
      <p style="{LBL}">Booking confirmed</p>
      <h1 style="{H1}">Thank you, Anna</h1>
      <p style="margin: 0; max-width: 520px;">Your stay is booked and paid. The confirmation is on its way to your email; keep it for check-in.</p>
      <dl style="margin: 0; padding: 8px 28px; border-radius: 24px; background: var(--surface); display: grid; grid-template-columns: 150px 1fr;">
        <sc-for list="{{{{summary}}}}" as="r" hint-placeholder-count="6">
          <dt style="padding: 12px 0; font-size: 15px; color: var(--text-2); border-bottom: 1px solid var(--line);">{{{{r.k}}}}</dt>
          <dd style="margin: 0; padding: 12px 0; font-size: 15px; font-weight: 500; border-bottom: 1px solid var(--line);">{{{{r.v}}}}</dd>
        </sc-for>
      </dl>
    </div>
    <div data-reveal="rise" style="grid-column: 8 / span 5; aspect-ratio: 4 / 5; border-radius: 999px 999px 24px 24px; overflow: hidden; background: var(--c-dark);"><img src="/_blob/3d4fe371301d6eb1243e6659e55b94e1" alt="A daybed with white curtains at sunset" style="display: block; width: 100%; height: 100%; object-fit: cover;"></div>
  </div>
</section>

<section style="padding: 0 var(--gutter) var(--sec-l);">
  <div style="max-width: var(--container); margin: 0 auto; display: flex; flex-direction: column; gap: 32px;">
    <h2 style="{H2}">What happens next</h2>
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px;">
      <sc-for list="{{{{next}}}}" as="c" hint-placeholder-count="3">
        <div style="padding: 32px; border-radius: 24px; background: var(--surface); display: flex; flex-direction: column; gap: 12px;">
          <p style="margin: 0; font-family: var(--font-head); font-size: 15px; color: var(--accent); letter-spacing: 0; font-weight: 500;">{{{{c.n}}}}</p>
          <h3 style="{H3}">{{{{c.h}}}}</h3>
          <p style="margin: 0; font-size: 16px;">{{{{c.t}}}}</p>
        </div>
      </sc-for>
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
      <x-import component-from-global-scope="Amour.Button" variant="primary" href="#top">Write on WhatsApp</x-import>
      <x-import component-from-global-scope="Amour.Button" variant="ghost" href="Guide.dc.html">Plan your days</x-import>
      <x-import component-from-global-scope="Amour.Button" variant="ghost" href="#top">Add to calendar</x-import>
    </div>
  </div>
</section>
''' + FOOT_HTML + script(2600, f'''class Component extends DCLogic {{
  renderVals() {{
    // the booking just made on the Book page, in the prototype; otherwise an example
    let b = {{}};
    try {{ b = JSON.parse(window.sessionStorage.getItem('amour-booking') || '{{}}') || {{}}; }} catch (e) {{ b = {{}}; }}
    return {{
      summary: [
        {{ k: 'Booking number', v: 'AMR-27-0418' }},
        {{ k: 'Stay', v: b.stay || 'Sat 15 May – Sat 22 May · 7 nights' }},
        {{ k: 'Room', v: b.room || 'Deluxe Queen Room' }},
        {{ k: 'Guests', v: b.guests || '2 adults' }},
        {{ k: 'Paid', v: b.paid || '€1,050 by card' }},
        {{ k: 'At the hotel', v: 'Climate fee [€…] a night' }}
      ],
      next: {json.dumps(STEPS)},
{FOOT_VALS}
    }};
  }}
}}''')

# ---------------------------------------------------------------- 404
nf = head('Page not found · Amour Relax Hotel') + f'''
<section id="top" style="min-height: min(calc(100vh - 84px), 860px); box-sizing: border-box; padding: 40px var(--gutter) var(--sec-m); display: flex; align-items: center;">
  <div style="width: 100%; max-width: var(--container); margin: 0 auto; display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 32px; align-items: center;">
    <div data-reveal="rise" style="grid-column: 1 / span 4; aspect-ratio: 3 / 4; border-radius: 999px 999px 24px 24px; overflow: hidden; background: var(--c-dark);"><img src="/_blob/16e92c844577adb7ca466250469281b0" alt="Late sun and the shadow of an olive tree on a closed wooden door" style="display: block; width: 100%; height: 100%; object-fit: cover;"></div>
    <div style="grid-column: 6 / span 7; display: flex; flex-direction: column; gap: 24px;">
      <p style="{LBL}">Error 404</p>
      <h1 style="{H1}">This path ends here</h1>
      <p style="margin: 0; max-width: 520px;">The page you were looking for has moved or never existed. Everything else is where we left it.</p>
      <div style="display: flex; flex-wrap: wrap; gap: 12px; padding-top: 8px;">
        <x-import component-from-global-scope="Amour.Button" variant="primary" href="Main-v2.dc.html">Back to home</x-import>
        <x-import component-from-global-scope="Amour.Button" variant="ghost" href="Rooms.dc.html">Rooms</x-import>
        <x-import component-from-global-scope="Amour.Button" variant="ghost" href="Villa-v2.dc.html">Villa Eudora</x-import>
        <x-import component-from-global-scope="Amour.Button" variant="ghost" href="Guide.dc.html">Crete guide</x-import>
      </div>
    </div>
  </div>
</section>
''' + FOOT_HTML + script(1800, f'''class Component extends DCLogic {{
  renderVals() {{
    return {{
{FOOT_VALS}
    }};
  }}
}}''')

for name, src in [('Book', book), ('About', about), ('Legal', legal), ('Thanks', thanks), ('NotFound', nf)]:
    open(P + name + '.dc.html', 'w').write(src)
    print(name, len(src))
