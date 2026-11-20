/**
 * The location block of the room and villa pages (room_template.html, renderVals: mz, pins, mapIn).
 *
 * Pinned for 760 px of scroll (mk 0 → 1, eased): the camera comes down from the whole island at 0.6×,
 * set in the part left of the card, to Almyrida at 6.2×. The zoom grows evenly on a log scale and the hotel's
 * offset from the middle moves evenly too, so the hotel never leaves the screen.
 * The road to Chania appears from 55% of the way, the nearby places from 60%. The card comes up once in view.
 * Not pinned with reduced motion or on short screens: the map stands close in (the CSS default).
 */

import { onScroll, clamp, smooth, reduceMotion } from '@amour/motion';

const loc = document.querySelector( '[data-loc]' );

if ( loc ) {
	const canvas = loc.querySelector( '[data-loc-canvas]' );
	const hx = Number( loc.dataset.hotelX );
	const hy = Number( loc.dataset.hotelY );
	// Whole island: the focus point (%) and the zoom; the card is on the right, so the island sits left of it.
	// A phone has the card below: the island a little larger, the hotel far enough in for its label.
	const FAR = { z: 0.6, dx: 72, dy: 50 };
	const FAR_PHONE = { z: 0.8, dx: 30.5, dy: 50 };
	// Close in: the hotel a little right of the middle; on a tablet the card is wider for the screen, so further left.
	const NEAR = { z: 6.2, dx: 25, dy: 31 };
	const NEAR_TABLET = { z: 6.2, dx: 28, dy: 31 };
	let pinned = null;

	if ( ! reduceMotion() ) {
		loc.classList.add( 'is-ready' );
		const seen = new IntersectionObserver(
			( [ entry ] ) => {
				if ( entry.isIntersecting ) {
					loc.classList.add( 'is-in' );
					seen.disconnect();
				}
			},
			{ rootMargin: '0px 0px -30% 0px' }
		);
		seen.observe( loc );
	}

	onScroll( ( { vh, vw } ) => {
		const far = vw <= 760 ? FAR_PHONE : FAR;
		const near = vw > 760 && vw < 1100 ? NEAR_TABLET : NEAR;
		const want = ! reduceMotion() && vh >= 480;
		if ( want !== pinned ) {
			pinned = want;
			loc.classList.toggle( 'is-pinned', pinned );
		}
		const mk = pinned ? clamp( -loc.getBoundingClientRect().top / 760 ) : 1;
		const me = smooth( mk );
		const z = Math.exp( Math.log( far.z ) + ( Math.log( near.z ) - Math.log( far.z ) ) * me );
		const ox = ( hx - far.dx ) * far.z + ( ( hx - near.dx ) * near.z - ( hx - far.dx ) * far.z ) * me;
		const oy = ( hy - far.dy ) * far.z + ( ( hy - near.dy ) * near.z - ( hy - far.dy ) * far.z ) * me;

		canvas.style.setProperty( '--z', z.toFixed( 4 ) );
		canvas.style.setProperty( '--fx', ( hx - ox / z ).toFixed( 3 ) );
		canvas.style.setProperty( '--fy', ( hy - oy / z ).toFixed( 3 ) );
		canvas.style.setProperty( '--u', ( 2400 / ( canvas.offsetWidth * z ) ).toFixed( 4 ) );
		canvas.style.setProperty( '--road-o', clamp( ( me - 0.55 ) / 0.35 ).toFixed( 3 ) );
		canvas.style.setProperty( '--spot-o', clamp( ( me - 0.6 ) / 0.3 ).toFixed( 3 ) );
		loc.classList.toggle( 'is-hint', pinned && mk < 0.15 );
	} );
}
