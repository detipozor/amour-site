import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const hero = ( page ) => page.locator( '[data-hero]' );
const cssVar = ( locator, name ) => locator.evaluate( ( el, n ) => Number( getComputedStyle( el ).getPropertyValue( n ) || el.style.getPropertyValue( n ) ), name );
const at = async ( page, y ) => {
	await page.evaluate( ( yy ) => window.scrollTo( 0, yy ), y );
	await page.waitForTimeout( 150 );
};
/** Scroll to a point of a scene; positions move as blocks above grow, so measure again until it holds. */
const goTo = async ( page, fn, arg ) => {
	for ( let i = 0; i < 3; i++ ) {
		await at( page, await page.evaluate( fn, arg ) );
	}
	await page.waitForTimeout( 300 );
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
		await expect( page.getByRole( 'dialog', { name: 'Choose your dates' } ) ).toBeVisible();
		await expect( page.locator( '[data-bk-open="arr"]' ) ).toHaveAttribute( 'aria-expanded', 'true' );
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

test.describe( 'home: A day at Amour (HANDOFF, section 6.5)', () => {
	const dayTop = ( page ) => page.locator( '[data-day]' ).evaluate( ( el ) => el.getBoundingClientRect().top + window.scrollY );
	const capOpacity = ( page, i ) => page.locator( '.am-day__cap' ).nth( i ).evaluate( ( el ) => Number( getComputedStyle( el ).opacity ) );

	test( 'desktop: pinned, every 45% of the screen moves the strip one frame, the middle one is the colour arch', async ( { page } ) => {
		await page.goto( '/' );
		await expect( page.locator( '.am-day__cap' ) ).toHaveCount( 5 );
		await expect( page.locator( '.am-day__card:not(.am-day__card--repeat)' ) ).toHaveCount( 5 );
		await expect( page.locator( '.am-day__card--repeat[aria-hidden="true"]' ) ).toHaveCount( 6 );
		await at( page, await dayTop( page ) );
		await expect.poll( () => capOpacity( page, 0 ) ).toBe( 1 );
		await at( page, ( await dayTop( page ) ) + 900 * 0.45 * 2 );
		await expect.poll( () => capOpacity( page, 2 ) ).toBe( 1 );
		expect( await capOpacity( page, 0 ) ).toBe( 0 );
		// Pinned: the stage stays at the top of the screen.
		expect( Math.round( ( await page.locator( '.am-day__stage' ).boundingBox() ).y ) ).toBe( 0 );
		// The middle card is the arch in colour.
		const middle = page.locator( '.am-day__card[data-k="2"]' );
		expect( await middle.locator( 'img' ).evaluate( ( el ) => getComputedStyle( el ).filter ) ).toBe( 'none' );
		expect( await page.locator( '.am-day__card[data-k="1"] img' ).evaluate( ( el ) => getComputedStyle( el ).filter ) ).toContain( 'grayscale' );
	} );

	test( 'the menu item "A day at Amour" lands on this block', async ( { page } ) => {
		await page.goto( '/#a-day-at-amour' );
		await expect( page.locator( '#a-day-at-amour' ) ).toBeAttached();
	} );

	test( 'phone: a strip that scrolls with a finger, reachable with the keyboard, the caption follows', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/' );
		const day = page.locator( '[data-day]' );
		await expect( day ).toHaveClass( /is-carousel/ );
		const strip = page.locator( '[data-day-strip]' );
		await expect( strip ).toHaveAttribute( 'tabindex', '0' );
		await expect( strip ).toHaveAttribute( 'role', 'region' );
		await expect( page.locator( '.am-day__cap.is-active .am-eyebrow' ) ).toHaveText( 'Morning' );
		await strip.evaluate( ( el ) => el.scrollTo( { left: el.querySelectorAll( '.am-day__card:not(.am-day__card--repeat)' )[ 2 ].offsetLeft - el.clientWidth * 0.11 } ) );
		await expect( page.locator( '.am-day__cap.is-active .am-eyebrow' ) ).toHaveText( 'Golden hour' );
	} );
} );

