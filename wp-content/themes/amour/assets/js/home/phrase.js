/**
 * The phrase on the wine block (board Main-v2, renderVals):
 * - --ph: the block grows from a framed card to the full width as it comes up (over 70% of the screen);
 * - --q:  the words light up one by one, from 85% of the screen height up over 75% of it.
 * With reduced motion the block is full width and the phrase is lit at once.
 */

import { onScroll, clamp, smooth, reduceMotion } from '@amour/motion';

const block = document.querySelector( '[data-phrase]' );

if ( block ) {
	onScroll( ( { vh } ) => {
		if ( reduceMotion() ) {
			block.style.setProperty( '--ph', '1' );
			block.style.setProperty( '--q', '1' );
			return;
		}
		const top = block.getBoundingClientRect().top;
		block.style.setProperty( '--ph', smooth( clamp( ( vh - top ) / ( vh * 0.7 ) ) ).toFixed( 4 ) );
		block.style.setProperty( '--q', clamp( ( vh * 0.85 - top ) / ( vh * 0.75 ) ).toFixed( 4 ) );
	} );
}
