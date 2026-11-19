import json, re, sys
S = '/tmp/claude-0/-home-claude/43f3c1c4-21ec-59cb-94ba-ae08e0d42e56/scratchpad/'
T = open(S + 'scripts/room_template.html').read()
B = lambda i: '/_blob/' + i
AMOUR_FOOTER = '''<div class="footer-live">
  <div class="olive-field" aria-hidden="true">
    <sc-for list="{{olives}}" as="o" hint-placeholder-count="96"><img src="/_blob/136a761c27b957778f5fa1fdbbe7982f" alt="" style="animation-delay: {{o.delay}};"></sc-for>
  </div>
  <div class="olive-field olive-lens" aria-hidden="true">
    <sc-for list="{{olives}}" as="o" hint-placeholder-count="96"><img src="/_blob/136a761c27b957778f5fa1fdbbe7982f" alt=""></sc-for>
  </div>
  <x-import component-from-global-scope="Amour.Footer" logo="/_blob/d363bf89f0c7b41d1d2e94590a1f7092" columns="{{footerCols}}" social="{{social}}" legal="{{legal}}" legal-links="{{legalLinks}}"></x-import>
  <img class="footer-word" src="/_blob/2ff4e86eb203fa11650a8bf02bd78349" alt="" aria-hidden="true">
</div>'''
AMOUR_FOOT_DATA = {
  'footerCols': [
    {'title': 'Stay', 'links': [{'label': 'Rooms', 'href': 'Rooms.dc.html'}, {'label': 'Villa Eudora', 'href': 'Villa-v2.dc.html'}, {'label': 'Court and pool', 'href': 'Estate.dc.html'}]},
    {'title': 'Discover', 'links': [{'label': 'Crete guide', 'href': 'Guide.dc.html'}, {'label': 'About us', 'href': 'About.dc.html'}, {'label': 'FAQ', 'href': 'FAQ.dc.html'}]},
    {'title': 'Contact', 'links': [{'label': '[Phone]'}, {'label': '[Email]'}, {'label': 'Almyrida, Crete', 'href': 'Contact.dc.html'}]}],
  'social': [{'label': 'Instagram', 'href': '#top'}, {'label': 'Facebook', 'href': '#top'}, {'label': 'TikTok', 'href': '#top'}],
  'legal': ['[Company name] · [Address]', 'MHTE [MHTE number]'],
  'legalLinks': [{'label': 'Privacy', 'href': 'Legal.dc.html#privacy'}, {'label': 'Cookies', 'href': 'Legal.dc.html#cookies'}, {'label': 'Terms', 'href': 'Legal.dc.html#terms'}]}
CARDS = {
  'standard': {'name': 'Standard', 'href': 'Room-standard.dc.html', 'src': B('2b59c90b2750bdb3ac2476355da8d974'), 'alt': 'Standard Queen Room with fresh linen', 'c1': 'about 20 m²', 'c2': '2 guests'},
  'deluxe': {'name': 'Deluxe', 'href': 'Room-deluxe.dc.html', 'src': B('fd095be6630808380801bf1a11f4b2b1'), 'alt': 'Deluxe Queen Room with a sitting corner and a queen bed', 'c1': '32 m²', 'c2': 'pool view'},
  'superior': {'name': 'Superior', 'href': 'Room-superior.dc.html', 'src': B('0b0dc5985e0129c7969b7aad231dd75f'), 'alt': 'Guests on a Superior Room terrace looking over the bay', 'c1': 'sea view', 'c2': '2 guests'},
  'villa': {'name': 'Villa Eudora', 'href': 'Villa-v2.dc.html', 'src': B('2aac80822b69d3836096e30d8c7de6da'), 'alt': 'The villa pool at dusk', 'c1': '5 bedrooms', 'c2': 'own pool'}}
STAY = [
  {'icon': 'calendar', 'k': 'Check-in', 'v': 'From 14:00'},
  {'icon': 'calendar', 'k': 'Check-out', 'v': 'By 11:00'},
  {'icon': 'breakfast-basket', 'k': 'Breakfast basket', 'v': '€15 for two, order the day before'},
  {'icon': 'tennis', 'k': 'Tennis court', 'v': 'Free, lit in the evening, rackets to borrow'},
  {'icon': 'pool', 'k': 'Pool and sun terrace', 'v': 'Free, [hours]'}]
