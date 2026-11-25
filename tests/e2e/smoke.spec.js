import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { PAGES, PAGES_DE, PAGES_PL, NOT_FOUND, WIDTHS } from './pages.js';

const ALL = [ ...PAGES, ...PAGES_DE, ...PAGES_PL ];

test.describe( 'every page answers', () => {
	for ( const path of ALL ) {
		test( `${ path } answers 200 with one h1 and a language`, async ( { page } ) => {
			const response = await page.goto( path );
			expect( response.status() ).toBe( 200 );
			await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
			await expect( page ).toHaveTitle( /\S/ );
			const lang = await page.locator( 'html' ).getAttribute( 'lang' );
			expect( lang ).toMatch( new RegExp( '^' + ( path.match( /^\/(de|pl)\// )?.[ 1 ] ?? 'en' ) ) );
		} );
	}

	test( 'a missing page answers 404 with its own page', async ( { page } ) => {
		const response = await page.goto( NOT_FOUND );
		expect( response.status() ).toBe( 404 );
		await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
	} );
} );

test.describe( 'no sideways scroll', () => {
	for ( const path of ALL ) {
		test( `${ path } fits 360 to 1440`, async ( { page } ) => {
			await page.goto( path );
			for ( const width of WIDTHS ) {
				await page.setViewportSize( { width, height: 900 } );
				const overflow = await page.evaluate( () => document.documentElement.scrollWidth - document.documentElement.clientWidth );
				expect( overflow, `document is wider than the window at ${ width }px` ).toBeLessThanOrEqual( 0 );
			}
		} );
	}
} );

test.describe( 'accessibility (axe, WCAG 2.2 AA)', () => {
	for ( const path of [ ...ALL, NOT_FOUND ] ) {
		for ( const width of [ 1440, 390 ] ) {
			test( `${ path } at ${ width }px has no serious violations`, async ( { page } ) => {
				await page.setViewportSize( { width, height: 900 } );
				await page.goto( path );
				// Text that lights up with the scroll is checked in its final state (reduced motion) below.
				const results = await new AxeBuilder( { page } )
					.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] )
					.exclude( '[data-axe-motion]' )
					.analyze();
				const serious = results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) );
				expect( serious.map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` ) ).toEqual( [] );
			} );
		}
	}
} );

test( 'Skip to content is the first stop for the keyboard and moves focus to the content', async ( { page } ) => {
	await page.goto( '/' );
	await page.keyboard.press( 'Tab' );
	const skip = page.locator( '.skip-link' );
	await expect( skip ).toBeFocused();
	await expect( skip ).toBeVisible();
	await page.keyboard.press( 'Enter' );
	await expect( page.locator( '#main' ) ).toBeFocused();
} );

test.describe( 'accessibility in the final state of every animation (reduced motion)', () => {
	test.use( { reducedMotion: 'reduce' } );
	for ( const path of [ '/', '/de/' ] ) {
		for ( const width of [ 1440, 390 ] ) {
			test( `${ path } at ${ width }px has no serious violations`, async ( { page } ) => {
				await page.setViewportSize( { width, height: 900 } );
				await page.goto( path );
				const results = await new AxeBuilder( { page } )
					.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] )
					.analyze();
				const serious = results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) );
				expect( serious.map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` ) ).toEqual( [] );
			} );
		}
	}
} );
