import { test, expect } from '@playwright/test';
import { PAGES, PAGES_PL } from './pages.js';

// The Polish pages (decision 25.11, HANDOFF section 19): every page has its Polish pair at a Polish address, the
// texts are Polish (data/pl.json, languages/pl_PL.po), the legal pages stay English until the lawyer has checked
// them, counts take the three Polish forms (1 noc, 2 noce, 7 nocy).

test.describe( 'Polish pages', () => {
	test( 'every page and its Polish pair point at each other', async ( { page } ) => {
		expect( PAGES_PL ).toHaveLength( PAGES.length );
		for ( const [ i, en ] of PAGES.entries() ) {
			const pl = PAGES_PL[ i ];
			await page.goto( en );
			await expect( page.locator( 'link[rel="alternate"][hreflang="pl"]' ), en ).toHaveAttribute( 'href', new RegExp( pl + '$' ) );
			await page.goto( pl );
			await expect( page.locator( 'link[rel="alternate"][hreflang="en"]' ), pl ).toHaveAttribute( 'href', new RegExp( en + '$' ) );
		}
	} );

	test( 'the menu and the footer in Polish lead to the Polish pages', async ( { page } ) => {
		await page.goto( '/pl/' );
		await page.getByRole( 'button', { name: 'Menu', exact: true } ).click();
		const menu = page.getByRole( 'dialog' );
		await expect( menu.getByRole( 'link', { name: 'Pokoje', exact: true } ) ).toHaveAttribute( 'href', /\/pl\/pokoje\/$/ );
		await page.keyboard.press( 'Escape' );
		const footer = page.locator( 'footer' );
		await expect( footer.getByText( 'Pobyt', { exact: true } ) ).toBeVisible();
		await expect( footer.getByText( 'Odkrywaj', { exact: true } ) ).toBeVisible();
		const legal = footer.getByRole( 'navigation', { name: 'Informacje prawne' } ).getByRole( 'link' );
		await expect( legal ).toHaveText( [ 'Prywatność', 'Cookies', 'Regulamin' ] );
		await expect( legal.nth( 0 ) ).toHaveAttribute( 'href', /\/pl\/polityka-prywatnosci\/$/ );
	} );

	test( 'texts, questions, photo descriptions and the score in Polish; Tenor Sans has the Polish letters', async ( { page } ) => {
		await page.goto( '/pl/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Hotel z dziewięcioma pokojami i własnym kortem, wśród gajów oliwnych nad zatoką Almyrida na Krecie.' );
		await expect( page.locator( '.am-hero__poster' ) ).toHaveAttribute( 'alt', /^Widok z drona/ );
		await expect( page.locator( '.am-reviews__score' ) ).toContainText( '9,1 na 10' );
		expect( await page.evaluate( async () => {
			await document.fonts.load( '13px "Tenor Sans"', 'ŁĄŚ' );
			return document.fonts.check( '13px "Tenor Sans"', 'ŁĄŚ' );
		} ) ).toBe( true );

		await page.goto( '/pl/pytania/' );
		await expect( page.getByRole( 'heading', { name: 'Rezerwacja i płatność' } ) ).toBeVisible();
		await expect( page.getByText( 'Czy cena jest taka sama jak na Booking.com?' ) ).toBeVisible();

		await page.goto( '/pl/pokoje/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Dziewięć pokoi, trzy kategorie' );
	} );

	test( 'the legal pages stay English, with a Polish line that says so', async ( { page } ) => {
		await page.goto( '/pl/polityka-prywatnosci/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Polityka prywatności' );
		await expect( page.locator( '.am-lg-top__lang' ) ).toHaveText( 'Ta strona jest na razie dostępna tylko po angielsku.' );
		await expect( page.locator( '.am-lg-text > div' ) ).toHaveAttribute( 'lang', 'en' );
	} );

	test( 'booking in Polish: dates in Polish, three forms of a count, ends on the Polish thank-you page', async ( { page } ) => {
		await page.goto( '/pl/rezerwacja/' );
		await expect( page.locator( '[data-bk-guests]' ) ).toHaveText( '2 osoby dorosłe' );
		await page.getByRole( 'button', { name: /Przyjazd/ } ).click();
		const shown = page.locator( '[data-bk-month-name]' );
		for ( let i = 0; i < 36 && ! /^maj 2027$/.test( await shown.textContent() ); i++ ) {
			await page.getByRole( 'button', { name: 'Następny miesiąc' } ).click();
		}
		const arrival = page.locator( `[data-day="${ Date.UTC( 2027, 4, 15 ) }"]` );
		await expect( arrival ).toHaveAttribute( 'aria-label', 'sob., 15 maj' );
		await arrival.click();
		await page.locator( `[data-day="${ Date.UTC( 2027, 4, 17 ) }"]` ).click();
		const first = page.locator( '[data-bk-unit]:visible' ).first();
		await expect( first.locator( '[data-bk-stay]' ) ).toHaveText( '2 noce · 2 osoby dorosłe' );

		await page.getByRole( 'button', { name: /Przyjazd/ } ).click();
		await arrival.click();
		await page.locator( `[data-day="${ Date.UTC( 2027, 4, 22 ) }"]` ).click();
		await expect( first.locator( '[data-bk-stay]' ) ).toHaveText( '7 nocy · 2 osoby dorosłe' );
		await first.getByRole( 'button', { name: 'Wybierz' } ).click();
		await page.waitForURL( /\/pl\/dziekujemy\/$/ );
		await expect( page.locator( '[data-booking="room"]' ) ).toHaveText( 'Pokój Standard Queen' );
	} );
} );
