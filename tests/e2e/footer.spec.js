import { test, expect } from '@playwright/test';

const footer = ( page ) => page.locator( 'footer.am-footer' );

test.describe( 'footer (HANDOFF, section 5; boards About and Villa-v2-b)', () => {
	test( 'hotel: Stay, Discover and Contact, networks, legal lines with placeholders on a test copy, links to legal pages', async ( { page } ) => {
		await page.goto( '/about-us/' );
		const f = footer( page );
		await expect( f.locator( '.am-footer__col .am-eyebrow' ) ).toHaveText( [ 'Stay', 'Discover', 'Contact' ] );
		await expect( f.getByRole( 'navigation', { name: 'Stay' } ).getByRole( 'link' ) ).toHaveText( [ 'Rooms', 'Villa Eudora', 'Court and pool' ] );
		await expect( f.getByRole( 'link', { name: 'Almyrida, Crete' } ) ).toHaveAttribute( 'href', /\/contacts\/$/ );
		await expect( f.locator( '.am-footer__social a' ) ).toHaveCount( 3 );
		await expect( f.locator( '.am-footer__social a' ).first() ).toHaveAttribute( 'target', '_blank' );
		await expect( f.locator( '.am-footer__legal-text p' ).first() ).toHaveText( '[Company name] · [Address] · MHTE [MHTE number]' );
		await expect( f.locator( '.am-footer__legal-text p' ).nth( 1 ) ).toContainText( 'GEMI' );
		await expect( f.getByRole( 'navigation', { name: 'Legal' } ).getByRole( 'link' ) ).toHaveText( [ 'Privacy', 'Cookies', 'Terms' ] );
		await expect( f.getByRole( 'link', { name: 'Amour Relax Hotel, home' } ) ).toBeVisible();
		await expect( f.locator( '.am-logo--amour-word' ) ).toBeVisible();
	} );

	test( 'villa: its own columns, the Booking.com score, the villa licence and EUDORA', async ( { page } ) => {
		await page.goto( '/villa-eudora/' );
		const f = footer( page );
		await expect( f.locator( '.am-footer__col .am-eyebrow' ) ).toHaveText( [ 'The villa', 'Around', 'Contact' ] );
		await expect( f.locator( '.am-footer__social' ) ).toContainText( 'Booking.com · 9.5' );
		await expect( f.locator( '.am-footer__legal-text p' ).first() ).toContainText( 'Villa licence' );
		await expect( f.locator( '.am-logo--eudora-word' ) ).toBeVisible();
		await expect( f.locator( '.am-footer__field--waves img' ).first() ).toBeAttached();
	} );

	test( 'the big word has risen into place once the footer is in view', async ( { page } ) => {
		await page.goto( '/about-us/' );
		await page.evaluate( () => window.scrollTo( 0, document.documentElement.scrollHeight ) );
		await expect.poll( async () => Number( await footer( page ).evaluate( ( el ) => getComputedStyle( el ).getPropertyValue( '--fw' ) ) ) ).toBeGreaterThan( 0.9 );
	} );

	test( 'the olives light up under the cursor', async ( { page } ) => {
		await page.goto( '/about-us/' );
		const box = await footer( page ).boundingBox();
		await page.mouse.move( box.x + 300, box.y + 200 );
		await expect.poll( () => footer( page ).evaluate( ( el ) => el.style.getPropertyValue( '--mx' ) ) ).toBe( '300px' );
	} );

	test( 'phone: footer links are at least 44 px tall', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/about-us/' );
		const links = footer( page ).locator( '.am-footer__col a, .am-footer__social a, .am-footer__legal a' );
		const count = await links.count();
		expect( count ).toBeGreaterThan( 8 );
		for ( let i = 0; i < count; i++ ) {
			const box = await links.nth( i ).boundingBox();
			expect( box.height, await links.nth( i ).textContent() ).toBeGreaterThanOrEqual( 44 );
		}
	} );

	test( 'reduced motion: the olives stand still and the word is in place', async ( { page } ) => {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.goto( '/about-us/' );
		const olive = footer( page ).locator( '.am-footer__field span' ).first();
		expect( await olive.evaluate( ( el ) => getComputedStyle( el ).animationName ) ).toBe( 'none' );
		expect( await footer( page ).evaluate( ( el ) => getComputedStyle( el ).getPropertyValue( '--fw' ) ) ).toBe( '1' );
	} );
} );
