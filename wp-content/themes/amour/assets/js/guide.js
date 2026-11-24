/**
 * The Crete guide (board Guide: the map component, and the Days out cards).
 *
 * The map. In the page it shows the whole island in a frame; a click or tap (or "Open the map") opens it on the
 * whole screen. There it zooms with the wheel or two fingers around the point under them (up to 3×, a phone 6×),
 * moves with a drag (the map always covers the frame: no edge of sea in sight), and goes back to the whole island
 * on a double click. A place flies the map to it — through clouds from the whole island, with a small lift from
 * close up — and opens its card. Pointing at or focusing a place draws the road there.
 * The keyboard: Tab goes through the places, Enter opens one; in the open map + and − zoom, the arrows move it,
 * Esc closes it. With reduced motion the moves are instant and there are no clouds.
 *
 * Days out: once in view the roads draw themselves, then the cards turn to the photo and the drive time.
 */

import { reduceMotion } from '@amour/motion';

const map = document.querySelector( '[data-gd-map]' );

if ( map ) {
	const vp = map.querySelector( '[data-gd-vp]' );
	const plane = map.querySelector( '[data-gd-plane]' );
	const pins = [ ...map.querySelectorAll( '.am-gd-pin' ) ];
	const roads = [ ...map.querySelectorAll( '.am-gd-road-on' ) ];
	const clouds = map.querySelector( '[data-gd-clouds]' );
	const card = map.querySelector( '[data-gd-card]' );
	const cardPics = [ ...card.querySelectorAll( 'img' ) ];
	const field = ( name ) => card.querySelector( `[data-gd-card-${ name }]` );
	const openBtn = map.querySelector( '[data-gd-open]' );
	const closeBtn = map.querySelector( '[data-gd-close]' );
	const data = JSON.parse( map.querySelector( '[data-gd-data]' ).textContent );
	const byIndex = ( i ) => pins.find( ( p ) => Number( p.dataset.i ) === i );
	const phone = () => window.innerWidth < 760;
	const zMax = () => ( phone() ? 6 : 3 );

	const s = { full: false, touched: false, z: 1, tx: 0, ty: 0, pin: Number( pins[ 0 ]?.dataset.i ?? 0 ), card: false, anim: 'base', fly: 0 };
	let drag = null;
	let pinch = null;
	const fingers = new Map();
	let lastTapSelect = 0;
	let returnFocus = null;
	const timers = [];

	/* The map always covers its frame: how far it may move at this zoom, in % of the map */
	const limit = ( z, tx, ty ) => {
		const pw = plane.offsetWidth;
		const ph = plane.offsetHeight;
		const lx = Math.max( 0, ( ( pw * z - vp.clientWidth ) / ( 2 * pw ) ) * 100 );
		const ly = Math.max( 0, ( ( ph * z - vp.clientHeight ) / ( 2 * ph ) ) * 100 );
		return { tx: Math.max( -lx, Math.min( lx, tx ) ), ty: Math.max( -ly, Math.min( ly, ty ) ) };
	};
	const at = ( x, y, z ) => limit( z, -( x - 50 ) * z, -( y - 50 ) * z );

	const render = () => {
		map.style.setProperty( '--z', s.z.toFixed( 3 ) );
		map.style.setProperty( '--tx', `${ s.tx.toFixed( 2 ) }%` );
		map.style.setProperty( '--ty', `${ s.ty.toFixed( 2 ) }%` );
		map.dataset.anim = reduceMotion() ? 'drag' : s.anim;
		map.classList.toggle( 'is-full', s.full );
		map.classList.toggle( 'has-card', s.card );
		map.classList.toggle( 'is-close', s.z >= 2.4 );
		map.classList.toggle( 'is-touched', s.touched ); // the hint goes once the map has been zoomed or moved
		map.classList.toggle( 'is-dragging', !! ( drag && drag.moved ) );
		// A phone: in the small frame the places are too small and close to point at; the frame opens the map.
		const inert = ! s.full && phone();
		pins.forEach( ( p ) => {
			p.classList.toggle( 'is-on', Number( p.dataset.i ) === s.pin );
			p.tabIndex = inert ? -1 : 0;
			p.setAttribute( 'aria-hidden', String( inert ) );
		} );
		roads.forEach( ( r ) => r.classList.toggle( 'is-on', Number( r.dataset.road ) === s.pin ) );
		const place = data[ s.pin ] || data[ 0 ];
		field( 'name' ).textContent = place.name;
		field( 'meta' ).textContent = place.meta;
		field( 'text' ).textContent = place.text;
		cardPics.forEach( ( img ) => img.classList.toggle( 'is-on', Number( img.dataset.photo ) === place.photo ) );
		card.setAttribute( 'aria-hidden', String( ! s.card ) );
	};
	const set = ( patch ) => {
		Object.assign( s, patch );
		render();
	};
	const later = ( ms, fn ) => timers.push( window.setTimeout( fn, ms ) );
	const flyTo = ( patch ) => {
		s.fly += 1;
		if ( ! reduceMotion() ) {
			clouds.classList.toggle( 'is-a', s.fly % 2 === 1 );
			clouds.classList.toggle( 'is-b', s.fly % 2 === 0 );
		}
		set( { anim: 'fly', ...patch } );
	};

	/* Open and close the whole-screen map */
	const open = () => {
		if ( s.full ) {
			return;
		}
		returnFocus = document.activeElement;
		map.setAttribute( 'role', 'dialog' );
		map.setAttribute( 'aria-modal', 'true' );
		document.documentElement.style.overflow = 'hidden';
		// A phone opens close in on the west of the island, where the places are, so they are big enough to tap.
		set( { full: true, touched: false, anim: 'base', z: phone() ? 2.4 : 1, tx: 0, ty: 0 } );
		if ( phone() ) {
			set( at( 29, 40, 2.4 ) );
		}
		closeBtn.focus( { preventScroll: true } );
	};
	const close = () => {
		map.removeAttribute( 'role' );
		map.removeAttribute( 'aria-modal' );
		document.documentElement.style.overflow = '';
		set( { full: false, card: false, z: 1, tx: 0, ty: 0, anim: 'base' } );
		drag = null;
		( returnFocus && document.contains( returnFocus ) ? returnFocus : openBtn ).focus( { preventScroll: true } );
	};
	openBtn.addEventListener( 'click', open );
	closeBtn.addEventListener( 'click', close );

	/* A place: from the whole island fly through clouds; from close up glide over with a small lift */
	const select = ( i ) => {
		const pin = byIndex( i );
		if ( ! pin ) {
			return;
		}
		const x = Number( pin.dataset.x );
		const y = Number( pin.dataset.y );
		const z = pin.classList.contains( 'am-gd-pin--hotel' ) ? 2 : 2.4;
		if ( ! s.full ) {
			returnFocus = pin;
			map.setAttribute( 'role', 'dialog' );
			map.setAttribute( 'aria-modal', 'true' );
			document.documentElement.style.overflow = 'hidden';
		}
		if ( ! s.full || s.z < 1.3 ) {
			flyTo( { pin: i, card: true, full: true, z, ...at( x, y, z ) } );
			return;
		}
		const goal = at( x, y, z );
		if ( Math.abs( goal.tx - s.tx ) < 0.5 && Math.abs( goal.ty - s.ty ) < 0.5 && Math.abs( s.z - z ) < 0.05 ) {
			set( { pin: i, card: true } );
			return;
		}
		const mz = Math.max( 1.4, Math.min( s.z, z ) * 0.7 );
		const cx = ( s.tx / s.z - ( x - 50 ) ) / 2;
		const cy = ( s.ty / s.z - ( y - 50 ) ) / 2;
		set( { pin: i, card: true, z: mz, anim: 'hop', ...limit( mz, cx * mz, cy * mz ) } );
		later( 520, () => set( { z, anim: 'hop', ...at( x, y, z ) } ) );
	};

	pins.forEach( ( pin ) => {
		const i = Number( pin.dataset.i );
		pin.addEventListener( 'mouseenter', () => set( { pin: i } ) );
		pin.addEventListener( 'focus', () => set( { pin: i } ) );
		pin.addEventListener( 'click', () => {
			if ( Date.now() - lastTapSelect > 700 ) {
				select( i );
			}
		} );
	} );

	/* Zoom around a point of the frame (clientX, clientY) */
	const zoomAt = ( factor, clientX, clientY, anim = 'quick' ) => {
		const z = Math.max( 1, Math.min( zMax(), s.z * factor ) );
		const r = z / s.z;
		const rect = vp.getBoundingClientRect();
		const cx = clientX - ( rect.left + rect.width / 2 );
		const cy = clientY - ( rect.top + rect.height / 2 );
		const tx = ( ( cx * ( 1 - r ) ) / plane.offsetWidth ) * 100 + s.tx * r;
		const ty = ( ( cy * ( 1 - r ) ) / plane.offsetHeight ) * 100 + s.ty * r;
		set( { z, anim, touched: true, ...limit( z, tx, ty ) } );
	};

	vp.addEventListener(
		'wheel',
		( event ) => {
			if ( ! s.full ) {
				return;
			}
			event.preventDefault();
			zoomAt( event.deltaY < 0 ? 1.12 : 1 / 1.12, event.clientX, event.clientY );
		},
		{ passive: false }
	);

	vp.addEventListener( 'dblclick', () => {
		if ( s.full && s.z > 1.05 ) {
			flyTo( { z: 1, card: false, ...at( 29, 40, 1 ) } );
		}
	} );

	/* Fingers and the mouse: one drags, two pinch (the point between them stays under them) */
	vp.addEventListener( 'pointerdown', ( event ) => {
		fingers.set( event.pointerId, { x: event.clientX, y: event.clientY } );
		if ( fingers.size === 2 && s.full ) {
			const [ a, c ] = [ ...fingers.values() ];
			const rect = vp.getBoundingClientRect();
			pinch = {
				d: Math.hypot( a.x - c.x, a.y - c.y ) || 1,
				z: s.z,
				tx: s.tx,
				ty: s.ty,
				cx: ( a.x + c.x ) / 2 - ( rect.left + rect.width / 2 ),
				cy: ( a.y + c.y ) / 2 - ( rect.top + rect.height / 2 ),
				ox: rect.left + rect.width / 2,
				oy: rect.top + rect.height / 2,
			};
			drag = null;
			return;
		}
		drag = { x: event.clientX, y: event.clientY, tx: s.tx, ty: s.ty, moved: false };
	} );

	vp.addEventListener( 'pointermove', ( event ) => {
		if ( fingers.has( event.pointerId ) ) {
			fingers.set( event.pointerId, { x: event.clientX, y: event.clientY } );
		}
		if ( pinch ) {
			if ( fingers.size < 2 ) {
				return;
			}
			const [ a, c ] = [ ...fingers.values() ];
			const z = Math.max( 1, Math.min( zMax(), ( pinch.z * Math.hypot( a.x - c.x, a.y - c.y ) ) / pinch.d ) );
			const pw = plane.offsetWidth;
			const ph = plane.offsetHeight;
			const cx = ( a.x + c.x ) / 2 - pinch.ox;
			const cy = ( a.y + c.y ) / 2 - pinch.oy;
			const tx = ( ( cx - ( ( pinch.cx - ( pinch.tx / 100 ) * pw ) / pinch.z ) * z ) / pw ) * 100;
			const ty = ( ( cy - ( ( pinch.cy - ( pinch.ty / 100 ) * ph ) / pinch.z ) * z ) / ph ) * 100;
			set( { z, anim: 'drag', touched: true, ...limit( z, tx, ty ) } );
			return;
		}
		if ( ! drag ) {
			return;
		}
		const dx = event.clientX - drag.x;
		const dy = event.clientY - drag.y;
		if ( ! drag.moved && Math.abs( dx ) + Math.abs( dy ) < 6 ) {
			return;
		}
		drag.moved = true;
		set( { anim: 'drag', touched: true, ...limit( s.z, drag.tx + ( dx / plane.offsetWidth ) * 100, drag.ty + ( dy / plane.offsetHeight ) * 100 ) } );
	} );

	const lift = ( event ) => {
		fingers.delete( event.pointerId );
		if ( pinch ) {
			pinch = fingers.size > 1 ? pinch : null;
			if ( fingers.size === 1 ) {
				const q = [ ...fingers.values() ][ 0 ];
				drag = { x: q.x, y: q.y, tx: s.tx, ty: s.ty, moved: true };
			} else {
				drag = null;
			}
			return;
		}
		const d = drag;
		drag = null;
		const pin = event.target.closest?.( '.am-gd-pin' );
		if ( d && ! d.moved && pin && event.type === 'pointerup' && event.pointerType !== 'mouse' ) {
			// A tap on a place: handled here, since on a phone the map opens under the finger.
			lastTapSelect = Date.now();
			select( Number( pin.dataset.i ) );
			return;
		}
		if ( d && ! d.moved && event.type === 'pointerup' && ! pin ) {
			if ( ! s.full ) {
				open();
			} else if ( s.card && phone() ) {
				set( { card: false } ); // a tap away from the places puts the card away
			}
		}
		render();
	};
	vp.addEventListener( 'pointerup', lift );
	vp.addEventListener( 'pointercancel', lift );
	vp.addEventListener( 'pointerleave', ( event ) => {
		if ( event.pointerType === 'mouse' ) {
			fingers.delete( event.pointerId );
			drag = null;
			render();
		}
	} );

	/* The keyboard in the open map */
	map.addEventListener( 'keydown', ( event ) => {
		if ( ! s.full ) {
			return;
		}
		const rect = vp.getBoundingClientRect();
		const mid = [ rect.left + rect.width / 2, rect.top + rect.height / 2 ];
		const step = 12 / s.z;
		const moves = {
			ArrowLeft: [ step, 0 ],
			ArrowRight: [ -step, 0 ],
			ArrowUp: [ 0, step ],
			ArrowDown: [ 0, -step ],
		};
		if ( event.key === 'Escape' ) {
			event.preventDefault();
			close();
		} else if ( event.key === '+' || event.key === '=' ) {
			event.preventDefault();
			zoomAt( 1.25, ...mid, 'base' );
		} else if ( event.key === '-' ) {
			event.preventDefault();
			zoomAt( 1 / 1.25, ...mid, 'base' );
		} else if ( moves[ event.key ] && ! event.target.closest( '.am-gd-pin' ) ) {
			event.preventDefault();
			set( { anim: 'base', touched: true, ...limit( s.z, s.tx + moves[ event.key ][ 0 ], s.ty + moves[ event.key ][ 1 ] ) } );
		} else if ( event.key === 'Tab' ) {
			// Keep the keyboard inside the open map: the places, then the close button (their order on the page).
			const stops = [ ...pins, closeBtn ];
			const first = stops[ 0 ];
			const last = stops[ stops.length - 1 ];
			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}
	} );

	window.addEventListener( 'resize', () => set( limit( s.z, s.tx, s.ty ) ) );
	render();
}

/* Days out: the roads draw themselves once the cards are in view, then the cards turn */
const days = document.querySelector( '.am-gd-days' );
if ( days && ! reduceMotion() ) {
	days.classList.add( 'is-ready' );
	const seen = new IntersectionObserver(
		( [ entry ] ) => {
			if ( entry.isIntersecting ) {
				days.classList.add( 'is-in' );
				seen.disconnect();
			}
		},
		{ rootMargin: '0px 0px -20% 0px' }
	);
	seen.observe( days );
}
