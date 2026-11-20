/**
 * Room page (boards Room-*; design/generators/room_template.html, renderVals: figs, stageBg, detIn, more).
 *
 * 1. Each photo comes in as an arch and opens into a soft rectangle while it rises through the screen:
 *    k = (98% of the screen − its top) / 60% of the screen, written as --k (CSS turns it into corners and zoom).
 * 2. The wine runs into limestone as the details come up: t = (95% of the screen − their top) / 35%
 *    (phone: from 55% over 30%, since the photos sit right above the details). Written as --t on the page;
 *    the details turn their text light while they are still dark, and tell the header which tone is under it.
 * 3. The details come up softly once in view; All details opens the rest.
 * 4. The price capsule steps aside while a key fact, a button, a heading or the footer would sit under it.
 *
 * With reduced motion: photos stand open, colours stand still, the details simply stand.
 */

import { onScroll, clamp, reduceMotion } from '@amour/motion';

const page = document.querySelector( '[data-room]' );

if ( page ) {
	const stage = page.querySelector( '[data-room-stage]' );
	const figs = [ ...page.querySelectorAll( '[data-room-fig]' ) ];
	const details = page.querySelector( '[data-room-details]' );
	const more = page.querySelector( '[data-room-more]' );
	const dock = page.querySelector( '[data-dock]' );

	/* 3. All details (works with reduced motion too) */
	if ( more ) {
		const all = document.getElementById( more.getAttribute( 'aria-controls' ) );
		const label = more.querySelector( '[data-room-more-label]' );
		more.addEventListener( 'click', () => {
			const open = more.getAttribute( 'aria-expanded' ) !== 'true';
			more.setAttribute( 'aria-expanded', String( open ) );
			all.hidden = ! open;
			label.textContent = open ? more.dataset.open : more.dataset.closed;
		} );
	}

	/* 4. The capsule: a band at the bottom of the screen; anything marked data-dock-avoid, or the footer, in it hides the pill */
	if ( dock ) {
		const avoid = [ ...document.querySelectorAll( '[data-dock-avoid], .am-footer' ) ];
		const inBand = new Set();
		let watch = null;
		const band = () => {
			watch?.disconnect();
			inBand.clear();
			const pill = dock.firstElementChild.getBoundingClientRect();
			const vh = window.innerHeight;
			// The band: where the pill stands when stuck to the bottom, plus 16 px around it.
			const top = Math.max( 0, vh - 24 - 48 - pill.height - 16 );
			watch = new IntersectionObserver(
				( entries ) => {
					entries.forEach( ( entry ) => ( entry.isIntersecting ? inBand.add( entry.target ) : inBand.delete( entry.target ) ) );
					dock.classList.toggle( 'is-away', inBand.size > 0 );
				},
				{ rootMargin: `${ -top }px 0px -${ 24 + 48 - 16 }px 0px` }
			);
			avoid.forEach( ( el ) => watch.observe( el ) );
		};
		band();
		window.addEventListener( 'resize', band );
	}

	if ( ! reduceMotion() ) {
		page.classList.add( 'is-live' );

		/* 3. The details come up once a quarter of the screen into view */
		details.classList.add( 'is-ready' );
		const seen = new IntersectionObserver(
			( [ entry ] ) => {
				if ( entry.isIntersecting ) {
					details.classList.add( 'is-in' );
					seen.disconnect();
				}
			},
			{ rootMargin: '0px 0px -25% 0px' }
		);
		seen.observe( details );

		/* 1–2. Photos and colour, every frame the page scrolls */
		let dark = null;
		onScroll( ( { vh, vw } ) => {
			const tops = figs.map( ( fig ) => fig.getBoundingClientRect().top );
			const detTop = details.getBoundingClientRect().top;

			tops.forEach( ( top, i ) => {
				figs[ i ].style.setProperty( '--k', clamp( ( vh * 0.98 - top ) / ( vh * 0.6 ) ).toFixed( 3 ) );
			} );

			const t = vw < 760 ? clamp( ( vh * 0.55 - detTop ) / ( vh * 0.3 ) ) : clamp( ( vh * 0.95 - detTop ) / ( vh * 0.35 ) );
			page.style.setProperty( '--t', t.toFixed( 3 ) );
			const isDark = t < 0.5;
			if ( isDark !== dark ) {
				dark = isDark;
				details.classList.toggle( 'is-dark', dark );
				details.dataset.tone = dark ? 'dark' : 'light';
				stage.dataset.tone = dark ? 'dark' : 'light';
			}
		} );
	}
}