test.describe( 'home: rooms grid (HANDOFF, section 6.6)', () => {
	test( 'three rooms and the villa from their pages, in the board order, with facts and price', async ( { page } ) => {
		await page.goto( '/' );
		await expect( page.locator( '.am-rooms__title' ) ).toHaveText( /Stay like\s*you live here/ );
		await expect( page.locator( '.am-chip' ) ).toHaveText( [ '3Rooms', '1Villa' ] );
		const cols = page.locator( '.am-rooms__col' );
		await expect( cols.nth( 0 ).locator( '.am-unit__name' ) ).toHaveText( [ 'Standard', 'Superior' ] );
		await expect( cols.nth( 1 ).locator( '.am-unit__name' ) ).toHaveText( [ 'Deluxe', 'Villa Eudora' ] );
		const standard = page.locator( '.am-unit', { hasText: 'Standard' } );
		await expect( standard ).toHaveAttribute( 'href', /\/rooms\/standard-queen-room\/$/ );
		await expect( standard ).toHaveAttribute( 'aria-label', 'Standard, 20 m², 2, from €160 a night' );
		await expect( page.locator( '.am-unit--villa' ) ).toHaveAttribute( 'data-brand', 'eudora' );
	} );

	test( 'desktop: the second photo shows under the pointer', async ( { page } ) => {
		await page.goto( '/' );
		const card = page.locator( '.am-unit', { hasText: 'Deluxe' } );
		await card.scrollIntoViewIfNeeded();
		await card.hover();
		await expect.poll( () => card.locator( '.am-unit__alt' ).evaluate( ( el ) => Number( getComputedStyle( el ).opacity ) ) ).toBe( 1 );
	} );

	test( 'phone: one column of tall photos that drift with the scroll, no second photo', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/' );
		const pic = page.locator( '.am-unit__pic' ).first();
		await pic.scrollIntoViewIfNeeded();
		const box = await pic.boundingBox();
		expect( Math.round( box.height / box.width * 100 ) ).toBe( 125 );
		await expect( page.locator( '.am-unit__alt' ).first() ).toBeHidden();
		await expect.poll( () => page.locator( '.am-unit__img' ).first().evaluate( ( el ) => el.style.scale ) ).toBe( '1.2' );
	} );
} );

test.describe( 'home: rooms → villa (HANDOFF, section 6.7)', () => {
	const roomsAt = ( rk ) => {
		const rooms = document.querySelector( '[data-rooms]' );
		const pin = rooms.querySelector( '[data-rooms-pin]' );
		return rooms.getBoundingClientRect().top + window.scrollY + ( pin.offsetHeight - window.innerHeight ) + 250 + rk * 700;
	};
	const villaAt = ( vk ) => document.querySelector( '[data-home-villa]' ).getBoundingClientRect().top + window.scrollY + vk * 1100;
	const opacity = ( locator ) => locator.evaluate( ( el ) => Number( getComputedStyle( el ).opacity ) );

	test( 'desktop: the villa card flies to the middle, the words come in, then the arch opens with its text', async ( { page } ) => {
		await page.goto( '/' );
		await goTo( page, roomsAt, 1 );
		const fly = await page.locator( '[data-rooms-fly]' ).boundingBox();
		expect( Math.abs( fly.x + fly.width / 2 - 720 ) ).toBeLessThan( 2 );
		expect( Math.abs( fly.y + fly.height / 2 - 450 ) ).toBeLessThan( 2 );
		expect( await opacity( page.locator( '.am-rooms [data-vword="l"]' ) ) ).toBe( 1 );
		expect( await opacity( page.locator( '.am-rooms__in' ) ) ).toBe( 0 );

		await goTo( page, villaAt, 0.95 );
		const arch = await page.locator( '[data-villa-arch]' ).boundingBox();
		expect( Math.round( arch.width ) ).toBe( 1440 );
		await expect( page.locator( '[data-villa-pin]' ) ).toHaveAttribute( 'data-tone', 'dark' );
		expect( await opacity( page.locator( '[data-villa-text]' ) ) ).toBeGreaterThan( 0.95 );
		await expect( page.locator( '.am-header' ) ).toHaveAttribute( 'data-brand', 'eudora' );
		await expect( page.getByRole( 'link', { name: 'Discover the villa' } ) ).toHaveAttribute( 'href', /\/villa-eudora\/$/ );
		await expect( page.getByRole( 'link', { name: 'Weddings and gatherings' } ) ).toHaveAttribute( 'href', /\/villa-eudora\/#gatherings$/ );
	} );

	test( 'phone: the words stand above and below the card', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/' );
		await goTo( page, roomsAt, 1 );
		const fly = await page.locator( '[data-rooms-fly]' ).boundingBox();
		const left = await page.locator( '.am-rooms [data-vword="l"]' ).boundingBox();
		const right = await page.locator( '.am-rooms [data-vword="r"]' ).boundingBox();
		expect( left.y + left.height ).toBeLessThanOrEqual( fly.y + 1 );
		expect( right.y ).toBeGreaterThanOrEqual( fly.y + fly.height - 1 );
		await expect( page.locator( '.am-rooms [data-vcap="l"]' ) ).toBeHidden();
	} );

	test( 'reduced motion: nothing pinned, the villa stands open with its text', async ( { page } ) => {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.goto( '/' );
		await expect( page.locator( '[data-rooms]' ) ).not.toHaveClass( /is-pinned/ );
		await expect( page.locator( '[data-home-villa]' ) ).not.toHaveClass( /is-pinned/ );
		const villa = page.locator( '[data-home-villa]' );
		await villa.scrollIntoViewIfNeeded();
		expect( Math.round( ( await page.locator( '[data-villa-arch]' ).boundingBox() ).width ) ).toBe( 1440 );
		expect( await opacity( page.locator( '[data-villa-text]' ) ) ).toBe( 1 );
		await expect( page.locator( '[data-villa-pin]' ) ).toHaveAttribute( 'data-tone', 'dark' );
	} );
} );

