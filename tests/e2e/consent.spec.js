import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const axe = async ( page ) => {
	const results = await new AxeBuilder( { page } ).withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] ).analyze();
	return results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) ).map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` );
};

/*
 * The site has no Tag Manager container until the company account exists (question 30), so these tests give the
 * pages a made-up one (GTM-TEST123) on the way to the browser, and answer the requests to Google themselves:
 * nothing leaves the test.
 */
const withContainer = async ( page ) => {
	const google = [];
	await page.route( 'https://www.googletagmanager.com/**', ( route ) => {
		google.push( route.request().url() );
		return route.fulfill( { status: 200, contentType: 'text/javascript', body: '' } );
	} );
	await page.route( '**/*', async ( route ) => {
		if ( route.request().resourceType() !== 'document' ) {
			return route.fallback();
		}
		const response = await route.fetch();
		const body = ( await response.text() ).replace( 'data-gtm=""', 'data-gtm="GTM-TEST123"' );
		return route.fulfill( { response, body } );
	} );
	return google;
};

const banner = ( page ) => page.getByRole( 'dialog', { name: 'Cookie settings' } );
const consent = ( page ) =>
	page.evaluate( () =>
		window.dataLayer
			.filter( ( entry ) => entry && entry[ 0 ] === 'consent' )
			.map( ( entry ) => [ entry[ 1 ], { analytics: entry[ 2 ].analytics_storage, ads: entry[ 2 ].ad_storage } ] )
	);
const stored = ( page ) => page.evaluate( () => JSON.parse( window.localStorage.getItem( 'amour-consent' ) || 'null' ) );

test.describe( 'Cookie banner (HANDOFF, section 14)', () => {
	test( 'without a Tag Manager container nothing tracks, so the banner does not ask', async ( { page } ) => {
		const google = [];
		await page.route( 'https://www.googletagmanager.com/**', ( route ) => google.push( route.request().url() ) && route.abort() );
		await page.goto( '/' );
		await expect( banner( page ) ).toBeHidden();
		expect( google ).toEqual( [] );
	} );

	test( 'with a container a first visit asks; nothing from Google before the answer; the two answers look the same', async ( { page } ) => {
		const google = await withContainer( page );
		await page.goto( '/' );
		await expect( banner( page ) ).toBeVisible();
		await expect( banner( page ) ).toContainText( 'Analytics and ads stay off until you agree.' );
		await expect( banner( page ).getByRole( 'link', { name: 'Cookie policy' } ) ).toHaveAttribute( 'href', /\/cookies\/$/ );
		const look = ( name ) =>
			banner( page )
				.getByRole( 'button', { name } )
				.evaluate( ( el ) => {
					const s = getComputedStyle( el );
					return [ s.backgroundColor, s.color, s.borderColor, s.fontSize, s.fontWeight, el.offsetHeight ].join( ' ' );
				} );
		expect( await look( 'Reject all' ) ).toBe( await look( 'Accept all' ) );
		expect( google ).toEqual( [] );
		// The page keeps what has the focus above the banner (WCAG 2.4.11).
		const padding = await page.evaluate( () => parseFloat( document.documentElement.style.scrollPaddingBottom ) );
		expect( padding ).toBeGreaterThan( await banner( page ).evaluate( ( el ) => el.offsetHeight ) );
	} );

	test( 'Accept all loads Tag Manager with everything granted, and the choice holds on the next page', async ( { page } ) => {
		const google = await withContainer( page );
		await page.goto( '/' );
		await banner( page ).getByRole( 'button', { name: 'Accept all' } ).click();
		await expect( banner( page ) ).toBeHidden();
		await expect.poll( () => google.length ).toBe( 1 );
		expect( google[ 0 ] ).toContain( 'gtm.js?id=GTM-TEST123' );
		expect( await consent( page ) ).toEqual( [
			[ 'default', { analytics: 'denied', ads: 'denied' } ],
			[ 'update', { analytics: 'granted', ads: 'granted' } ],
		] );
		expect( await stored( page ) ).toMatchObject( { analytics: true, ads: true } );
		await page.goto( '/rooms/' );
		await expect( banner( page ) ).toBeHidden();
		await expect.poll( () => google.length ).toBe( 2 );
	} );

	test( 'Reject all loads nothing, now or on the next page', async ( { page } ) => {
		const google = await withContainer( page );
		await page.goto( '/' );
		await banner( page ).getByRole( 'button', { name: 'Reject all' } ).click();
		await expect( banner( page ) ).toBeHidden();
		expect( await stored( page ) ).toMatchObject( { analytics: false, ads: false } );
		await page.goto( '/rooms/' );
		await expect( banner( page ) ).toBeHidden();
		expect( await consent( page ) ).toEqual( [
			[ 'default', { analytics: 'denied', ads: 'denied' } ],
			[ 'update', { analytics: 'denied', ads: 'denied' } ],
		] );
		expect( google ).toEqual( [] );
	} );

	test( 'Settings: analytics only, with the keyboard', async ( { page } ) => {
		const google = await withContainer( page );
		await page.goto( '/' );
		const settings = banner( page ).getByRole( 'button', { name: 'Settings', exact: true } );
		await expect( settings ).toHaveAttribute( 'aria-expanded', 'false' );
		await settings.focus();
		await page.keyboard.press( 'Enter' );
		const analytics = banner( page ).getByRole( 'checkbox', { name: /Analytics/ } );
		await expect( analytics ).toBeFocused();
		await expect( analytics ).not.toBeChecked();
		await expect( banner( page ).getByRole( 'checkbox', { name: /Advertising/ } ) ).not.toBeChecked();
		await page.keyboard.press( 'Space' );
		await banner( page ).getByRole( 'button', { name: 'Save choices' } ).click();
		await expect( banner( page ) ).toBeHidden();
		expect( ( await consent( page ) ).at( -1 ) ).toEqual( [ 'update', { analytics: 'granted', ads: 'denied' } ] );
		await expect.poll( () => google.length ).toBe( 1 );
	} );

	test( 'Cookie settings on the Cookies page reopens the banner with the choice made; Esc closes it', async ( { page } ) => {
		await page.goto( '/cookies/' );
		await expect( banner( page ) ).toBeHidden();
		const open = page.getByRole( 'button', { name: 'Cookie settings' } );
		await open.click();
		await expect( banner( page ) ).toBeVisible();
		await expect( banner( page ).getByRole( 'button', { name: 'Settings', exact: true } ) ).toBeFocused();
		await page.keyboard.press( 'Escape' );
		await expect( banner( page ) ).toBeHidden();
		await expect( open ).toBeFocused();

		await open.click();
		await banner( page ).getByRole( 'button', { name: 'Accept all' } ).click();
		await expect( open ).toBeFocused();
		await open.click();
		await banner( page ).getByRole( 'button', { name: 'Settings', exact: true } ).click();
		await expect( banner( page ).getByRole( 'checkbox', { name: /Analytics/ } ) ).toBeChecked();
		await expect( banner( page ).getByRole( 'checkbox', { name: /Advertising/ } ) ).toBeChecked();
	} );

	for ( const width of [ 1440, 390 ] ) {
		test( `axe finds nothing serious with the banner and its choices open (${ width }px)`, async ( { page } ) => {
			await page.setViewportSize( { width, height: 900 } );
			await withContainer( page );
			await page.goto( '/book/' );
			await banner( page ).getByRole( 'button', { name: 'Settings', exact: true } ).click();
			expect( await axe( page ) ).toEqual( [] );
		} );
	}
} );

test.describe( 'Booking events for Tag Manager (HANDOFF, section 14)', () => {
	test( 'the search, the room chosen and the way to the engine; the purchase on the thank-you page, once', async ( { page } ) => {
		await page.goto( '/book/' );
		await page.getByRole( 'button', { name: /Arrive/ } ).click();
		const shown = page.locator( '[data-bk-month-name]' );
		for ( let i = 0; i < 36 && ( await shown.textContent() ) !== 'May 2027'; i++ ) {
			await page.getByRole( 'button', { name: 'Next month' } ).click();
		}
		await page.locator( `[data-day="${ Date.UTC( 2027, 4, 15 ) }"]` ).click();
		await page.locator( `[data-day="${ Date.UTC( 2027, 4, 22 ) }"]` ).click();
		const events = () => page.evaluate( () => window.dataLayer.filter( ( e ) => e && e.event && ! e.event.startsWith( 'gtm' ) ) );
		await expect.poll( async () => ( await events() ).map( ( e ) => e.event ) ).toEqual( [ 'search' ] );
		expect( ( await events() )[ 0 ] ).toMatchObject( { arrive: '2027-05-15', leave: '2027-05-22', nights: 7, guests: 2 } );

		await page.evaluate( () => {
			// Keep what is pushed before the page goes to the thank-you page.
			const push = window.dataLayer.push.bind( window.dataLayer );
			window.dataLayer.push = ( entry ) => {
				window.sessionStorage.setItem( 'pushed', JSON.stringify( [ ...JSON.parse( window.sessionStorage.getItem( 'pushed' ) || '[]' ), entry.event ] ) );
				return push( entry );
			};
		} );
		await page.locator( '[data-bk-unit]:visible' ).first().getByRole( 'button', { name: 'Select' } ).click();
		await page.waitForURL( /\/thank-you\/$/ );
		expect( await page.evaluate( () => JSON.parse( window.sessionStorage.getItem( 'pushed' ) ) ) ).toEqual( [ 'select_item', 'begin_checkout' ] );
		await expect.poll( async () => ( await events() ).map( ( e ) => e.event ) ).toEqual( [ 'purchase' ] );
		expect( ( await events() )[ 0 ].items ).toEqual( [ { item_name: 'Standard Queen Room' } ] );
		await page.reload();
		expect( ( await events() ).map( ( e ) => e.event ) ).toEqual( [] );
	} );
} );
