import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const axe = async ( page ) => {
	const results = await new AxeBuilder( { page } ).withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] ).analyze();
	return results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) ).map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` );
};

// The season the seed sets (Hotel settings → Booking): first night 1 March, last night 31 October 2027.
const calendar = ( page ) => page.getByRole( 'dialog', { name: 'Choose your dates' } );
const day = ( page, y, m, d ) => page.locator( `[data-day="${ Date.UTC( y, m - 1, d ) }"]` );
const MONTHS = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];
const index = ( name ) => {
	const [ month, year ] = name.split( ' ' );
	return Number( year ) * 12 + MONTHS.indexOf( month );
};
const toMonth = async ( page, name ) => {
	const shown = page.locator( '[data-bk-month-name]' );
	for ( let i = 0; i < 36 && ( await shown.textContent() ) !== name; i++ ) {
		const back = index( name ) < index( await shown.textContent() );
		await page.getByRole( 'button', { name: back ? 'Previous month' : 'Next month' } ).click();
	}
	await expect( shown ).toHaveText( name );
};
const pickMay = async ( page ) => {
	await page.getByRole( 'button', { name: /Arrive/ } ).click();
	await toMonth( page, 'May 2027' );
	await day( page, 2027, 5, 15 ).click();
	await day( page, 2027, 5, 22 ).click();
};
const units = ( page ) => page.locator( '[data-bk-unit]:visible' );

test.describe( 'Book your stay (board Book)', () => {
	test( 'before the search: the steps, the fields, the hint, no rooms yet', async ( { page } ) => {
		await page.goto( '/book/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Book your stay' );
		await expect( page.locator( '.am-bk-top__lead' ) ).toHaveText( 'Same price as on Booking.com, paid securely by card.' );
		await expect( page.locator( '[data-bk-step]' ) ).toHaveText( [ '1 Dates', '2 Room', '3 Details', '4 Payment' ] );
		await expect( page.locator( '[data-bk-step]' ).first() ).toHaveAttribute( 'aria-current', 'step' );
		await expect( page.locator( '[data-bk-value]' ) ).toHaveText( [ 'Add date', 'Add date' ] );
		await expect( page.locator( '[data-bk-guests]' ) ).toHaveText( '2 adults' );
		await expect( page.locator( '[data-bk-note]' ) ).toHaveText( /^Choose your dates/ );
		await expect( units( page ) ).toHaveCount( 0 );
		await expect( calendar( page ) ).toBeHidden();
		await expect( page.getByRole( 'link', { name: 'Booking terms' } ) ).toHaveAttribute( 'href', /\/booking-terms\/$/ );
	} );

	test( 'the calendar: the season can be picked, the rest is crossed out; two days run the search', async ( { page } ) => {
		await page.goto( '/book/' );
		// Search without dates opens the calendar on the arrival.
		await page.getByRole( 'button', { name: 'Search' } ).click();
		await expect( calendar( page ) ).toBeVisible();
		await expect( page.locator( '[data-bk-open="arr"]' ) ).toHaveAttribute( 'aria-expanded', 'true' );
		await expect( page.locator( '[data-bk-cal-hint]' ) ).toHaveText( 'We are open from 1 March to 31 October 2027.' );
		await toMonth( page, 'February 2027' );
		await expect( day( page, 2027, 2, 28 ) ).toHaveAttribute( 'aria-disabled', 'true' );
		await page.getByRole( 'button', { name: 'Next month' } ).click();
		await expect( day( page, 2027, 3, 1 ) ).not.toHaveAttribute( 'aria-disabled', 'true' );
		await page.getByRole( 'button', { name: 'Previous month' } ).click();
		await day( page, 2027, 2, 28 ).click( { force: true } );
		await expect( page.locator( '[data-bk-value="arr"]' ) ).toHaveText( 'Add date' );

		await toMonth( page, 'May 2027' );
		await day( page, 2027, 5, 15 ).click();
		await expect( page.locator( '[data-bk-value="arr"]' ) ).toHaveText( 'Sat 15 May' );
		await expect( page.locator( '[data-bk-open="dep"]' ) ).toHaveAttribute( 'aria-expanded', 'true' );
		await expect( page.locator( '[data-bk-cal-hint]' ) ).toHaveText( 'Now the day you leave.' );
		await day( page, 2027, 5, 22 ).click();

		await expect( calendar( page ) ).toBeHidden();
		await expect( page.locator( '[data-bk-value="dep"]' ) ).toHaveText( 'Sat 22 May' );
		await expect( page.locator( '[data-bk-note]' ) ).toHaveText( 'Free for Sat 15 May – Sat 22 May, 7 nights · 2 adults.' );
		await expect( page.locator( '[data-bk-step]' ).nth( 1 ) ).toHaveAttribute( 'aria-current', 'step' );
		await expect( page.locator( '[data-bk-step]' ).first() ).toHaveClass( /is-done/ );
	} );

	test( 'what is free: the rooms and the villa with the stay, the price and the cancellation rule next to it', async ( { page } ) => {
		await page.goto( '/book/' );
		await pickMay( page );
		await expect( units( page ).locator( 'h2' ) ).toHaveText( [ 'Standard Queen Room', 'Deluxe Queen Room', 'Superior Queen Room', 'Villa Eudora' ] );
		const first = units( page ).first();
		await expect( first.locator( '.am-bk-pill' ) ).toHaveText( [ '20 m²', '2 guests' ] );
		await expect( first.locator( '[data-bk-stay]' ) ).toHaveText( '7 nights · 2 adults' );
		// The test engine: the price from in the admin (for now the old site's 160 €) × 7 nights.
		await expect( first.locator( '[data-bk-total]' ) ).toHaveText( '€1,120' );
		await expect( first.locator( '.am-bk-unit__terms' ) ).toHaveText( 'Free cancellation until [14] days before arrival · climate fee paid at the hotel' );
		await expect( first.getByRole( 'button', { name: 'Select' } ) ).toHaveAccessibleDescription( 'Standard Queen Room' );
		await expect( page.locator( '[data-bk-test]' ) ).toBeVisible();
	} );

	test( 'guests: more than two leave the villa; one is one adult; never more than the villa takes', async ( { page } ) => {
		await page.goto( '/book/' );
		await pickMay( page );
		const more = page.getByRole( 'button', { name: 'More guests' } );
		await more.click();
		await expect( page.locator( '[data-bk-guests]' ) ).toHaveText( '3 adults' );
		await expect( units( page ).locator( 'h2' ) ).toHaveText( [ 'Villa Eudora' ] );
		await expect( page.locator( '[data-bk-note]' ) ).toHaveText( 'For more than two guests: the villa, or several rooms booked one by one.' );
		for ( let i = 0; i < 10; i++ ) {
			await more.click();
		}
		await expect( page.locator( '[data-bk-guests]' ) ).toHaveText( '11 adults' );
		const fewer = page.getByRole( 'button', { name: 'Fewer guests' } );
		for ( let i = 0; i < 12; i++ ) {
			await fewer.click();
		}
		await expect( page.locator( '[data-bk-guests]' ) ).toHaveText( '1 adult' );
		await expect( units( page ) ).toHaveCount( 4 );
	} );

	test( 'the keyboard: the arrows move the day, Enter picks, Esc closes and gives the focus back', async ( { page } ) => {
		await page.goto( '/book/' );
		await page.getByRole( 'button', { name: /Arrive/ } ).focus();
		await page.keyboard.press( 'Enter' );
		await expect( calendar( page ) ).toBeVisible();
		const focused = () => page.evaluate( () => Number( document.activeElement.dataset.day ) );
		expect( await focused() ).toBe( Date.UTC( 2027, 2, 1 ) );
		await page.keyboard.press( 'ArrowRight' );
		await page.keyboard.press( 'ArrowDown' );
		expect( await focused() ).toBe( Date.UTC( 2027, 2, 9 ) );
		await page.keyboard.press( 'PageDown' );
		await expect( page.locator( '[data-bk-month-name]' ) ).toHaveText( 'April 2027' );
		await page.keyboard.press( 'Enter' );
		await expect( page.locator( '[data-bk-value="arr"]' ) ).toHaveText( 'Fri 9 Apr' );
		await expect( page.locator( '[data-bk-open="dep"]' ) ).toHaveAttribute( 'aria-expanded', 'true' );
		await page.keyboard.press( 'Escape' );
		await expect( calendar( page ) ).toBeHidden();
		await expect( page.locator( '[data-bk-open="dep"]' ) ).toBeFocused();
	} );

	test( 'Select hands over to the engine: the test engine opens the thank-you page with the booking and Add to calendar', async ( { page } ) => {
		await page.goto( '/book/' );
		await pickMay( page );
		await units( page ).nth( 2 ).getByRole( 'button', { name: 'Select' } ).click();
		await page.waitForURL( /\/thank-you\/$/ );
		await expect( page.locator( '[data-booking="room"]' ) ).toHaveText( 'Superior Queen Room' );
		await expect( page.locator( '[data-booking="stay"]' ) ).toHaveText( 'Sat 15 May – Sat 22 May · 7 nights' );
		await expect( page.locator( '[data-booking="guests"]' ) ).toHaveText( '2 adults' );
		const add = page.getByRole( 'button', { name: 'Add to calendar' } );
		await expect( add ).toBeVisible();
		const [ download ] = await Promise.all( [ page.waitForEvent( 'download' ), add.click() ] );
		expect( download.suggestedFilename() ).toBe( 'amour-stay.ics' );
		const ics = await ( await download.createReadStream() ).toArray().then( ( parts ) => Buffer.concat( parts ).toString() );
		expect( ics ).toContain( 'DTSTART;VALUE=DATE:20270515' );
		expect( ics ).toContain( 'DTEND;VALUE=DATE:20270522' );
		expect( ics ).toContain( 'SUMMARY:Amour Relax Hotel · Superior Queen Room' );
		expect( ics ).toContain( 'Check-in from 14:00\\, check-out by 11:00.' );
	} );

	test( 'the thank-you page without a booking keeps Add to calendar hidden', async ( { page } ) => {
		await page.goto( '/thank-you/' );
		await expect( page.getByRole( 'button', { name: 'Add to calendar' } ) ).toBeHidden();
	} );

	test( 'phone: dates side by side, guests and Search full width, the rooms in one column', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/book/' );
		const box = async ( selector ) => page.locator( selector ).boundingBox();
		const arr = await box( '[data-bk-open="arr"]' );
		const dep = await box( '[data-bk-open="dep"]' );
		const guests = await box( '.am-bk-guests' );
		expect( Math.round( dep.y ) ).toBe( Math.round( arr.y ) );
		expect( Math.round( guests.width ) ).toBe( 390 - 32 );
		await pickMay( page );
		const pic = await units( page ).first().locator( '.am-bk-unit__pic' ).boundingBox();
		const name = await units( page ).first().locator( 'h2' ).boundingBox();
		expect( name.y ).toBeGreaterThan( pic.y + pic.height );
		expect( await page.evaluate( () => document.documentElement.scrollWidth ) ).toBe( 390 );
	} );
} );

test.describe( 'Book your stay, axe', () => {
	test.use( { reducedMotion: 'reduce' } );

	test( 'nothing serious with the calendar open, nor with the rooms found', async ( { page } ) => {
		await page.goto( '/book/' );
		await page.getByRole( 'button', { name: /Arrive/ } ).click();
		await toMonth( page, 'May 2027' );
		expect( await axe( page ) ).toEqual( [] );
		await day( page, 2027, 5, 15 ).click();
		await day( page, 2027, 5, 22 ).click();
		await expect( units( page ) ).toHaveCount( 4 );
		expect( await axe( page ) ).toEqual( [] );
	} );
} );
