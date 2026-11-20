/**
 * Shared helpers for scroll-linked blocks: one scroll listener and one animation frame for all of them.
 * Blocks read the scroll position and write unitless CSS variables; CSS turns them into sizes and colours.
 */

export const reduceMotion = () => window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

export const clamp = ( v, min = 0, max = 1 ) => Math.max( min, Math.min( max, v ) );

/** The boards' soft curve: slow start, slow finish. */
export const smooth = ( v ) => v * v * ( 3 - 2 * v );

const listeners = new Set();
let queued = false;

const run = () => {
	queued = false;
	const state = { y: window.scrollY, vh: window.innerHeight, vw: window.innerWidth };
	listeners.forEach( ( listener ) => listener( state ) );
};

const queue = () => {
	if ( ! queued ) {
		queued = true;
		requestAnimationFrame( run );
	}
};

window.addEventListener( 'scroll', queue, { passive: true } );
window.addEventListener( 'resize', queue );

/**
 * Call a function on every frame in which the page scrolled or resized (and once now).
 *
 * @param {Function} listener Receives { y, vh, vw }.
 * @return {Function} Stops listening.
 */
export const onScroll = ( listener ) => {
	listeners.add( listener );
	queue();
	return () => listeners.delete( listener );
};
