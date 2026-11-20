/**
 * Book direct on the home page (board Main-v2-b, "cta": enter('cta')): the text comes up softly, from 16 px
 * lower and out of a light blur, once the block is well in view. With reduced motion it simply stands.
 */

import { reduceMotion } from '@amour/motion';

const block = document.querySelector( '[data-direct]' );

if ( block && ! reduceMotion() ) {
	block.classList.add( 'is-ready' );
	const seen = new IntersectionObserver(
		( [ entry ] ) => {
			if ( entry.isIntersecting ) {
				block.classList.add( 'is-in' );
				seen.disconnect();
			}
		},
		{ threshold: 0.3 }
	);
	seen.observe( block );
}
