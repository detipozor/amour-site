/**
 * Moving between pages (decision 19.11): the curtain for ordinary links, the soft dissolve from the menu.
 * The arriving half runs before the first paint, from a small script in <head> (amour_arrival_script()):
 * it reads sessionStorage "amour-arrive" and puts is-arriving--curtain or is-arriving--soft on <html>.
 * Nothing here runs with reduced motion: links open as usual.
 */

import { loadLogos } from '@amour/logos';

const root = document.documentElement;
const curtain = document.querySelector( '.am-curtain' );
const villa = document.querySelector( '.am-header' )?.dataset.villa || '';
const KEY = 'amour-arrive';

export const reduceMotion = () => window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

const remember = ( kind ) => {
	try {
		window.sessionStorage.setItem( KEY, kind );
	} catch {
		// Private mode or storage blocked: the next page simply appears without the second half.
	}
};

/** From the menu: the names lift away (menu.css), then the next page loads under the dissolving panel. */
export const softGo = ( href ) => {
	remember( 'soft' );
	setTimeout( () => window.location.assign( href ), 460 );
};

/** Ordinary link: the curtain in the colour of the next page's house covers the screen, then the page changes. */
const curtainGo = ( href ) => {
	const target = new URL( href );
	const house = villa && target.pathname.startsWith( new URL( villa ).pathname ) ? 'eudora' : 'amour';
	loadLogos(); // a click before the page has loaded: the other house's logo is still waiting
	curtain?.setAttribute( 'data-theme', house );
	root.classList.add( 'is-leaving' );
	remember( 'curtain' );
	setTimeout( () => window.location.assign( href ), 680 );
};

const followsCurtain = ( link, event ) => {
	if ( ! link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
		return false;
	}
	if ( ( link.target && link.target !== '_self' ) || link.hasAttribute( 'download' ) || link.closest( '.am-menu, #wpadminbar, [data-no-curtain]' ) ) {
		return false;
	}
	const url = new URL( link.href, window.location.href );
	if ( url.origin !== window.location.origin || ! /^https?:$/.test( url.protocol ) ) {
		return false;
	}
	if ( url.pathname === window.location.pathname && url.search === window.location.search ) {
		return false; // an anchor on this page
	}
	return ! /^\/wp-(admin|login)|\.(pdf|zip|jpe?g|png|webp|svg|mp4)$/i.test( url.pathname );
};

if ( curtain && ! reduceMotion() ) {
	document.addEventListener( 'click', ( event ) => {
		const link = event.target.closest?.( 'a[href]' );
		if ( followsCurtain( link, event ) ) {
			event.preventDefault();
			curtainGo( link.href );
		}
	} );
}

/* The arriving half: once it has played, clear the classes so nothing replays */
if ( root.classList.contains( 'is-arriving' ) ) {
	setTimeout( () => root.classList.remove( 'is-arriving', 'is-arriving--curtain', 'is-arriving--soft' ), 1300 );
}

/* Back or forward from the page cache: show the page as it is, without a curtain over it */
window.addEventListener( 'pageshow', ( event ) => {
	if ( event.persisted ) {
		root.classList.remove( 'is-leaving', 'is-arriving', 'is-arriving--curtain', 'is-arriving--soft' );
		curtain?.removeAttribute( 'data-theme' );
	}
} );
