import { test, expect } from '@playwright/test';

const hero = ( page ) => page.locator( '[data-hero]' );
const cssVar = ( locator, name ) => locator.evaluate( ( el, n ) => Number( getComputedStyle( el ).getPropertyValue( n ) || el.style.getPropertyValue( n ) ), name );
const at = async ( page, y ) => {
	await page.evaluate( ( yy ) => window.scrollTo( 0, yy ), y );
	await page.waitForTimeout( 150 );
};

test.describe( 'home: first screen (HANDOFF, section 6.1)', () => {
	test( 'the heading, the poster and the video come from the admin', async ( { page } ) => {
		await page.goto( '/' );
		await expect( page.locator( 'h1' ) ).toHaveText( 'A nine-room hotel with its own court, among olive groves above Almyrida bay, Crete.' );
		await expect( page.locator( '.am-hero__poster' ) ).toHaveAttribute( 'alt', /Drone view/ );
		await expect( page.locator( '.am-hero__poster' ) ).toHaveAttribute( 'fetchpriority', 'high' );
		await expect( page.locator( '.am-hero__video' ) ).toHaveAttribute( 'src', /home-hero-video.*\.mp4$/ );
		await expect( page.locator( '.am-hero__note' ) ).toHaveText( 'Open from March to October.' );
	} );

	test( 'the arch opens over the first 520 px of scroll and the screen stays pinned meanwhile', async ( { page } ) => {
		await page.goto( '/' );
		expect( await cssVar( hero( page ), '--p' ) ).toBe( 0 );
		await at( page, 260 );
		const half = await cssVar( hero( page ), '--p' );
		expect( half ).toBeGreaterThan( 0.4 );
		expect( half ).toBeLessThan( 0.6 );
		await at( page, 520 );
		expect( await cssVar( hero( page ), '--p' ) ).toBe( 1 );
		// Pinned: the stage is still at the top, the arch fills the screen.
		const stage = await page.locator( '.am-hero__stage' ).boundingBox();
		expect( Math.round( stage.y ) ).toBe( 0 );
		const arch = await page.locator( '.am-hero__arch' ).boundingBox();
		expect( Math.round( arch.width ) ).toBe( 1440 );
	} );

	test( 'the video plays and can be paused (WCAG 2.2.2)', async ( { page } ) => {
		await page.goto( '/' );
		const pause = page.locator( '[data-hero-pause]' );
		await expect( hero( page ) ).toHaveClass( /is-playing/ );
		await expect( pause ).toHaveAccessibleName( 'Pause video' );
		await pause.click();
		await expect( pause ).toHaveAccessibleName( 'Play video' );
		expect( await page.locator( '.am-hero__video' ).evaluate( ( v ) => v.paused ) ).toBe( true );
		await pause.click();
		await expect( pause ).toHaveAccessibleName( 'Pause video' );
		expect( await page.locator( '.am-hero__video' ).evaluate( ( v ) => v.paused ) ).toBe( false );
	} );

	test( 'Check dates goes to the date search below', async ( { page } ) => {
		await page.goto( '/' );
		await page.locator( '.am-hero__side--right .am-btn' ).click();
		await expect( page ).toHaveURL( /#book$/ );
		await expect( page.locator( '#book' ) ).toBeInViewport();
	} );

	test( 'phone: the line on top, the arch below, no note on the right', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/' );
		await expect( page.locator( '.am-hero__side--right' ) ).toBeHidden();
		const line = await page.locator( 'h1' ).boundingBox();
		const arch = await page.locator( '.am-hero__arch' ).boundingBox();
		expect( line.y + line.height ).toBeLessThan( arch.y );
		expect( Math.round( arch.width ) ).toBe( 390 - 32 );
	} );
} );

test.describe( 'home: date search (HANDOFF, section 5)', () => {
	test( 'each field opens the booking page with the calendar asked for', async ( { page } ) => {
		await page.goto( '/' );
		const fields = page.locator( '.am-search a' );
		await expect( fields ).toHaveCount( 4 );
		await expect( page.locator( '.am-search__label' ) ).toHaveText( [ 'Arrive', 'Leave', 'Guests' ] );
		await fields.first().click();
		await page.waitForURL( /\/book\/$/ );
		expect( await page.evaluate( () => window.sessionStorage.getItem( 'amour-cal' ) ) ).toBe( 'arr' );
	} );

	test( 'phone: two columns, guests and the button full width', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/' );
		const [ arrive, leave, guests ] = await Promise.all( [ 0, 1, 2 ].map( ( i ) => page.locator( '.am-search__field' ).nth( i ).boundingBox() ) );
		expect( Math.round( arrive.y ) ).toBe( Math.round( leave.y ) );
		expect( guests.width ).toBeGreaterThan( arrive.width * 1.8 );
	} );
} );

test.describe( 'home: phrase and estate (HANDOFF, sections 6.3, 6.4)', () => {
	test( 'the words light up with the scroll and the block grows to the full width', async ( { page } ) => {
		await page.goto( '/' );
		const block = page.locator( '[data-phrase]' );
		const words = block.locator( '.am-phrase__text span' );
		await expect( words ).toHaveCount( 15 );
		const lastOpacity = () => words.last().evaluate( ( el ) => Number( getComputedStyle( el ).opacity ) );
		expect( await lastOpacity() ).toBeLessThan( 0.3 );
		const top = await block.evaluate( ( el ) => el.getBoundingClientRect().top + window.scrollY );
		await at( page, top );
		await expect.poll( lastOpacity ).toBeGreaterThan( 0.95 );
		expect( await cssVar( block, '--ph' ) ).toBe( 1 );
	} );

	test( 'the estate photo grows from half the width to the screen edges', async ( { page } ) => {
		await page.goto( '/' );
		const block = page.locator( '[data-estate]' );
		const top = await block.evaluate( ( el ) => el.getBoundingClientRect().top + window.scrollY );
		await at( page, top - 900 );
		const small = await page.locator( '.am-estate__photo' ).boundingBox();
		expect( small.width ).toBeLessThan( 700 );
		await at( page, top );
		await expect.poll( async () => Math.round( ( await page.locator( '.am-estate__photo' ).boundingBox() ).width ) ).toBe( 1440 );
	} );
} );

test.describe( 'home with reduced motion: final states, no video, no pinning', () => {
	test.use( { reducedMotion: 'reduce' } );

	test( 'the poster stays, the phrase is lit, the estate is full width', async ( { page } ) => {
		await page.goto( '/' );
		await expect( page.locator( '.am-hero__video' ) ).not.toHaveAttribute( 'src', /.+/ );
		await expect( page.locator( '[data-hero-pause]' ) ).toBeHidden();
		const stage = page.locator( '.am-hero__stage' );
		expect( await stage.evaluate( ( el ) => getComputedStyle( el ).position ) ).toBe( 'relative' );
		expect( await cssVar( page.locator( '[data-phrase]' ), '--q' ) ).toBe( 1 );
		expect( await cssVar( page.locator( '[data-estate]' ), '--r' ) ).toBe( 1 );
	} );
} );

test( 'smooth scroll: the wheel glides instead of jumping', async ( { page } ) => {
	await page.goto( '/' );
	await page.mouse.move( 720, 450 );
	await page.mouse.wheel( 0, 600 );
	await page.waitForTimeout( 50 );
	const early = await page.evaluate( () => window.scrollY );
	expect( early ).toBeLessThan( 600 );
	await page.waitForFunction( () => Math.abs( window.scrollY - 600 ) < 2 );
} );
