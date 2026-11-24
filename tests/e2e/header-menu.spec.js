import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { AUTH_FILE } from './auth.js';

/**
 * HANDOFF, section 15: axe without serious problems, except in the middle of an animation — so wait for the
 * animations that end (the menu opening, colour fades). Endless ones, like the olive wave in the footer, are decor.
 */
/** Wait until the smooth scroll has glided to where the wheel sent it. */
const glidedTo = ( page, y ) => page.waitForFunction( ( target ) => Math.abs( window.scrollY - target ) < 2, y );

const settle = ( page ) =>
	page.waitForFunction( () =>
		document.getAnimations().every( ( a ) => a.playState !== 'running' || a.effect?.getTiming().iterations === Infinity )
	);

const axe = async ( page ) => {
	await settle( page );
	const results = await new AxeBuilder( { page } )
		.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] )
		.exclude( '#wpadminbar' )
		.analyze();
	return results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) ).map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` );
};

test.describe( 'header (HANDOFF, section 5)', () => {
	test( 'desktop: Menu, the logo to the home page, language and Book', async ( { page } ) => {
		await page.goto( '/questions/' );
		const menu = page.getByRole( 'button', { name: 'Menu', exact: true } );
		await expect( menu ).toBeVisible();
		await expect( menu ).toHaveAttribute( 'aria-expanded', 'false' );
		await expect( page.locator( '.am-header' ).getByRole( 'link', { name: 'Amour Relax Hotel, home' } ) ).toHaveAttribute( 'href', /\/$/ );
		await expect( page.locator( '.am-lang__toggle' ) ).toHaveText( 'EN' );
		await expect( page.locator( '.am-header__book' ) ).toHaveAttribute( 'href', /\/book\/$/ );
		await expect( page.locator( '.am-header .am-logo--amour-horizontal' ) ).toBeVisible();
	} );

	test( 'phone: Menu keeps only its icon and its name, the language moves into the menu', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/questions/' );
		const menu = page.getByRole( 'button', { name: 'Menu', exact: true } );
		await expect( menu ).toBeVisible();
		expect( ( await menu.boundingBox() ).width ).toBeLessThanOrEqual( 46 );
		await expect( page.locator( '.am-lang' ) ).toBeHidden();
		await expect( page.locator( '.am-header__book' ) ).toBeVisible();
	} );

	test( 'the villa shows the Eudora logo in its colours', async ( { page } ) => {
		await page.goto( '/villa-eudora/' );
		await expect( page.locator( '.am-header' ) ).toHaveAttribute( 'data-brand', 'eudora' );
		await expect( page.locator( '.am-header' ).getByRole( 'link', { name: 'Villa Eudora, home' } ) ).toHaveAttribute( 'href', /villa-eudora\/$/ );
	} );

	test( 'language list opens, offers German and closes with Esc', async ( { page } ) => {
		await page.goto( '/questions/' );
		const toggle = page.locator( '.am-lang__toggle' );
		await toggle.click();
		await expect( toggle ).toHaveAttribute( 'aria-expanded', 'true' );
		const german = page.locator( '.am-lang__list a[hreflang="de-DE"]' );
		await expect( german ).toBeVisible();
		await expect( german ).toHaveAttribute( 'href', /\/de\/$/ );
		await page.keyboard.press( 'Escape' );
		await expect( page.locator( '.am-lang__list' ) ).toBeHidden();
		await expect( toggle ).toBeFocused();
	} );
} );

test.describe( 'header while scrolling (styleguide is long enough)', () => {
	test.use( { storageState: AUTH_FILE } );

	test( 'desktop: the logo steps aside reading down and returns scrolling up; light over dark blocks', async ( { page } ) => {
		await page.goto( '/styleguide/' );
		const header = page.locator( '.am-header' );
		// The wheel glides (smooth scroll), so wait each time until the page stands still.
		await page.mouse.move( 720, 450 );
		await page.mouse.wheel( 0, 700 );
		await glidedTo( page, 700 );
		await expect( header ).toHaveClass( /is-up/ );
		await page.mouse.wheel( 0, -150 );
		await glidedTo( page, 550 );
		await expect( header ).not.toHaveClass( /is-up/ );

		// A wine block under the logo: the logo turns limestone.
		await page.evaluate( () => {
			const block = document.querySelector( '.sg-deep' );
			window.scrollTo( 0, block.getBoundingClientRect().top + window.scrollY - 20 );
		} );
		await expect( header ).toHaveClass( /is-light/ );
		await page.evaluate( () => window.scrollTo( 0, 0 ) );
		await expect( header ).not.toHaveClass( /is-light/ );
	} );

	test( 'phone: the logo shows only near the top of the page', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/styleguide/' );
		const header = page.locator( '.am-header' );
		await page.evaluate( () => window.scrollTo( 0, 400 ) );
		await expect( header ).toHaveClass( /is-up/ );
		await page.evaluate( () => window.scrollTo( 0, 300 ) );
		await expect( header ).toHaveClass( /is-up/ );
		await page.evaluate( () => window.scrollTo( 0, 100 ) );
		await expect( header ).not.toHaveClass( /is-up/ );
	} );
} );

test.describe( 'menu (board Menu-list)', () => {
	for ( const width of [ 1440, 390 ] ) {
		test( `opens with the keyboard inside, closes with Esc and returns focus to Menu (${ width }px)`, async ( { page } ) => {
			await page.setViewportSize( { width, height: width > 760 ? 900 : 844 } );
			await page.goto( '/questions/' );
			const button = page.getByRole( 'button', { name: 'Menu', exact: true } );
			await button.click();

			const dialog = page.getByRole( 'dialog', { name: 'Menu' } );
			await expect( dialog ).toBeVisible();
			await expect( button ).toHaveAttribute( 'aria-expanded', 'true' );
			const links = dialog.getByRole( 'navigation', { name: 'Main' } ).getByRole( 'link' );
			await expect( links ).toHaveText( [ 'Rooms', 'Villa Eudora', 'A day at Amour', 'Court and pool', 'Crete guide', 'About us', 'Contact' ] );
			await expect( links.first() ).toBeFocused();

			// The page behind is inert: Tab never leaves the menu.
			for ( let i = 0; i < 20; i++ ) {
				await page.keyboard.press( 'Tab' );
				expect( await page.evaluate( () => !! document.activeElement.closest( '#am-menu' ) || document.activeElement === document.body ) ).toBe( true );
			}

			expect( await axe( page ) ).toEqual( [] );

			await page.keyboard.press( 'Escape' );
			await expect( dialog ).toBeHidden();
			await expect( button ).toBeFocused();
			await expect( button ).toHaveAttribute( 'aria-expanded', 'false' );
		} );
	}

	test( 'desktop: the photo and the line follow the name under the pointer (from the admin)', async ( { page } ) => {
		await page.goto( '/questions/' );
		await page.getByRole( 'button', { name: 'Menu', exact: true } ).click();
		const court = page.locator( '.am-menu__nav a', { hasText: 'Court and pool' } );
		await settle( page ); // the names rise into place first
		await court.hover();
		await expect( court ).toHaveClass( /is-active/ );
		await expect( page.locator( '.am-menu__line' ) ).toHaveText( 'Stone terraces, a pool and our own court among olive groves.' );
		await expect( page.locator( '.am-menu__arch img.is-active' ) ).toHaveAttribute( 'data-index', '3' );
	} );

	test( 'footer of the menu: languages, contacts (placeholders on a test copy) and networks', async ( { page } ) => {
		await page.goto( '/questions/' );
		await page.getByRole( 'button', { name: 'Menu', exact: true } ).click();
		const foot = page.locator( '.am-menu__foot' );
		await expect( foot.locator( '.am-menu__langs a' ) ).toHaveText( [ 'EN', 'DE' ] );
		await expect( foot.locator( '.am-menu__contacts' ) ).toContainText( '[Phone]' );
		await expect( foot.locator( '.am-menu__social a' ) ).toHaveCount( 2 );
	} );

	test( 'a page from the menu opens after the soft dissolve, and nothing stays over it', async ( { page } ) => {
		await page.goto( '/questions/' );
		await page.getByRole( 'button', { name: 'Menu', exact: true } ).click();
		await page.locator( '.am-menu__nav a', { hasText: 'About us' } ).click();
		await page.waitForURL( /\/about-us\/$/ );
		await expect( page.locator( 'h1' ) ).toHaveText( 'About Amour' );
		await expect( page.locator( 'html' ) ).not.toHaveClass( /is-arriving/, { timeout: 3000 } );
		await expect( page.locator( '#am-menu' ) ).toBeHidden();
	} );
} );

test.describe( 'moving between pages', () => {
	test( 'the curtain covers the page, the next one opens, Back shows the page without the curtain', async ( { page } ) => {
		await page.goto( '/questions/' );
		await page.locator( '.am-footer__legal a', { hasText: 'Cookies' } ).click();
		await expect( page.locator( 'html' ) ).toHaveClass( /is-leaving/ );
		await page.waitForURL( /\/cookies\/$/ );
		await expect( page.locator( 'html' ) ).not.toHaveClass( /is-arriving/, { timeout: 3000 } );
		await page.goBack();
		await page.waitForURL( /\/questions\/$/ );
		await expect( page.locator( 'html' ) ).not.toHaveClass( /is-leaving/ );
	} );

	test( 'with reduced motion links open at once and the menu has no animation', async ( { page } ) => {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.goto( '/questions/' );
		await page.locator( '.am-footer__legal a', { hasText: 'Cookies' } ).click();
		await page.waitForURL( /\/cookies\/$/ );
		await expect( page.locator( 'html' ) ).not.toHaveClass( /is-leaving|is-arriving/ );
		await page.getByRole( 'button', { name: 'Menu', exact: true } ).click();
		await expect( page.locator( '#am-menu' ) ).toBeVisible();
		await expect( page.locator( '.am-menu__nav a' ).first() ).toBeFocused();
	} );
} );
