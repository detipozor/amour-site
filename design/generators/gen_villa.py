import json, re, importlib.util
S = '/tmp/claude-0/-home-claude/43f3c1c4-21ec-59cb-94ba-ae08e0d42e56/scratchpad/'
spec = importlib.util.spec_from_file_location('gr', S + 'scripts/gen_rooms.py')
T = open(S + 'scripts/room_template.html').read()
B = lambda i: '/_blob/' + i
EUDORA_FOOTER = '''<div class="footer-live" data-theme="eudora">
  <div class="olive-field" aria-hidden="true" style="display: block; padding: 0;"><img src="/_blob/5df094c0a412b92d724a2c1bba79ae9d" alt="" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.16; animation: none;"></div>
  <div class="olive-field olive-lens" aria-hidden="true" style="display: block; padding: 0;"><img src="/_blob/5df094c0a412b92d724a2c1bba79ae9d" alt="" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.85;"></div>
  <x-import component-from-global-scope="Amour.Footer" logo="/_blob/eb8582559f2d00dae3e431652377f8c0" columns="{{footerCols}}" social="{{social}}" legal="{{legal}}" legal-links="{{legalLinks}}"></x-import>
  <img class="footer-word" src="/_blob/e0ac7361b358ec2023518a69d88f0026" alt="" aria-hidden="true">
</div>'''
FOOT = {
  'footerCols': [
    {'title': 'The villa', 'links': [{'label': 'The house', 'href': 'Villa-v2.dc.html'}, {'label': 'Weddings and gatherings', 'href': '#events'}, {'label': 'Check dates', 'href': '#book'}]},
    {'title': 'Around', 'links': [{'label': 'Court and pool at the hotel', 'href': 'Estate.dc.html'}, {'label': 'Crete guide', 'href': 'Guide.dc.html'}, {'label': 'Questions', 'href': 'FAQ.dc.html'}, {'label': 'Amour Relax Hotel', 'href': 'Main-v2.dc.html'}]},
    {'title': 'Contact', 'links': [{'label': '[Phone]'}, {'label': '[Email]'}, {'label': 'Almyrida, Crete', 'href': 'Contact.dc.html'}]}],
  'social': [{'label': '[Villa Instagram]', 'href': '#top'}, {'label': 'Booking.com · 9.5', 'href': '#top'}],
  'legal': ['Villa Eudora · [Owner company] · [Address]', 'Villa licence [MHTE or AMA number]'],
  'legalLinks': [{'label': 'Privacy', 'href': 'Legal.dc.html#privacy'}, {'label': 'Cookies', 'href': 'Legal.dc.html#cookies'}, {'label': 'Terms', 'href': 'Legal.dc.html#terms'}]}
