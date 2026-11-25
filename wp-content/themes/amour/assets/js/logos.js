/**
 * Logos that are not on the first screen (inc/brand.php, amour_logo() with $later) get their file a moment
 * after the page has loaded, so they never compete with the first screen on a phone (HANDOFF, section 13).
 * Leaving for the other house (assets/js/transitions.js) loads them at once.
 */

export const loadLogos = () => {
	for ( const use of document.querySelectorAll( 'use[data-href]' ) ) {
		use.setAttribute( 'href', use.dataset.href );
		delete use.dataset.href;
	}
};

const idle = window.requestIdleCallback || ( ( callback ) => window.setTimeout( callback, 200 ) );
const later = () => window.setTimeout( () => idle( loadLogos, { timeout: 2000 } ), 1500 );

if ( document.readyState === 'complete' ) {
	later();
} else {
	window.addEventListener( 'load', later, { once: true } );
}
