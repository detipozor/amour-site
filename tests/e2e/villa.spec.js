import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const VILLA = '/villa-eudora/';
const axe = async ( page ) => {
	const results = await new AxeBuilder( { page } ).withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] ).analyze();
	return results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) ).map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` );
};

test.describe( 'villa page (HANDOFF, section 6 "Вилла"; boards Villa-v2, Villa-v2-b)', () => {
	test( 'the room page in the villa colours: Eudora, seven photos, the house with its own stay rules', async ( { page } ) => {
		await page.goto( VILLA );
		await expect( page.locator( 'html' ) ).toHaveAttribute( 'data-theme', 'eudora' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Eudora' );
		await expect( page.locator( '.am-room__kicker' ) ).toHaveText( 'Villa · Almyrida' );
		await expect( page.locator( '.am-room__meta' ) ).toHaveText( '5 bedrooms · up to 11 guests · private pool · 256 m²' );
		await expect( page.locator( '[data-room-fig]' ) ).toHaveCount( 7 );
		expect( await page.locator( '[data-room-stage]' ).evaluate( ( el ) => getComputedStyle( el ).backgroundColor ) ).toMatch( /^(rgb\(31, 58, 71\)|color\(srgb 0\.121\d* 0\.227\d* 0\.278\d*\))$/ );

		await expect( page.getByRole( 'heading', { level: 2, name: 'The house', exact: true } ) ).toBeVisible();
		await expect( page.locator( '.am-room__row--facts dt' ) ).toHaveText( [ 'House size', 'Guests', 'Bedrooms', 'View', 'Kitchen', 'Outside' ] );
		const more = page.locator( '[data-room-more]' );
		await expect( more ).toHaveText( /All details · 15/ );
		await more.click();
		const all = page.locator( '#am-room-all' );
		await expect( all.locator( '.am-room__fact', { hasText: 'Minimum stay' } ) ).toBeVisible();
		await expect( all.locator( '.am-room__fact', { hasText: 'Check-in' } ).locator( 'dd' ) ).toHaveText( '[From 16:00]' ); // the villa's, not the hotel's 14:00
		await expect( page.locator( '.am-dock__price' ) ).toHaveText( 'From €[price] a night for the whole villa' );
	} );

	test( 'Five bedrooms, and the house floor by floor: the drawing lights up the floor in focus', async ( { page } ) => {
		await page.goto( VILLA );
		await expect( page.getByRole( 'heading', { level: 2, name: 'Five bedrooms' } ) ).toBeVisible();
		await expect( page.locator( '.am-villa-bed h3' ) ).toHaveText( [ 'Bedroom 1', 'Bedroom 2', 'Bedroom 3', 'Bedroom 4', 'Bedroom 5' ] );

		const floors = page.locator( '[data-villa-floors]' );
		await expect( floors.getByRole( 'img', { name: /Drawing of the villa/ } ) ).toBeAttached();
		const buttons = floors.getByRole( 'button' );
		await expect( buttons ).toHaveCount( 3 );
		await expect( buttons.first() ).toHaveAttribute( 'aria-pressed', 'true' );
		await expect( floors ).toHaveAttribute( 'data-on', '0' );
		await buttons.nth( 1 ).hover();
		await expect( floors ).toHaveAttribute( 'data-on', '1' );
		await expect( buttons.nth( 1 ) ).toHaveAttribute( 'aria-pressed', 'true' );
		await expect( buttons.first() ).toHaveAttribute( 'aria-pressed', 'false' );
		await buttons.nth( 2 ).focus();
		await expect( floors ).toHaveAttribute( 'data-on', '2' );
		await expect.poll( () => floors.locator( '.am-house__garden' ).evaluate( ( el ) => getComputedStyle( el ).opacity ) ).toBe( '1' ); // after its fade
	} );

	test( 'Weddings and gatherings at #gatherings: the heading comes up in view, Plan a gathering, WhatsApp', async ( { page } ) => {
		await page.goto( VILLA );
		const events = page.locator( '#gatherings' );
		await expect( events ).toHaveClass( /is-ready/ );
		await expect( events.getByRole( 'heading', { level: 2 } ) ).toHaveText( /Long tables,\s*long evenings/ );
		await expect( events.getByRole( 'link', { name: 'Plan a gathering' } ) ).toHaveAttribute( 'href', /\/contacts\/$/ );
		await expect( events.locator( '.am-villa-events__wa' ) ).toHaveText( '[Write on WhatsApp]' ); // no number yet: a placeholder on the test copy
		await events.scrollIntoViewIfNeeded();
		await expect( events ).toHaveClass( /is-in/ );
	} );

	test( 'the map: the villa is the main place, the hotel 4 min away; the card lists what is near the villa', async ( { page } ) => {
		await page.goto( VILLA );
		await expect( page.locator( '.am-loc__spot.is-main .am-loc__long' ) ).toHaveText( 'Villa Eudora' );
		await expect( page.locator( '.am-loc__spot .am-loc__long', { hasText: 'Amour Relax Hotel · 4 min' } ) ).toHaveCount( 1 );
		await expect( page.locator( '.am-loc__spot .am-loc__long', { hasText: 'Almyrida beach' } ) ).toHaveCount( 0 );
		const card = page.locator( '[data-loc-card]' );
		await expect( card.locator( 'dt' ).first() ).toHaveText( 'Amour Relax Hotel' );
		await expect( card.locator( 'dd' ).first() ).toHaveText( '4 min by car' );
		await expect( page.getByRole( 'img', { name: /the villa and the hotel above Almyrida bay/ } ) ).toBeAttached();
	} );

	test( 'Or stay at the hotel: the three rooms, and a link to all of them', async ( { page } ) => {
		await page.goto( VILLA );
		const others = page.locator( '[data-others]' );
		await expect( others.getByRole( 'heading', { level: 2 } ) ).toHaveText( 'Or stay at the hotel' );
		await expect( others.locator( '.am-other__name' ) ).toHaveText( [ 'Standard', 'Deluxe', 'Superior' ] );
		await expect( others.getByRole( 'link', { name: /All rooms at the hotel/ } ) ).toHaveAttribute( 'href', /\/rooms\/$/ );
	} );

	test( 'phone: the bedrooms scroll sideways with a finger, the gatherings in one column', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( VILLA );
		const list = page.locator( '.am-villa-beds__list' );
		expect( await list.evaluate( ( el ) => el.scrollWidth > el.clientWidth ) ).toBe( true );
		await expect( list ).toHaveAttribute( 'tabindex', '0' ); // the keyboard reaches it
		await expect( list ).toHaveAccessibleName( 'Bedrooms' );
		expect( await page.evaluate( () => document.documentElement.scrollWidth - document.documentElement.clientWidth ) ).toBeLessThanOrEqual( 0 );
		const text = await page.locator( '.am-villa-events__text' ).boundingBox();
		const fig = await page.locator( '.am-villa-events__fig' ).boundingBox();
		expect( fig.y ).toBeGreaterThan( text.y + text.height - 1 );
	} );

	test( 'axe: the bedrooms, the floors, the gatherings', async ( { page } ) => {
		await page.goto( VILLA );
		await page.locator( '[data-villa-floors]' ).scrollIntoViewIfNeeded();
		await page.waitForTimeout( 900 );
		expect( await axe( page ) ).toEqual( [] );
		await page.locator( '#gatherings' ).scrollIntoViewIfNeeded();
		await page.waitForTimeout( 2000 );
		expect( await axe( page ) ).toEqual( [] );
	} );

	test( 'reduced motion: the bedrooms and the gatherings simply stand, the floors still light up', async ( { page } ) => {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.goto( VILLA );
		await expect( page.locator( '#gatherings' ) ).not.toHaveClass( /is-ready/ );
		await expect( page.locator( '[data-villa-beds]' ) ).not.toHaveClass( /is-ready/ );
		expect( await page.locator( '.am-villa-events__arch' ).evaluate( ( el ) => getComputedStyle( el ).clipPath ) ).toBe( 'none' );
		await page.locator( '[data-villa-floors]' ).getByRole( 'button' ).nth( 1 ).click();
		await expect( page.locator( '[data-villa-floors]' ) ).toHaveAttribute( 'data-on', '1' );
	} );
} );