def F(icon, k, v): return {'icon': icon, 'k': k, 'v': v}
BR = [('2b59c90b2750bdb3ac2476355da8d974', 'A queen bed with fresh linen'), ('80e77fe1010b549279e89e9a35d5b698', 'A bright bedroom with a sitting corner'), ('1c8b6c14e093b7b1da7fc5f2887bcb7c', 'A bedroom with a door to the terrace'), ('fd095be6630808380801bf1a11f4b2b1', 'A bedroom with a sofa by the window'), ('32e28806b605d17f5d198c1110f270d3', 'A guest on the bed in a bright room')]
D = {
  'deep': 'var(--deep)', 'deepRgb': [31, 58, 71], 'bgRgb': [242, 234, 219], 'blank': B('136a761c27b957778f5fa1fdbbe7982f'),
  'title': 'Eudora', 'kicker': 'Villa · Almyrida', 'meta': '5 bedrooms · up to [11] guests · private pool · 256 m²', 'cue': 'Scroll to see the house',
  'detailsTitle': 'The house', 'descLabel': 'The villa', 'price': 'From €[price] a night for the whole villa',
  'desc': 'A stone villa on the hill above Almyrida, made for a family or friends travelling together. Five bedrooms, a private pool and a long terrace with a table that seats everyone, looking over the bay. The sea is about 250 m away, and our hotel’s court and pool are a four-minute drive.',
  'mapAlt': 'Relief map of Apokoronas: the villa and the hotel above Almyrida bay, Chania and the airport to the north-west',
  'photos': [
    {'src': B('5a3bd934ace28fc571185ce354dcf308'), 'ar': '3 / 4', 'cap': 'The long table on the terrace', 'alt': 'A long table set on the villa terrace above the bay'},
    {'src': B('8c07121482921ffe3be6f861ce5e82fe'), 'ar': '3 / 2', 'cap': 'Terrace above the bay', 'alt': 'Friends with wine on the terrace, the bay and the mountains behind'},
    {'src': B('2aac80822b69d3836096e30d8c7de6da'), 'ar': '3 / 4', 'cap': 'Private pool at dusk', 'alt': 'Two guests by the villa pool at dusk'},
    {'src': B('6a5a8a6835d7e53b392e87cee9ce7606'), 'ar': '3 / 2', 'cap': 'Friends on the terrace', 'alt': 'Four friends with wine on the villa terrace, the bay behind'},
    {'src': B('9989fe9c7d128b11170156869f218802'), 'ar': '3 / 4', 'cap': 'Evenings with friends', 'alt': 'Guests raising glasses on the villa terrace at dusk'},
    {'src': B('0ea0b22b4b66cdb9570c69b36d15f3c2'), 'ar': '3 / 2', 'cap': 'Dinner at sunset', 'alt': 'Friends at dinner on the terrace with the bay behind'},
    {'src': B('7c609c20a8007c37f999624541c13116'), 'ar': '3 / 4', 'cap': 'The table is set', 'alt': 'A bottle of Cretan red and a glass on the dinner table'}],
  'feats': [F('area', 'House size', '256 m²'), F('villa', 'Bedrooms', '5'), F('bath', 'Bathrooms', '[…]'), F('guests', 'Guests', 'Up to [11], any age'),
            F('bed', 'Beds', '[… double, … single]'), F('pool', 'Pool', 'Private, [size]'), F('kitchen', 'Kitchen', 'Full kitchen, [dishwasher, oven]'),
            F('terrace', 'Outside', 'Terrace with a table for [12]'), F('sea-view', 'View', 'Over the bay'), F('beach', 'Sea', 'About 250 m'),
            F('air-conditioning', 'Air conditioning', '[In every bedroom]'), F('wifi', 'Wi-Fi', '[Free]'), F('parking', 'Parking', '[Free, on site]'),
            F('pets', 'Pets', '[On request]'), F('tennis', 'Hotel court and pool', 'Free, 4 min by car')],
  'stay': [F('calendar', 'Check-in', '[From 16:00]'), F('calendar', 'Check-out', '[By 10:00]'), F('key', 'Minimum stay', '[… nights]'),
           F('check', 'Linen and cleaning', '[Included]'), F('breakfast-basket', 'Breakfast basket', '[On request]'), F('guests', 'Gatherings', 'Welcome, see below')],
  'pins': [['Villa Eudora', 24.180, 35.454, 'row', True], ['Amour Relax Hotel · 4 min', 24.172, 35.447, 'row-reverse', False],
           ['Chania · 40 min', 24.018, 35.513, 'row', False], ['Chania airport · [… min]', 24.149, 35.532, 'row-reverse', False],
           ['Georgioupoli beach · [… min]', 24.259, 35.362, 'row', False]],
  'others': [
    {'name': 'Standard', 'href': 'Room-standard.dc.html', 'src': B('2b59c90b2750bdb3ac2476355da8d974'), 'alt': 'Standard Queen Room with fresh linen', 'c1': 'about 20 m²', 'c2': '2 guests'},
    {'name': 'Deluxe', 'href': 'Room-deluxe.dc.html', 'src': B('fd095be6630808380801bf1a11f4b2b1'), 'alt': 'Deluxe Queen Room with a sitting corner', 'c1': '32 m²', 'c2': 'pool view'},
    {'name': 'Superior', 'href': 'Room-superior.dc.html', 'src': B('0b0dc5985e0129c7969b7aad231dd75f'), 'alt': 'Superior Queen Room terrace over the bay', 'c1': 'sea view', 'c2': '2 guests'}],
  # stand-in photos from the hotel rooms until the villa bedrooms are shot
  'bedrooms': [{'n': 'Bedroom ' + str(i + 1), 't': t, 'd': '%.2fs' % (i * 0.08), 'src': '/_blob/' + BR[i][0], 'alt': BR[i][1]} for i, t in enumerate(
      ['[King bed] · [own bathroom] · [sea view]', '[King bed] · [own bathroom]', '[Double bed] · [balcony]', '[Two singles]', '[Two singles] · [garden side]'])],
  'floors': [{'k': 'Ground floor', 'v': '[Living room], kitchen and dining room opening onto the terrace; [bedroom 1 with its own bathroom].'},
             {'k': 'Upper floor', 'v': '[Bedrooms 2 to 5], [… bathrooms], [balconies with a sea view].'},
             {'k': 'Outside', 'v': 'Private pool with sunbeds, the long table [under a pergola], [barbecue], olive trees and parking.'}],
  'near': [['Amour Relax Hotel', '4 min by car'], ['Almyrida beach', '[… min] on foot'], ['Chania old town', '40 min'], ['Chania airport', '[… min]']],
  'events': [{'k': 'Seated dinner', 'v': 'Up to [..] guests'}, {'k': 'Music', 'v': 'Until [..]'}, {'k': 'Partners', 'v': '[Catering, flowers, photographer]'}],
  'seasons': [{'s': 'Spring', 'm': 'March to May', 'p': 'from €[price]', 'n': '[…] nights minimum', 'd': '0s'},
              {'s': 'Summer', 'm': 'June to August', 'p': 'from €[price]', 'n': '[…] nights minimum', 'd': '.08s'},
              {'s': 'Autumn', 'm': 'September and October', 'p': 'from €[price]', 'n': '[…] nights minimum', 'd': '.16s'}],
  'footer': FOOT}
