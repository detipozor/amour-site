import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const ESTATE = '/court-and-pool/';
const at = async ( page, y ) => {
	await page.evaluate( ( yy ) => window.scrollTo( 0, yy ), y );
	await page.waitForTimeout( 250 );
};
/** Scroll so the top of an element is this many pixels above the top of the screen. */
const past = async ( page, selector, px ) => {
	for ( let i = 0; i < 2; i++ ) {
		await at( page, await page.evaluate( ( [ s, p ] ) => document.querySelector( s ).getBoundingClientRect().top + window.scrollY + p, [ selector, px ] ) );
	}
};
const cssVar = ( page, name ) => page.locator( '[data-estate]' ).evaluate( ( el, n ) => Number( getComputedStyle( el ).getPropertyValue( n ) ), name );
const axe = async ( page ) => {
	const results = await new AxeBuilder( { page } ).withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] ).analyze();
	return results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) ).map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` );
};

test.describe( 'Court and pool (board Estate)', () => {
	test( 'the first screen from the admin, with the court video and its Pause button (WCAG 2.2.2)', async ( { page } ) => {
		await page.goto( ESTATE );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Game, set, sunset' );
		await expect( page.locator( '.am-es-hero__facts' ) ).toHaveText( 'Free · floodlit · rackets to borrow · [07:00–23:00] · [surface]' );
		await expect( page.locator( '.am-es-hero' ).getByRole( 'link', { name: 'Check dates' } ) ).toHaveAttribute( 'href', /\/book\/$/ );
		await expect( page.locator( '.am-es-hero__wa' ) ).toHaveText( '[Book a court slot on WhatsApp]' );
		await expect( page.locator( '.am-es-hero__video' ) ).toHaveAttribute( 'src', /court-video.*\.mp4$/ );
		const pause = page.locator( '[data-es-pause]' );
		await expect( pause ).toHaveAccessibleName( 'Pause video' );
		await pause.click();
		await expect( pause ).toHaveAccessibleName( 'Play video' );
		expect( await page.locator( '.am-es-hero__video' ).evaluate( ( v ) => v.paused ) ).toBe( true );
	} );

	test( 'the court turns from day to night as the page scrolls', async ( { page } ) => {
		await page.goto( ESTATE );
		await past( page, '[data-es-court]', 0 );
		expect( await cssVar( page, '--night' ) ).toBe( 0 );
		await expect( page.locator( '.am-es-court__bar' ) ).toBeVisible();
		await past( page, '[data-es-court]', 800 );
		expect( await cssVar( page, '--night' ) ).toBe( 1 );
		expect( await page.locator( '.am-es-court__img--night' ).evaluate( ( el ) => getComputedStyle( el ).opacity ) ).toBe( '1' );
		await expect( page.locator( '.am-es-court__title' ) ).toHaveText( /Morning sets,\s*evening sets/ );
	} );

	test( 'the pool: one point and one photo per step', async ( { page } ) => {
		await page.goto( ESTATE );
		const pin = page.locator( '[data-es-pool-pin]' );
		await past( page, '[data-es-pool-pin]', 10 );
		await expect( pin ).toHaveAttribute( 'data-step', '0' );
		await expect( page.locator( '.am-es-point' ).first() ).toHaveClass( /is-on/ );
		await past( page, '[data-es-pool-pin]', 600 );
		await expect( pin ).toHaveAttribute( 'data-step', '1' );
		await expect( page.locator( '[data-es-count]' ) ).toHaveText( '02' );
		await past( page, '[data-es-pool-pin]', 1100 );
		await expect( pin ).toHaveAttribute( 'data-step', '2' );
		await expect( page.locator( '.am-es-pool__img--2' ) ).toHaveClass( /is-shown/ );
		expect( await page.locator( '.am-es-point__body' ).first().evaluate( ( el ) => getComputedStyle( el ).opacity ) ).toBe( '0.85' );
	} );

	test( 'from above: pointing at a name previews the place, a click keeps it, a click on the photo closes it', async ( { page } ) => {
		await page.goto( ESTATE );
		const box = page.locator( '[data-es-plan-box]' );
		await box.scrollIntoViewIfNeeded();
		await expect( box ).toHaveAttribute( 'data-on', '-1' );
		await page.locator( '.am-es-spot[data-place="1"]' ).hover();
		await expect( box ).toHaveAttribute( 'data-on', '1' );
		await expect( page.locator( '.am-es-card[data-card="1"]' ) ).toBeVisible();
		await expect( page.locator( '.am-es-card[data-card="1"]' ) ).toContainText( 'Tennis court' );
		await page.mouse.move( 2, 2 );
		await expect( box ).toHaveAttribute( 'data-on', '-1' );

		await page.locator( '.am-es-spot[data-place="0"]' ).click();
		await expect( page.locator( '.am-es-spot[data-place="0"]' ) ).toHaveAttribute( 'aria-pressed', 'true' );
		await page.mouse.move( 2, 2 );
		await expect( box ).toHaveAttribute( 'data-on', '0' ); // it stays open
		await expect( page.locator( '[data-es-say]' ) ).toContainText( 'The pool and its limestone sun terrace' );
		// The card of the pool (top middle of the photo) sits at the bottom, away from it.
		await expect( page.locator( '.am-es-card[data-card="0"]' ) ).toHaveAttribute( 'data-side', 'bottom-left' );

		const b = await box.boundingBox();
		await page.mouse.click( b.x + 20, b.y + b.height / 2 );
		await expect( box ).toHaveAttribute( 'data-on', '-1' );
	} );

	test( 'the keyboard: focusing a place previews it, Enter keeps it open', async ( { page } ) => {
		await page.goto( ESTATE );
		const spot = page.locator( '.am-es-spot[data-place="3"]' );
		await spot.focus();
		await expect( page.locator( '[data-es-plan-box]' ) ).toHaveAttribute( 'data-on', '3' );
		await page.keyboard.press( 'Enter' );
		await expect( spot ).toHaveAttribute( 'aria-pressed', 'true' );
		await expect( spot ).toHaveAccessibleName( 'Barbecue' ); // the number shows only on a phone
	} );

	test( 'phone: numbers on the photo, the names as chips, the first place open from the start', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( ESTATE );
		await expect( page.locator( '.am-es-spot[data-place="0"] .am-es-spot__n' ) ).toBeVisible();
		await expect( page.locator( '.am-es-spot[data-place="0"]' ) ).toHaveAccessibleName( '1 Pool' );
		const chips = page.locator( '.am-es-chip' );
		await expect( chips ).toHaveCount( 6 );
		await expect( chips.first() ).toHaveClass( /is-on/ );
		await expect( page.locator( '[data-es-say]' ) ).toBeVisible();
		await expect( page.locator( '[data-es-say-name]' ) ).toHaveText( 'Pool' );
		await chips.nth( 3 ).click();
		await expect( chips.nth( 3 ) ).toHaveAttribute( 'aria-pressed', 'true' );
		await expect( page.locator( '[data-es-say-name]' ) ).toHaveText( 'Barbecue' );
		// The pool: photo on top, the points under it.
		await past( page, '[data-es-pool-pin]', 10 );
		const photo = await page.locator( '.am-es-pool__photo' ).boundingBox();
		const list = await page.locator( '.am-es-pool__points' ).boundingBox();
		expect( photo.y ).toBeLessThan( list.y );
	} );

	test( 'axe: the first screen, the court at night, the pool and a place open', async ( { page } ) => {
		await page.goto( ESTATE );
		await page.waitForTimeout( 800 );
		expect( await axe( page ) ).toEqual( [] );
		await past( page, '[data-es-court]', 800 );
		expect( await axe( page ) ).toEqual( [] );
		await page.locator( '.am-es-spot[data-place="2"]' ).click();
		await page.waitForTimeout( 900 );
		expect( await axe( page ) ).toEqual( [] );
	} );

	test( 'reduced motion: nothing pinned, the poster instead of the video, the places still open', async ( { page } ) => {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.goto( ESTATE );
		await expect( page.locator( '[data-estate]' ) ).not.toHaveClass( /is-pinned/ );
		await expect( page.locator( '.am-es-hero__video' ) ).not.toHaveAttribute( 'src', /.+/ );
		await expect( page.locator( '[data-es-pause]' ) ).toBeHidden();
		await expect( page.locator( '.am-es-hero__poster' ) ).toBeVisible();
		await page.locator( '.am-es-point' ).nth( 2 ).hover();
		await expect( page.locator( '[data-es-pool-pin]' ) ).toHaveAttribute( 'data-step', '2' );
		await page.locator( '.am-es-spot[data-place="4"]' ).click();
		await expect( page.locator( '[data-es-plan-box]' ) ).toHaveAttribute( 'data-on', '4' );
	} );
} );
