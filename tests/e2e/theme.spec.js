import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { AUTH_FILE } from './auth.js';

const style = ( page, selector, property ) =>
	page.locator( selector ).first().evaluate( ( el, prop ) => getComputedStyle( el ).getPropertyValue( prop ), property );

test.describe( 'tokens and fonts (HANDOFF, section 3)', () => {
	test( 'Amour palette, fonts from our server and the type scale on desktop', async ( { page } ) => {
		await page.goto( '/rooms/' );
		await expect( page.locator( 'html' ) ).toHaveAttribute( 'data-theme', 'amour' );
		expect( await style( page, 'body', 'background-color' ) ).toBe( 'rgb(242, 234, 219)' );
		expect( await style( page, 'body', 'color' ) ).toBe( 'rgb(42, 36, 32)' );
		expect( await style( page, 'body', 'font-size' ) ).toBe( '18px' );

		const fonts = await page.evaluate( async () => {
			await document.fonts.ready;
			await document.fonts.load( '18px "Instrument Sans"' );
			await document.fonts.load( '13px "Tenor Sans"' );
			return [ document.fonts.check( '18px "Instrument Sans"' ), document.fonts.check( '13px "Tenor Sans"' ) ];
		} );
		expect( fonts ).toEqual( [ true, true ] );

		expect( await style( page, 'h1', 'font-size' ) ).toBe( '72px' );
		expect( await style( page, 'h1', 'font-family' ) ).toMatch( /^"?Instrument Sans/ );
		await expect( page.locator( 'link[rel="preload"][as="font"][href*="InstrumentSans-latin.woff2"]' ) ).toHaveCount( 1 );
	} );

	test( 'phone type scale', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/rooms/' );
		expect( await style( page, 'body', 'font-size' ) ).toBe( '16px' );
		expect( await style( page, 'h1', 'font-size' ) ).toBe( '40px' );
	} );

	test( 'the villa page has its own palette', async ( { page } ) => {
		await page.goto( '/villa-eudora/' );
		await expect( page.locator( 'html' ) ).toHaveAttribute( 'data-theme', 'eudora' );
		expect( await style( page, 'body', 'color' ) ).toBe( 'rgb(28, 34, 38)' );
	} );
} );

test( 'the styleguide is hidden from visitors', async ( { page } ) => {
	const response = await page.goto( '/styleguide/' );
	expect( response.status() ).toBe( 404 );
} );

test.describe( 'styleguide, logged in', () => {
	test.use( { storageState: AUTH_FILE } );

	test( 'shows tokens, buttons, icons and logos without accessibility problems', async ( { page } ) => {
		for ( const width of [ 1440, 390 ] ) {
			await page.setViewportSize( { width, height: 900 } );
			const response = await page.goto( '/styleguide/' );
			expect( response.status() ).toBe( 200 );
			await expect( page.locator( 'h1' ) ).toHaveCount( 1 );

			// Logos come from the package files and get their size from CSS.
			const logos = page.locator( 'svg.am-logo' );
			expect( await logos.count() ).toBeGreaterThan( 5 );
			const box = await logos.first().boundingBox();
			expect( box.width ).toBeGreaterThan( 100 );

			const overflow = await page.evaluate( () => document.documentElement.scrollWidth - document.documentElement.clientWidth );
			expect( overflow, `sideways scroll at ${ width }px` ).toBeLessThanOrEqual( 0 );

			const results = await new AxeBuilder( { page } )
				.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] )
				.exclude( '#wpadminbar' )
				.analyze();
			const serious = results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) );
			expect( serious.map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` ) ).toEqual( [] );
		}
	} );

	test( 'buttons lift on hover, and stay still with reduced motion', async ( { page } ) => {
		await page.goto( '/styleguide/' );
		const button = page.locator( 'a.am-btn:not(.am-btn--ghost):not(.am-btn--small)' ).first();
		const before = await button.evaluate( ( el ) => getComputedStyle( el ).backgroundColor );
		await button.hover();
		await page.waitForTimeout( 450 );
		const after = await button.evaluate( ( el ) => [ getComputedStyle( el ).backgroundColor, getComputedStyle( el ).transform ] );
		expect( before ).toBe( 'rgb(168, 72, 42)' );
		expect( after[ 0 ] ).toBe( 'rgb(155, 66, 39)' );
		expect( after[ 1 ] ).toBe( 'matrix(1, 0, 0, 1, 0, -2)' );

		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.mouse.move( 0, 0 );
		await button.hover();
		expect( await button.evaluate( ( el ) => getComputedStyle( el ).transform ) ).toBe( 'none' );
	} );
} );
