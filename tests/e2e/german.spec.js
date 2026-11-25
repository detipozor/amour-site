import { test, expect } from '@playwright/test';
import { PAGES, PAGES_DE } from './pages.js';

// The German pages (decisions 24.11 and 25.11, HANDOFF section 19): every page has its German pair at a German
// address, the menus and the footer lead to the German pages, the texts are German (data/de.json, languages/de_DE.po),
// the legal pages stay English until the lawyer has checked them.

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

	test( 'the menu and the footer lead to the German pages, in German', async ( { page } ) => {
		await page.goto( '/de/' );
		await page.getByRole( 'button', { name: 'Menü' } ).click();
		const menu = page.getByRole( 'dialog' );
		await expect( menu.getByRole( 'link', { name: 'Zimmer', exact: true } ) ).toHaveAttribute( 'href', /\/de\/zimmer\/$/ );
		await expect( menu.getByRole( 'link', { name: 'Villa Eudora', exact: true } ) ).toHaveAttribute( 'href', /\/de\/ferienvilla-eudora\/$/ );
		await expect( menu.getByText( 'Neun Zimmer, jedes mit eigener Küche und Terrasse.' ) ).toBeAttached();
		await page.keyboard.press( 'Escape' );
		const footer = page.locator( 'footer' );
		// The footer columns are named after the German menus (decision 25.11).
		await expect( footer.getByText( 'Wohnen', { exact: true } ) ).toBeVisible();
		await expect( footer.getByText( 'Entdecken', { exact: true } ) ).toBeVisible();
		const legal = footer.getByRole( 'navigation', { name: 'Rechtliches' } ).getByRole( 'link' );
		await expect( legal ).toHaveText( [ 'Datenschutz', 'Cookies', 'AGB' ] );
		await expect( legal.nth( 0 ) ).toHaveAttribute( 'href', /\/de\/datenschutz\/$/ );
		await expect( legal.nth( 2 ) ).toHaveAttribute( 'href', /\/de\/buchungsbedingungen\/$/ );
		await expect( page.locator( '.am-search__go' ) ).toHaveAttribute( 'href', /\/de\/buchen\/$/ );
	} );

	test( 'German pages speak German: texts, titles, questions, map places, settings, photo descriptions', async ( { page } ) => {
		await page.goto( '/de/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Ein Hotel mit neun Zimmern und eigenem Tennisplatz, zwischen Olivenhainen über der Bucht von Almyrida auf Kreta.' );
		await expect( page.locator( '.am-hero__poster' ) ).toHaveAttribute( 'alt', /^Drohnenblick/ );
		await expect( page.locator( 'meta[name="description"]' ) ).toHaveAttribute( 'content', /^Ein Hotel mit neun Zimmern/ );
		await expect( page.locator( '.am-test-note' ) ).toHaveCount( 0 );

		await page.goto( '/de/zimmer/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Neun Zimmer, drei Kategorien' );
		await expect( page.locator( 'main img[alt]' ).first() ).not.toHaveAttribute( 'alt', /\b(the|with|and)\b/ );

		await page.goto( '/de/fragen/' );
		await expect( page.getByRole( 'heading', { name: 'Buchung und Bezahlung' } ) ).toBeVisible();
		await expect( page.getByText( 'Ist der Preis derselbe wie auf Booking.com?' ) ).toBeVisible();

		await page.goto( '/de/zimmer/superior-queen-room/' );
		await expect( page.locator( 'h1' ) ).toContainText( 'Superior' );
		await expect( page.getByText( 'Über der Bucht von Almyrida in Apokoronas, zwischen Chania und Rethymno.' ) ).toBeAttached();
		await expect( page.locator( '.am-loc' ).getByText( 'Strand von Almyrida · 1,2 km' ) ).toBeAttached();

		// The English pages stay English.
		await page.goto( '/rooms/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Nine rooms, three kinds' );
	} );

	test( 'the legal pages stay English until the lawyer has checked them, and say so in German', async ( { page } ) => {
		await page.goto( '/de/datenschutz/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Datenschutzerklärung' );
		await expect( page.locator( '.am-lg-top__lang' ) ).toHaveText( 'Diese Seite gibt es vorerst nur auf Englisch.' );
		await expect( page.locator( '.am-lg-text > div' ) ).toHaveAttribute( 'lang', 'en' );
		await expect( page.locator( '.am-lg-text h2' ).first() ).toBeVisible();
		await expect( page.getByRole( 'navigation', { name: 'Auf dieser Seite' } ) ).toHaveAttribute( 'lang', 'en' );
		await page.goto( '/privacy-policy/' );
		await expect( page.locator( '.am-lg-top__lang' ) ).toHaveCount( 0 );
		await expect( page.locator( '.am-lg-text > div' ) ).not.toHaveAttribute( 'lang', 'en' );
	} );

	test( 'booking in German: German dates, ends on the German thank-you page', async ( { page } ) => {
		await page.goto( '/de/buchen/' );
		await page.getByRole( 'button', { name: /Anreise/ } ).click();
		const shown = page.locator( '[data-bk-month-name]' );
		await expect( shown ).toHaveText( /^(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember) \d{4}$/ );
		for ( let i = 0; i < 36 && ! /^Mai 2027$/.test( await shown.textContent() ); i++ ) {
			await page.getByRole( 'button', { name: 'Nächster Monat' } ).click();
		}
		const arrival = page.locator( `[data-day="${ Date.UTC( 2027, 4, 15 ) }"]` );
		await expect( arrival ).toHaveAttribute( 'aria-label', 'Sa., 15. Mai' );
		await arrival.click();
		await page.locator( `[data-day="${ Date.UTC( 2027, 4, 22 ) }"]` ).click();
		await page.locator( '[data-bk-unit]:visible' ).first().getByRole( 'button', { name: 'Auswählen' } ).click();
		await page.waitForURL( /\/de\/danke\/$/ );
		await expect( page.locator( '[data-booking="room"]' ) ).toHaveText( 'Zimmer Standard Queen' );
		await expect( page.locator( 'h1' ) ).toContainText( 'Vielen Dank' );
	} );
} );
