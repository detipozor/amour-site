/**
 * Guest reviews on the home page (board Main-v2-b, "reviews": rvShift, rvTick; HANDOFF, sections 5 and 6.9).
 *
 * 1. The huge heading moves only with the scroll: 0.3 px to the left for every pixel the block has risen.
 * 2. The cards drift right to left at 34 px/s, but only while the page stands still: scrolling, the pointer over
 *    the strip, a finger (which can also drag it), keyboard focus on the strip and the Pause button stop them
 *    (WCAG 2.2.2); the arrow keys then move it card by card. The speed eases in and out.
 *    The loop runs only while the block is on the screen.
 * 3. While the cards move, the one nearest the middle reads in full and the others step back; stopped, all read
 *    in full, so Pause also gives full contrast.
 *
 * With reduced motion nothing moves: the cards stand and scroll by hand (the CSS default), no Pause button.
 */

import { onScroll, reduceMotion } from '@amour/motion';

const root = document.querySelector( '[data-reviews]' );

if ( root && ! reduceMotion() ) {
	const strip = root.querySelector( '[data-reviews-strip]' );
	const scroller = root.querySelector( '[data-reviews-scroller]' );
	const track = root.querySelector( '[data-reviews-track]' );
	const pause = root.querySelector( '[data-reviews-pause]' );
	const cards = [ ...track.children ];
	const copy = track.querySelector( '.is-copy' );

	root.classList.add( 'is-live' );

	// 1. The heading
	onScroll( ( { vh } ) => {
		const top = root.getBoundingClientRect().top;
		strip.style.transform = `translate3d(${ ( 260 - Math.max( 0, vh - top ) * 0.3 ).toFixed( 1 ) }px, 0, 0)`;
	} );

	// 2. The cards. Sizes are read once and again on resize, never while the strip moves.
	let period = 1;
	let spots = [];
	const measure = () => {
		period = copy ? copy.offsetLeft - cards[ 0 ].offsetLeft : track.scrollWidth / 2;
		spots = cards.map( ( card ) => ( { card, mid: card.offsetLeft + card.offsetWidth / 2 } ) );
	};
	measure();
	window.addEventListener( 'resize', measure );

	let x = 0;
	let speed = 0;
	let lastScroll = 0;
	let prev = 0;
	let hover = false;
	let paused = false;
	let drag = null;
	let frame = 0;
	let on = null;
	let moving = false;

	const wrap = ( v ) => ( ( v % period ) + period ) % period;
	const place = () => {
		track.style.transform = `translate3d(${ ( -x ).toFixed( 2 ) }px, 0, 0)`;
	};

	const tick = ( now ) => {
		frame = requestAnimationFrame( tick );
		const dt = Math.min( 64, now - ( prev || now ) );
		prev = now;
		const focused = scroller.contains( document.activeElement );
		const still = now - lastScroll > 450 && ! hover && ! paused && ! focused && ! drag;
		speed += ( ( still ? 34 : 0 ) - speed ) * Math.min( 1, dt / 380 );
		if ( paused || ( ! still && speed < 0.5 ) ) {
			speed = 0; // a full stop, not an endless crawl
		}
		if ( ! drag ) {
			x = wrap( x + ( speed * dt ) / 1000 );
		}
		place();

		const nowMoving = still || speed > 2;
		if ( nowMoving !== moving ) {
			moving = nowMoving;
			root.classList.toggle( 'is-moving', moving );
		}
		if ( moving ) {
			const mid = window.innerWidth / 2 + x;
			let best = null;
			let distance = Infinity;
			spots.forEach( ( spot ) => {
				const d = Math.abs( spot.mid - mid );
				if ( d < distance ) {
					distance = d;
					best = spot.card;
				}
			} );
			if ( best !== on ) {
				on?.classList.remove( 'is-on' );
				best.classList.add( 'is-on' );
				on = best;
			}
		}
	};

	// Run only while the block is on the screen.
	new IntersectionObserver( ( [ entry ] ) => {
		if ( entry.isIntersecting && ! frame ) {
			prev = 0;
			frame = requestAnimationFrame( tick );
		} else if ( ! entry.isIntersecting && frame ) {
			cancelAnimationFrame( frame );
			frame = 0;
		}
	} ).observe( root );

	onScroll( () => {
		lastScroll = performance.now();
	} );
	window.addEventListener(
		'wheel',
		() => {
			lastScroll = performance.now();
		},
		{ passive: true }
	);

	scroller.addEventListener( 'mouseenter', () => {
		hover = true;
	} );
	scroller.addEventListener( 'mouseleave', () => {
		hover = false;
	} );

	// A finger stops the strip and drags it; let go, and it runs on after a moment.
	scroller.addEventListener( 'pointerdown', ( event ) => {
		if ( event.pointerType !== 'mouse' ) {
			drag = { from: event.clientX, x0: x };
		}
	} );
	scroller.addEventListener( 'pointermove', ( event ) => {
		if ( drag ) {
			x = wrap( drag.x0 - ( event.clientX - drag.from ) );
		}
	} );
	const release = () => {
		if ( drag ) {
			drag = null;
			lastScroll = performance.now() + 900;
		}
	};
	scroller.addEventListener( 'pointerup', release );
	scroller.addEventListener( 'pointercancel', release );

	// The keyboard: a link in a card off the screen brings that card in.
	scroller.addEventListener( 'focusin', ( event ) => {
		const card = event.target.closest( '.am-review' );
		scroller.scrollLeft = 0;
		if ( card ) {
			const left = card.offsetLeft - x;
			if ( left < 0 || left + card.offsetWidth > scroller.clientWidth ) {
				x = wrap( card.offsetLeft - track.offsetLeft - parseFloat( getComputedStyle( track ).paddingLeft ) );
				place();
			}
		}
	} );

	// Arrow keys move the strip by one card (focus on the strip has already stopped it).
	scroller.addEventListener( 'keydown', ( event ) => {
		const step = { ArrowRight: 1, ArrowLeft: -1 }[ event.key ];
		if ( step ) {
			event.preventDefault();
			x = wrap( x + step * ( cards[ 1 ].offsetLeft - cards[ 0 ].offsetLeft ) );
			place();
		}
	} );

	pause.addEventListener( 'click', () => {
		paused = ! paused;
		pause.textContent = paused ? pause.dataset.play : pause.dataset.pause;
	} );
}
