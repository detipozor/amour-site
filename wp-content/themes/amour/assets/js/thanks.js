/**
 * Thank you: the name and the booking summary, and Add to calendar. The booking engine will give the booking; until
 * then the booking page leaves it in sessionStorage "amour-booking" ({ name, number, stay, room, guests, paid, fee,
 * arrive, leave }; arrive and leave as YYYY-MM-DD). Without it the page shows its placeholders (a test copy) or
 * hides the summary (the live site), and Add to calendar stays hidden.
 */

const page = document.querySelector( '[data-thanks]' );

/**
 * An .ics file with the stay as an all-day event, from the arrival day to the departure day.
 *
 * @param {Object} event   Title, place and note from the page.
 * @param {Object} booking The booking.
 * @return {string} The file.
 */
const ics = ( event, booking ) => {
	const day = ( iso ) => iso.replace( /-/g, '' );
	// Commas, semicolons and backslashes are escaped in iCalendar text.
	const esc = ( value ) => String( value || '' ).replace( /[\\;,]/g, ( c ) => '\\' + c ).replace( /\n/g, '\\n' );
	const stamp = new Date().toISOString().replace( /[-:]/g, '' ).replace( /\.\d+/, '' );
	const title = booking.room ? event.title + ' · ' + booking.room : event.title;
	const note = [ booking.number, event.note ].filter( Boolean ).join( ' · ' );
	return [
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//Amour Relax Hotel//Booking//EN',
		'BEGIN:VEVENT',
		'UID:' + day( booking.arrive ) + '-' + ( booking.number || stamp ) + '@amourrelaxhotel.com',
		'DTSTAMP:' + stamp,
		'DTSTART;VALUE=DATE:' + day( booking.arrive ),
		'DTEND;VALUE=DATE:' + day( booking.leave ),
		'SUMMARY:' + esc( title ),
		event.place ? 'LOCATION:' + esc( event.place ) : '',
		note ? 'DESCRIPTION:' + esc( note ) : '',
		'END:VEVENT',
		'END:VCALENDAR',
	]
		.filter( Boolean )
		.join( '\r\n' );
};

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

	const calendar = page.querySelector( '[data-thanks-calendar]' );
	const dates = /^\d{4}-\d{2}-\d{2}$/;
	if ( calendar && dates.test( booking.arrive || '' ) && dates.test( booking.leave || '' ) ) {
		calendar.hidden = false;
		calendar.addEventListener( 'click', () => {
			const file = new Blob( [ ics( JSON.parse( calendar.dataset.event ), booking ) ], { type: 'text/calendar;charset=utf-8' } );
			const link = document.createElement( 'a' );
			link.href = URL.createObjectURL( file );
			link.download = 'amour-stay.ics';
			document.body.append( link );
			link.click();
			link.remove();
			setTimeout( () => URL.revokeObjectURL( link.href ), 1000 );
		} );
	}
}
