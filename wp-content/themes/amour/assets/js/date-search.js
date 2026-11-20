/**
 * Date search links (HANDOFF, section 5): they open the booking page with its calendar ready.
 * The booking page reads sessionStorage "amour-cal" (week 5).
 */

document.addEventListener( 'click', ( event ) => {
	const link = event.target.closest?.( '[data-amour-cal]' );
	if ( ! link ) {
		return;
	}
	try {
		window.sessionStorage.setItem( 'amour-cal', link.dataset.amourCal );
	} catch {
		// Storage blocked: the booking page opens without the calendar unfolded.
	}
} );
