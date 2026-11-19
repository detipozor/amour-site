/* @ds-bundle: {"format":4,"namespace":"Amour","components":[{"name":"Button"},{"name":"Badge"},{"name":"Icon"},{"name":"DateSearch"},{"name":"MobileBookBar"},{"name":"RoomCard"},{"name":"PlaceCard"},{"name":"ArchFrame"},{"name":"PhotoPlaceholder"},{"name":"SectionIntro"},{"name":"FactStrip"},{"name":"AmenityList"},{"name":"Reviews"},{"name":"FaqItem"},{"name":"Header"},{"name":"Hero"},{"name":"BookDirect"},{"name":"Footer"},{"name":"CookieBanner"}]} */
(function () {
/* interface glyphs (interim drawings) — the brand uses no pictograms */
var ICONS={"arrow-right":[0,[["M7,26 L39,26 A2,2 0 0 0 39,22 L7,22 A2,2 0 0 0 7,26 Z",0],["M27.62,15.45 L38.12,25.45 A2,2 0 0 0 40.88,22.55 L30.38,12.55 A2,2 0 0 0 27.62,15.45 Z",0],["M30.38,35.45 L40.88,25.45 A2,2 0 0 0 38.12,22.55 L27.62,32.55 A2,2 0 0 0 30.38,35.45 Z",0]]],"chevron-down":[0,[["M9.59,19.41 L22.59,32.41 A2,2 0 0 0 25.41,29.59 L12.41,16.59 A2,2 0 0 0 9.59,19.41 Z",0],["M35.59,16.59 L22.59,29.59 A2,2 0 0 0 25.41,32.41 L38.41,19.41 A2,2 0 0 0 35.59,16.59 Z",0]]],"close":[0,[["M10.59,13.41 L34.59,37.41 A2,2 0 0 0 37.41,34.59 L13.41,10.59 A2,2 0 0 0 10.59,13.41 Z",0],["M34.59,10.59 L10.59,34.59 A2,2 0 0 0 13.41,37.41 L37.41,13.41 A2,2 0 0 0 34.59,10.59 Z",0]]],"menu":[0,[["M7,18 L41,18 A2,2 0 0 0 41,14 L7,14 A2,2 0 0 0 7,18 Z",0],["M7,26 L41,26 A2,2 0 0 0 41,22 L7,22 A2,2 0 0 0 7,26 Z",0],["M7,34 L41,34 A2,2 0 0 0 41,30 L7,30 A2,2 0 0 0 7,34 Z",0]]],"plus":[0,[["M22,10 L22,38 A2,2 0 0 0 26,38 L26,10 A2,2 0 0 0 22,10 Z",0],["M10,26 L38,26 A2,2 0 0 0 38,22 L10,22 A2,2 0 0 0 10,26 Z",0]]],"minus":[0,[["M10,26 L38,26 A2,2 0 0 0 38,22 L10,22 A2,2 0 0 0 10,26 Z",0]]],"check":[0,[["M7.59,26.41 L18.09,36.91 A2,2 0 0 0 20.91,34.09 L10.41,23.59 A2,2 0 0 0 7.59,26.41 Z",0],["M20.95,36.88 L40.45,16.38 A2,2 0 0 0 37.55,13.62 L18.05,34.12 A2,2 0 0 0 20.95,36.88 Z",0]]]};
var ICON_LABELS={"arrow-right": "Arrow right", "chevron-down": "Chevron down", "close": "Close", "menu": "Menu", "plus": "Plus", "minus": "Minus", "check": "Check"};
  var React = window.React;
  var h = React.createElement;
  var useState = React.useState;

  function cx() {
    var out = [];
    for (var i = 0; i < arguments.length; i++) if (arguments[i]) out.push(arguments[i]);
    return out.join(' ');
  }

  /* ---------- Icon ---------- */
  function Icon(props) {
    var data = ICONS[props.name];
    if (!data) return null;
    var size = props.size || 24;
    var paths = data[1].map(function (l, i) {
      var p = { key: i, fillRule: 'evenodd', d: l[0] };
      if (l[1]) p.transform = 'rotate(' + l[1] + ' 24 24)';
      return h('path', p);
    });
    var g = data[0] ? h('g', { transform: 'rotate(' + data[0] + ' 24 24)' }, paths) : paths;
    var a11y = props.title ? { role: 'img', 'aria-label': props.title } : { 'aria-hidden': 'true', focusable: 'false' };
    return h('svg', Object.assign({
      xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 48 48', width: size, height: size,
      fill: 'currentColor', className: cx('am-icon', props.className)
    }, a11y), props.title ? h('title', null, props.title) : null, g);
  }
  Icon.names = Object.keys(ICONS);
  Icon.labels = ICON_LABELS;

  /* ---------- Button ---------- */
  function Button(props) {
    var variant = props.variant || 'primary';
    var cls = cx('am-btn', 'am-btn--' + variant, props.block && 'am-btn--block', props.className);
    var inner = [
      props.icon ? h(Icon, { key: 'i', name: props.icon, size: 18 }) : null,
      h('span', { key: 't' }, props.children),
      props.iconRight ? h(Icon, { key: 'r', name: props.iconRight, size: 18 }) : null
    ];
    if (props.href) return h('a', { className: cls, href: props.href, target: props.target, rel: props.rel, onClick: props.onClick }, inner);
    return h('button', { className: cls, type: props.type || 'button', onClick: props.onClick, disabled: props.disabled }, inner);
  }

  /* ---------- Badge ---------- */
  function Badge(props) {
    return h('span', { className: cx('am-badge', 'am-badge--' + (props.tone || 'soft'), props.className) },
      props.icon ? h(Icon, { name: props.icon, size: 16 }) : null, props.children);
  }

  /* ---------- PhotoPlaceholder ---------- */
  function PhotoPlaceholder(props) {
    return h('div', {
      className: cx('am-ph', props.tone === 'dark' && 'am-ph--dark', props.arch && 'am-arch', props.className),
      style: { aspectRatio: props.ratio || '3 / 2' }, role: 'img', 'aria-label': props.label || 'Photo after the shoot'
    }, h('span', { className: 'am-ph__label' }, props.label || 'Photo after the shoot'));
  }

  function Photo(props) {
    if (!props.src) return h(PhotoPlaceholder, { ratio: props.ratio, label: props.placeholder, tone: props.tone, arch: props.arch, className: props.className });
    return h('img', { className: cx('am-photo', props.arch && 'am-arch', props.className), src: props.src, alt: props.alt || '', loading: props.eager ? 'eager' : 'lazy', style: { aspectRatio: props.ratio || '3 / 2' } });
  }

  /* ---------- ArchFrame ---------- */
  function ArchFrame(props) {
    return h('figure', { className: cx('am-archframe', props.className) },
      h(Photo, { src: props.src, alt: props.alt, ratio: props.ratio || '3 / 4', arch: true, placeholder: props.placeholder }),
      props.caption ? h('figcaption', { className: 'am-archframe__caption' }, props.caption) : null);
  }

  /* ---------- SectionIntro ---------- */
  function SectionIntro(props) {
    var H = 'h' + (props.level || 2);
    return h('div', { className: cx('am-intro', props.align === 'center' && 'am-intro--center', props.className) },
      props.eyebrow ? h('p', { className: 'am-eyebrow' }, props.eyebrow) : null,
      h(H, { className: props.level === 1 ? 'am-h1' : props.level === 3 ? 'am-h3' : 'am-h2' }, props.title),
      props.text ? h('p', { className: 'am-intro__text' }, props.text) : null,
      props.children);
  }

  /* ---------- DateSearch ---------- */
  function iso(d) { return d.toISOString().slice(0, 10); }
  function DateSearch(props) {
    var today = new Date();
    var s = useState(props.checkIn || ''), checkIn = s[0], setIn = s[1];
    var o = useState(props.checkOut || ''), checkOut = o[0], setOut = o[1];
    var a = useState(props.adults || 2), adults = a[0], setAdults = a[1];
    var c = useState(props.children || 0), kids = c[0], setKids = c[1];
    var maxAdults = props.maxAdults || 2, maxKids = props.maxChildren == null ? 1 : props.maxChildren;
    var minDate = props.minDate || iso(today);
    var id = props.id || 'am-ds';
    function submit(e) {
      e.preventDefault();
      if (props.onSearch) props.onSearch({ checkIn: checkIn, checkOut: checkOut, adults: adults, children: kids, roomCode: props.roomCode });
    }
    function range(from, to) { var r = []; for (var i = from; i <= to; i++) r.push(i); return r; }
    return h('form', { className: cx('am-search', 'am-search--' + (props.variant || 'bar'), props.className), onSubmit: submit, 'aria-label': 'Check availability' },
      h('div', { className: 'am-search__field' },
        h('label', { htmlFor: id + '-in' }, 'Check-in'),
        h('input', { id: id + '-in', type: 'date', min: minDate, value: checkIn, onChange: function (e) { setIn(e.target.value); } })),
      h('div', { className: 'am-search__field' },
        h('label', { htmlFor: id + '-out' }, 'Check-out'),
        h('input', { id: id + '-out', type: 'date', min: checkIn || minDate, value: checkOut, onChange: function (e) { setOut(e.target.value); } })),
      h('div', { className: 'am-search__field am-search__field--guests' },
        h('span', { className: 'am-search__label', id: id + '-g' }, 'Guests'),
        h('div', { className: 'am-search__guests', role: 'group', 'aria-labelledby': id + '-g' },
          h('select', { id: id + '-ad', 'aria-label': 'Adults', value: adults, onChange: function (e) { setAdults(+e.target.value); } },
            range(1, maxAdults).map(function (n) { return h('option', { key: n, value: n }, n + (n === 1 ? ' adult' : ' adults')); })),
          maxKids > 0 ? h('select', { id: id + '-ch', 'aria-label': 'Children', value: kids, onChange: function (e) { setKids(+e.target.value); } },
            range(0, maxKids).map(function (n) { return h('option', { key: n, value: n }, n + (n === 1 ? ' child' : ' children')); })) : null)),
      h(Button, { type: 'submit', variant: 'primary', className: 'am-search__submit' }, props.cta || 'Check dates'));
  }

  /* ---------- MobileBookBar ---------- */
  function MobileBookBar(props) {
    return h('div', { className: cx('am-bookbar', props.fixed === false && 'am-bookbar--static', props.className) },
      h(Button, { href: props.href, onClick: props.onBook, variant: 'primary', block: true }, props.label || 'Check dates'),
      props.whatsappHref ? h('a', { className: 'am-bookbar__chat', href: props.whatsappHref, target: '_blank', rel: 'noopener' }, 'WhatsApp') : null);
  }

  /* ---------- RoomCard ---------- */
  function RoomCard(props) {
    return h('article', { className: cx('am-room', props.className) },
      h('a', { className: 'am-room__media', href: props.href, tabIndex: -1, 'aria-hidden': 'true' },
        h(Photo, { src: props.image, alt: '', ratio: '3 / 2' })),
      h('div', { className: 'am-room__body' },
        h('h3', { className: 'am-h3' }, h('a', { href: props.href }, props.name)),
        props.details ? h('p', { className: 'am-room__details' }, props.details) : null,
        props.badges && props.badges.length ? h('div', { className: 'am-room__badges' }, props.badges.map(function (b, i) { return h(Badge, { key: i }, b); })) : null,
        h('div', { className: 'am-room__foot' },
          props.priceFrom != null ? h('p', { className: 'am-price' }, 'from €' + props.priceFrom, h('span', null, ' / night')) : null,
          h('a', { className: 'am-link', href: props.href }, props.cta || 'View room', h(Icon, { name: 'arrow-right', size: 18 })))));
  }

  /* ---------- FactStrip ---------- */
  function FactStrip(props) {
    return h('ul', { className: cx('am-facts', props.className) },
      (props.items || []).map(function (it, i) {
        return h('li', { key: i }, it.icon && ICONS[it.icon] ? h(Icon, { name: it.icon, size: 28 }) : null, h('span', null, it.label));
      }));
  }

  /* ---------- AmenityList ---------- */
  function AmenityList(props) {
    return h('ul', { className: cx('am-amenities', props.className), style: { '--am-cols': props.columns || 2 } },
      (props.items || []).map(function (it, i) {
        return h('li', { key: i }, it.icon && ICONS[it.icon] ? h(Icon, { name: it.icon, size: 24 }) : h('i', { className: 'am-amenities__mark', 'aria-hidden': 'true' }), h('span', null, it.label));
      }));
  }

  /* ---------- PlaceCard ---------- */
  function PlaceCard(props) {
    var meta = [props.km != null ? props.km + ' km' : null, props.minutes != null ? props.minutes + ' min by car' : null].filter(Boolean).join(' · ');
    var Tag = props.href ? 'a' : 'article';
    return h(Tag, { className: cx('am-place', props.className), href: props.href, target: props.href ? '_blank' : undefined, rel: props.href ? 'noopener' : undefined },
      h(Photo, { src: props.image, alt: '', ratio: '4 / 5' }),
      h('div', { className: 'am-place__body' },
        props.category ? h('p', { className: 'am-eyebrow' }, props.category) : null,
        h('h3', { className: 'am-h3' }, props.name),
        meta ? h('p', { className: 'am-place__meta' }, meta) : null,
        props.text ? h('p', { className: 'am-place__text' }, props.text) : null));
  }

  /* ---------- Reviews ---------- */
  function Reviews(props) {
    return h('section', { className: cx('am-reviews', props.className), 'aria-label': 'Guest reviews' },
      h('div', { className: 'am-reviews__score' },
        h('p', { className: 'am-reviews__num' }, props.score),
        h('p', { className: 'am-reviews__src' }, (props.count ? props.count + ' reviews on ' : 'on ') + (props.source || 'Booking.com')),
        props.sourceHref ? h('a', { className: 'am-link', href: props.sourceHref, target: '_blank', rel: 'noopener' }, 'Read all reviews', h(Icon, { name: 'arrow-right', size: 18 })) : null),
      h('ul', { className: 'am-reviews__list' }, (props.reviews || []).map(function (r, i) {
        return h('li', { key: i },
          h('blockquote', null, h('p', null, r.text)),
          h('p', { className: 'am-reviews__who' }, [r.name, r.country, r.date].filter(Boolean).join(' · ')));
      })));
  }

  /* ---------- FaqItem ---------- */
  function FaqItem(props) {
    return h('details', { className: cx('am-faq', props.className), open: props.open },
      h('summary', null, h('span', null, props.question), h(Icon, { name: 'chevron-down', size: 20, className: 'am-faq__chev' })),
      h('div', { className: 'am-faq__a' }, props.children));
  }

  /* ---------- BookDirect ---------- */
  function BookDirect(props) {
    return h('section', { className: cx('am-direct', props.className) },
      h('div', { className: 'am-direct__in' },
        h('h2', { className: 'am-h2' }, props.title || 'Book directly with us'),
        h('ul', null, (props.items || []).map(function (it, i) { return h('li', { key: i }, h(Icon, { name: 'check', size: 20 }), h('span', null, it)); })),
        h(Button, { href: props.href, onClick: props.onBook, variant: 'dark' }, props.cta || 'Check dates')));
  }

  /* ---------- Header ---------- */
  function Header(props) {
    var m = useState(false), open = m[0], setOpen = m[1];
    var over = props.overPhoto;
    var logo = over ? (props.logoOnPhoto || props.logo) : props.logo;
    return h('header', { className: cx('am-header', over && 'am-header--over', open && 'am-header--open', props.className) },
      h('div', { className: 'am-header__in' },
        h('a', { className: 'am-header__logo', href: props.homeHref || '/' }, h('img', { src: logo, alt: props.logoAlt || 'Amour Relax Hotel' })),
        h('nav', { className: 'am-header__nav', 'aria-label': 'Main' },
          (props.nav || []).map(function (n, i) {
            return h('a', { key: i, href: n.href, 'aria-current': n.current ? 'page' : undefined }, n.label);
          })),
        h('div', { className: 'am-header__actions' },
          h(Button, { href: props.bookHref, onClick: props.onBook, variant: over ? 'on-photo' : 'primary', className: 'am-header__book' }, props.bookLabel || 'Book'),
          h('button', { type: 'button', className: 'am-header__burger', 'aria-expanded': open, 'aria-label': open ? 'Close menu' : 'Open menu', onClick: function () { setOpen(!open); } },
            h(Icon, { name: open ? 'close' : 'menu', size: 24 })))),
      open ? h('nav', { className: 'am-header__drawer', 'aria-label': 'Main' },
        (props.nav || []).concat(props.navMore || []).map(function (n, i) { return h('a', { key: i, href: n.href }, n.label); })) : null);
  }

  /* ---------- Hero ---------- */
  function Hero(props) {
    return h('section', { className: cx('am-hero', props.className) },
      h('div', { className: 'am-hero__media' }, h(Photo, { src: props.image, alt: props.imageAlt, ratio: 'auto', tone: 'dark', placeholder: props.placeholder, eager: true })),
      h('div', { className: 'am-hero__scrim', 'aria-hidden': 'true' }),
      props.header ? h('div', { className: 'am-hero__header' }, props.header) : null,
      h('div', { className: 'am-hero__content' },
        props.eyebrow ? h('p', { className: 'am-eyebrow am-eyebrow--light' }, props.eyebrow) : null,
        h('h1', { className: 'am-h1' }, props.title),
        props.text ? h('p', { className: 'am-hero__text' }, props.text) : null,
        props.children ? h('div', { className: 'am-hero__slot' }, props.children) : null));
  }

  /* ---------- Footer ---------- */
  function Footer(props) {
    return h('footer', { className: cx('am-footer', props.className) },
      props.pattern ? h('div', { className: 'am-footer__pattern', 'aria-hidden': 'true', style: { backgroundImage: 'url("' + props.pattern + '")' } }) : null,
      h('div', { className: 'am-footer__in' },
        h('div', { className: 'am-footer__brand' }, props.logo ? h('img', { src: props.logo, alt: props.logoAlt || 'Amour Relax Hotel' }) : null),
        h('div', { className: 'am-footer__cols' },
          (props.columns || []).map(function (col, i) {
            return h('div', { key: i, className: 'am-footer__col' },
              h('p', { className: 'am-eyebrow' }, col.title),
              h('ul', null, col.links.map(function (l, j) { return h('li', { key: j }, l.href ? h('a', { href: l.href }, l.label) : h('span', null, l.label)); })));
          })),
        props.social && props.social.length ? h('ul', { className: 'am-footer__social' }, props.social.map(function (s, i) {
          return h('li', { key: i }, h('a', { href: s.href, target: '_blank', rel: 'noopener' }, s.label));
        })) : null,
        h('div', { className: 'am-footer__legal' },
          h('p', null, (props.legal || []).join(' · ')),
          props.legalLinks ? h('ul', null, props.legalLinks.map(function (l, i) { return h('li', { key: i }, h('a', { href: l.href, onClick: l.onClick }, l.label)); })) : null)));
  }

  /* ---------- CookieBanner ---------- */
  function CookieBanner(props) {
    return h('div', { className: cx('am-cookie', props.fixed === false && 'am-cookie--static', props.className), role: 'dialog', 'aria-label': 'Cookie settings' },
      h('p', null, props.text || 'We use cookies to understand which pages help guests plan their stay. Analytics and ads stay off until you agree.',
        props.policyHref ? h('a', { href: props.policyHref }, ' Cookie policy') : null),
      h('div', { className: 'am-cookie__actions' },
        h(Button, { variant: 'ghost', onClick: props.onSettings }, 'Settings'),
        h(Button, { variant: 'ghost', onClick: props.onReject }, 'Reject all'),
        h(Button, { variant: 'primary', onClick: props.onAccept }, 'Accept all')));
  }

  var api = {
    Icon: Icon, Button: Button, Badge: Badge, PhotoPlaceholder: PhotoPlaceholder, ArchFrame: ArchFrame,
    SectionIntro: SectionIntro, DateSearch: DateSearch, MobileBookBar: MobileBookBar, RoomCard: RoomCard,
    FactStrip: FactStrip, AmenityList: AmenityList, PlaceCard: PlaceCard, Reviews: Reviews, FaqItem: FaqItem,
    BookDirect: BookDirect, Header: Header, Hero: Hero, Footer: Footer, CookieBanner: CookieBanner
  };
  window.Amour = window.Amour || {};
  Object.assign(window.Amour, api);
})();
