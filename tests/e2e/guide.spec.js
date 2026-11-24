import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const GUIDE = '/crete-guide/';
const map = ( page ) => page.locator( '[data-gd-map]' );
const zoom = ( page ) => map( page ).evaluate( ( el ) => Number( getComputedStyle( el ).getPropertyValue( '--z' ) ) );
/** A place is tapped on its dot: its hidden label may lie under a neighbour. */
const dot = ( page, i ) => page.locator( `.am-gd-pin[data-i="${ i }"] .am-gd-pin__dot` );
const axe = async ( page ) => {
	const results = await new AxeBuilder( { page } ).withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] ).analyze();
	return results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) ).map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` );
};

test.describe( 'Crete guide (board Guide)', () => {
	test( 'the opening and the map from Places: the hotel with the villa, six places, Heraklion', async ( { page } ) => {
		await page.goto( GUIDE );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Your home base for Crete' );
		await expect( page.locator( '.am-gd-pin' ) ).toHaveCount( 7 );
		await expect( page.locator( '.am-gd-pin--hotel .am-gd-pin__long' ) ).toHaveText( 'Amour Relax Hotel · Villa Eudora' );
		await expect( page.locator( '.am-gd-far__label' ) ).toHaveText( 'Heraklion · airport' );
		await expect( page.locator( '.am-gd-pin[data-i="3"]' ) ).toHaveAttribute( 'data-side', 'row-reverse' ); // the guide's own side for Samaria
	} );

	test( 'pointing at a place draws the road there', async ( { page } ) => {
		await page.goto( GUIDE );
		await map( page ).scrollIntoViewIfNeeded();
		await page.locator( '.am-gd-pin[data-i="2"]' ).hover();
		await expect( page.locator( '.am-gd-road-on[data-road="2"]' ).last() ).toHaveClass( /is-on/ );
		await expect( page.locator( '.am-gd-road-on.is-on' ) ).toHaveCount( 2 ); // the road and its casing
	} );

	test( 'the whole-screen map: wheel to zoom, a place flies it there with its card, Esc closes and gives the focus back', async ( { page } ) => {
		await page.goto( GUIDE );
		const open = page.locator( '[data-gd-open]' );
		await open.click();
		await expect( map( page ) ).toHaveClass( /is-full/ );
		await expect( map( page ) ).toHaveAttribute( 'role', 'dialog' );
		await expect( page.locator( '[data-gd-close]' ) ).toBeFocused();
		await expect( page.locator( '.am-gd-hint__desk' ) ).toBeVisible();

		await page.mouse.move( 600, 450 );
		await page.mouse.wheel( 0, -300 );
		await expect.poll( () => zoom( page ) ).toBeGreaterThan( 1 );

		await dot( page, 3 ).click();
		await expect( map( page ) ).toHaveClass( /has-card/ );
		await expect( page.locator( '[data-gd-card-name]' ) ).toHaveText( 'Samaria Gorge' );
		await expect( page.locator( '[data-gd-card-meta]' ) ).toHaveText( /65 km/ );
		await expect( page.locator( '[data-gd-card]' ) ).toHaveAttribute( 'aria-hidden', 'false' );
		await expect.poll( () => zoom( page ) ).toBe( 2.4 );

		await page.keyboard.press( 'Escape' );
		await expect( map( page ) ).not.toHaveClass( /is-full/ );
		await expect( open ).toBeFocused();
		expect( await page.evaluate( () => document.documentElement.style.overflow ) ).toBe( '' );
	} );

	test( 'the keyboard: Enter on a place opens the map there; + zooms, the arrows move it, Tab stays inside', async ( { page } ) => {
		await page.goto( GUIDE );
		await page.locator( '.am-gd-pin[data-i="2"]' ).focus();
		await page.keyboard.press( 'Enter' );
		await expect( map( page ) ).toHaveClass( /is-full/ );
		await expect( page.locator( '[data-gd-card-name]' ) ).toHaveText( 'Chania old harbour' );
		const before = await zoom( page );
		await page.keyboard.press( '+' );
		await expect.poll( () => zoom( page ) ).toBeGreaterThan( before );
		for ( let i = 0; i < 10; i++ ) {
			await page.keyboard.press( 'Tab' );
		}
		expect( await page.evaluate( () => document.querySelector( '[data-gd-map]' ).contains( document.activeElement ) ) ).toBe( true );
	} );

	test( 'Almyrida bay, Days out and Getting here from the admin; the day cards turn to the photo, and back to the road on focus', async ( { page } ) => {
		await page.goto( GUIDE );
		await expect( page.getByRole( 'heading', { level: 2, name: 'Almyrida bay' } ) ).toBeVisible();
		await expect( page.locator( '.am-gd-bay__facts dt' ) ).toHaveText( [ 'Almyrida beach', 'Kalyves' ] );
		const days = page.locator( '.am-day' );
		await expect( days ).toHaveCount( 6 );
		await expect( days.locator( '.am-day__name' ) ).toHaveText( [ 'Seitan Limania', 'Chania old harbour', 'Samaria Gorge', 'Lake Kournas', 'Rethymno old town', 'Arkadi Monastery' ] );
		await expect( days.first().locator( '.am-day__time' ) ).toHaveText( '1 h' );
		await expect( days.first().locator( '.am-day__km' ) ).toHaveText( '41 km by car · go early, little shade' );
		await expect( days.nth( 2 ).locator( '.am-day__time' ) ).toHaveText( '1 h 30' );
		await expect( days.nth( 1 ).locator( '.am-day__flip' ) ).toHaveAccessibleName( /Chania old harbour: 40 min, 23 km by car/ );
		await expect( page.locator( '.am-gd-getting__list h3' ) ).toHaveCount( 4 );

		await page.locator( '[data-gd-days]' ).scrollIntoViewIfNeeded();
		await expect( page.locator( '.am-gd-days' ) ).toHaveClass( /is-in/ );
		const turned = () => days.first().locator( '.am-day__inner' ).evaluate( ( el ) => getComputedStyle( el ).transform );
		await expect.poll( turned, { timeout: 6000 } ).toMatch( /^matrix3d\(-1/ );
		await days.first().locator( '.am-day__flip' ).focus();
		await expect.poll( turned, { timeout: 4000 } ).toBe( 'none' );
	} );

	test( 'phone: short names, the card along the bottom, the days in one row that scrolls with a finger', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( GUIDE );
		await expect( page.locator( '.am-gd-pin--hotel .am-gd-pin__short' ) ).toHaveText( 'Amour · Villa Eudora' );
		await page.locator( '[data-gd-open]' ).click();
		await expect( page.locator( '.am-gd-hint__phone' ) ).toBeVisible();
		await expect.poll( () => page.locator( '.am-gd-hint' ).evaluate( ( el ) => getComputedStyle( el ).opacity ) ).toBe( '1' ); // after its fade
		await expect.poll( () => zoom( page ) ).toBe( 2.4 ); // a phone opens close in, so the places are big enough to tap
		await dot( page, 3 ).click();
		await expect( map( page ) ).toHaveClass( /has-card/ );
		await expect.poll( async () => {
			const card = await page.locator( '[data-gd-card]' ).boundingBox();
			return Math.round( card.y + card.height );
		} ).toBe( 844 - 12 ); // once it has come in
		await page.keyboard.press( 'Escape' );
		const list = page.locator( '[data-gd-days]' );
		expect( await list.evaluate( ( el ) => el.scrollWidth > el.clientWidth ) ).toBe( true );
		expect( await page.evaluate( () => document.documentElement.scrollWidth - document.documentElement.clientWidth ) ).toBeLessThanOrEqual( 0 );
	} );

	test( 'axe: the page, and the open map with a card', async ( { page } ) => {
		await page.goto( GUIDE );
		await page.waitForTimeout( 600 );
		expect( await axe( page ) ).toEqual( [] );
		await page.locator( '[data-gd-open]' ).click();
		await dot( page, 6 ).click();
		await page.waitForTimeout( 1800 );
		expect( await axe( page ) ).toEqual( [] );
	} );

	test( 'reduced motion: the day cards show the photo and the drive time, the map still opens and flies', async ( { page } ) => {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.goto( GUIDE );
		await expect( page.locator( '.am-gd-days' ) ).not.toHaveClass( /is-ready/ );
		expect( await page.locator( '.am-day__inner' ).first().evaluate( ( el ) => getComputedStyle( el ).transform ) ).toMatch( /^matrix3d\(-1/ );
		await page.locator( '[data-gd-open]' ).click();
		await dot( page, 1 ).click();
		await expect( page.locator( '[data-gd-card-name]' ) ).toHaveText( 'Seitan Limania' );
	} );
} );
