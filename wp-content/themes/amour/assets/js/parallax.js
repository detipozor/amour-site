/**
 * Photo parallax (design/js/amour-live.js, part 2): a picture that fills a clipped frame is scaled up a little
 * and drifts against the scroll. Only pictures marked data-parallax take part:
 *   data-parallax=""       everywhere (6% drift);
 *   data-parallax="phone"  only on a phone, stronger (8%), as the room cards at Casa Momo.
 * Off with reduced motion.
 */

import { onScroll, clamp, reduceMotion } from '@amour/motion';

const pictures = [ ...document.querySelectorAll( 'img[data-parallax]' ) ];

if ( pictures.length && ! reduceMotion() ) {
	onScroll( ( { vh, vw } ) => {
		const phone = vw < 760;
		for ( const img of pictures ) {
			const phoneOnly = img.dataset.parallax === 'phone';
			if ( phoneOnly && ! phone ) {
				img.style.removeProperty( 'scale' );
				img.style.removeProperty( 'translate' );
				continue;
			}
			const frame = img.parentElement.getBoundingClientRect();
			if ( frame.bottom < -40 || frame.top > vh + 40 ) {
				continue;
			}
			const k = clamp( ( frame.top + frame.height / 2 - vh / 2 ) / ( vh / 2 + frame.height / 2 ), -1, 1 );
			img.style.scale = phoneOnly ? '1.2' : '1.14';
			img.style.translate = `0 ${ ( -k * ( phoneOnly ? 8 : 6 ) ).toFixed( 2 ) }%`;
		}
	} );
}
