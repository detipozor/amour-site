import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const axe = async ( page ) => {
	const results = await new AxeBuilder( { page } ).withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] ).analyze();
	return results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) ).map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` );
};

test.describe( 'Questions (board FAQ)', () => {
	test( 'topics and questions from the admin; the first one open; the others open and close', async ( { page } ) => {
		await page.goto( '/questions/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Questions and house rules' );
		await expect( page.locator( '.am-faq-nav__link' ) ).toHaveText( [ 'Booking and payment', 'Arrival and stay', 'Children and pets', 'Court and pool', 'Villa Eudora' ] );
		await expect( page.locator( '.am-faq-q' ) ).toHaveCount( 13 );
		const first = page.locator( '.am-faq-q' ).first();
		await expect( first ).toHaveAttribute( 'open', '' );
		await expect( first.locator( 'p' ) ).toBeVisible();
		const second = page.locator( '.am-faq-q' ).nth( 1 );
		await expect( second.locator( 'p' ) ).toBeHidden();
		await second.locator( 'summary' ).click();
		await expect( second.locator( 'p' ) ).toHaveText( /In full when you book/ );
		await expect( page.locator( '.am-faq-note' ) ).toContainText( 'not legal terms' );
	} );

	test( 'the topic being read is marked in the list', async ( { page } ) => {
		await page.goto( '/questions/' );
		await page.locator( '.am-faq-nav__link', { hasText: 'Children and pets' } ).click();
		await expect( page ).toHaveURL( /#family$/ );
		await expect( page.locator( '[data-faq-link="family"]' ) ).toHaveAttribute( 'aria-current', 'true' );
		await expect( page.locator( '[data-faq-link="booking"]' ) ).not.toHaveClass( /is-on/ );
	} );

	test( 'the box at the bottom leads to WhatsApp and email (the contact page while there is no number)', async ( { page } ) => {
		await page.goto( '/questions/' );
		const box = page.locator( '.am-faq-cta' );
		await expect( box.getByRole( 'heading', { level: 2 } ) ).toHaveText( 'Still wondering about something?' );
		await expect( box.getByRole( 'link', { name: 'Write on WhatsApp' } ) ).toHaveAttribute( 'href', /\/contacts\/$/ );
		await expect( box.getByRole( 'link', { name: 'Email us' } ) ).toHaveAttribute( 'href', /\/contacts\/#write$/ );
	} );

	test( 'phone: the topics in a row of chips, the buttons full width', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/questions/' );
		const a = await page.locator( '.am-faq-nav__link' ).nth( 0 ).boundingBox();
		const b = await page.locator( '.am-faq-nav__link' ).nth( 1 ).boundingBox();
		expect( Math.round( b.y ) ).toBe( Math.round( a.y ) );
		const button = await page.locator( '.am-faq-cta .am-btn' ).first().boundingBox();
		expect( Math.round( button.width ) ).toBe( 390 - 32 - 48 );
	} );
} );

test.describe( 'Contact (board Contact)', () => {
	test( 'the hosts, the contacts and the map, with placeholders for what is not filled in yet; Getting here', async ( { page } ) => {
		await page.goto( '/contacts/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Find us in Almyrida' );
		await expect( page.locator( '.am-ct-hosts strong' ) ).toHaveText( '[Name] and [Name], your hosts' );
		await expect( page.locator( '.am-ct-list dt' ) ).toHaveText( [ 'WhatsApp', 'Phone', 'Email', 'Address', 'Hosts on site' ] );
		await expect( page.locator( '.am-ct-list dd' ).first() ).toHaveText( '[+30 …]' );
		await expect( page.locator( '.am-ct-pin__label' ) ).toHaveText( [ 'Amour Relax Hotel', 'Villa Eudora · 4 min', 'Chania', 'Chania airport' ] );
		await expect( page.getByRole( 'img', { name: /Relief map of Apokoronas/ } ) ).toBeVisible();
		await expect( page.locator( '.am-ct-route h3' ) ).toHaveText( [ 'Chania airport', 'Heraklion airport', 'Souda port' ] );
		await expect( page.locator( '.am-ct-write__text p' ) ).toContainText( 'within [24 hours]' );
		await expect( page.locator( '.am-form__privacy a' ) ).toHaveAttribute( 'href', /\/privacy-policy\/$/ );
	} );

	test( 'the form: Send when empty marks the fields and puts the focus on the first one', async ( { page } ) => {
		await page.goto( '/contacts/' );
		await page.getByRole( 'button', { name: 'Send' } ).click();
		const name = page.getByLabel( 'Your name' );
		await expect( name ).toBeFocused();
		await expect( name ).toHaveAttribute( 'aria-invalid', 'true' );
		await expect( page.locator( '#ct-name-error' ) ).toBeVisible();
		await expect( page.locator( '#ct-email-error' ) ).toHaveText( 'Please add an email like name@example.com, so we can reply.' );
		await expect( page.getByLabel( 'Message' ) ).toHaveAccessibleDescription( 'Please write a few words.' );
		await name.fill( 'Anna' );
		await expect( name ).toHaveAttribute( 'aria-invalid', 'false' );
		await expect( page.locator( '#ct-name-error' ) ).toBeHidden();
		await page.getByLabel( 'Email' ).fill( 'anna@' );
		await page.getByRole( 'button', { name: 'Send' } ).click();
		await expect( page.getByLabel( 'Email' ) ).toBeFocused();
		expect( await axe( page ) ).toEqual( [] );
	} );

	test( 'a filled form goes without leaving the page and says thank you', async ( { page } ) => {
		await page.goto( '/contacts/' );
		await page.getByLabel( 'Your name' ).fill( 'Anna' );
		await page.getByLabel( 'Email' ).fill( 'anna@example.com' );
		await page.getByLabel( 'Dates' ).fill( '14 – 21 May' );
		await page.getByLabel( 'Message' ).fill( 'Is the court free in the morning?' );
		await page.getByRole( 'button', { name: 'Send' } ).click();
		await expect( page.getByRole( 'status' ) ).toHaveText( 'Thank you. We reply by email within [24 hours].' );
		await expect( page ).toHaveURL( /\/contacts\/$/ );
		await expect( page.getByLabel( 'Your name' ) ).toHaveValue( '' );
	} );

	test( 'without JavaScript the form posts and the page comes back with the thank-you line', async ( { browser } ) => {
		const context = await browser.newContext( { javaScriptEnabled: false, storageState: undefined } );
		const page = await context.newPage();
		await page.goto( '/contacts/' );
		await page.getByLabel( 'Your name' ).fill( 'Anna' );
		await page.getByLabel( 'Email' ).fill( 'anna@example.com' );
		await page.getByLabel( 'Message' ).fill( 'Hello' );
		await page.getByRole( 'button', { name: 'Send' } ).click();
		await expect( page ).toHaveURL( /\/contacts\/\?sent=1#write$/ );
		await expect( page.getByRole( 'status' ) ).toHaveText( 'Thank you. We reply by email within [24 hours].' );
		await context.close();
	} );

	test( 'the site checks the message too: a post without an email is refused', async ( { page } ) => {
		await page.goto( '/contacts/' );
		const result = await page.evaluate( async () => {
			const form = document.querySelector( '[data-contact-form]' );
			const data = new FormData( form );
			data.set( 'name', 'Anna' );
			data.set( 'email', 'not an email' );
			data.set( 'message', 'Hello' );
			const response = await fetch( form.getAttribute( 'action' ), { method: 'POST', body: data, headers: { Accept: 'application/json' } } );
			return { status: response.status, body: await response.json() };
		} );
		expect( result.status ).toBe( 422 );
		expect( result.body.errors ).toEqual( { email: true } );
	} );

	test( 'phone: one column, the map under the contacts, the form fields one under the other', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/contacts/' );
		const list = await page.locator( '.am-ct-list' ).boundingBox();
		const map = await page.locator( '.am-ct-map' ).boundingBox();
		expect( map.y ).toBeGreaterThan( list.y );
		const name = await page.getByLabel( 'Your name' ).boundingBox();
		const email = await page.getByLabel( 'Email' ).boundingBox();
		expect( email.y ).toBeGreaterThan( name.y );
	} );
} );
