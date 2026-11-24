import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const axe = async ( page ) => {
	const results = await new AxeBuilder( { page } ).withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] ).analyze();
	return results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) ).map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` );
};

const lastWord = ( page ) => page.locator( '.am-ab-phil__text span' ).last();
const opacity = ( locator ) => locator.evaluate( ( el ) => Number( getComputedStyle( el ).opacity ) );

test.describe( 'About (board About)', () => {
	test( 'the heading, the three photos at their heights and the box at the bottom, from the admin', async ( { page } ) => {
		await page.goto( '/about-us/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'About Amour' );
		await expect( page.locator( '.am-ab-top__arch img' ) ).toBeVisible();
		await expect( page.locator( '.am-ab-phil h2' ) ).toHaveText( 'Our philosophy' );
		const figures = page.locator( '.am-ab-fig' );
		await expect( figures.locator( 'figcaption' ) ).toHaveText( [ 'Our court at sunset1 / 3', 'The stairs to the pool2 / 3', 'One of the nine rooms3 / 3' ] );
		const tops = await figures.evaluateAll( ( els ) => els.map( ( el ) => Math.round( el.getBoundingClientRect().top - els[ 0 ].getBoundingClientRect().top ) ) );
		expect( tops ).toEqual( [ 0, 120, 40 ] );
		const end = page.locator( '.am-ab-end' );
		await expect( end.getByRole( 'heading', { level: 2 } ) ).toHaveText( 'Stay like you live here' );
		await expect( end.getByRole( 'link', { name: 'Book your stay' } ) ).toHaveAttribute( 'href', /\/book\/$/ );
		await expect( end.getByRole( 'link', { name: 'See the rooms' } ) ).toHaveAttribute( 'href', /\/rooms\/$/ );
	} );

	test( 'the philosophy lights up word by word as it rises through the screen', async ( { page } ) => {
		await page.setViewportSize( { width: 1440, height: 900 } );
		await page.goto( '/about-us/' );
		await expect.poll( () => opacity( lastWord( page ) ) ).toBeLessThan( 0.3 );
		// The phrase's top at 15% of the screen: the whole phrase is lit.
		await page.evaluate( () => {
			const top = document.querySelector( '[data-ab-phrase]' ).getBoundingClientRect().top + window.scrollY;
			window.scrollTo( 0, top - window.innerHeight * 0.15 );
		} );
		await expect.poll( () => opacity( lastWord( page ) ) ).toBe( 1 );
	} );

	test( 'phone: one column, the photos without the steps, the heading fits the screen', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/about-us/' );
		const lefts = await page.locator( '.am-ab-fig' ).evaluateAll( ( els ) => els.map( ( el ) => Math.round( el.getBoundingClientRect().left ) ) );
		expect( new Set( lefts ).size ).toBe( 1 );
		expect( await page.locator( 'h1' ).evaluate( ( el ) => parseFloat( getComputedStyle( el ).fontSize ) ) ).toBeLessThanOrEqual( 44 );
	} );
} );

test.describe( 'About with reduced motion', () => {
	test.use( { reducedMotion: 'reduce' } );

	test( 'the philosophy is lit at once, and axe finds nothing serious in that state', async ( { page } ) => {
		await page.goto( '/about-us/' );
		await expect.poll( () => opacity( lastWord( page ) ) ).toBe( 1 );
		expect( await axe( page ) ).toEqual( [] );
	} );
} );

test.describe( 'Legal pages (board Legal)', () => {
	test( 'the switch between the three documents marks the one open', async ( { page } ) => {
		await page.goto( '/privacy-policy/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Privacy policy' );
		const docs = page.getByRole( 'navigation', { name: 'Documents' } ).getByRole( 'link' );
		await expect( docs ).toHaveText( [ 'Privacy policy', 'Cookie policy', 'Booking terms' ] );
		await expect( docs.first() ).toHaveAttribute( 'aria-current', 'page' );
		await docs.nth( 2 ).click();
		await expect( page ).toHaveURL( /\/booking-terms\/$/ );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Booking terms' );
		await expect( page.getByRole( 'navigation', { name: 'Documents' } ).getByRole( 'link', { name: 'Booking terms' } ) ).toHaveAttribute( 'aria-current', 'page' );
	} );

	test( 'the contents on the side follow the headings of the text and lead to them', async ( { page } ) => {
		await page.goto( '/privacy-policy/' );
		const toc = page.getByRole( 'navigation', { name: 'On this page' } ).getByRole( 'link' );
		const headings = await page.locator( '.am-lg-text h2' ).allTextContents();
		expect( headings.length ).toBeGreaterThan( 3 );
		await expect( toc ).toHaveText( headings );
		await toc.filter( { hasText: 'Your rights' } ).click();
		await expect( page ).toHaveURL( /#sec-6$/ );
		await expect( page.locator( '#sec-6' ) ).toBeInViewport();
		await expect( page.locator( '.am-lg-top__date' ) ).toContainText( 'Draft' );
		await expect( page.locator( '.am-lg-ask' ).getByRole( 'link', { name: 'contact us' } ) ).toHaveAttribute( 'href', /\/contacts\/$/ );
	} );

	test( 'the cookie table sits inside the text column', async ( { page } ) => {
		await page.goto( '/cookies/' );
		const table = page.locator( '.am-lg-text table' );
		await expect( table ).toBeVisible();
		const text = await page.locator( '.am-lg-text' ).boundingBox();
		const box = await table.boundingBox();
		expect( box.x + box.width ).toBeLessThanOrEqual( text.x + text.width + 1 );
	} );

	test( 'phone: the contents are hidden, the text takes the screen', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/booking-terms/' );
		await expect( page.locator( '.am-lg-toc' ) ).toBeHidden();
		const text = await page.locator( '.am-lg-text' ).boundingBox();
		expect( Math.round( text.width ) ).toBe( 390 - 32 );
	} );
} );

test.describe( 'Thank you (board Thanks)', () => {
	test( 'not indexed; placeholders until the booking engine gives the booking; three next steps', async ( { page } ) => {
		await page.goto( '/thank-you/' );
		await expect( page.locator( 'meta[name="robots"]' ) ).toHaveAttribute( 'content', /noindex/ );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Thank you, [name]' );
		await expect( page.locator( '.am-th-summary dt' ) ).toHaveText( [ 'Booking number', 'Stay', 'Room', 'Guests', 'Paid', 'At the hotel' ] );
		await expect( page.locator( '[data-booking="fee"]' ) ).toHaveText( 'Climate fee [€…] a night' );
		await expect( page.locator( '.am-th-next h3' ) ).toHaveText( [ 'Before you travel', 'Getting here', 'Arrival' ] );
		await expect( page.getByRole( 'link', { name: 'Plan your days' } ) ).toHaveAttribute( 'href', /\/crete-guide\/$/ );
	} );

	test( 'the name and the summary from the booking', async ( { page } ) => {
		await page.addInitScript( () => {
			window.sessionStorage.setItem(
				'amour-booking',
				JSON.stringify( { name: 'Test Guest', number: 'TEST-1', stay: 'Test dates', room: 'Test room', guests: 'Test guests', paid: 'Test amount', fee: 'Test fee' } )
			);
		} );
		await page.goto( '/thank-you/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Thank you, Test Guest' );
		await expect( page.locator( '.am-th-summary dd' ) ).toHaveText( [ 'TEST-1', 'Test dates', 'Test room', 'Test guests', 'Test amount', 'Climate fee Test fee a night' ] );
	} );

	test( 'phone: the photo under the summary, the steps in one column', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/thank-you/' );
		const summary = await page.locator( '.am-th-summary' ).boundingBox();
		const arch = await page.locator( '.am-th-top__arch' ).boundingBox();
		expect( arch.y ).toBeGreaterThan( summary.y + summary.height );
		const lefts = await page.locator( '.am-th-next__list li' ).evaluateAll( ( els ) => els.map( ( el ) => Math.round( el.getBoundingClientRect().left ) ) );
		expect( new Set( lefts ).size ).toBe( 1 );
	} );
} );

test.describe( 'Page not found (board NotFound)', () => {
	test( 'answers 404 and leads home, to the rooms, the villa and the guide', async ( { page } ) => {
		const response = await page.goto( '/no-such-page-here/' );
		expect( response.status() ).toBe( 404 );
		await expect( page.locator( 'h1' ) ).toHaveText( 'This path ends here' );
		const links = page.locator( '.am-nf__actions a' );
		await expect( links ).toHaveText( [ 'Back to home', 'Rooms', 'Villa Eudora', 'Crete guide' ] );
		await expect( links.first() ).toHaveAttribute( 'href', /\/$/ );
		await expect( page.locator( '.am-nf__arch img' ) ).toBeVisible();
	} );
} );
