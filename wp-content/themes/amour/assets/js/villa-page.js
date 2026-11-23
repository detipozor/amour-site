/**
 * The villa page, on top of the room page (gen_villa.py: extraIn, floorsUI, the #events reveal).
 *
 * 1. Five bedrooms: the heading and the cards come up one after another once in view. Where the row scrolls
 *    sideways (tablets, phones), the keyboard can reach it and scroll it with the arrows.
 * 2. How the house works: pointing at, focusing or tapping a floor lights it up in the drawing.
 * 3. Weddings and gatherings: once in view the heading rises line by line, the text fades up,
 *    the arch opens from the bottom.
 * With reduced motion everything stands; the floors still light up.
 */

import { reduceMotion } from '@amour/motion';

const once = ( el, margin ) => {
	if ( ! el || reduceMotion() ) {
		return;
	}
	el.classList.add( 'is-ready' );
	const seen = new IntersectionObserver(
		( [ entry ] ) => {
			if ( entry.isIntersecting ) {
				el.classList.add( 'is-in' );
				seen.disconnect();
			}
		},
		{ rootMargin: `0px 0px ${ margin } 0px` }
	);
	seen.observe( el );
};

once( document.querySelector( '[data-villa-beds]' ), '-20%' );

const beds = document.querySelector( '[data-villa-beds-list]' );
if ( beds ) {
	const reach = () => {
		if ( beds.scrollWidth > beds.clientWidth + 1 ) {
			beds.tabIndex = 0;
		} else {
			beds.removeAttribute( 'tabindex' );
		}
	};
	reach();
	window.addEventListener( 'resize', reach );
}
once( document.querySelector( '[data-villa-events]' ), '-30%' );

const floors = document.querySelector( '[data-villa-floors]' );
if ( floors ) {
	const buttons = [ ...floors.querySelectorAll( '[data-floor]' ) ];
	const show = ( button ) => {
		floors.dataset.on = button.dataset.floor;
		buttons.forEach( ( b ) => b.setAttribute( 'aria-pressed', String( b === button ) ) );
	};
	buttons.forEach( ( button ) => {
		[ 'mouseenter', 'focus', 'click' ].forEach( ( type ) => button.addEventListener( type, () => show( button ) ) );
	} );
}
