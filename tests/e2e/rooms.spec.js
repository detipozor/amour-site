import { test, expect } from '@playwright/test';

const ROOMS = '/rooms/';

test.describe( 'Rooms page (board Rooms)', () => {
	test( 'the opening with the date search, then the three kinds of room from their pages', async ( { page } ) => {
		await page.goto( ROOMS );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Nine rooms, three kinds' );
		await expect( page.getByRole( 'navigation', { name: 'Breadcrumb' } ) ).toHaveText( /Home\s*·\s*Rooms/ );
		const search = page.locator( '.am-search__in a' );
		await expect( search ).toHaveCount( 4 );
		await expect( search.last() ).toHaveAttribute( 'href', /\/book\/$/ );

		const rooms = page.locator( '.am-rl-room' );
		await expect( rooms ).toHaveCount( 3 );
		await expect( rooms.locator( 'h2' ) ).toHaveText( [ 'Standard Queen', 'Deluxe Queen', 'Superior Queen' ] );
		await expect( rooms.locator( '.am-eyebrow' ) ).toHaveText( [ 'Room 01', 'Room 02', 'Room 03' ] );
		await expect( rooms.first().locator( 'dt' ) ).toHaveText( [ 'Size', 'Bed', 'Kitchen', 'Outside', 'Guests' ] );
		await expect( rooms.first().locator( 'dd' ).first() ).toHaveText( 'about 20 m²' );
		await expect( rooms.first().locator( '.am-rl-room__price' ) ).toHaveText( 'from €160 a night' );
		await expect( page.getByRole( 'link', { name: /^View room\s*:\s*Standard Queen$/ } ) ).toHaveAttribute( 'href', /\/rooms\/standard-queen-room\/$/ );
		await expect( page.getByRole( 'link', { name: /^Check dates\s*:\s*Deluxe Queen$/ } ) ).toHaveAttribute( 'href', /\/book\/$/ );

		// Photo and text take turns on a wide screen: the second room has its photo on the right.
		const pic = await rooms.nth( 1 ).locator( '.am-rl-room__pic' ).boundingBox();
		const text = await rooms.nth( 1 ).locator( '.am-rl-room__text' ).boundingBox();
		expect( pic.x ).toBeGreaterThan( text.x );
	} );

	test( 'In every room, the villa for families and groups, Good to know', async ( { page } ) => {
		await page.goto( ROOMS );
		await expect( page.locator( '.am-rl-every__list li' ) ).toHaveCount( 8 );

		const family = page.locator( '.am-rl-family' );
		await expect( family ).toHaveAttribute( 'data-theme', 'eudora' );
		await expect( family.getByRole( 'img', { name: 'Villa Eudora' } ) ).toBeVisible();
		await expect( family.getByRole( 'heading', { level: 2 } ) ).toHaveText( 'Travelling as a family or a group?' );
		await expect( family.locator( 'dt' ) ).toHaveText( [ 'Bedrooms', 'Guests', 'To the hotel' ] );
		await expect( family.locator( 'dd' ) ).toHaveText( [ '5', '11', '4 min' ] );
		await expect( family.getByRole( 'link', { name: 'Discover the villa' } ) ).toHaveAttribute( 'href', /\/villa-eudora\/$/ );

		const notes = page.locator( '.am-rl-note' );
		await expect( notes ).toHaveCount( 6 );
		await expect( notes.locator( '.am-rl-note__label' ) ).toHaveText( [ 'Arrival', 'Breakfast basket', 'Children', 'Pets', 'Arriving late', 'Travelling alone' ] );
		await expect( notes.nth( 0 ).locator( '.am-rl-note__fig' ) ).toHaveText( '14:00' );
		await expect( notes.nth( 3 ).locator( 'svg' ) ).toBeVisible(); // the paw
		await expect( page.getByRole( 'link', { name: 'All questions and house rules' } ) ).toHaveAttribute( 'href', /\/questions\/$/ );
	} );

	test( 'phone: one column, photo above the text, the search button full width', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( ROOMS );
		const go = await page.locator( '.am-search__go' ).boundingBox();
		expect( Math.round( go.width ) ).toBe( 390 - 32 );
		const room = page.locator( '.am-rl-room' ).nth( 1 );
		const pic = await room.locator( '.am-rl-room__pic' ).boundingBox();
		const text = await room.locator( '.am-rl-room__text' ).boundingBox();
		expect( pic.y + pic.height ).toBeLessThanOrEqual( text.y );
		expect( Math.round( pic.width ) ).toBe( 390 - 32 );
	} );
} );
