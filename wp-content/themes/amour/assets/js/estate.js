/**
 * Court and pool (board Estate, renderVals: cd, ps, pl, places, card).
 *
 * 1. First screen: the court video loads after the page, plays muted, can be paused (WCAG 2.2.2) and rests off
 *    screen. As the court rises over it (ce = (screen − court top) / screen) it darkens and the court's corners
 *    straighten.
 * 2. The court, pinned for 1000 px (ck): the serve in the low sun turns into the lit court at night from 30% to
 *    70% of the way (night, eased); the day line fades back, the night line comes up; the dot runs Day → Night.
 * 3. The pool, pinned for 3 × 460 px: one point and one photo per step; the photo comes up from the bottom.
 *    Not pinned: pointing at or focusing a point shows its photo.
 * 4. From above: pointing at a name previews the place, a click keeps it open, a click on the photo closes it;
 *    a phone opens the first place from the start. The photo drifts 3.5% against the scroll.
 * 5. Headings and texts come up softly once in view.
 * Reduced motion: nothing pinned, no video, no drift; the places still open.
 */

import { onScroll, clamp, smooth, reduceMotion } from '@amour/motion';

const page = document.querySelector( '[data-estate]' );

if ( page ) {
	const motion = ! reduceMotion();
	const hero = page.querySelector( '[data-es-hero]' );
	const court = page.querySelector( '[data-es-court]' );
	const poolPin = page.querySelector( '[data-es-pool-pin]' );
	const points = [ ...page.querySelectorAll( '.am-es-point' ) ];
	const pictures = [ ...page.querySelectorAll( '.am-es-pool__img' ) ];
	const count = page.querySelector( '[data-es-count]' );
	const box = page.querySelector( '[data-es-plan-box]' );

	/* 1. The video */
	const video = hero?.querySelector( 'video' );
	const pause = hero?.querySelector( '[data-es-pause]' );
	if ( video && pause && motion ) {
		let userPaused = false;
		const setPaused = ( paused ) => {
			hero.classList.toggle( 'is-paused', paused );
			pause.setAttribute( 'aria-label', paused ? pause.dataset.labelPlay : pause.dataset.labelPause );
		};
		const play = () => video.play().catch( () => setPaused( true ) );
		video.addEventListener( 'playing', () => {
			hero.classList.add( 'is-playing' );
			setPaused( false );
		} );
		const start = () => {
			video.src = video.dataset.src;
			pause.hidden = false;
			play();
		};
		if ( document.readyState === 'complete' ) {
			start();
		} else {
			window.addEventListener( 'load', start, { once: true } );
		}
		pause.addEventListener( 'click', () => {
			userPaused = ! video.paused;
			if ( video.paused ) {
				play();
			} else {
				video.pause();
				setPaused( true );
			}
		} );
		new IntersectionObserver( ( [ entry ] ) => {
			if ( ! video.src ) {
				return;
			}
			if ( ! entry.isIntersecting ) {
				video.pause();
			} else if ( ! userPaused ) {
				play();
			}
		} ).observe( hero );
	}

	/* 3. The pool: which point is up */
	let step = -1;
	const setStep = ( k ) => {
		if ( k === step ) {
			return;
		}
		step = k;
		poolPin.dataset.step = String( k );
		points.forEach( ( p, i ) => p.classList.toggle( 'is-on', i === k ) );
		pictures.forEach( ( p, i ) => p.classList.toggle( 'is-shown', i <= k ) );
		count.textContent = `0${ k + 1 }`;
	};
	if ( poolPin ) {
		setStep( 0 );
		points.forEach( ( p, i ) => {
			[ 'mouseenter', 'focusin' ].forEach( ( type ) =>
				p.addEventListener( type, () => {
					if ( ! page.classList.contains( 'is-pinned' ) ) {
						setStep( i );
					}
				} )
			);
		} );
	}

	/* 4. From above */
	if ( box ) {
		const holes = [ ...box.querySelectorAll( '.am-es-plan__hole' ) ];
		const outlines = [ ...box.querySelectorAll( '.am-es-plan__outlines path' ) ];
		const spots = [ ...box.querySelectorAll( '.am-es-spot' ) ];
		const cards = [ ...box.querySelectorAll( '.am-es-card' ) ];
		const chips = [ ...page.querySelectorAll( '.am-es-chip' ) ];
		const sayName = page.querySelector( '[data-es-say-name]' );
		const sayText = page.querySelector( '[data-es-say-text]' );
		const phone = () => document.documentElement.clientWidth <= 760;
		let zone = -1;
		let hover = -1;

		const show = () => {
			const on = hover >= 0 ? hover : zone < 0 && phone() ? 0 : zone;
			box.dataset.on = String( on );
			const mark = ( list ) => list.forEach( ( el ) => el.classList.toggle( 'is-on', Number( el.dataset.place ?? el.dataset.card ) === on ) );
			[ holes, outlines, spots, cards, chips ].forEach( mark );
			[ ...spots, ...chips ].forEach( ( el ) => el.setAttribute( 'aria-pressed', String( Number( el.dataset.place ) === zone && on === zone ) ) );
			const card = cards[ on ];
			sayName.textContent = card ? card.querySelector( '.am-es-card__name' ).textContent : '';
			sayText.textContent = card ? card.querySelector( '.am-es-card__text' ).textContent : '';
		};
		const toggle = ( i ) => {
			zone = zone === i ? -1 : i;
			hover = -1;
			show();
		};

		spots.forEach( ( spot, i ) => {
			spot.addEventListener( 'mouseenter', () => {
				hover = i;
				show();
			} );
			spot.addEventListener( 'focus', () => {
				hover = i;
				show();
			} );
			[ 'mouseleave', 'blur' ].forEach( ( type ) =>
				spot.addEventListener( type, () => {
					hover = -1;
					show();
				} )
			);
			spot.addEventListener( 'click', ( event ) => {
				event.stopPropagation();
				toggle( i );
			} );
		} );
		chips.forEach( ( chip, i ) => chip.addEventListener( 'click', () => toggle( i ) ) );
		box.addEventListener( 'click', () => {
			zone = -1;
			hover = -1;
			show();
		} );
		show();
		window.addEventListener( 'resize', show );
		// Fades only from the next frame on, so the first state just stands (estate.css).
		requestAnimationFrame( () => requestAnimationFrame( () => page.querySelector( '[data-es-plan]' ).classList.add( 'is-live' ) ) );
	}

	/* 5. Come up once in view */
	if ( motion ) {
		page.querySelectorAll( '.am-es-enter' ).forEach( ( el ) => {
			el.classList.add( 'is-ready' );
			const seen = new IntersectionObserver(
				( [ entry ] ) => {
					if ( entry.isIntersecting ) {
						el.classList.add( 'is-in' );
						seen.disconnect();
					}
				},
				{ rootMargin: '0px 0px -22% 0px' }
			);
			seen.observe( el );
		} );
	}

	/* 1–4. Scroll */
	let pinned = null;
	onScroll( ( { vh } ) => {
		const want = motion && vh >= 480;
		if ( want !== pinned ) {
			pinned = want;
			page.classList.toggle( 'is-pinned', pinned );
		}
		const courtTop = court ? court.getBoundingClientRect().top : 0;
		const poolTop = poolPin ? poolPin.getBoundingClientRect().top : 0;
		const plan = box ? box.getBoundingClientRect() : null;

		const ce = pinned ? clamp( ( vh - courtTop ) / vh ) : 1;
		const ck = pinned ? clamp( -courtTop / 1000 ) : 0;
		page.style.setProperty( '--ce', ce.toFixed( 3 ) );
		page.style.setProperty( '--ck', ck.toFixed( 3 ) );
		page.style.setProperty( '--night', smooth( clamp( ( ck - 0.3 ) / 0.4 ) ).toFixed( 3 ) );

		if ( poolPin && pinned ) {
			setStep( Math.min( points.length - 1, Math.floor( clamp( -poolTop / 1380 ) * 3 ) ) );
		}

		if ( box ) {
			// The frame holds the whole photo: on a short screen it narrows instead of losing the bottom.
			const docW = document.documentElement.clientWidth;
			const gut = clamp( window.innerWidth * 0.04, 16, 56 );
			const free = docW - 2 * gut;
			box.style.setProperty( '--plan-w', `${ Math.round( pinned ? Math.min( free, Math.max( 560, ( vh - 48 ) * 1.54 ) ) : free ) }px` );
			const drift = pinned ? clamp( ( vh - plan.top ) / ( vh + plan.height ) ) : 0.5;
			box.style.setProperty( '--plan-ty', `${ ( pinned ? ( 0.5 - drift ) * 7 : 0 ).toFixed( 2 ) }%` );
			box.style.setProperty( '--plan-sc', pinned ? '1.08' : '1' );
		}
	} );
}