PINS = [['Amour Relax Hotel', 24.172, 35.447, 'row-reverse', True], ['Almyrida beach · 1.2 km', 24.176, 35.451, 'row', False],
        ['Chania · 40 min', 24.018, 35.513, 'row', False], ['Chania airport · [… min]', 24.149, 35.532, 'row-reverse', False],
        ['Georgioupoli beach · [… min]', 24.259, 35.362, 'row', False]]
BASE = {'deep': 'var(--deep)', 'deepRgb': [92, 30, 28], 'bgRgb': [242, 234, 219], 'blank': B('136a761c27b957778f5fa1fdbbe7982f'),
        'detailsTitle': 'Room details', 'descLabel': 'The room', 'cue': 'Scroll to see the room', 'price': 'From €[price] a night',
        'mapAlt': 'Relief map of Apokoronas: the hotel above Almyrida bay, Chania and the airport to the north-west', 'stay': STAY, 'pins': PINS, 'footer': AMOUR_FOOT_DATA}
def F(icon, k, v): return {'icon': icon, 'k': k, 'v': v}
ROOMS = {
 'standard': dict(title='Standard', kicker='Queen room · for two', meta='about 20 m² · queen bed · kitchenette · terrace',
   desc='A compact room for two with a queen bed, a kitchenette for your morning coffee and a terrace for the evening. Bright, simple and quiet, a short walk from the pool and the court.',
   photos=[{'src': B('2b59c90b2750bdb3ac2476355da8d974'), 'ar': '3 / 2', 'cap': 'Bedroom', 'alt': 'The queen bed with fresh linen'},
           {'src': B('80e77fe1010b549279e89e9a35d5b698'), 'ar': '3 / 4', 'cap': 'Bedroom', 'alt': 'A bright room with a queen bed and a sitting corner'},
           {'src': B('b30babdbb27abc422be7864666db529c'), 'ar': '3 / 2', 'cap': 'Fresh linen', 'alt': 'Crisp white linen on the bed'},
           {'src': B('d8dddd38954583f628f74d74f3662916'), 'ar': '3 / 4', 'cap': 'The whole room', 'alt': 'A bright room with a sofa, a coffee table and the bed by the wall'},
           {'src': B('b1c196ccffe7ccdfc16a183b6e2ef519'), 'ar': '3 / 2', 'cap': 'Terrace', 'alt': 'Two guests at a table on a shaded terrace above the olive groves'}],
   feats=[F('area', 'Room size', 'about 20 m²'), F('guests', 'Guests', '2 · recommended 12+'), F('bed', 'Bed', 'Queen, [160 × 200 cm]'),
          F('bath', 'Bathroom', '[Shower]'), F('kitchen', 'Kitchen', 'Kitchenette: [hob, fridge, kettle]'), F('terrace', 'Outside', 'Private terrace'),
          F('sea-view', 'View', '[Garden and olive groves]'), F('air-conditioning', 'Air conditioning', '[Yes]'), F('wifi', 'Wi-Fi', '[Free]'),
          F('key', 'Linen and towels', '[Changed every … days]'), F('parking', 'Parking', '[Free, on site]'), F('pets', 'Pets', 'Welcome, [conditions]')],
   others=['deluxe', 'superior', 'villa']),
 'deluxe': dict(title='Deluxe', kicker='Queen room · pool view', meta='32 m² · queen bed · kitchen · sitting corner · terrace',
   desc='More room to spread out: 32 m² with a queen bed, a sitting corner with a sofa, a full kitchen and a terrace looking over the pool. Made for long stays and slow mornings.',
   photos=[{'src': B('1c8b6c14e093b7b1da7fc5f2887bcb7c'), 'ar': '3 / 2', 'cap': 'Bedroom and sitting corner', 'alt': 'Queen bed, sitting corner and the door to the terrace'},
           {'src': B('fd095be6630808380801bf1a11f4b2b1'), 'ar': '3 / 4', 'cap': 'The whole room', 'alt': 'The room from the door: sofa, coffee table and the bed by the window'},
           {'src': B('50a48adb1ec985cbfc091cd0c9e9dca1'), 'ar': '3 / 2', 'cap': 'Coffee on the terrace', 'alt': 'Two guests with coffee at a table on the terrace, the hills behind'},
           {'src': B('479bd1e535be68edf0e0df2d492f72c0'), 'ar': '3 / 4', 'cap': 'Sitting corner', 'alt': 'Two guests reading on the sofa under a lamp'},
           {'src': B('3e03be045ef749e0d13cdb0ef58aa776'), 'ar': '3 / 4', 'cap': 'The pool, steps from your terrace', 'alt': 'The pool lit at night by the limestone wall'}],
   feats=[F('area', 'Room size', '32 m²'), F('guests', 'Guests', '2 · recommended 12+'), F('bed', 'Bed', 'Queen, [160 × 200 cm]'),
          F('check', 'Sitting corner', 'Sofa and coffee table'), F('bath', 'Bathroom', '[Bath or shower]'), F('kitchen', 'Kitchen', 'Full kitchen: [hob, fridge, kettle, coffee maker]'),
          F('terrace', 'Outside', 'Private terrace'), F('pool', 'View', 'Pool view'), F('air-conditioning', 'Air conditioning', '[Yes]'), F('wifi', 'Wi-Fi', '[Free]'),
          F('key', 'Linen and towels', '[Changed every … days]'), F('parking', 'Parking', '[Free, on site]'), F('pets', 'Pets', 'Welcome, [conditions]')],
   others=['standard', 'superior', 'villa']),
 'superior': dict(title='Superior', kicker='Queen room · sea view', meta='[m²] · queen bed · kitchen · terrace with a sea view',
   desc='Our rooms with a view of the sea. The terrace looks over the olive groves to the bay of Almyrida, with a table for two for morning coffee and an evening glass of wine. Inside: a queen bed and a kitchen.',
   photos=[{'src': B('c52dc0b422d93e5648121425daaa19a4'), 'ar': '3 / 2', 'cap': 'Terrace · sea view', 'alt': 'The terrace of a Superior Room looking out to the sea'},
           {'src': B('0b0dc5985e0129c7969b7aad231dd75f'), 'ar': '3 / 4', 'cap': 'Golden hour on the terrace', 'alt': 'Two guests with wine on the terrace above the bay'},
           {'src': B('6f164c147ab703fd3ec7aa63bd120a57'), 'ar': '3 / 2', 'cap': 'Coffee with a view of the bay', 'alt': 'Two guests having coffee on the terrace with the bay behind them'},
           {'src': B('32e28806b605d17f5d198c1110f270d3'), 'ar': '3 / 4', 'cap': 'Bedroom', 'alt': 'A guest on the queen bed in a bright room'},
           {'src': B('16175242e07136a77226dd8cb574b487'), 'ar': '3 / 4', 'cap': 'Evening on the terrace', 'alt': 'Two guests looking out over the olive groves from the terrace'}],
   feats=[F('area', 'Room size', '[m²]'), F('guests', 'Guests', '2 · recommended 12+'), F('bed', 'Bed', 'Queen, [160 × 200 cm]'),
          F('bath', 'Bathroom', '[Shower]'), F('kitchen', 'Kitchen', 'Kitchen: [hob, fridge, kettle]'), F('terrace', 'Outside', 'Private terrace with a table for two'),
          F('sea-view', 'View', 'Sea view over the bay'), F('air-conditioning', 'Air conditioning', '[Yes]'), F('wifi', 'Wi-Fi', '[Free]'),
          F('key', 'Linen and towels', '[Changed every … days]'), F('parking', 'Parking', '[Free, on site]'), F('pets', 'Pets', 'Welcome, [conditions]')],
   others=['standard', 'deluxe', 'villa'])}
for key, r in ROOMS.items():
    D = dict(BASE); D.update({k: v for k, v in r.items() if k != 'others'})
    D['others'] = [CARDS[o] for o in r['others']]
    html = (T.replace('__TITLE__', r['title'] + ' Queen Room').replace('__THEME__', 'amour').replace('__HOME__', 'Main-v2.dc.html')
             .replace('__BRAND__', 'Amour Relax Hotel').replace('__LOGO__', B('2901163e35c220801b42aced9684d164')).replace('__LOGOH__', '76px').replace('__LOGOM__', '-16px 0')
             .replace('__EXTRA__', '').replace('__FOOTER__', AMOUR_FOOTER).replace('__PREVIEWH__', '6600')
             .replace('__DATA__', json.dumps(D, ensure_ascii=False)))
    open(S + 'canvas/project/Room-' + key + '.dc.html', 'w').write(html)
    print(key, len(html))
