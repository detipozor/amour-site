/**
 * Thank you: the name and the booking summary. The booking engine will give them (week 5); until then the booking
 * page leaves them in sessionStorage "amour-booking" ({ name, number, stay, room, guests, paid, fee }), as in the
 * prototype. Without them the page shows its placeholders (a test copy) or hides the summary (the live site).
 */

const page = document.querySelector( '[data-thanks]' );

if ( page ) {
	let booking = {};
	try {
		booking = JSON.parse( window.sessionStorage.getItem( 'amour-booking' ) || '{}' ) || {};
	} catch {
		booking = {};
	}
	const title = page.querySelector( '[data-thanks-title]' );
	if ( booking.name ) {
		title.textContent = title.dataset.withName.replace( '%s', booking.name );
	}
	page.querySelectorAll( '[data-booking]' ).forEach( ( cell ) => {
		const value = booking[ cell.dataset.booking ];
		if ( value ) {
			cell.textContent = cell.dataset.with ? cell.dataset.with.replace( '%s', value ) : value;
			cell.parentElement.hidden = false;
			cell.closest( 'dl' ).hidden = false;
		}
	} );
}
