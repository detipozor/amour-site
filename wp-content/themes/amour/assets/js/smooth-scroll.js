/**
 * Smooth, slightly heavy scroll in the manner of the prototype (design/prototype-shell/app.js, "glide";
 * decision 19.11): the mouse wheel moves a target and the page glides to it (7.5% of the distance per frame).
 * Left alone: Ctrl + wheel (zoom), sideways gestures, boxes that scroll by themselves, the open menu.
 * Keys, the scrollbar, touch and anchors take over at once. Off with reduced motion.
 * Links to an anchor on the same page glide there too.
 */

import { reduceMotion } from '@amour/motion';

const root = document.documentElement;
const glide = { on: false, cur: 0, target: 0 };

const stop = () => {
	glide.on = false;
	glide.cur = glide.target = window.scrollY;
};

const step = () => {
	if ( ! glide.on ) {
		return;
	}
	glide.cur += ( glide.target - glide.cur ) * 0.075;
	if ( Math.abs( glide.target - glide.cur ) < 0.4 ) {
		glide.cur = glide.target;
		glide.on = false;
	}
	window.scrollTo( 0, glide.cur );
	if ( glide.on ) {
		requestAnimationFrame( step );
	}
};

const scrollsItself = ( el ) => {
	for ( let node = el; node && node !== document.body && node.nodeType === 1; node = node.parentElement ) {
		const overflow = getComputedStyle( node ).overflowY;
		if ( ( overflow === 'auto' || overflow === 'scroll' ) && node.scrollHeight > node.clientHeight + 1 ) {
			return true;
		}
	}
	return false;
};

const glideTo = ( y ) => {
	if ( ! glide.on ) {
		glide.cur = glide.target = window.scrollY;
	}
	glide.target = Math.max( 0, Math.min( root.scrollHeight - window.innerHeight, y ) );
	if ( ! glide.on ) {
		glide.on = true;
		requestAnimationFrame( step );
	}
};

if ( ! reduceMotion() ) {
	window.addEventListener(
		'wheel',
		( event ) => {
			if ( event.ctrlKey || Math.abs( event.deltaX ) > Math.abs( event.deltaY ) || root.classList.contains( 'menu-open' ) || scrollsItself( event.target ) ) {
				return;
			}
			event.preventDefault();
			let delta = event.deltaY;
			if ( event.deltaMode === 1 ) {
				delta *= 32;
			} else if ( event.deltaMode === 2 ) {
				delta *= window.innerHeight;
			}
			glideTo( ( glide.on ? glide.target : window.scrollY ) + delta );
		},
		{ passive: false }
	);

	// The scrollbar, a key, a touch or a jump elsewhere takes over at once.
	window.addEventListener( 'scroll', () => glide.on && Math.abs( window.scrollY - Math.round( glide.cur ) ) > 3 && stop(), { passive: true } );
	[ 'keydown', 'mousedown', 'touchstart' ].forEach( ( type ) => window.addEventListener( type, () => glide.on && stop(), { passive: true } ) );
}

// Anchors on the same page: glide there (or jump with reduced motion), then move focus for the keyboard.
document.addEventListener( 'click', ( event ) => {
	const link = event.target.closest?.( 'a[href*="#"]' );
	if ( ! link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey ) {
		return;
	}
	const url = new URL( link.href, window.location.href );
	if ( url.pathname !== window.location.pathname || url.search !== window.location.search || url.hash.length < 2 || link.closest( '.am-menu' ) || link.classList.contains( 'skip-link' ) ) {
		return;
	}
	const target = document.getElementById( decodeURIComponent( url.hash.slice( 1 ) ) );
	if ( ! target ) {
		return;
	}
	event.preventDefault();
	const y = target.getBoundingClientRect().top + window.scrollY - parseFloat( getComputedStyle( root ).scrollPaddingTop || '0' );
	if ( reduceMotion() ) {
		window.scrollTo( 0, y );
	} else {
		glideTo( y );
	}
	history.replaceState( null, '', url.hash );
	target.setAttribute( 'tabindex', target.getAttribute( 'tabindex' ) ?? '-1' );
	target.focus( { preventScroll: true } );
} );
