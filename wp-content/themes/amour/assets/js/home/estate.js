/**
 * The estate (board Main-v2, renderVals: r = smooth((vh - top) / vh)): the photo grows to the full width,
 * its rounded top flattens and the heading rises 40 px. With reduced motion it stands at full width.
 */

import { onScroll, clamp, smooth, reduceMotion } from '@amour/motion';

const block = document.querySelector( '[data-estate]' );

if ( block ) {
	onScroll( ( { vh } ) => {
		const r = reduceMotion() ? 1 : smooth( clamp( ( vh - block.getBoundingClientRect().top ) / vh ) );
		block.style.setProperty( '--r', r.toFixed( 4 ) );
	} );
}
