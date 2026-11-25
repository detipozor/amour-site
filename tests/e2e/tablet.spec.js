import { test, expect } from '@playwright/test';

// Tablet, 761–1100 px (HANDOFF, section 4; docs/13, section 1): split blocks in one column, card grids two in
// a row, fact tables in two columns, pinned blocks as on a desktop.

const box = async ( page, selector ) => page.locator( selector ).first().boundingBox();

/** The second element starts below the first one ends: one column. */
const stacked = async ( page, first, second ) => {
	const a = await box( page, first );
	const b = await box( page, second );
	expect( b.y, `${ second } under ${ first }` ).toBeGreaterThanOrEqual( a.y + a.height - 1 );
};

/** The first two items of a list share a row and the third starts a new one: two in a row. */
const twoInARow = async ( page, items ) => {
	const list = page.locator( items );
	const [ a, b, c ] = await Promise.all( [ 0, 1, 2 ].map( ( i ) => list.nth( i ).boundingBox() ) );
	expect( Math.abs( a.y - b.y ), `${ items }: first two in one row` ).toBeLessThan( 2 );
	expect( c.y, `${ items }: the third in the next row` ).toBeGreaterThan( a.y + a.height - 1 );
};

for ( const size of [ { width: 768, height: 1024 }, { width: 1024, height: 768 } ] ) {
	test.describe( `tablet ${ size.width } × ${ size.height }`, () => {
		test.use( { viewport: size, reducedMotion: 'reduce' } );

		test( 'openings: the heading above the text', async ( { page } ) => {
			await page.goto( '/rooms/' );
			await stacked( page, '.am-rl-open__title', '.am-rl-open__text' );
			await stacked( page, '.am-rl-every__title', '.am-rl-every__list' );
			await page.goto( '/questions/' );
			await stacked( page, '.am-pg-open__left', '.am-pg-open__text' );
			await page.goto( '/crete-guide/' );
			await stacked( page, '.am-gd-open__left', '.am-gd-open__text' );
			await stacked( page, '.am-gd-getting__title', '.am-gd-getting__list' );
			await page.goto( '/court-and-pool/' );
			await stacked( page, '.am-es-plan__head > div', '.am-es-plan__text' );
		} );

		test( 'room and villa: the label above the text, the facts in two columns, other ways to stay two in a row', async ( { page } ) => {
			await page.goto( '/rooms/superior-queen-room/' );
			await stacked( page, '.am-room__row--desc .am-room__label', '.am-room__desc' );
			await twoInARow( page, '.am-room__facts .am-room__fact' );
			await twoInARow( page, '.am-others__grid > li' );
			await page.goto( '/villa-eudora/' );
			await stacked( page, '.am-villa-floors__fig', '.am-villa-floors__text' );
			await stacked( page, '.am-villa-events__text', '.am-villa-events__fig' );
		} );

		test( 'contact: the text, the map and the form in one column, the routes two in a row', async ( { page } ) => {
			await page.goto( '/contacts/' );
			await stacked( page, '.am-ct-top__text', '.am-ct-map' );
			await stacked( page, '.am-ct-write__text', '.am-form' );
			await twoInARow( page, '.am-ct-routes__grid > *' );
		} );

		test( 'thank you: the next steps two in a row', async ( { page } ) => {
			await page.goto( '/thank-you/' );
			await twoInARow( page, '.am-th-next__list > li' );
		} );
	} );
}

test.describe( 'tablet: the pinned map of a room', () => {
	test( 'upright, the card goes under the island; sideways, it stays on the right', async ( { page } ) => {
		await page.setViewportSize( { width: 820, height: 1180 } );
		await page.goto( '/rooms/superior-queen-room/' );
		let pin = await box( page, '.am-loc__pin' );
		let card = await box( page, '.am-loc__card' );
		expect( card.y + card.height / 2 ).toBeGreaterThan( pin.y + pin.height / 2 );
		expect( card.x ).toBeLessThan( 60 );

		await page.setViewportSize( { width: 1024, height: 768 } );
		await page.goto( '/rooms/superior-queen-room/' );
		pin = await box( page, '.am-loc__pin' );
		card = await box( page, '.am-loc__card' );
		expect( card.x ).toBeGreaterThan( pin.x + pin.width / 2 );
	} );
} );
