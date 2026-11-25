/**
 * Book your stay (board Book, its renderVals()): the steps, Arrive and Leave with our own calendar, Guests with
 * − and +, Search, and the rooms and the villa free for the dates with the price for the whole stay. What is free,
 * what it costs and where Select leads come from the BookingProvider (booking-provider.js).
 *
 * The calendar: the season from Hotel settings (and nothing before today) can be picked, the rest is crossed out.
 * Picking the arrival moves on to the leaving day; picking that runs the search, as on the board.
 * The keyboard: the arrows move a day or a week, Page Up and Page Down a month, Enter picks, Esc closes and
 * gives the focus back to the field. Dates are written with the site's own month and day names, never the
 * browser's (the page language decides).
 */

import { bookingProvider } from '@amour/booking-provider';

const page = document.querySelector( '[data-book]' );

if ( page ) {
	const config = JSON.parse( page.dataset.bookConfig || '{}' );
	const provider = bookingProvider( config );
	const text = config.text;
	const DAY = 86400000;
	const $ = ( selector ) => page.querySelector( selector );
	const $$ = ( selector ) => [ ...page.querySelectorAll( selector ) ];

	const search = $( '[data-bk-search]' );
	const cal = $( '[data-bk-cal]' );
	const days = $( '[data-bk-days]' );
	const monthName = $( '[data-bk-month-name]' );
	const calHint = $( '[data-bk-cal-hint]' );
	const fields = { arr: $( '[data-bk-open="arr"]' ), dep: $( '[data-bk-open="dep"]' ) };
	const guestsOut = $( '[data-bk-guests]' );
	const note = $( '[data-bk-note]' );
	const list = $( '[data-bk-units]' );
	const test = $( '[data-bk-test]' );
	const steps = $$( '[data-bk-step]' );

	// The days that can be booked: the season, and nothing before today.
	const now = new Date();
	const today = Date.UTC( now.getFullYear(), now.getMonth(), now.getDate() );
	const day = ( iso ) => ( iso ? Date.parse( iso + 'T00:00:00Z' ) : NaN );
	const first = Math.max( today, day( config.season.first ) || today );
	const last = day( config.season.last ) || today + 365 * DAY;
	const iso = ( t ) => new Date( t ).toISOString().slice( 0, 10 );

	const state = {
		arr: null,
		dep: null,
		guests: 2,
		max: Number( guestsOut.dataset.max ) || 2,
		open: null,
		view: new Date( first ),
		searched: false,
		found: [],
	};

	// The order of the parts comes with the language (inc/booking.php): Mon 1 Mar, Mo., 1. März.
	const fill = ( pattern, ...parts ) => parts.reduce( ( out, part, i ) => out.replace( `%${ i + 1 }$s`, part ), pattern );
	const fmt = ( t ) => {
		const d = new Date( t );
		return fill( text.day, config.weekdays[ d.getUTCDay() ], d.getUTCDate(), config.short[ d.getUTCMonth() ] );
	};
	const long = ( t, year ) => {
		const d = new Date( t );
		return fill( text.date, d.getUTCDate(), ( config.genitive || config.months )[ d.getUTCMonth() ] ) + ( year ? ' ' + d.getUTCFullYear() : '' );
	};
	// A count in the language's own forms: 1 night, 2 nights; 1 noc, 2 noce, 5 nocy (inc/booking.php).
	const plural = new Intl.PluralRules( config.locale || 'en' );
	const count = ( n, forms ) => ( forms[ plural.select( n ) ] || forms.other ).replace( '%d', n );
	const nights = () => ( state.arr && state.dep ? Math.round( ( state.dep - state.arr ) / DAY ) : 0 );
	const guestsText = () => count( state.guests, text.adults );
	const stayText = () => count( nights(), text.nights ) + ' · ' + guestsText();
	const money = new Intl.NumberFormat( config.locale || 'en', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 } );
	const closedArrive = ( t ) => t < first || t > last;
	const closedLeave = ( t ) => t < first || t > last + DAY;

	// ---------- The calendar ----------
	const renderDays = () => {
		const y = state.view.getUTCFullYear();
		const m = state.view.getUTCMonth();
		const lead = ( new Date( Date.UTC( y, m, 1 ) ).getUTCDay() + 6 ) % 7;
		const length = new Date( Date.UTC( y, m + 1, 0 ) ).getUTCDate();
		monthName.textContent = config.months[ m ] + ' ' + y;
		calHint.textContent =
			state.open === 'dep' ? text.leave : text.open.replace( '%1$s', long( first ) ).replace( '%2$s', long( last, true ) );

		const cells = [];
		for ( let i = 0; i < lead; i++ ) {
			cells.push( '<span aria-hidden="true"></span>' );
		}
		for ( let d = 1; d <= length; d++ ) {
			const t = Date.UTC( y, m, d );
			const closed = state.open === 'dep' && state.arr && t > state.arr ? closedLeave( t ) : closedArrive( t );
			const end = t === state.arr || t === state.dep;
			const mid = state.arr && state.dep && t > state.arr && t < state.dep;
			const classes = [ 'am-bk-day', end && 'is-end', mid && 'is-mid', closed && 'is-closed' ].filter( Boolean ).join( ' ' );
			cells.push(
				`<button type="button" class="${ classes }" data-day="${ t }" tabindex="-1" aria-label="${ fmt( t ) }${ closed ? ', ' + text.closed : '' }" aria-pressed="${ end }"${ closed ? ' aria-disabled="true"' : '' }>${ d }</button>`
			);
		}
		days.innerHTML = cells.join( '' );
	};

	// The one day the Tab key lands on: the picked one, or the first open one of the month.
	const focusDay = ( t ) => {
		const buttons = [ ...days.querySelectorAll( '[data-day]' ) ];
		const target =
			buttons.find( ( b ) => Number( b.dataset.day ) === t ) ||
			buttons.find( ( b ) => Number( b.dataset.day ) === ( state.open === 'dep' ? state.dep : state.arr ) ) ||
			buttons.find( ( b ) => ! b.hasAttribute( 'aria-disabled' ) ) ||
			buttons[ 0 ];
		buttons.forEach( ( b ) => ( b.tabIndex = b === target ? 0 : -1 ) );
		return target;
	};

	const openCal = ( which, moveFocus ) => {
		state.open = which;
		const at = which === 'dep' ? state.dep || state.arr : state.arr;
		state.view = new Date( at || first );
		state.view.setUTCDate( 1 );
		cal.hidden = false;
		cal.classList.toggle( 'is-leave', which === 'dep' );
		Object.entries( fields ).forEach( ( [ key, button ] ) => {
			button.setAttribute( 'aria-expanded', String( key === which ) );
			button.classList.toggle( 'is-open', key === which );
		} );
		renderDays();
		const target = focusDay();
		if ( moveFocus && target ) {
			target.focus();
		}
	};

	const closeCal = ( giveBack ) => {
		const was = state.open;
		state.open = null;
		cal.hidden = true;
		Object.values( fields ).forEach( ( button ) => {
			button.setAttribute( 'aria-expanded', 'false' );
			button.classList.remove( 'is-open' );
		} );
		if ( giveBack && was ) {
			fields[ was ].focus();
		}
	};

	const pick = ( t ) => {
		const leaving = state.open === 'dep' && state.arr && t > state.arr;
		if ( leaving ? closedLeave( t ) : closedArrive( t ) ) {
			return;
		}
		if ( ! leaving ) {
			state.arr = t;
			state.dep = null;
			state.searched = false;
			render();
			openCal( 'dep', true );
			return;
		}
		state.dep = t;
		closeCal( false );
		fields.dep.focus();
		run();
	};

	days.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '[data-day]' );
		if ( button ) {
			pick( Number( button.dataset.day ) );
		}
	} );

	days.addEventListener( 'keydown', ( event ) => {
		const button = event.target.closest( '[data-day]' );
		if ( ! button ) {
			return;
		}
		const t = Number( button.dataset.day );
		const step = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[ event.key ];
		let next = null;
		if ( step ) {
			next = t + step * DAY;
		} else if ( event.key === 'PageUp' || event.key === 'PageDown' ) {
			const d = new Date( t );
			d.setUTCMonth( d.getUTCMonth() + ( event.key === 'PageUp' ? -1 : 1 ) );
			next = d.getTime();
		}
		if ( next === null ) {
			return;
		}
		event.preventDefault();
		const n = new Date( next );
		if ( n.getUTCMonth() !== state.view.getUTCMonth() || n.getUTCFullYear() !== state.view.getUTCFullYear() ) {
			state.view = new Date( Date.UTC( n.getUTCFullYear(), n.getUTCMonth(), 1 ) );
			renderDays();
		}
		focusDay( next )?.focus();
	} );

	cal.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '[data-bk-month]' );
		if ( ! button ) {
			return;
		}
		state.view.setUTCMonth( state.view.getUTCMonth() + Number( button.dataset.bkMonth ) );
		renderDays();
		focusDay();
	} );

	cal.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			event.preventDefault();
			closeCal( true );
		}
	} );

	Object.entries( fields ).forEach( ( [ key, button ] ) => {
		button.addEventListener( 'click', () => {
			if ( state.open === key ) {
				closeCal( false );
			} else {
				openCal( key, true );
			}
		} );
	} );

	// A click or the focus outside the search closes the calendar. The path of the click, not its target: a picked
	// day is drawn anew at once, so the button clicked is no longer on the page when the click gets here.
	document.addEventListener( 'click', ( event ) => {
		if ( state.open && ! event.composedPath().includes( search ) ) {
			closeCal( false );
		}
	} );
	search.addEventListener( 'focusout', ( event ) => {
		if ( state.open && event.relatedTarget && ! search.contains( event.relatedTarget ) ) {
			closeCal( false );
		}
	} );

	// ---------- Guests ----------
	$$( '[data-bk-guest]' ).forEach( ( button ) => {
		button.addEventListener( 'click', () => {
			state.guests = Math.min( state.max, Math.max( 1, state.guests + Number( button.dataset.bkGuest ) ) );
			render();
			if ( state.searched ) {
				run();
			}
		} );
	} );

	// ---------- Search and results ----------
	$( '[data-bk-go]' ).addEventListener( 'click', () => {
		if ( ! state.arr ) {
			openCal( 'arr', true );
		} else if ( ! state.dep ) {
			openCal( 'dep', true );
		} else {
			run();
		}
	} );

	const query = () => ( {
		arrive: iso( state.arr ),
		leave: iso( state.dep ),
		guests: state.guests,
		nights: nights(),
		stayText: fmt( state.arr ) + ' – ' + fmt( state.dep ) + ' · ' + count( nights(), text.nights ),
		guestsText: guestsText(),
	} );

	// Events for Tag Manager (HANDOFF, section 14): the search, the room chosen, the way to the engine. They wait in
	// dataLayer and leave the browser only when Tag Manager loads, after the guest agreed (consent.js).
	const track = ( data ) => ( window.dataLayer = window.dataLayer || [] ).push( data );
	const item = ( unit ) => ( { item_id: unit.code || String( unit.id ), item_name: unit.name } );

	const run = async () => {
		state.searched = true;
		const q = query();
		state.found = await provider.search( q );
		track( { event: 'search', search_term: q.arrive + ' – ' + q.leave, arrive: q.arrive, leave: q.leave, nights: q.nights, guests: q.guests } );
		render();
	};

	list.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '[data-bk-select]' );
		if ( ! button ) {
			return;
		}
		const id = Number( button.closest( '[data-bk-unit]' ).dataset.bkUnit );
		const unit = state.found.find( ( u ) => u.id === id );
		if ( unit ) {
			const q = query();
			q.paidText = typeof unit.total === 'number' ? text.paid.replace( '%s', money.format( unit.total ) ) : '';
			track( { event: 'select_item', items: [ item( unit ) ] } );
			track( { event: 'begin_checkout', currency: 'EUR', value: typeof unit.total === 'number' ? unit.total : undefined, items: [ item( unit ) ] } );
			window.location.assign( provider.handoff( unit, q ) );
		}
	} );

	// ---------- Everything on the page that follows the state ----------
	const render = () => {
		fields.arr.querySelector( '[data-bk-value]' ).textContent = state.arr ? fmt( state.arr ) : text.addDate;
		fields.dep.querySelector( '[data-bk-value]' ).textContent = state.dep ? fmt( state.dep ) : text.addDate;
		guestsOut.textContent = guestsText();

		const current = state.searched ? 1 : 0;
		steps.forEach( ( li, i ) => {
			li.classList.toggle( 'is-done', i < current );
			if ( i === current ) {
				li.setAttribute( 'aria-current', 'step' );
			} else {
				li.removeAttribute( 'aria-current' );
			}
		} );

		const ids = state.searched ? state.found.map( ( u ) => u.id ) : [];
		list.hidden = ! ids.length;
		if ( test ) {
			test.hidden = ! ids.length;
		}
		$$( '[data-bk-unit]' ).forEach( ( li ) => {
			const unit = state.found.find( ( u ) => u.id === Number( li.dataset.bkUnit ) );
			li.hidden = ! state.searched || ! unit;
			if ( unit ) {
				li.querySelector( '[data-bk-stay]' ).textContent = stayText();
				const total = li.querySelector( '[data-bk-total]' );
				total.textContent = typeof unit.total === 'number' ? money.format( unit.total ) : config.price;
				total.hidden = ! total.textContent;
			}
		} );

		const rooms = config.units.filter( ( u ) => ! u.villa ).map( ( u ) => u.guests );
		let say = note.dataset.hint;
		if ( state.searched && ! ids.length ) {
			say = text.none;
		} else if ( state.searched && rooms.length && state.guests > Math.max( ...rooms ) ) {
			say = text.many;
		} else if ( state.searched ) {
			say = text.free.replace( '%1$s', fmt( state.arr ) ).replace( '%2$s', fmt( state.dep ) ).replace( '%3$s', stayText() );
		}
		if ( note.textContent !== say ) {
			note.textContent = say;
		}
		note.classList.toggle( 'is-hint', ! state.searched );
		if ( state.open ) {
			renderDays();
			focusDay();
		}
	};

	render();

	// Coming from a date search elsewhere on the site: the calendar is already open (date-search.js).
	const asked = () => {
		try {
			const ask = window.sessionStorage.getItem( 'amour-cal' );
			window.sessionStorage.removeItem( 'amour-cal' );
			return ask;
		} catch {
			return null;
		}
	};
	if ( asked() === 'arr' ) {
		openCal( 'arr', false );
	}
}
