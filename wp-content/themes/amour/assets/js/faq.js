/**
 * Questions: the topic in view is marked in the list of topics (on a phone the chip scrolls into sight).
 * The accordion itself is the browser's own (details and summary), so it works without this script.
 */

const page = document.querySelector( '[data-faq]' );

if ( page ) {
	const links = [ ...page.querySelectorAll( '[data-faq-link]' ) ];
	const mark = ( slug ) => {
		links.forEach( ( link ) => {
			const on = link.dataset.faqLink === slug;
			link.classList.toggle( 'is-on', on );
			if ( on ) {
				link.setAttribute( 'aria-current', 'true' );
			} else {
				link.removeAttribute( 'aria-current' );
			}
		} );
	};
	// The topic whose top has passed the upper third of the screen is the one being read.
	const spy = new IntersectionObserver(
		( entries ) => {
			entries.filter( ( e ) => e.isIntersecting ).forEach( ( e ) => mark( e.target.id ) );
		},
		{ rootMargin: '-30% 0px -60% 0px' }
	);
	page.querySelectorAll( '[data-faq-topic]' ).forEach( ( topic ) => spy.observe( topic ) );
}