EXTRA_A = '''<section ref="{{setExtra}}" style="padding: 0 var(--gutter) var(--sec-m);">
  <div style="max-width: var(--container); margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 32px; margin-bottom: 48px;">
      <h2 class="giant enter" style="font-size: 96px; opacity: {{extraIn.o}}; transform: translateY({{extraIn.y}}); filter: {{extraIn.b}};">Five bedrooms</h2>
      <p style="margin: 0 0 8px; max-width: 400px; color: var(--text-2);">Room for up to [11] guests: [two bedrooms with their own bathrooms] and three more for children or friends.</p>
    </div>
    <div class="beds" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px;">
      <sc-for list="{{d.bedrooms}}" as="br" hint-placeholder-count="5">
        <div class="enter" style="display: flex; flex-direction: column; gap: 14px; opacity: {{extraIn.o}}; transform: translateY({{extraIn.y}}); filter: {{extraIn.b}}; transition-delay: {{br.d}};">
          <div style="position: relative; aspect-ratio: 3 / 4; border-radius: 50% 50% 24px 24px / 30% 30% 24px 24px; overflow: hidden; background: var(--c-foam); display: flex; align-items: center; justify-content: center;">
            <img src="{{br.src}}" alt="{{br.alt}}" style="position: absolute; inset: 0; display: block; width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div>
            <p style="margin: 0; font-family: var(--font-head); font-size: 20px; letter-spacing: -0.01em; font-weight: 500;">{{br.n}}</p>
            <p style="margin: 4px 0 0; font-size: 15px; line-height: 1.45; color: var(--text-2);">{{br.t}}</p>
          </div>
        </div>
      </sc-for>
    </div>
  </div>
</section>
<!--SPLIT-->
<section style="padding: var(--sec-m) var(--gutter) var(--sec-m);">
  <div style="max-width: var(--container); margin: 0 auto; display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 32px; align-items: center;">
    <figure style="grid-column: 1 / span 7; margin: 0; display: flex; flex-direction: column; gap: 14px;">
      <div style="border-radius: 24px; overflow: hidden;"><svg viewBox="0 0 720 520" role="img" aria-label="Schematic section of the villa: ground floor, upper floor and the garden with the pool" style="display: block; width: 100%; height: auto;">
        <rect x="0" y="0" width="720" height="520" fill="#e6eeed"></rect>
        <g fill="none" stroke="#c3d3d2" stroke-width="1.4"><path d="M0 470 Q30 464 60 470 T120 470 T180 470 T240 470 T300 470 T360 470 T420 470 T480 470 T540 470 T600 470 T660 470 T720 470"></path><path d="M0 490 Q30 484 60 490 T120 490 T180 490 T240 490 T300 490 T360 490 T420 490 T480 490 T540 490 T600 490 T660 490 T720 490"></path></g>
        <rect x="0" y="430" width="720" height="22" fill="#d9c9ab"></rect>
        <rect x="440" y="300" width="270" height="130" rx="10" style="fill: var(--accent-soft); opacity: {{fz.o}}; transition: opacity .4s;"></rect>
        <rect x="474" y="404" width="210" height="26" rx="3" fill="#6f98a8"></rect><path d="M486 416h40M552 418h50M630 414h34" stroke="#cfe0e4" stroke-width="2" stroke-linecap="round"></path>
        <path d="M478 372h112" stroke="#1f3a47" stroke-width="5" stroke-linecap="round"></path><g fill="#1f3a47"><circle cx="490" cy="384" r="4"></circle><circle cx="512" cy="384" r="4"></circle><circle cx="534" cy="384" r="4"></circle><circle cx="556" cy="384" r="4"></circle><circle cx="578" cy="384" r="4"></circle></g><path d="M484 376v28M584 376v28" stroke="#1f3a47" stroke-width="3"></path>
        <g><path d="M646 430v-54" stroke="#6b5a45" stroke-width="5"></path><ellipse cx="646" cy="360" rx="34" ry="26" fill="#8a9a6a"></ellipse><ellipse cx="628" cy="350" rx="18" ry="14" fill="#7b8c5c"></ellipse></g>
        <g><path d="M46 430v-64" stroke="#6b5a45" stroke-width="5"></path><ellipse cx="46" cy="352" rx="36" ry="28" fill="#8a9a6a"></ellipse><ellipse cx="62" cy="342" rx="18" ry="14" fill="#7b8c5c"></ellipse></g>
        <rect x="96" y="290" width="330" height="140" style="fill: {{fz.g}}; stroke: #1f3a47; stroke-width: 2.5; transition: fill .4s;"></rect>
        <rect x="132" y="170" width="258" height="120" style="fill: {{fz.u}}; stroke: #1f3a47; stroke-width: 2.5; transition: fill .4s;"></rect>
        <path d="M122 170h278M86 290h350" stroke="#1f3a47" stroke-width="5" stroke-linecap="square"></path>
        <g fill="#2d5264"><path d="M158 268v-38a14 14 0 0 1 28 0v38z"></path><path d="M212 268v-38a14 14 0 0 1 28 0v38z"></path><path d="M282 268v-38a14 14 0 0 1 28 0v38z"></path><path d="M336 268v-38a14 14 0 0 1 28 0v38z"></path>
          <path d="M126 410v-56a16 16 0 0 1 32 0v56z"></path><path d="M188 430v-80a24 24 0 0 1 48 0v80z"></path><path d="M268 410v-56a16 16 0 0 1 32 0v56z"></path><path d="M330 410v-56a16 16 0 0 1 32 0v56z"></path></g>
        <text x="261" y="203" text-anchor="middle" style="font: 500 13px var(--font-body); letter-spacing: .2em; fill: {{fz.ul}}; transition: fill .4s;">UPPER FLOOR</text>
        <text x="261" y="322" text-anchor="middle" style="font: 500 13px var(--font-body); letter-spacing: .2em; fill: {{fz.gl}}; transition: fill .4s;">GROUND FLOOR</text>
        <text x="575" y="330" text-anchor="middle" style="font: 500 13px var(--font-body); letter-spacing: .2em; fill: {{fz.ol}}; transition: fill .4s;">OUTSIDE</text>
      </svg></div>
      <figcaption style="font-size: 14px; color: var(--text-2);">[Schematic. The floor plan from the architect comes here.]</figcaption>
    </figure>
    <div style="grid-column: 9 / span 4; display: flex; flex-direction: column; gap: 24px;">
      <p class="lbl" style="color: var(--accent);">Floor by floor</p>
      <h2 style="margin: 0; font-family: var(--font-head); font-weight: 400; font-size: 44px; line-height: 1.1; letter-spacing: -0.025em;">How the house works</h2>
      <div style="display: flex; flex-direction: column;">
        <sc-for list="{{floorsUI}}" as="fl" hint-placeholder-count="3">
          <button type="button" onMouseEnter="{{fl.enter}}" onFocus="{{fl.enter}}" onClick="{{fl.enter}}" aria-pressed="{{fl.on}}" style="display: flex; flex-direction: column; gap: 6px; padding: 14px 0; margin: 0; border: 0; border-left: 0; background: none; text-align: left; font: inherit; color: var(--text); cursor: pointer; transition: border-color .3s;">
            <span class="lbl" style="color: {{fl.kc}}; transition: color .3s;">{{fl.k}}</span>
            <span style="font-size: 17px; line-height: 1.5;">{{fl.v}}</span>
          </button>
        </sc-for>
      </div>
    </div>
  </div>
</section>

<section id="events" data-cta data-reveal="events" style="position: relative; margin: 0; min-height: {{screenH}}; overflow: hidden; background: var(--deep); color: var(--c-limestone); display: flex; align-items: center;">
  <img src="/_blob/167426f58d62fe435700966790505f2a" alt="The bay at dusk from the rocks below the villa" style="position: absolute; inset: 0; display: block; width: 100%; height: 100%; object-fit: cover;">
  <div aria-hidden="true" style="position: absolute; inset: 0; background: linear-gradient(90deg, rgba(18, 34, 43, 0.82) 0%, rgba(18, 34, 43, 0.5) 45%, rgba(18, 34, 43, 0.08) 100%);"></div>
  <div style="position: relative; z-index: 1; width: 100%; max-width: var(--container); margin: 0 auto; padding: 120px var(--gutter); box-sizing: content-box; display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 32px; align-items: center;">
    <div style="grid-column: 1 / span 7; display: flex; flex-direction: column; gap: 28px;">
      <p class="lbl rv-fade" style="color: var(--c-foam);">Weddings and gatherings</p>
      <h2 style="margin: 0; font-family: var(--font-head); font-weight: 400; font-size: min(4.4vw, 8.4vh, 66px); line-height: 1.04; max-width: 760px; letter-spacing: -0.03em;"><span class="ln"><span>Long tables,</span></span><span class="ln"><span>long evenings</span></span></h2>
      <p class="rv-fade" style="margin: 0; max-width: 460px; font-size: 18px; color: rgba(242, 234, 219, 0.88);">Small weddings, birthdays and family reunions: up to [..] guests at one table above the bay.</p>
      <div class="rv-fade" style="margin-top: 4px; display: flex; flex-wrap: wrap; align-items: center; gap: 16px 24px;"><a class="am-btn" href="#events" style="background: #f2eadb; color: #1f3a47; border-color: #f2eadb;">Plan a gathering</a><a href="#events" style="color: var(--c-limestone); font-size: 15px; font-weight: 500;">Write on WhatsApp</a></div>
    </div>
    <figure style="grid-column: 9 / span 4; margin: 0; display: flex; flex-direction: column; gap: 12px;">
      <div class="rv-arch" style="aspect-ratio: 3 / 4; border-radius: 999px 999px 20px 20px; overflow: hidden; box-shadow: 0 30px 60px rgba(8, 16, 20, 0.4);"><img src="/_blob/0ea0b22b4b66cdb9570c69b36d15f3c2" alt="Friends at dinner on the villa terrace with the bay behind" style="display: block; width: 100%; height: 100%; object-fit: cover; object-position: 50% 75%;"></div>
      <figcaption class="rv-fade" style="font-size: 14px; color: rgba(242, 234, 219, 0.85);">Dinner at sunset on the terrace</figcaption>
    </figure>
  </div>
</section>
'''
html = (T.replace('__TITLE__', 'Villa Eudora').replace('__THEME__', 'eudora').replace('__HOME__', 'Villa-v2.dc.html')
         .replace('__BRAND__', 'Eudora · Villa Almyrida').replace('__LOGO__', B('eb8582559f2d00dae3e431652377f8c0')).replace('__LOGOH__', '88px').replace('__LOGOM__', '-18px 0')
         .replace('__EXTRA__', EXTRA_A).replace('__FOOTER__', EUDORA_FOOTER)
         .replace('__DATA__', json.dumps(D, ensure_ascii=False)))
html = html.replace("Room details", "The house").replace('href="Rooms.dc.html" style="font-size: 15px; font-weight: 500;">All rooms and the villa →', 'href="Rooms.dc.html" style="font-size: 15px; font-weight: 500;">All rooms at the hotel →')
html = html.replace('Other ways to stay</h2>', 'Or stay at the hotel</h2>')
# Split for the canvas (a board shows at most 8000 px): part 1 = stage, details, bedrooms; part 2 = the rest
head, rest = html.split('<!--SPLIT-->')
script = html[html.index('<script type="text/x-dc"'):]
wrap_open = html[:html.index('<section ref="{{setStage}}"')]
part1 = head + '\n</div>\n\n</div>\n</x-dc>\n' + script.replace('__PREVIEWH__', '6400')
part2 = wrap_open + rest.replace('__PREVIEWH__', '5600')
part1 = part1.replace('__PREVIEWH__', '6400')
open(S + 'canvas/project/Villa-v2.dc.html', 'w').write(part1)
open(S + 'canvas/project/Villa-v2-b.dc.html', 'w').write(part2)
print(len(part1), len(part2), part1.count('<section'), part2.count('<section'))
