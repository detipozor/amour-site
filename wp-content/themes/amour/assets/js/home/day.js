/**
 * A day at Amour (board Main-v2, renderVals: "dy", "dayCards", "dayCaps").
 * Desktop: the block is pinned; each 45% of the screen height of scroll moves the strip one frame along on a
 * gentle sine curve with a short rest on every frame, and the strip follows with a little inertia so a jerky
 * wheel never makes the arch jump. The frame in the middle is the colour arch; the others are smaller,
 * rounder at the bottom and quiet (grey and a little sepia).
 * Phone and reduced motion: a strip that scrolls with a finger; the same look follows each card's distance
 * from the middle (reduced motion: no change of size or colour). The caption of the middle frame shows.
 */

import { onScroll, clamp, smooth, reduceMotion } from '@amour/motion';

const section = document.querySelector( '[data-day]' );

if ( section ) {
	const strip = section.querySelector( '[data-day-strip]' );
	const cards = [ ...strip.querySelectorAll( '.am-day__card' ) ];
	const own = cards.filter( ( card ) => ! card.classList.contains( 'am-day__card--repeat' ) );
	const caps = [ ...section.querySelectorAll( '.am-day__cap' ) ];
	const NF = caps.length;
	const lerp = ( a, b, k ) => a + ( b - a ) * k;
	const sine = ( v ) => ( 1 - Math.cos( Math.PI * v ) ) / 2;

	let carousel = null;
	let geometry = null;
	let shown = null; // the frame the strip shows now (follows the target with inertia)
	let target = 0;
	let raf = 0;

	const setActiveCaption = ( index ) => caps.forEach( ( cap, i ) => cap.classList.toggle( 'is-active', i === index ) );

	/* ---------- Desktop: pinned strip ---------- */

	const measure = ( vw, vh ) => {
		const topPad = vh < 700 ? 84 : 100;
		const headPx = Math.min( vw * 0.044, vh * 0.07, 64 );
		const capPx = vh < 700 ? 84 : 104;
		const Hc = Math.max( 200, Math.min( 560, vh - topPad - 24 - headPx - 28 - capPx - 24 ) );
		section.style.setProperty( '--top-pad', `${ topPad }px` );
		section.style.setProperty( '--head-fs', `${ headPx.toFixed( 1 ) }px` );
		section.style.setProperty( '--cap-h', `${ capPx }px` );
		section.style.setProperty( '--line-fs', vh < 700 ? '21px' : 'clamp(22px, 1.9vw, 28px)' );
		return { Hc, Wc: Hc * 0.75, Hs: Hc * 0.6, Ws: Hc * 0.48, gap: Math.max( 18, Math.min( 32, vw * 0.02 ) ), vw };
	};

	const render = ( PD ) => {
		const { Hc, Wc, Hs, Ws, gap, vw } = geometry;
		const geo = cards.map( ( card ) => {
			const k = Number( card.dataset.k );
			const a = Math.min( 1, Math.abs( k - PD ) );
			return { card, a, w: lerp( Wc, Ws, a ), h: lerp( Hc, Hs, a ) };
		} );
		const X = [ 0 ];
		for ( let j = 1; j < geo.length; j++ ) {
			X.push( X[ j - 1 ] + geo[ j - 1 ].w / 2 + gap + geo[ j ].w / 2 );
		}
		const f0 = Math.floor( PD );
		const i0 = f0 + 3;
		const i1 = Math.min( geo.length - 1, i0 + 1 );
		const Xc = lerp( X[ i0 ], X[ i1 ], PD - f0 );

		geo.forEach( ( { card, a, w, h }, j ) => {
			const x = X[ j ] - Xc;
			const ea = smooth( a ); // shape and colour change on a soft S-curve
			const R = lerp( Wc / 2, 24, ea );
			const off = Math.abs( x ) - w / 2 > vw / 2 + 40;
			const style = card.style;
			style.width = `${ w.toFixed( 1 ) }px`;
			style.height = `${ h.toFixed( 1 ) }px`;
			style.transform = `translate3d(calc(-50% + ${ x.toFixed( 1 ) }px), -50%, 0)`;
			style.borderRadius = `${ R.toFixed( 1 ) }px ${ R.toFixed( 1 ) }px 24px 24px`;
			style.visibility = off ? 'hidden' : 'visible';
			style.zIndex = String( Math.round( 10 - a * 5 ) );
			const img = card.firstElementChild;
			if ( img ) {
				img.style.filter = a < 0.01 ? 'none' : `grayscale(${ ( 0.88 * ea ).toFixed( 3 ) }) sepia(${ ( 0.32 * ea ).toFixed( 3 ) }) contrast(${ ( 1 - 0.08 * ea ).toFixed( 3 ) }) brightness(${ ( 1 + 0.05 * ea ).toFixed( 3 ) })`;
				img.style.transform = `scale(${ ( 1 + 0.06 * ea ).toFixed( 4 ) })`;
			}
		} );

		caps.forEach( ( cap, i ) => {
			const d = Math.abs( i - PD );
			cap.style.opacity = clamp( 1.6 - d * 4 ).toFixed( 3 );
			cap.style.transform = `translateY(${ Math.round( ( i - PD ) * 14 ) }px)`;
		} );
	};

	const follow = () => {
		raf = 0;
		shown += ( target - shown ) * 0.11;
		if ( Math.abs( target - shown ) < 0.0006 ) {
			shown = target;
		}
		render( shown );
		if ( shown !== target ) {
			raf = requestAnimationFrame( follow );
		}
	};

	const pinned = ( { vh } ) => {
		const raw = clamp( -section.getBoundingClientRect().top / ( vh * 0.45 ), 0, NF - 1 );
		const fl = Math.min( NF - 2, Math.floor( raw ) );
		target = fl + sine( clamp( ( raw - fl - 0.15 ) / 0.7 ) );
		if ( shown === null || Math.abs( target - shown ) > 1.5 ) {
			shown = target;
			render( shown );
		}
		if ( ! raf ) {
			raf = requestAnimationFrame( follow );
		}
	};

	/* ---------- Phone and reduced motion: finger strip ---------- */

	const swipe = () => {
		const box = strip.getBoundingClientRect();
		const middle = box.left + box.width / 2;
		let nearest = 0;
		let best = Infinity;
		own.forEach( ( card, i ) => {
			const r = card.getBoundingClientRect();
			const d = Math.abs( r.left + r.width / 2 - middle );
			if ( d < best ) {
				best = d;
				nearest = i;
			}
			const a = clamp( d / ( card.offsetWidth + 12 ) );
			card.style.setProperty( '--ea', reduceMotion() ? '0' : smooth( a ).toFixed( 3 ) );
		} );
		setActiveCaption( nearest );
	};

	/* ---------- Switching between the two ---------- */

	const clear = () => {
		cards.forEach( ( card ) => {
			card.removeAttribute( 'style' );
			// Keep the focus point of each photo (set in the admin); drop only what the pinned strip wrote.
			card.firstElementChild?.style.removeProperty( 'filter' );
			card.firstElementChild?.style.removeProperty( 'transform' );
		} );
		caps.forEach( ( cap ) => cap.removeAttribute( 'style' ) );
		[ '--top-pad', '--head-fs', '--cap-h', '--line-fs' ].forEach( ( name ) => section.style.removeProperty( name ) );
	};

	onScroll( ( state ) => {
		const wantCarousel = state.vw < 760 || state.vh < 480 || reduceMotion();
		if ( wantCarousel !== carousel ) {
			carousel = wantCarousel;
			section.classList.toggle( 'is-carousel', carousel );
			cancelAnimationFrame( raf );
			raf = 0;
			shown = null;
			if ( carousel ) {
				clear();
				strip.tabIndex = 0; // a box that scrolls sideways must be reachable with the keyboard
				strip.setAttribute( 'aria-label', section.querySelector( '.am-day__title' )?.textContent || '' );
				strip.setAttribute( 'role', 'region' );
				swipe();
			} else {
				strip.removeAttribute( 'tabindex' );
				strip.removeAttribute( 'role' );
				strip.removeAttribute( 'aria-label' );
			}
		}
		if ( ! carousel ) {
			geometry = measure( state.vw, state.vh );
			pinned( state );
		}
	} );

	strip.addEventListener( 'scroll', () => carousel && requestAnimationFrame( swipe ), { passive: true } );
}
