/**
 * Footer motion (design/js/amour-live.js, parts 1):
 * - the olive wave runs only while the footer is on screen;
 * - the olives light up under the cursor (--mx, --my for the lens mask);
 * - the big word rises in as the footer comes into view (--fw from 0 to 1).
 * With reduced motion the word simply stands in place and the olives stay still (CSS).
 */

const footer = document.querySelector( '.am-footer' );

if ( footer ) {
	const still = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	const clamp = ( v ) => Math.max( 0, Math.min( 1, v ) );

	let visible = false;
	let queued = false;

	const rise = () => {
		queued = false;
		if ( still.matches ) {
			footer.style.setProperty( '--fw', '1' );
			return;
		}
		const vh = window.innerHeight;
		const top = footer.getBoundingClientRect().top;
		footer.style.setProperty( '--fw', clamp( ( vh - top ) / ( vh * 0.9 ) ).toFixed( 3 ) );
	};
	const queue = () => {
		if ( visible && ! queued ) {
			queued = true;
			requestAnimationFrame( rise );
		}
	};

	new IntersectionObserver( ( [ entry ] ) => {
		visible = entry.isIntersecting;
		footer.classList.toggle( 'is-live', visible );
		queue();
	} ).observe( footer );

	window.addEventListener( 'scroll', queue, { passive: true } );
	window.addEventListener( 'resize', queue );
	rise();

	footer.addEventListener(
		'pointermove',
		( event ) => {
			if ( event.pointerType !== 'mouse' ) {
				return;
			}
			const box = footer.getBoundingClientRect();
			footer.style.setProperty( '--mx', `${ Math.round( event.clientX - box.left ) }px` );
			footer.style.setProperty( '--my', `${ Math.round( event.clientY - box.top ) }px` );
		},
		{ passive: true }
	);
	footer.addEventListener( 'pointerleave', () => {
		footer.style.setProperty( '--mx', '-999px' );
		footer.style.setProperty( '--my', '-999px' );
	} );
}
