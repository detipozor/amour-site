/**
 * Rooms → villa on the home page (board Main-v2 "rm" and Main-v2-b "vh2"; HANDOFF, section 6.7).
 *
 * 1. Rooms: the grid holds (sticky until 950 px of scroll have passed). From 250 px on, over 700 px, the grid
 *    fades, the villa card flies from its place to the middle of the screen at its own size, and "Room for" /
 *    "everyone" slide in on both sides (phone: above and below the card), with two labels under them.
 * 2. Villa: starts exactly there (it overlaps the end of the rooms block by one screen), pinned for 1100 px:
 *    the background turns from limestone to foam, the words part and fade, the arch opens to the whole screen
 *    (its top flattens), then the line, the button and the facts come up over a flat shade.
 *
 * The arch only ever grows: grid card → middle → full screen, so both parts use the same card size.
 * Not pinned on short screens (under 480 px) and with reduced motion: the villa stands open.
 */

import { onScroll, clamp, smooth, reduceMotion } from '@amour/motion';

const rooms = document.querySelector( '[data-rooms]' );
const villa = document.querySelector( '[data-home-villa]' );

if ( rooms && villa ) {
	const pin = rooms.querySelector( '[data-rooms-pin]' );
	const grid = pin.querySelector( '.am-rooms__in' );
	const fly = pin.querySelector( '[data-rooms-fly]' );
	const flyImg = fly?.querySelector( 'img' );
	const card = rooms.querySelector( '.am-unit--villa .am-unit__pic' );
	const vpin = villa.querySelector( '[data-villa-pin]' );
	const arch = villa.querySelector( '[data-villa-arch]' );
	const archImg = arch.querySelector( 'img' );
	const lerp = ( a, b, k ) => a + ( b - a ) * k;
	const px = ( v ) => `${ v.toFixed( 1 ) }px`;
	const words = ( root ) => ( {
		l: root.querySelector( '[data-vword="l"]' ),
		r: root.querySelector( '[data-vword="r"]' ),
		cl: root.querySelector( '[data-vcap="l"]' ),
		cr: root.querySelector( '[data-vcap="r"]' ),
	} );
	const roomWords = words( pin );
	const villaWords = words( vpin );

	const clearStyles = () => {
		[ pin, grid, fly, flyImg, card, vpin, arch, archImg, ...Object.values( roomWords ), ...Object.values( villaWords ) ]
			.filter( Boolean )
			.forEach( ( el ) => el.removeAttribute( 'style' ) );
		vpin.dataset.tone = 'dark'; // the open photo is under the header
		vpin.style.setProperty( '--vt', '1' );
	};

	let pinned = null;

	onScroll( ( { vh, vw } ) => {
		const want = ! reduceMotion() && vh >= 480;
		if ( want !== pinned ) {
			pinned = want;
			rooms.classList.toggle( 'is-pinned', pinned );
			villa.classList.toggle( 'is-pinned', pinned );
			if ( ! pinned ) {
				clearStyles();
				return;
			}
		}
		if ( ! pinned ) {
			return;
		}

		// The villa card's size in the grid (both parts use it).
		const docW = document.documentElement.clientWidth;
		const gut = clamp( vw * 0.04, 16, 56 );
		const phone = docW < 760;
		const cardW = phone ? docW - 2 * gut : ( Math.min( docW - 2 * gut, 1240 ) - 24 ) / 2;
		const cardH = phone ? ( cardW * 5 ) / 4 : vh < 760 ? cardW : ( cardW * 7 ) / 6;
		const wfs = phone ? Math.min( 44, docW * 0.11 ) : Math.max( 28, Math.min( 84, vh * 0.09, ( ( docW - cardW ) / 2 - gut - 32 ) / 6.6 ) );
		const capW = ( docW - cardW ) / 2 - gut - 28;
		const wordShift = phone ? cardH / 2 + wfs * 0.9 : 0;
		const capShift = wfs * 0.62 + 18;

		/* 1. Rooms */
		const Hin = pin.offsetHeight;
		pin.style.setProperty( '--pin-top', `${ Math.round( vh - Hin ) }px` );
		const rk = clamp( ( -rooms.getBoundingClientRect().top - ( Hin - vh ) - 250 ) / 700 );
		const fe = smooth( rk );
		const inTop = pin.getBoundingClientRect().top;
		grid.style.opacity = ( 1 - smooth( clamp( rk / 0.4 ) ) ).toFixed( 3 );

		if ( fly && card ) {
			card.style.opacity = rk > 0 ? '0' : '';
			fly.style.opacity = rk > 0 ? '1' : '0';
			if ( rk > 0 ) {
				const st = card.getBoundingClientRect();
				fly.style.left = px( lerp( st.left, ( docW - cardW ) / 2, fe ) );
				fly.style.top = px( lerp( st.top, ( vh - cardH ) / 2, fe ) - inTop );
				fly.style.width = px( lerp( st.width, cardW, fe ) );
				fly.style.height = px( lerp( st.height, cardH, fe ) );
				flyImg.style.transform = `scale(${ ( 1 + 0.12 * fe ).toFixed( 4 ) })`;
			}
		}

		const wo = smooth( clamp( ( rk - 0.6 ) / 0.4 ) );
		const slide = 80 * ( 1 - wo );
		const placeRoomWord = ( el, top, x ) => {
			el.style.top = px( top );
			el.style.fontSize = px( wfs );
			el.style.opacity = wo.toFixed( 3 );
			el.style.transform = `translate(${ x.toFixed( 0 ) }px, -50%)`;
		};
		placeRoomWord( roomWords.l, vh / 2 - inTop - wordShift, -slide );
		placeRoomWord( roomWords.r, vh / 2 - inTop + wordShift, slide );
		[ [ roomWords.cl, -slide ], [ roomWords.cr, slide ] ].forEach( ( [ el, x ] ) => {
			el.style.top = px( vh / 2 - inTop + capShift );
			el.style.maxWidth = px( capW );
			el.style.opacity = wo.toFixed( 3 );
			el.style.transform = `translateX(${ x.toFixed( 0 ) }px)`;
		} );

		/* 2. Villa */
		const vTop = villa.getBoundingClientRect().top;
		const vk = clamp( -vTop / 1100 );
		const vbg = smooth( clamp( vk / 0.3 ) );
		const vg = smooth( clamp( ( vk - 0.12 ) / 0.55 ) );
		const vt = smooth( clamp( ( vk - 0.72 ) / 0.2 ) );

		vpin.style.visibility = vTop <= 1 ? 'visible' : 'hidden';
		vpin.style.background = `color-mix(in srgb, var(--c-foam) ${ ( vbg * 100 ).toFixed( 1 ) }%, var(--c-limestone))`;
		vpin.dataset.tone = vg > 0.6 ? 'dark' : 'light';
		vpin.style.setProperty( '--vt', vt.toFixed( 3 ) );
		vpin.querySelector( '[data-villa-text]' ).style.pointerEvents = vt > 0.5 ? 'auto' : 'none';

		arch.style.width = `calc(${ px( cardW ) } + (100% - ${ px( cardW ) }) * ${ vg.toFixed( 4 ) })`;
		arch.style.height = `calc(${ px( cardH ) } + (100% - ${ px( cardH ) }) * ${ vg.toFixed( 4 ) })`;
		const top = vg < 0.02 ? '999px 999px' : `${ Math.round( 180 * ( 1 - vg ) ) }px ${ Math.round( 180 * ( 1 - vg ) ) }px`;
		const bottom = Math.round( 24 * ( 1 - vg ) );
		arch.style.borderRadius = `${ top } ${ bottom }px ${ bottom }px`;
		archImg.style.transform = `scale(${ ( 1.12 - 0.08 * vg ).toFixed( 4 ) })`;

		const vwo = ( 1 - smooth( clamp( vg * 1.6 ) ) ).toFixed( 3 );
		const placeVillaWord = ( el, offset, sign ) => {
			el.style.top = `calc(50% + ${ px( offset ) })`;
			el.style.fontSize = px( wfs );
			el.style.opacity = vwo;
			el.style.transform = `translate(calc(${ sign * 28 }vw * ${ vg.toFixed( 4 ) }), -50%)`;
		};
		placeVillaWord( villaWords.l, -wordShift, -1 );
		placeVillaWord( villaWords.r, wordShift, 1 );
		[ [ villaWords.cl, -1 ], [ villaWords.cr, 1 ] ].forEach( ( [ el, sign ] ) => {
			el.style.top = `calc(50% + ${ px( capShift ) })`;
			el.style.maxWidth = px( capW );
			el.style.opacity = vwo;
			el.style.transform = `translateX(calc(${ sign * 28 }vw * ${ vg.toFixed( 4 ) }))`;
		} );
	} );
}
