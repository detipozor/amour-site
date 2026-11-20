/**
 * The map on the home page (board Main-v2-b, renderVals: "Home map", gi, gPins, gc, gTabs).
 * Pinned for 2300 px (gp from 0 to 1):
 *   gp < 0.14  the opening: photos of places, then they part and the camera drops through the clouds;
 *   gp < 0.30  view 0, the whole island;  < 0.52  view 1, the hotel;  < 0.72  view 2, the villa;  else  view 3, day trips.
 * The camera moves between views with a CSS transition; between the island and a place it flies through clouds.
 * Day trips: pointing at, focusing or tapping a place draws the road there and tells about it in the card.
 * Phone: closer views and short labels; tablets: short labels, the places fitted beside the card. Not pinned with reduced motion or on short screens: the tabs switch views.
 */

import { onScroll, clamp, smooth, reduceMotion } from '@amour/motion';

const map = document.querySelector( '[data-map]' );

if ( map ) {
	const data = JSON.parse( map.querySelector( '[data-map-data]' ).textContent );
	const frame = map.querySelector( '[data-map-frame]' );
	const canvas = map.querySelector( '[data-map-canvas]' );
	const intro = map.querySelector( '[data-map-intro]' );
	const figs = [ ...intro.querySelectorAll( '[data-fig]' ) ];
	const introClouds = [ ...intro.querySelectorAll( '.am-clouds > span' ) ];
	const introText = intro.querySelector( '.am-map__intro-text' );
	const flight = map.querySelector( '[data-map-clouds]' );
	const card = map.querySelector( '[data-map-card]' );
	const cardBody = card.querySelector( '[data-card-body]' );
	const cardField = ( name ) => card.querySelector( `[data-card="${ name }"]` );
	const cardPics = [ ...card.querySelectorAll( '[data-card-pics] img' ) ];
	const tabs = [ ...map.querySelectorAll( '.am-map__tab' ) ];
	const pins = [ ...map.querySelectorAll( '.am-pin' ) ];
	const villa = pins.find( ( pin ) => pin.dataset.kind === 'villa' );
	const roads = [ ...map.querySelectorAll( '.am-road-on' ) ];

	// Camera views: focus point (% of the map) and zoom; a phone looks closer and a little higher.
	const VIEWS = [ { fx: 44, fy: 52, z: 1.18 }, { fx: 25.73, fy: 33.62, z: 7 }, { fx: 26.0, fy: 32.95, z: 7 }, { fx: 35, fy: 36, z: 2.1 } ];
	const VIEWS_PHONE = [ { fx: 33, fy: 38, z: 2.1 }, { fx: 25.8, fy: 33.4, z: 16 }, { fx: 25.95, fy: 33.1, z: 16 }, { fx: 29, fy: 36, z: 3.3 } ];
	// Opening photos: left, top, width, shape, and where they fly (x in 60vw, y in 50vh).
	const FIGS = [
		[ '16%', '32%', 'min(19vw, 280px)', '4 / 5', -1, -0.7 ],
		[ '85%', '27%', 'min(15vw, 220px)', '3 / 4', 1, -0.8 ],
		[ '84%', '77%', 'min(16vw, 240px)', '4 / 5', 1, 0.8 ],
		[ '18%', '78%', 'min(18vw, 260px)', '4 / 3', -1, 0.9 ],
		[ '62%', '89%', 'min(11vw, 160px)', '1 / 1', 0, 1.2 ],
		[ '38%', '13%', 'min(10vw, 150px)', '1 / 1', 0, -1.2 ],
	];
	const FIGS_PHONE = [
		[ '27%', '22%', '42vw', '4 / 5', -1, -0.7 ],
		[ '73%', '24%', '38vw', '3 / 4', 1, -0.8 ],
		[ '27%', '78%', '40vw', '4 / 5', -1, 0.9 ],
		[ '73%', '76%', '42vw', '4 / 5', 1, 0.8 ],
	];
	const TAB_AT = [ 0, 880, 1380, 1880 ]; // where each view opens, in px of the pinned scroll
	const RANGE = [ [ 0.3, 0.52 ], [ 0.52, 0.72 ], [ 0.72, 1 ] ];

	let pinned = null;
	let phone = false;
	let compact = false; // phones and tablets: short labels for day trips

	/*
	 * Tablets (760–1100 px) are not on the boards. There the camera fits the places of views 1–3 into the part
	 * of the screen the card leaves free: above the card on a tall screen, left of it on a wide one.
	 * Label widths are estimated from their length (13 px type), so nothing is measured while scrolling.
	 */
	const fits = new Map();
	const spotsOf = ( k ) =>
		pins
			.filter( ( pin ) => pin.dataset.kind === 'hotel' || ( k === 3 ? pin.dataset.trip !== undefined : pin === villa ) )
			.map( ( pin ) => {
				const hotel = pin.dataset.kind === 'hotel';
				const text = hotel || pin === villa ? pin.dataset.label : pin.dataset.short;
				return {
					x: parseFloat( pin.style.left ),
					y: parseFloat( pin.style.top ),
					w: text.length * 7 + 36,
					row: hotel ? k === 3 : pin === villa || pin.dataset.dirPhone === 'row',
				};
			} );
	const fitView = ( k, fw, fh ) => {
		const key = `${ k }:${ fw }:${ fh }`;
		if ( ! fits.has( key ) ) {
			const gut = clamp( fw * 0.04, 16, 56 );
			const box = fh > fw
				? { l: gut, r: fw - gut, t: 96, b: fh - 140 - Math.min( 196, fh * 0.21 ) - 310 }
				: { l: gut, r: fw - gut - Math.min( 368, fw * 0.36 ) - 24, t: 96, b: fh - 130 };
			const spots = spotsOf( k );
			const extent = ( z ) => {
				const kx = ( fw * z ) / 100;
				const ky = ( ( fw * 1029 ) / 2400 ) * z / 100;
				const e = { kx, ky, l: Infinity, r: -Infinity, t: Infinity, b: -Infinity };
				spots.forEach( ( s ) => {
					e.l = Math.min( e.l, s.x * kx - ( s.row ? 12 : s.w ) );
					e.r = Math.max( e.r, s.x * kx + ( s.row ? s.w : 12 ) );
					e.t = Math.min( e.t, s.y * ky - 20 );
					e.b = Math.max( e.b, s.y * ky + 20 );
				} );
				return e;
			};
			let z = k === 3 ? 4 : VIEWS[ k ].z;
			let e = extent( z );
			while ( k === 3 && z > 1 && ( e.r - e.l > box.r - box.l || e.b - e.t > box.b - box.t ) ) {
				z -= 0.05;
				e = extent( z );
			}
			fits.set( key, {
				fx: ( fw / 2 + ( e.l + e.r ) / 2 - ( box.l + box.r ) / 2 ) / e.kx,
				fy: ( fh / 2 + ( e.t + e.b ) / 2 - ( box.t + box.b ) / 2 ) / e.ky,
				z: Number( z.toFixed( 2 ) ),
			} );
		}
		return fits.get( key );
	};
	let manualStep = 0;
	let step = 0;
	let trip = -1;
	let gp = 0;
	let lastFlight = null;
	let flights = 0;
	let cardKey = null;

	const setCard = () => {
		const key = `${ step }:${ step === 3 ? trip : -1 }:${ phone }`;
		if ( key === cardKey ) {
			return;
		}
		cardKey = key;
		const view = data.views[ step ];
		let c = { ...view, text: phone && view.textPhone ? view.textPhone : view.text, wantsPhoto: !! view.photo };
		if ( step === 3 && trip >= 0 && data.trips[ trip ] ) {
			const t = data.trips[ trip ];
			c = { eyebrow: data.strings.trip, title: t.title, meta: t.meta, text: t.text, photo: t.photo, link: data.strings.tripLink, href: data.strings.tripHref, wantsPhoto: true };
		}
		cardField( 'eyebrow' ).textContent = c.eyebrow;
		cardField( 'title' ).textContent = c.title;
		cardField( 'meta' ).textContent = c.meta;
		cardField( 'text' ).textContent = c.text;
		const link = cardField( 'link' );
		link.textContent = c.link;
		link.href = c.href;
		link.hidden = ! c.link;
		cardPics.forEach( ( img ) => img.classList.toggle( 'is-on', Number( img.dataset.photo ) === c.photo ) );
		card.classList.toggle( 'has-photo', !! c.wantsPhoto );
		card.classList.toggle( 'no-photo', !! c.wantsPhoto && ! c.photo );
		cardBody.classList.remove( 'is-new' );
		void cardBody.offsetWidth; // restart the fade
		cardBody.classList.add( 'is-new' );
	};

	const setPins = () => {
		pins.forEach( ( pin ) => {
			const kind = pin.dataset.kind;
			const i = pin.dataset.trip === undefined ? -1 : Number( pin.dataset.trip );
			const shown = kind === 'hotel' || ( kind === 'villa' ? step === 1 || step === 2 : step === 3 );
			const on = kind === 'hotel' ? step !== 2 && ! ( step === 3 && trip >= 0 ) : kind === 'villa' ? step === 2 : step === 3 && trip === i;
			let side = compact ? pin.dataset.dirPhone : pin.dataset.dir;
			if ( kind === 'villa' ) {
				side = 'row';
			} else if ( kind === 'hotel' ) {
				side = step === 1 || step === 2 ? 'row-reverse' : 'row';
			}
			let label = compact && kind === 'trip' ? pin.dataset.short : pin.dataset.label;
			if ( kind === 'hotel' && step === 0 && villa ) {
				// Both names on one label; on a phone in two lines, so it stays on the screen.
				label = `${ pin.dataset.label }${ phone ? '\n' : ' · ' }${ villa.dataset.label }`;
			}
			pin.dataset.side = side;
			const text = pin.querySelector( '.am-pin__label' );
			if ( text.textContent !== label ) {
				text.textContent = label;
			}
			pin.classList.toggle( 'is-shown', shown );
			pin.classList.toggle( 'is-on', on );
			if ( kind === 'trip' ) {
				pin.tabIndex = shown ? 0 : -1;
			}
			pin.setAttribute( 'aria-hidden', shown ? 'false' : 'true' );
		} );
		roads.forEach( ( road ) => road.classList.toggle( 'is-on', step === 3 && Number( road.dataset.road ) === trip ) );
	};

	const setIntro = ( ix, isIntro ) => {
		map.classList.toggle( 'is-intro', isIntro );
		if ( ! isIntro ) {
			return;
		}
		const k = smooth( clamp( ix / 0.4 ) );
		const a = 0.96 * ( 1 - smooth( clamp( ( ix - 0.35 ) / 0.5 ) ) );
		intro.style.background = `color-mix(in srgb, color-mix(in srgb, var(--c-sky) ${ ( k * 100 ).toFixed( 1 ) }%, var(--c-limestone)) ${ ( a * 100 ).toFixed( 1 ) }%, transparent)`;
		introText.style.opacity = ( 1 - smooth( clamp( ix * 2 ) ) ).toFixed( 3 );
		const table = phone ? FIGS_PHONE : FIGS;
		figs.forEach( ( fig, i ) => {
			const q = table[ i ];
			if ( ! q ) {
				fig.style.display = 'none';
				return;
			}
			fig.style.display = '';
			fig.style.left = q[ 0 ];
			fig.style.top = q[ 1 ];
			fig.style.width = q[ 2 ];
			fig.firstElementChild.style.aspectRatio = q[ 3 ];
			fig.style.transform = `translate(-50%, -50%) translate(${ ( q[ 4 ] * 60 * ix ).toFixed( 1 ) }vw, ${ ( q[ 5 ] * 50 * ix ).toFixed( 1 ) }vh) scale(${ ( 1 + 1.4 * ix ).toFixed( 3 ) })`;
			fig.style.opacity = ( 1 - smooth( clamp( ( ix - 0.25 ) / 0.6 ) ) ).toFixed( 3 );
		} );
		const cw = Math.sin( Math.PI * clamp( ( ix - 0.15 ) / 0.85 ) );
		const [ c1, c2, c3 ] = introClouds;
		c1.style.opacity = ( 0.6 * cw ).toFixed( 3 );
		c1.style.transform = `translateX(${ ( -40 * ix ).toFixed( 1 ) }%) scale(${ ( 0.8 + 1.7 * ix ).toFixed( 3 ) })`;
		c2.style.opacity = ( 0.6 * cw ).toFixed( 3 );
		c2.style.transform = `translateX(${ ( 40 * ix ).toFixed( 1 ) }%) scale(${ ( 0.9 + 1.6 * ix ).toFixed( 3 ) })`;
		c3.style.opacity = ( 0.5 * cw ).toFixed( 3 );
		c3.style.transform = `scale(${ ( 0.7 + 2.2 * ix ).toFixed( 3 ) })`;
	};

	const update = ( { vh, vw } ) => {
		const want = ! reduceMotion() && vh >= 480;
		if ( want !== pinned ) {
			pinned = want;
			map.classList.toggle( 'is-pinned', pinned );
		}
		phone = vw <= 760; // as the CSS: max-width 760px
		compact = vw < 1100;

		let gk = 1;
		let ix = 1;
		let isIntro = false;
		if ( pinned ) {
			const top = map.getBoundingClientRect().top;
			gk = smooth( clamp( ( vh - top ) / vh ) );
			gp = clamp( -top / 2300 );
			step = gp < 0.3 ? 0 : gp < 0.52 ? 1 : gp < 0.72 ? 2 : 3;
			isIntro = gp < 0.14;
			ix = smooth( clamp( ( gp - 0.05 ) / 0.11 ) );
		} else {
			step = manualStep;
		}

		frame.style.setProperty( '--gk', gk.toFixed( 4 ) );
		frame.style.setProperty( '--map-o', pinned ? smooth( clamp( ( ix - 0.4 ) / 0.4 ) ).toFixed( 3 ) : '1' );
		const view = compact && ! phone && step > 0 ? fitView( step, vw, vh ) : ( phone ? VIEWS_PHONE : VIEWS )[ step ];
		canvas.style.setProperty( '--fx', view.fx );
		canvas.style.setProperty( '--fy', view.fy );
		canvas.style.setProperty( '--z', view.z );
		canvas.style.setProperty( '--far-o', step === 0 || step === 3 ? '1' : '0' );

		map.classList.toggle( 'is-trips', step === 3 );
		map.classList.toggle( 'is-hint', pinned && step === 0 && ! isIntro && ! phone );
		setIntro( ix, isIntro );

		// Through the clouds whenever the camera jumps between the whole island and a place.
		const kind = isIntro || step === 1 || step === 2 ? 'a' : 'b';
		if ( kind !== lastFlight ) {
			lastFlight = kind;
			flights += 1;
			if ( pinned && flights > 1 ) {
				flight.classList.toggle( 'is-a', flights % 2 === 1 );
				flight.classList.toggle( 'is-b', flights % 2 === 0 );
			}
		}

		setPins();
		setCard();
		tabs.forEach( ( tab, j ) => {
			const k = j + 1;
			tab.setAttribute( 'aria-selected', String( step === k ) );
			tab.tabIndex = step === k || ( step === 0 && j === 0 ) ? 0 : -1;
			const fill = pinned ? clamp( ( gp - RANGE[ j ][ 0 ] ) / ( RANGE[ j ][ 1 ] - RANGE[ j ][ 0 ] ) ) : step === k ? 1 : 0;
			tab.style.setProperty( '--fill', fill.toFixed( 3 ) );
		} );
	};

	let last = { vh: window.innerHeight, vw: window.innerWidth };
	onScroll( ( state ) => {
		last = state;
		update( state );
	} );

	/* Day trips: the place under the pointer, in focus or tapped */
	pins.filter( ( pin ) => pin.dataset.trip !== undefined ).forEach( ( pin ) => {
		const choose = () => {
			trip = Number( pin.dataset.trip );
			update( last );
		};
		[ 'mouseenter', 'focus', 'click' ].forEach( ( type ) => pin.addEventListener( type, choose ) );
	} );

	/* Tabs: scroll to where the view opens (pinned) or switch the view (not pinned); arrows move between tabs */
	const go = ( k ) => {
		if ( pinned ) {
			const top = map.getBoundingClientRect().top + window.scrollY + TAB_AT[ k ] + 30;
			window.scrollTo( { top, behavior: reduceMotion() ? 'auto' : 'smooth' } );
		} else {
			manualStep = k;
			update( last );
		}
	};
	tabs.forEach( ( tab, j ) => {
		tab.addEventListener( 'click', () => go( j + 1 ) );
		tab.addEventListener( 'keydown', ( event ) => {
			const move = { ArrowRight: 1, ArrowLeft: -1 }[ event.key ];
			if ( move ) {
				event.preventDefault();
				const next = tabs[ ( j + move + tabs.length ) % tabs.length ];
				next.focus();
				next.click();
			}
		} );
	} );
}
