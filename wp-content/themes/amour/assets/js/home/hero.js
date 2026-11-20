/**
 * First screen (board Main-v2, renderVals: p = smooth(y / 520)).
 * - --p from 0 to 1 over the first 520 px of scroll: the arch opens to the whole screen (home.css).
 * - The video loads after the page and plays muted; the poster stays with reduced motion.
 * - Pause button for the video (WCAG 2.2.2); the video also rests while it is off screen.
 */

import { onScroll, clamp, smooth, reduceMotion } from '@amour/motion';

const hero = document.querySelector( '[data-hero]' );

if ( hero ) {
	onScroll( ( { y } ) => {
		const p = reduceMotion() ? 0 : smooth( clamp( y / 520 ) );
		hero.style.setProperty( '--p', p.toFixed( 4 ) );
	} );

	const video = hero.querySelector( 'video' );
	const pause = hero.querySelector( '[data-hero-pause]' );

	if ( video && ! reduceMotion() ) {
		const phone = window.innerWidth < 760 && video.dataset.srcPhone;
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
			video.src = phone || video.dataset.src;
			pause.hidden = false;
			play();
		};
		// After the page itself, so the video never competes with the first paint.
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

		// Off screen it rests; back on screen it plays again unless the visitor paused it.
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
}