test.describe( 'home: map (HANDOFF, section 6.8)', () => {
	/** A point of the pinned map: 0 is where it pins, 1 is 2300 px later. */
	const mapAt = ( gp ) => document.querySelector( '[data-map]' ).getBoundingClientRect().top + window.scrollY + gp * 2300;
	const map = ( page ) => page.locator( '[data-map]' );
	const cardTitle = ( page ) => page.locator( '[data-map-card] [data-card="title"]' );
	const cardLink = ( page ) => page.locator( '[data-map-card] [data-card="link"]' );
	const tab = ( page, name ) => page.getByRole( 'tab', { name } );
	const shownTrips = ( page ) => page.locator( '.am-pin--trip.is-shown' );
	/** Point at or tap a place where it is on the screen (a locator would first scroll the pinned map away). */
	const pointAt = async ( page, selector, click = false ) => {
		const b = await page.locator( selector ).boundingBox();
		await page.mouse[ click ? 'click' : 'move' ]( b.x + b.width / 2, b.y + b.height / 2 );
	};
	const overlaps = ( a, b ) => a.x < b.x + b.width && b.x < a.x + a.width && a.y < b.y + b.height && b.y < a.y + a.height;

	test( 'desktop: the opening, then the island, the hotel, the villa and day trips as the page scrolls', async ( { page } ) => {
		await page.goto( '/' );
		await goTo( page, mapAt, 0.02 );
		await expect( map( page ) ).toHaveClass( /is-intro/ );
		await expect( page.locator( '.am-map__intro-title' ) ).toHaveText( 'West Crete' );
		await expect( page.locator( '.am-map__fig' ) ).toHaveCount( 6 );

		await goTo( page, mapAt, 0.2 );
		await expect( map( page ) ).not.toHaveClass( /is-intro/ );
		await expect( cardTitle( page ) ).toHaveText( 'Above Almyrida bay' );
		await expect( page.locator( '.am-pin--hotel .am-pin__label' ) ).toHaveText( 'Amour Relax Hotel · Villa Eudora' );
		await expect( page.locator( '.am-pin--villa' ) ).not.toHaveClass( /is-shown/ );
		await expect( page.getByRole( 'tab', { selected: true } ) ).toHaveCount( 0 );

		await goTo( page, mapAt, 0.4 );
		await expect( cardTitle( page ) ).toHaveText( 'Amour Relax Hotel' );
		await expect( tab( page, 'The hotel' ) ).toHaveAttribute( 'aria-selected', 'true' );
		await expect( cardLink( page ) ).toHaveText( 'See the rooms' );
		await expect( cardLink( page ) ).toHaveAttribute( 'href', /\/#rooms$/ );
		await expect( page.locator( '[data-map-card] img.is-on' ) ).toHaveCount( 1 );
		await expect( page.locator( '.am-pin--villa' ) ).toHaveClass( /is-shown/ );
		await expect( shownTrips( page ) ).toHaveCount( 0 );

		await goTo( page, mapAt, 0.6 );
		await expect( cardTitle( page ) ).toHaveText( 'Villa Eudora' );
		await expect( tab( page, 'Villa Eudora' ) ).toHaveAttribute( 'aria-selected', 'true' );
		await expect( cardLink( page ) ).toHaveAttribute( 'href', /\/villa-eudora\/$/ );
		await expect( page.locator( '.am-pin--villa' ) ).toHaveClass( /is-on/ );

		await goTo( page, mapAt, 0.85 );
		await expect( cardTitle( page ) ).toHaveText( 'A base for west Crete' );
		await expect( tab( page, 'Day trips' ) ).toHaveAttribute( 'aria-selected', 'true' );
		await expect( shownTrips( page ) ).toHaveCount( 6 );
		await expect( page.locator( '.am-pin--villa' ) ).not.toHaveClass( /is-shown/ );
		await expect( page.locator( '.am-pin[data-trip="1"] .am-pin__label' ) ).toHaveText( 'Chania · 40 min' );
	} );

	test( 'desktop: pointing at a place draws the road there and tells about it in the card', async ( { page } ) => {
		await page.goto( '/' );
		await goTo( page, mapAt, 0.85 );
		await expect( page.locator( '.am-road-on.is-on' ) ).toHaveCount( 0 );
		await pointAt( page, '.am-pin[data-trip="1"]' );
		await expect( cardTitle( page ) ).toHaveText( 'Chania old harbour' );
		await expect( page.locator( '[data-map-card] [data-card="meta"]' ) ).toHaveText( /40 min by car/ );
		await expect( page.locator( '.am-road-on[data-road="1"]' ) ).toHaveClass( [ /is-on/, /is-on/ ] );
		await expect( page.locator( '.am-road-on.is-on' ) ).toHaveCount( 2 ); // the road and its casing
		await expect( page.locator( '.am-pin[data-trip="1"]' ) ).toHaveClass( /is-on/ );
		await expect( cardLink( page ) ).toHaveText( 'Open the Crete guide' );
		await expect( page.locator( '[data-map-card]' ) ).toHaveClass( /has-photo/ );

		// The keyboard reaches the places too.
		await page.locator( '.am-pin[data-trip="0"]' ).focus();
		await expect( cardTitle( page ) ).toHaveText( 'Seitan Limania' );
		await expect( page.locator( '.am-road-on[data-road="0"]' ).last() ).toHaveClass( /is-on/ );
		await expect( page.locator( '.am-road-on[data-road="1"]' ).last() ).not.toHaveClass( /is-on/ );
	} );

	test( 'the tabs take you to their view; the arrow keys move between them', async ( { page } ) => {
		await page.goto( '/' );
		await goTo( page, mapAt, 0.2 );
		await tab( page, 'Villa Eudora' ).click();
		await expect( cardTitle( page ) ).toHaveText( 'Villa Eudora', { timeout: 5000 } );
		await expect( tab( page, 'Villa Eudora' ) ).toHaveAttribute( 'aria-selected', 'true' );
		await page.keyboard.press( 'ArrowRight' );
		await expect( tab( page, 'Day trips' ) ).toBeFocused();
		await expect( cardTitle( page ) ).toHaveText( 'A base for west Crete', { timeout: 5000 } );
		await page.keyboard.press( 'ArrowRight' );
		await expect( tab( page, 'The hotel' ) ).toBeFocused();
		await expect( cardTitle( page ) ).toHaveText( 'Amour Relax Hotel', { timeout: 5000 } );
	} );

	test( 'phone: closer views, short labels, the card without a photo', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/' );
		await goTo( page, mapAt, 0.2 );
		// Both names in two lines, so the label stays on the screen.
		const both = page.locator( '.am-pin--hotel .am-pin__label' );
		await expect( both ).toHaveText( 'Amour Relax Hotel\nVilla Eudora' );
		const box = await both.boundingBox();
		expect( box.x + box.width ).toBeLessThanOrEqual( 390 );

		await goTo( page, mapAt, 0.85 );
		await expect( cardTitle( page ) ).toHaveText( 'A base for west Crete' );
		await expect( page.locator( '[data-map-card] [data-card="text"]' ) ).toHaveText( 'Tap a place on the map to see the road there.' );
		await expect( page.locator( '.am-pin[data-trip="1"] .am-pin__label' ) ).toHaveText( 'Chania' );
		await pointAt( page, '.am-pin[data-trip="1"]', true );
		await expect( cardTitle( page ) ).toHaveText( 'Chania old harbour' );
		expect( ( await page.locator( '[data-card-pics]' ).boundingBox() ).height ).toBe( 0 );
		for ( const pin of await page.locator( '.am-pin.is-shown' ).all() ) {
			const b = await pin.boundingBox();
			expect( b.x ).toBeGreaterThanOrEqual( 0 );
			expect( b.x + b.width ).toBeLessThanOrEqual( 390 );
		}
	} );

	for ( const [ width, height ] of [ [ 768, 1024 ], [ 1024, 768 ] ] ) {
		test( `tablet ${ width }×${ height }: the places stay clear of the card`, async ( { page } ) => {
			await page.setViewportSize( { width, height } );
			await page.goto( '/' );
			for ( const gp of [ 0.4, 0.6, 0.85 ] ) {
				await goTo( page, mapAt, gp );
				await page.waitForTimeout( 1400 ); // the camera moves for 1.3 s
				const card = await page.locator( '[data-map-card]' ).boundingBox();
				const pins = page.locator( '.am-pin.is-shown' );
				expect( await pins.count() ).toBeGreaterThan( 0 );
				for ( const pin of await pins.all() ) {
					const b = await pin.boundingBox();
					expect( overlaps( b, card ), `${ await pin.getAttribute( 'aria-label' ) } at ${ gp }` ).toBe( false );
					expect( b.x ).toBeGreaterThanOrEqual( 0 );
					expect( b.x + b.width ).toBeLessThanOrEqual( width );
				}
			}
		} );
	}

	test( 'axe: the day trips view with a road drawn', async ( { page } ) => {
		await page.goto( '/' );
		await goTo( page, mapAt, 0.85 );
		await pointAt( page, '.am-pin[data-trip="2"]' );
		await page.waitForTimeout( 700 );
		const results = await new AxeBuilder( { page } )
			.include( '[data-map]' )
			.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] )
			.analyze();
		const serious = results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) );
		expect( serious.map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` ) ).toEqual( [] );
	} );

	test( 'reduced motion: nothing pinned, no opening, the tabs switch the views', async ( { page } ) => {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.goto( '/' );
		await expect( map( page ) ).not.toHaveClass( /is-pinned/ );
		await map( page ).scrollIntoViewIfNeeded();
		await expect( map( page ) ).not.toHaveClass( /is-intro/ );
		await expect( cardTitle( page ) ).toHaveText( 'Above Almyrida bay' );
		await tab( page, 'The hotel' ).click();
		await expect( cardTitle( page ) ).toHaveText( 'Amour Relax Hotel' );
		await expect( tab( page, 'The hotel' ) ).toHaveAttribute( 'aria-selected', 'true' );
		await tab( page, 'Day trips' ).click();
		await expect( shownTrips( page ) ).toHaveCount( 6 );
		await page.locator( '.am-pin[data-trip="3"]' ).click();
		await expect( cardTitle( page ) ).toHaveText( 'Lake Kournas' );
	} );
} );

test.describe( 'home: guest reviews (HANDOFF, sections 5 and 6.9)', () => {
	const reviewsAt = ( k ) => {
		const el = document.querySelector( '[data-reviews]' );
		return el.getBoundingClientRect().top + window.scrollY + k * el.offsetHeight - window.innerHeight / 3;
	};
	const shift = ( page ) => page.locator( '[data-reviews-track]' ).evaluate( ( el ) => Number( /translate3d\((-?[\d.]+)px/.exec( el.style.transform )?.[ 1 ] ?? 0 ) );
	const pause = ( page ) => page.locator( '[data-reviews-pause]' );

	test( 'the heading, the Booking.com score and the reviews from the admin; the loop copies are hidden', async ( { page } ) => {
		await page.goto( '/' );
		await expect( page.getByRole( 'heading', { name: 'Guest reviews: 9.1 out of 10 on Booking.com' } ) ).toBeAttached();
		await expect( page.locator( '.am-reviews__score' ) ).toHaveText( '9.1 out of 10 on Booking.com · [number] reviews' );
		const cards = page.locator( '.am-review:not(.is-copy)' );
		await expect( cards ).toHaveCount( 5 );
		await expect( cards.first().locator( '.am-review__text' ) ).toHaveText( /^“\[A real review from Booking\.com, word for word/ );
		await expect( cards.first().locator( '.am-review__name' ) ).toHaveText( '[Name] · [Country]' );
		await expect( cards.first().locator( '.am-review__when' ) ).toHaveText( '[Month Year] · Deluxe Queen Room' );
		await expect( cards.last().locator( '.am-review__when' ) ).toHaveText( /Villa Eudora$/ );
		const copies = page.locator( '.am-review.is-copy' );
		await expect( copies ).toHaveCount( 5 );
		await expect( copies.first() ).toHaveAttribute( 'aria-hidden', 'true' );
		await expect( copies.first() ).toHaveAttribute( 'inert', '' );
	} );

	test( 'the cards drift right to left and stand still after Pause (WCAG 2.2.2)', async ( { page } ) => {
		await page.goto( '/' );
		await goTo( page, reviewsAt, 0.5 );
		await page.mouse.move( 5, 5 );
		await page.waitForTimeout( 1200 ); // speeding up
		const a = await shift( page );
		await page.waitForTimeout( 1000 );
		const b = await shift( page );
		expect( a - b ).toBeGreaterThan( 25 ); // about 34 px a second, to the left
		expect( a - b ).toBeLessThan( 45 );
		await expect( page.locator( '[data-reviews]' ) ).toHaveClass( /is-moving/ );
		await expect( page.locator( '.am-review.is-on' ) ).toHaveCount( 1 );

		await pause( page ).click();
		await expect( pause( page ) ).toHaveText( 'Play reviews' );
		await page.mouse.move( 5, 5 );
		await page.waitForTimeout( 300 );
		const c = await shift( page );
		await page.waitForTimeout( 800 );
		expect( await shift( page ) ).toBe( c );
		await expect( page.locator( '[data-reviews]' ) ).not.toHaveClass( /is-moving/ ); // stopped, every card reads in full

		await pause( page ).click();
		await expect( pause( page ) ).toHaveText( 'Pause reviews' );
		await page.mouse.move( 5, 5 );
		await page.waitForTimeout( 1500 );
		expect( await shift( page ) ).toBeLessThan( c );
	} );

	test( 'the pointer over the cards stops them', async ( { page } ) => {
		await page.goto( '/' );
		await goTo( page, reviewsAt, 0.5 );
		const box = await page.locator( '[data-reviews-scroller]' ).boundingBox();
		await page.mouse.move( box.x + box.width / 2, box.y + box.height / 2 );
		await page.waitForTimeout( 1500 ); // it slows down softly
		const a = await shift( page );
		await page.waitForTimeout( 800 );
		expect( await shift( page ) ).toBe( a );
		await page.mouse.move( 5, 5 );
		await page.waitForTimeout( 1500 );
		expect( await shift( page ) ).toBeLessThan( a );
	} );

	test( 'the keyboard: focus on the strip stops it, the arrows move it one card', async ( { page } ) => {
		await page.goto( '/' );
		await goTo( page, reviewsAt, 0.5 );
		await page.mouse.move( 5, 5 );
		await pause( page ).focus();
		await page.keyboard.press( 'Tab' );
		const scroller = page.locator( '[data-reviews-scroller]' );
		await expect( scroller ).toBeFocused();
		await expect( scroller ).toHaveAccessibleName( 'Review cards: arrow keys move them' );
		await page.waitForTimeout( 1500 );
		const a = await shift( page );
		await page.waitForTimeout( 500 );
		expect( await shift( page ) ).toBe( a );
		const step = await page.locator( '.am-review' ).evaluateAll( ( cards ) => cards[ 1 ].offsetLeft - cards[ 0 ].offsetLeft );
		await page.keyboard.press( 'ArrowRight' );
		expect( Math.round( a - ( await shift( page ) ) ) ).toBe( step );
	} );

	test( 'phone: a finger stops the strip and drags it', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/' );
		await goTo( page, reviewsAt, 0.5 );
		const scroller = page.locator( '[data-reviews-scroller]' );
		const touch = ( type, clientX ) => scroller.evaluate( ( el, [ t, cx ] ) => el.dispatchEvent( new PointerEvent( t, { pointerType: 'touch', clientX: cx, bubbles: true } ) ), [ type, clientX ] );
		await touch( 'pointerdown', 300 );
		const a = await shift( page );
		await touch( 'pointermove', 200 );
		await page.waitForTimeout( 100 );
		expect( Math.round( a - ( await shift( page ) ) ) ).toBe( 100 );
		await page.waitForTimeout( 600 );
		const held = await shift( page );
		await page.waitForTimeout( 400 );
		expect( await shift( page ) ).toBe( held ); // the finger is still down
		await touch( 'pointerup', 200 );
	} );

	test( 'axe: the reviews after Pause and Book direct', async ( { page } ) => {
		await page.goto( '/' );
		await goTo( page, reviewsAt, 0.5 );
		await pause( page ).click();
		await page.waitForTimeout( 900 );
		await page.locator( '[data-direct]' ).scrollIntoViewIfNeeded();
		await expect( page.locator( '[data-direct]' ) ).toHaveClass( /is-in/ );
		await page.waitForTimeout( 1200 );
		const results = await new AxeBuilder( { page } )
			.include( '[data-reviews]' )
			.include( '[data-direct]' )
			.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] )
			.analyze();
		const serious = results.violations.filter( ( v ) => [ 'serious', 'critical' ].includes( v.impact ) );
		expect( serious.map( ( v ) => `${ v.id }: ${ v.help } (${ v.nodes.length })` ) ).toEqual( [] );
	} );

	test( 'reduced motion: the cards stand, no Pause button, they scroll by hand', async ( { page } ) => {
		await page.emulateMedia( { reducedMotion: 'reduce' } );
		await page.goto( '/' );
		const reviews = page.locator( '[data-reviews]' );
		await reviews.scrollIntoViewIfNeeded();
		await expect( reviews ).not.toHaveClass( /is-live/ );
		await expect( pause( page ) ).toBeHidden();
		await expect( page.locator( '.am-review.is-copy' ).first() ).toBeHidden();
		const scrolled = await page.locator( '[data-reviews-scroller]' ).evaluate( ( el ) => {
			el.scrollLeft = 400;
			return el.scrollLeft;
		} );
		expect( scrolled ).toBeGreaterThan( 300 );
		// The keyboard reaches the strip and scrolls it with the arrows.
		await page.locator( '[data-reviews-scroller]' ).evaluate( ( el ) => {
			el.scrollLeft = 0;
			el.focus();
		} );
		await page.keyboard.press( 'ArrowRight' );
		await expect.poll( () => page.locator( '[data-reviews-scroller]' ).evaluate( ( el ) => el.scrollLeft ) ).toBeGreaterThan( 0 );
	} );
} );

test.describe( 'home: Book direct (HANDOFF, section 6.10)', () => {
	test( 'the text comes up once in view; Check dates goes to the date search', async ( { page } ) => {
		await page.goto( '/' );
		const block = page.locator( '[data-direct]' );
		await expect( block ).toHaveClass( /is-ready/ );
		await expect( block ).not.toHaveClass( /is-in/ );
		await block.scrollIntoViewIfNeeded();
		await expect( block ).toHaveClass( /is-in/ );
		await expect( block.getByRole( 'heading', { level: 2 } ) ).toHaveText( 'Same price, straight to us' );
		await expect( block.locator( '.am-eyebrow' ) ).toHaveText( 'Book direct' );
		await block.getByRole( 'link', { name: 'Check dates' } ).click();
		await expect( page ).toHaveURL( /#book$/ );
		await expect( page.locator( '#book' ) ).toBeInViewport();
	} );

	test( 'phone: the text keeps to the gutters', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await page.goto( '/' );
		const text = page.locator( '[data-direct-text]' );
		await text.scrollIntoViewIfNeeded();
		const box = await text.boundingBox();
		expect( Math.round( box.x ) ).toBe( 16 );
		expect( Math.round( box.x + box.width ) ).toBe( 390 - 16 );
	} );
} );
