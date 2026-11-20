import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const ROOM = '/rooms/deluxe-queen-room/';
const at = async ( page, y ) => {
	await page.evaluate( ( yy ) => window.scrollTo( 0, yy ), y );
	await page.waitForTimeout( 200 );
};
/** Scroll so the top of an element stands at a share of the screen (0 is the top edge). */
const putAt = async ( page, selector, share ) => {
	for ( let i = 0; i < 2; i++ ) {
		await at( page, await page.evaluate( ( [ s, k ] ) => document.querySelector( s ).getBoundingClientRect().top + window.scrollY - k * window.innerHeight, [ selector, share ] ) );
	}
};
/** A corner of 24 px, however the browser writes it (24px or calc(0% + 24px)). */
const OPEN = /^(24px|calc\(0% \+ 24px\))$/;
/** Limestone, however the browser writes it (rgb or color(srgb …) after color-mix). */
const LIMESTONE = /^(rgb\(242, 234, 219\)|color\(srgb 0\.949\d* 0\.917\d* 0\.858\d*\))$/;
const cssVar = ( locator, name ) => locator.evaluate( ( el, n ) => Number( getComputedStyle( el ).getPropertyValue( n ) ), name );
const axe = async ( page ) => {
	const results = await new AxeBuilder( { page } ).withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] ).analyze();
	return results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) ).map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` );
};

test.describe( 'room page (HANDOFF, section 6 "Страница номера"; boards Room-*)', () => {
	test( 'the wine screen: name, label, line and the photos with captions, from the admin', async ( { page } ) => {
		await page.goto( ROOM );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Deluxe' );
		await expect( page.locator( '.am-room__kicker' ) ).toHaveText( 'Queen room · pool view' );
		await expect( page.locator( '.am-room__meta' ) ).toHaveText( '32 m² · queen bed · kitchen · sitting corner · terrace' );
		const figs = page.locator( '[data-room-fig]' );
		await expect( figs ).toHaveCount( 5 );
		await expect( figs.first().locator( 'figcaption' ) ).toHaveText( /Bedroom and sitting corner\s*1 \/ 5/ );
		await expect( figs.first().locator( 'img' ) ).toHaveAttribute( 'alt', /\S/ );
		await expect( figs.nth( 1 ) ).toHaveAttribute( 'data-shape', 'upright' );
		await expect( figs.nth( 1 ) ).toHaveAttribute( 'data-side', 'right' );
		await expect( page.locator( '[data-room-stage]' ) ).toHaveAttribute( 'data-tone', 'dark' );
	} );

	test( 'each photo opens from an arch into a soft rectangle as it rises', async ( { page } ) => {
		await page.goto( ROOM );
		const second = page.locator( '[data-room-fig]' ).nth( 1 );
		await putAt( page, '[data-room-fig]:nth-child(2)', 1 );
		expect( await cssVar( second, '--k' ) ).toBeLessThan( 0.1 );
		const arch = await second.locator( '.am-room__pic' ).evaluate( ( el ) => getComputedStyle( el ).borderTopLeftRadius );
		expect( arch ).not.toMatch( OPEN );
		await putAt( page, '[data-room-fig]:nth-child(2)', 0.3 );
		expect( await cssVar( second, '--k' ) ).toBe( 1 );
		expect( await second.locator( '.am-room__pic' ).evaluate( ( el ) => getComputedStyle( el ).borderTopLeftRadius ) ).toMatch( OPEN );
	} );

	test( 'the wine runs into limestone as the details come up, and the header follows', async ( { page } ) => {
		await page.goto( ROOM );
		const details = page.locator( '[data-room-details]' );
		await expect( details ).toHaveAttribute( 'data-tone', 'dark' );
		await putAt( page, '[data-room-details]', 0.8 );
		const mid = await cssVar( page.locator( '[data-room]' ), '--t' );
		expect( mid ).toBeGreaterThan( 0 );
		expect( mid ).toBeLessThan( 1 );
		await putAt( page, '[data-room-details]', 0.2 );
		expect( await cssVar( page.locator( '[data-room]' ), '--t' ) ).toBe( 1 );
		await expect( details ).toHaveAttribute( 'data-tone', 'light' );
		expect( await details.evaluate( ( el ) => getComputedStyle( el ).backgroundColor ) ).toMatch( LIMESTONE );
	} );

	test( 'Room details: the description, six key facts, the rest and the stay rules under All details', async ( { page } ) => {
		await page.goto( ROOM );
		await expect( page.getByRole( 'heading', { level: 2, name: 'Room details' } ) ).toBeVisible();
		await expect( page.locator( '.am-room__desc' ) ).toHaveText( /^More room to spread out: 32 m²/ );
		await expect( page.locator( '.am-room__row--facts dt' ) ).toHaveText( [ 'Room size', 'Guests', 'Bed', 'View', 'Kitchen', 'Outside' ] );
		const more = page.locator( '[data-room-more]' );
		await expect( more ).toHaveText( /All details · 12/ );
		await expect( more ).toHaveAttribute( 'aria-expanded', 'false' );
		const all = page.locator( '#am-room-all' );
		await expect( all ).toBeHidden();
		await more.click();
		await expect( more ).toHaveAttribute( 'aria-expanded', 'true' );
		await expect( more ).toHaveText( /Fewer details/ );
		await expect( all ).toBeVisible();
		await expect( all.locator( 'dt' ) ).toHaveCount( 12 );
		await expect( all.locator( '.am-room__fact', { hasText: 'Check-in' } ).locator( 'dd' ) ).toHaveText( 'From 14:00' );
		await expect( all.locator( '.am-room__fact', { hasText: 'Check-out' } ).locator( 'dd' ) ).toHaveText( 'By 11:00' );
		await expect( all.locator( 'dt' ).last() ).toHaveText( 'Pool and sun terrace' );
		await more.click();
		await expect( all ).toBeHidden();
	} );

	test( 'the price capsule: price from the admin, Check dates to the booking page, steps aside at the footer', async ( { page } ) => {
		await page.goto( ROOM );
		const dock = page.locator( '[data-dock]' );
		await expect( dock.locator( '.am-dock__price' ) ).toHaveText( 'From €[price] a night' );
		await expect( dock.getByRole( 'link', { name: 'Check dates' } ) ).toHaveAttribute( 'href', /\/book\/$/ );
		await expect( dock ).not.toHaveClass( /is-away/ );
		const pill = await dock.locator( '.am-dock__pill' ).boundingBox();
		expect( pill.y + pill.height ).toBeGreaterThan( 900 - 120 ); // at the bottom of the screen
		await at( page, await page.evaluate( () => document.documentElement.scrollHeight ) );
		await expect( dock ).toHaveClass( /is-away/ );
	} );

	test( 'phone: the name across the screen, photos 72vw and 86vw in one column, facts in one column', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( ROOM );
		const size = await page.locator( 'h1' ).evaluate( ( el ) => parseFloat( getComputedStyle( el ).fontSize ) );
		expect( Math.round( size ) ).toBe( Math.round( 390 * 0.135 ) );
		const figs = page.locator( '[data-room-fig]' );
		expect( Math.round( ( await figs.first().boundingBox() ).width ) ).toBe( Math.round( 390 * 0.86 ) );
		expect( Math.round( ( await figs.nth( 1 ).boundingBox() ).width ) ).toBe( Math.round( 390 * 0.72 ) );
		const facts = page.locator( '.am-room__row--facts .am-room__fact' );
		const a = await facts.nth( 0 ).boundingBox();
		const b = await facts.nth( 1 ).boundingBox();
		expect( b.y ).toBeGreaterThan( a.y ); // one under the other
		expect( Math.round( b.x ) ).toBe( Math.round( a.x ) );
	} );

	test( 'axe: the first screen, the photos, and the details with everything open', async ( { page } ) => {
		await page.goto( ROOM );
		await page.waitForTimeout( 1500 );
		expect( await axe( page ) ).toEqual( [] );
		await putAt( page, '[data-room-details]', 0 );
		await page.locator( '[data-room-more]' ).click();
		await page.waitForTimeout( 1200 );
		expect( await axe( page ) ).toEqual( [] );
	} );

	test( 'reduced motion: photos stand open, the colours stand still, the details simply stand', async ( { page } ) => {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.goto( ROOM );
		await expect( page.locator( '[data-room]' ) ).not.toHaveClass( /is-live/ );
		const second = page.locator( '[data-room-fig]' ).nth( 1 ).locator( '.am-room__pic' );
		expect( await second.evaluate( ( el ) => getComputedStyle( el ).borderTopLeftRadius ) ).toMatch( OPEN );
		await putAt( page, '[data-room-details]', 0.5 );
		expect( await page.locator( '[data-room-details]' ).evaluate( ( el ) => getComputedStyle( el ).backgroundColor ) ).toMatch( LIMESTONE );
		expect( await page.locator( '.am-room__desc' ).evaluate( ( el ) => getComputedStyle( el ).opacity ) ).toBe( '1' );
		await page.locator( '[data-room-more]' ).click();
		await expect( page.locator( '#am-room-all' ) ).toBeVisible();
	} );

	test( 'Almyrida, Crete: the camera comes down from the whole island to the hotel, then the road and nearby places', async ( { page } ) => {
		await page.goto( ROOM );
		const loc = page.locator( '[data-loc]' );
		const canvas = page.locator( '[data-loc-canvas]' );
		await putAt( page, '[data-loc]', 0 );
		await expect( loc ).toHaveClass( /is-pinned/ );
		expect( await cssVar( canvas, '--z' ) ).toBeCloseTo( 0.6, 1 );
		expect( await cssVar( canvas, '--road-o' ) ).toBe( 0 );
		await expect( loc ).toHaveClass( /is-hint/ );
		await expect( page.locator( '[data-dock]' ) ).toHaveClass( /is-away/ ); // the map is under the capsule

		await at( page, await page.evaluate( () => document.querySelector( '[data-loc]' ).getBoundingClientRect().top + window.scrollY + 760 ) );
		expect( await cssVar( canvas, '--z' ) ).toBeCloseTo( 6.2, 1 );
		expect( await cssVar( canvas, '--road-o' ) ).toBe( 1 );
		expect( await cssVar( canvas, '--spot-o' ) ).toBe( 1 );
		const hotel = await page.locator( '.am-loc__spot.is-main' ).boundingBox();
		expect( hotel.x ).toBeGreaterThan( 0 );
		expect( hotel.y ).toBeGreaterThan( 0 );
		expect( hotel.y + hotel.height ).toBeLessThan( 900 );

		const card = page.locator( '[data-loc-card]' );
		await expect( card.getByRole( 'heading', { level: 2 } ) ).toHaveText( 'Almyrida, Crete' );
		await expect( card.locator( 'dt' ) ).toHaveText( [ 'Almyrida beach', 'Chania old town', 'Rethymno', 'Chania airport' ] );
		await expect( card.locator( 'dd' ).first() ).toHaveText( '1.2 km on foot' );
		await expect( card.locator( '.am-loc__maps' ) ).toHaveText( '[Open in Google Maps]' ); // no link yet: a placeholder on the test copy
		await expect( page.locator( '.am-loc__spot .am-loc__long', { hasText: 'Chania · 40 min' } ) ).toBeVisible();
	} );

	test( 'Other ways to stay: the other two rooms and the villa, with a link to all of them', async ( { page } ) => {
		await page.goto( ROOM );
		const others = page.locator( '[data-others]' );
		await expect( others.getByRole( 'heading', { level: 2 } ) ).toHaveText( 'Other ways to stay' );
		await expect( others.locator( '.am-other__name' ) ).toHaveText( [ 'Standard', 'Superior', 'Villa Eudora' ] );
		await expect( others.locator( '.am-other__link' ).first() ).toHaveAttribute( 'href', /\/rooms\/standard-queen-room\/$/ );
		await expect( others.locator( '.am-other__link' ).last() ).toHaveAccessibleName( /^Villa Eudora/ );
		await expect( others.getByRole( 'link', { name: /All rooms and the villa/ } ) ).toHaveAttribute( 'href', /\/rooms\/$/ );
		await others.scrollIntoViewIfNeeded();
		await expect( others ).toHaveClass( /is-in/ );
	} );

	test( 'phone: the map card below the island, short labels, the hotel in view all the way', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( ROOM );
		await putAt( page, '[data-loc]', 0 );
		let hotel = await page.locator( '.am-loc__spot.is-main' ).boundingBox();
		expect( hotel.x ).toBeGreaterThanOrEqual( 0 );
		const card = await page.locator( '[data-loc-card]' ).boundingBox();
		expect( Math.round( card.x ) ).toBe( 16 );
		expect( Math.round( card.y + card.height ) ).toBe( 844 - 24 );
		await at( page, await page.evaluate( () => document.querySelector( '[data-loc]' ).getBoundingClientRect().top + window.scrollY + 760 ) );
		hotel = await page.locator( '.am-loc__spot.is-main' ).boundingBox();
		expect( hotel.x ).toBeGreaterThanOrEqual( 0 );
		expect( hotel.y + hotel.height ).toBeLessThan( card.y );
		await expect( page.locator( '.am-loc__short', { hasText: /^Chania$/ } ) ).toBeVisible();
	} );

	test( 'reduced motion: the map stands close in, nothing pinned', async ( { page } ) => {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.goto( ROOM );
		const loc = page.locator( '[data-loc]' );
		await loc.scrollIntoViewIfNeeded();
		await expect( loc ).not.toHaveClass( /is-pinned/ );
		expect( await cssVar( page.locator( '[data-loc-canvas]' ), '--z' ) ).toBeCloseTo( 6.2, 1 );
		expect( await cssVar( page.locator( '[data-loc-canvas]' ), '--road-o' ) ).toBe( 1 );
		await expect( page.locator( '[data-loc-card]' ) ).toBeVisible();
	} );

	test( 'axe: the map close in and Other ways to stay', async ( { page } ) => {
		await page.goto( ROOM );
		await at( page, await page.evaluate( () => document.querySelector( '[data-loc]' ).getBoundingClientRect().top + window.scrollY + 760 ) );
		await page.waitForTimeout( 800 );
		expect( await axe( page ) ).toEqual( [] );
		await page.locator( '[data-others]' ).scrollIntoViewIfNeeded();
		await page.waitForTimeout( 1000 );
		expect( await axe( page ) ).toEqual( [] );
	} );
} );
