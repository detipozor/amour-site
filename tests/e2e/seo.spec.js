import { test, expect } from '@playwright/test';

// Search and sharing (HANDOFF, section 13).

const meta = ( page, property ) => page.locator( `meta[property="${ property }"]` );
const schema = async ( page ) => JSON.parse( await page.locator( 'script[type="application/ld+json"]' ).textContent() );

test.describe( 'Search and sharing (HANDOFF, section 13)', () => {
	test( 'titles as on the boards: page · Amour Relax Hotel, with their own for the home page, the villa, the guide', async ( { page } ) => {
		const titles = {
			'/': 'Amour Relax Hotel · Almyrida, Crete',
			'/rooms/': 'Rooms · Amour Relax Hotel',
			'/rooms/superior-queen-room/': 'Superior Queen Room · Amour Relax Hotel',
			'/villa-eudora/': 'Villa Eudora · Almyrida, Crete',
			'/crete-guide/': 'Almyrida and Crete guide · Amour Relax Hotel',
			'/book/': 'Book your stay · Amour Relax Hotel',
			'/no-such-page-here/': 'Page not found · Amour Relax Hotel',
			'/de/zimmer/': 'Rooms · Amour Relax Hotel',
		};
		for ( const [ path, title ] of Object.entries( titles ) ) {
			await page.goto( path );
			await expect( page, path ).toHaveTitle( title );
		}
	} );

	test( 'Open Graph: the title, the address, the language and a picture cut to 1200 × 630', async ( { page } ) => {
		await page.goto( '/rooms/deluxe-queen-room/' );
		await expect( meta( page, 'og:title' ) ).toHaveAttribute( 'content', 'Deluxe Queen Room · Amour Relax Hotel' );
		await expect( meta( page, 'og:url' ) ).toHaveAttribute( 'content', /\/rooms\/deluxe-queen-room\/$/ );
		await expect( meta( page, 'og:locale' ) ).toHaveAttribute( 'content', 'en_US' );
		await expect( meta( page, 'og:locale:alternate' ) ).toHaveAttribute( 'content', 'de_DE' );
		await expect( meta( page, 'og:image' ) ).toHaveAttribute( 'content', /^https?:\/\/.+-1200x630\.\w+$/ );
		await expect( meta( page, 'og:image:width' ) ).toHaveAttribute( 'content', '1200' );
		await expect( page.locator( 'meta[name="twitter:card"]' ) ).toHaveAttribute( 'content', 'summary_large_image' );
		const image = await meta( page, 'og:image' ).getAttribute( 'content' );
		expect( ( await page.request.get( image ) ).ok() ).toBe( true );

		await page.goto( '/de/zimmer/' );
		await expect( meta( page, 'og:locale' ) ).toHaveAttribute( 'content', 'de_DE' );
		await expect( meta( page, 'og:url' ) ).toHaveAttribute( 'content', /\/de\/zimmer\/$/ );
	} );

	test( 'schema.org: the Hotel, a HotelRoom in it, the villa as LodgingBusiness; no placeholders', async ( { page } ) => {
		await page.goto( '/' );
		const hotel = await schema( page );
		expect( hotel ).toMatchObject( { '@type': 'Hotel', name: 'Amour Relax Hotel', numberOfRooms: 9, address: { addressLocality: 'Almyrida', addressCountry: 'GR' } } );
		expect( JSON.stringify( hotel ) ).not.toContain( '"[' );

		await page.goto( '/rooms/standard-queen-room/' );
		const graph = ( await schema( page ) )[ '@graph' ];
		expect( graph.map( ( item ) => item[ '@type' ] ) ).toEqual( [ 'Hotel', 'HotelRoom' ] );
		expect( graph[ 1 ] ).toMatchObject( { name: 'Standard Queen Room', occupancy: { maxValue: 2 }, containedInPlace: { '@id': graph[ 0 ][ '@id' ] } } );

		await page.goto( '/villa-eudora/' );
		expect( await schema( page ) ).toMatchObject( { '@type': 'LodgingBusiness', name: 'Villa Eudora', numberOfRooms: 5 } );

		await page.goto( '/questions/' );
		await expect( page.locator( 'script[type="application/ld+json"]' ) ).toHaveCount( 0 );
	} );

	test( 'a test copy is not for search engines', async ( { page } ) => {
		for ( const path of [ '/', '/rooms/', '/de/' ] ) {
			await page.goto( path );
			await expect( page.locator( 'meta[name="robots"]' ), path ).toHaveAttribute( 'content', /noindex, nofollow/ );
		}
		await expect( page.locator( 'meta[name="generator"]' ) ).toHaveCount( 0 );
	} );

	test( 'the sitemap lists the pages without the thank-you page; /sitemap.xml leads to it', async ( { page } ) => {
		const pages = await ( await page.request.get( '/wp-sitemap-posts-page-1.xml' ) ).text();
		expect( pages ).toContain( '/rooms/</loc>' );
		expect( pages ).toContain( '/book/</loc>' );
		expect( pages ).not.toContain( '/thank-you/' );
		const german = await ( await page.request.get( '/de/wp-sitemap-posts-page-1.xml' ) ).text();
		expect( german ).toContain( '/de/zimmer/</loc>' );
		expect( german ).not.toContain( '/de/danke/' );
		const index = await page.request.get( '/sitemap.xml', { maxRedirects: 0 } );
		expect( index.status() ).toBe( 301 );
		expect( index.headers().location ).toMatch( /\/wp-sitemap\.xml$/ );
	} );

	test( 'the old checkout page leads to the booking page for good (301)', async ( { page } ) => {
		const response = await page.request.get( '/checkout-page/', { maxRedirects: 0 } );
		expect( response.status() ).toBe( 301 );
		expect( response.headers().location ).toMatch( /\/book\/$/ );
		await page.goto( '/checkout-page/' );
		await expect( page ).toHaveURL( /\/book\/$/ );
	} );
} );
