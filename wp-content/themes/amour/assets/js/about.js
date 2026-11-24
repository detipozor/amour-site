/**
 * About: the philosophy lights up word by word as it rises through the screen (board About: q from 85% of the
 * screen over 70% of it; each word from 16% to full). CSS turns --q into each word's opacity.
 * With reduced motion, and on a screen taller than 1600 px (the whole phrase is in view at once), it is lit.
 */

import { onScroll, clamp, reduceMotion } from '@amour/motion';

const phrase = document.querySelector( '[data-ab-phrase]' );

if ( phrase ) {
	onScroll( ( { vh } ) => {
		const q = reduceMotion() || vh > 1600 ? 1 : clamp( ( vh * 0.85 - phrase.getBoundingClientRect().top ) / ( vh * 0.7 ) );
		phrase.style.setProperty( '--q', q.toFixed( 4 ) );
	} );
}
