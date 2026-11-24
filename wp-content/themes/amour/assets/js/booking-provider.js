/**
 * BookingProvider (HANDOFF, section 7): the booking page asks it what is free and where to send the guest, so
 * connecting the real engine changes only this file.
 *
 *   search( query )        → Promise of the free units, each with nights and total (a number, or null when unknown)
 *   handoff( unit, query ) → the address the guest goes to after Select
 *
 * query: { arrive: 'YYYY-MM-DD', leave: 'YYYY-MM-DD', guests: 2, nights: 7 }.
 * unit (inc/booking.php): { id, name, guests, price, seasons: [ { first, last, price } ], min, code, villa }.
 *
 * Which one runs is set in inc/booking.php:
 * - link: the engine address from Hotel settings with {arrive}, {leave}, {guests} and {room} filled in. It does not
 *   know what is free or what it costs: it offers every unit that fits, and the engine tells the rest.
 * - mock: a test copy before the engine. Everything that fits is free; a room costs its price "from" a night, the
 *   villa its price for the season of each night. Select leaves the booking for the thank-you page, as if paid.
 * - contact: the live site before the engine: what fits, without prices; Select leads to the contact page.
 */

const DAY = 86400000;
const time = ( day ) => Date.parse( day + 'T00:00:00Z' );

/**
 * The units that take this many guests for this many nights.
 *
 * @param {Array}  units The units.
 * @param {Object} query The search.
 * @return {Array} The units that fit.
 */
const fitting = ( units, query ) => units.filter( ( unit ) => unit.guests >= query.guests && query.nights >= ( unit.min || 1 ) );

/**
 * The test price: a room's price "from" for every night, the villa's season price for each night.
 *
 * @param {Object} unit  The unit.
 * @param {Object} query The search.
 * @return {?number} The total, or null when a night has no price.
 */
const testTotal = ( unit, query ) => {
	if ( ! unit.villa || ! unit.seasons.length ) {
		return typeof unit.price === 'number' ? unit.price * query.nights : null;
	}
	let sum = 0;
	for ( let i = 0; i < query.nights; i++ ) {
		const night = time( query.arrive ) + i * DAY;
		const season = unit.seasons.find( ( s ) => night >= time( s.first ) && night <= time( s.last ) );
		if ( ! season ) {
			return null;
		}
		sum += season.price;
	}
	return sum;
};

/**
 * The booking the thank-you page shows (assets/js/thanks.js reads it).
 *
 * @param {Object} booking What the guest booked.
 */
const remember = ( booking ) => {
	try {
		window.sessionStorage.setItem( 'amour-booking', JSON.stringify( booking ) );
	} catch {
		// Storage blocked: the thank-you page shows its placeholders.
	}
};

/**
 * The provider for this site.
 *
 * @param {Object} config What inc/booking.php gives: provider, engine, contact, thanks, units.
 * @return {{search: Function, handoff: Function}} The provider.
 */
export function bookingProvider( config ) {
	const units = config.units || [];

	if ( config.provider === 'link' ) {
		return {
			search: async ( query ) => fitting( units, query ).map( ( unit ) => ( { ...unit, nights: query.nights, total: null } ) ),
			handoff: ( unit, query ) => {
				const values = { arrive: query.arrive, leave: query.leave, guests: query.guests, room: unit.code };
				return config.engine.replace( /\{(arrive|leave|guests|room)\}/g, ( _, key ) => encodeURIComponent( values[ key ] ) );
			},
		};
	}

	if ( config.provider === 'mock' ) {
		return {
			search: async ( query ) => fitting( units, query ).map( ( unit ) => ( { ...unit, nights: query.nights, total: testTotal( unit, query ) } ) ),
			handoff: ( unit, query ) => {
				remember( { room: unit.name, stay: query.stayText, guests: query.guestsText, paid: query.paidText, arrive: query.arrive, leave: query.leave } );
				return config.thanks;
			},
		};
	}

	return {
		search: async ( query ) => fitting( units, query ).map( ( unit ) => ( { ...unit, nights: query.nights, total: null } ) ),
		handoff: () => config.contact,
	};
}
