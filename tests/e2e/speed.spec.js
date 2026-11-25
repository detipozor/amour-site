import { test, expect } from '@playwright/test';

// Speed (HANDOFF, sections 10 and 13). Lighthouse itself runs in its own job (npm run speed, lighthouserc.json).

const logo = ( name ) => `use:is([href*="/brand/${ name }.svg"], [data-href*="/brand/${ name }.svg"])`;

test.describe( 'Speed (HANDOFF, section 13)', () => {
	test( 'the first screen loads only the logos it shows; the rest come a moment after the page', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		const asked = [];
		page.on( 'request', ( request ) => {
			const match = request.url().match( /\/brand\/([a-z-]+)\.svg/ );
			if ( match ) {
				asked.push( match[ 1 ] );
			}
		} );
		await page.goto( '/', { waitUntil: 'load' } );
		// The olive field of the footer is a CSS mask of 10 KB, drawn with the page.
		expect( asked.filter( ( name ) => name !== 'olive' ).sort() ).toEqual( [ 'amour-horizontal', 'amour-word' ] );
		// The header and the curtain of the other house, the footer: waiting.
		await expect( page.locator( `.am-header ${ logo( 'eudora-horizontal' ) }` ) ).toHaveAttribute( 'data-href', /eudora-horizontal/ );
		await expect( page.locator( `.am-footer__brand ${ logo( 'amour-main' ) }` ) ).toHaveAttribute( 'data-href', /amour-main/ );

		await expect( page.locator( 'use[data-href]' ) ).toHaveCount( 0, { timeout: 5000 } );
		await expect( page.locator( `.am-footer__brand ${ logo( 'amour-main' ) }` ) ).toHaveAttribute( 'href', /amour-main\.svg\?v=[\d.]+#logo$/ );
		expect( asked ).toContain( 'amour-main' );
	} );

	test( 'on the villa the Eudora logo comes first and the Amour one waits', async ( { page } ) => {
		await page.goto( '/villa-eudora/' );
		await expect( page.locator( `.am-header ${ logo( 'eudora-horizontal' ) }` ) ).toHaveAttribute( 'href', /eudora-horizontal/ );
		await expect( page.locator( `.am-header ${ logo( 'amour-horizontal' ) }` ) ).toHaveAttribute( 'data-href', /amour-horizontal/ );
	} );

	test( 'the logos for the site are thinned: under 60 KB each', async ( { page } ) => {
		for ( const name of [ 'amour-horizontal', 'amour-main', 'amour-word', 'eudora-horizontal', 'eudora-word', 'olive' ] ) {
			const response = await page.request.get( `/wp-content/themes/amour/assets/brand/${ name }.svg` );
			expect( response.ok(), name ).toBe( true );
			expect( ( await response.body() ).length, name ).toBeLessThan( 60 * 1024 );
		}
	} );

	test( 'the home video: about 2.5 MB at 1080p on a computer, a 720p one on a phone', async ( { page } ) => {
		await page.goto( '/' );
		const video = page.locator( '.am-hero__video' );
		await expect( video ).toHaveAttribute( 'data-src-phone', /home-hero-video-phone.*\.mp4$/ );
		await expect( video ).toHaveAttribute( 'src', /home-hero-video(?!-phone).*\.mp4$/ );
		const big = await page.request.head( await video.getAttribute( 'data-src' ) );
		expect( Number( big.headers()[ 'content-length' ] ) ).toBeLessThan( 2.8 * 1024 * 1024 );

		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/' );
		await expect( video ).toHaveAttribute( 'src', /home-hero-video-phone.*\.mp4$/ );
		const small = await page.request.head( await video.getAttribute( 'src' ) );
		expect( Number( small.headers()[ 'content-length' ] ) ).toBeLessThan( 1.6 * 1024 * 1024 );
	} );

	test( 'no emoji scripts and no block editor presets; the legal pages keep the block styles', async ( { page } ) => {
		await page.goto( '/' );
		await expect( page.locator( 'script[src*="emoji"]' ) ).toHaveCount( 0 );
		await expect( page.locator( '#global-styles-inline-css, #classic-theme-styles-inline-css, #wp-block-library-inline-css' ) ).toHaveCount( 0 );
		await expect( page.locator( 'link[rel="preload"][as="font"][href*="InstrumentSans-latin.woff2"]' ) ).toHaveCount( 1 );

		await page.goto( '/cookies/' );
		await expect( page.locator( '#wp-block-table-inline-css' ) ).toHaveCount( 1 );
		await expect( page.locator( '#global-styles-inline-css' ) ).toHaveCount( 0 );
	} );
} );
