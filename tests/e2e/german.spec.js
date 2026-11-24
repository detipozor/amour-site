import { test, expect } from '@playwright/test';
import { PAGES, PAGES_DE } from './pages.js';

// The German pages (decision 24.11, HANDOFF section 19): every page has its German pair at a German address,
// the menus and the footer lead to the German pages, the texts stay English until week 7 and a test copy says so.

test.describe( 'German pages', () => {
	test( 'every page and its German pair point at each other: hreflang and the language switch', async ( { page } ) => {
		expect( PAGES_DE ).toHaveLength( PAGES.length );
		for ( const [ i, en ] of PAGES.entries() ) {
			const de = PAGES_DE[ i ];
			for ( const [ path, other, code ] of [
				[ en, de, 'de-DE' ],
				[ de, en, 'en-US' ],
			] ) {
				await page.goto( path );
				await expect( page.locator( 'link[rel="alternate"][hreflang="de"]' ), path ).toHaveAttribute( 'href', new RegExp( de + '$' ) );
				await expect( page.locator( 'link[rel="alternate"][hreflang="en"]' ), path ).toHaveAttribute( 'href', new RegExp( en + '$' ) );
				await expect( page.locator( `.am-lang__list a[hreflang="${ code }"]` ), path ).toHaveAttribute( 'href', new RegExp( other + '$' ) );
			}
		}
	} );

	test( 'the menu and the footer lead to the German pages', async ( { page } ) => {
		await page.goto( '/de/' );
		await page.getByRole( 'button', { name: 'Menu' } ).click();
		const menu = page.getByRole( 'dialog' );
		await expect( menu.getByRole( 'link', { name: 'Rooms', exact: true } ) ).toHaveAttribute( 'href', /\/de\/zimmer\/$/ );
		await expect( menu.getByRole( 'link', { name: 'Villa Eudora', exact: true } ) ).toHaveAttribute( 'href', /\/de\/ferienvilla-eudora\/$/ );
		await page.keyboard.press( 'Escape' );
		const legal = page.locator( 'footer' ).getByRole( 'navigation', { name: 'Legal' } ).getByRole( 'link' );
		await expect( legal ).toHaveText( [ 'Privacy', 'Cookies', 'Terms' ] );
		await expect( legal.nth( 0 ) ).toHaveAttribute( 'href', /\/de\/datenschutz\/$/ );
		await expect( legal.nth( 2 ) ).toHaveAttribute( 'href', /\/de\/buchungsbedingungen\/$/ );
		await expect( page.locator( '.am-search__go' ) ).toHaveAttribute( 'href', /\/de\/buchen\/$/ );
	} );

	test( 'a test copy says the German text is to come, on the German pages only', async ( { page } ) => {
		await page.goto( '/de/zimmer/' );
		const note = page.locator( '.am-test-note' );
		await expect( note ).toHaveText( '[German text to come: this page shows the English one]' );
		await expect( note ).toHaveAttribute( 'lang', 'en' );
		await page.goto( '/rooms/' );
		await expect( note ).toHaveCount( 0 );
	} );

	test( 'until it is translated a German page shows the English text and fields', async ( { page } ) => {
		await page.goto( '/de/datenschutz/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Privacy policy' );
		await expect( page.locator( '.am-lg-text h2' ).first() ).toBeVisible();
		await expect( page.getByRole( 'navigation', { name: 'On this page' } ).getByRole( 'link' ).first() ).toBeVisible();
		await page.goto( '/de/ueber-uns/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'About Amour' );
		await expect( page.locator( '.am-ab-fig' ) ).toHaveCount( 3 );
	} );

	test( 'booking in German ends on the German thank-you page', async ( { page } ) => {
		await page.goto( '/de/buchen/' );
		await page.getByRole( 'button', { name: /Arrive/ } ).click();
		const shown = page.locator( '[data-bk-month-name]' );
		for ( let i = 0; i < 36 && ! /^May 2027$/.test( await shown.textContent() ); i++ ) {
			await page.getByRole( 'button', { name: 'Next month' } ).click();
		}
		await page.locator( `[data-day="${ Date.UTC( 2027, 4, 15 ) }"]` ).click();
		await page.locator( `[data-day="${ Date.UTC( 2027, 4, 22 ) }"]` ).click();
		await page.locator( '[data-bk-unit]:visible' ).first().getByRole( 'button', { name: 'Select' } ).click();
		await page.waitForURL( /\/de\/danke\/$/ );
		await expect( page.locator( '[data-booking="room"]' ) ).toHaveText( 'Standard Queen Room' );
	} );
} );
