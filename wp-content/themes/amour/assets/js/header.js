/**
 * Site header (logic from design/prototype-shell/app.js, "Header trio").
 * - Light or dark logo by what is under it: [data-tone="dark|light"], a photo or video, or the background colour.
 * - A block split down the middle ([data-hdr="split"]): the logo moves over the photo half.
 * - The logo sits on an edge between light and dark: it steps out until the edge has passed.
 * - A block of the villa ([data-brand="eudora"]) shows the Eudora logo.
 * - Reading down: on a desktop the logo steps aside and returns on scrolling up; on a phone it shows
 *   only near the top of the page (240 px).
 * - The language pill opens a short list.
 */

import { loadLogos } from '@amour/logos';

const header = document.querySelector( '.am-header' );

if ( header ) {
	const logo = header.querySelector( '.am-header__logo' );
	const phone = window.matchMedia( '(max-width: 760px)' );
	const homeBrand = header.dataset.homeBrand || 'amour';
	const skip = ( el ) => header.contains( el ) || el.closest?.( '.am-curtain, .am-menu, #wpadminbar' ) || el === document.documentElement || el === document.body;

	const darkAt = ( x, y ) => {
		for ( const el of document.elementsFromPoint( x, y ) ) {
			if ( skip( el ) ) {
				continue;
			}
			const tone = el.closest?.( '[data-tone]' );
			if ( tone ) {
				return tone.dataset.tone === 'dark';
			}
			if ( el.tagName === 'IMG' || el.tagName === 'VIDEO' || el.tagName === 'PICTURE' ) {
				return true;
			}
			const match = /rgba?\(([^)]+)\)/.exec( getComputedStyle( el ).backgroundColor );
			if ( match ) {
				const [ r, g, b, a = 1 ] = match[ 1 ].split( ',' ).map( parseFloat );
				if ( a > 0.5 ) {
					return ( 0.2126 * r + 0.7152 * g + 0.0722 * b ) / 255 < 0.5;
				}
			}
		}
		return false;
	};

	let queued = false;
	let swapTimer = 0;

	const adapt = () => {
		queued = false;
		if ( document.documentElement.classList.contains( 'menu-open' ) ) {
			return;
		}
		// Probe where the logo normally sits (the middle of the window), not where a shift has moved it.
		const box = logo.getBoundingClientRect();
		const cx = window.innerWidth / 2;
		const cy = box.top + box.height / 2;
		const half = phone.matches ? 52 : 104;
		const tall = phone.matches ? 16 : 32;
		const points = [ [ cx - half, cy ], [ cx + half, cy ], [ cx, cy - tall ], [ cx, cy + tall ] ];
		const under = document.elementsFromPoint( window.innerWidth / 2, cy ).find( ( el ) => ! skip( el ) );
		const split = !! under?.closest?.( '[data-hdr="split"]' );
		const dark = points.map( ( [ x, y ] ) => darkAt( x, y ) );
		const mixed = dark.some( ( d ) => d !== dark[ 0 ] );

		header.classList.toggle( 'is-shift', split && mixed && ! phone.matches );
		header.classList.toggle( 'is-edge', mixed && ! split );
		header.classList.toggle( 'is-light', split && mixed ? darkAt( window.innerWidth / 4, cy ) : dark.filter( Boolean ).length >= 3 );

		const brand = under?.closest?.( '[data-brand]' )?.dataset.brand || homeBrand;
		if ( brand !== header.dataset.brand ) {
			loadLogos(); // the other house's logo, if the page has not given it yet
			header.classList.add( 'is-swap' );
			clearTimeout( swapTimer );
			swapTimer = setTimeout( () => {
				header.dataset.brand = brand;
				header.classList.remove( 'is-swap' );
			}, 260 );
		}
	};

	const queue = () => {
		if ( ! queued ) {
			queued = true;
			requestAnimationFrame( adapt );
		}
	};

	let lastY = window.scrollY;
	const onScroll = () => {
		const y = window.scrollY;
		if ( y < 240 || ( ! phone.matches && y < lastY - 0.5 ) ) {
			header.classList.remove( 'is-up' );
		} else if ( phone.matches || y > lastY + 0.5 ) {
			header.classList.add( 'is-up' );
		}
		lastY = y;
		queue();
	};

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', queue );
	window.addEventListener( 'load', queue );
	// Pages animate in and videos load: check again now and then while the tab is visible.
	setInterval( () => ! document.hidden && queue(), 700 );
	queue();

	/* Language list */
	const toggle = header.querySelector( '.am-lang__toggle' );
	const list = header.querySelector( '.am-lang__list' );
	if ( toggle && list ) {
		const setOpen = ( open, focusBack = false ) => {
			toggle.setAttribute( 'aria-expanded', String( open ) );
			list.hidden = ! open;
			if ( open ) {
				( list.querySelector( '[aria-current]' ) || list.querySelector( 'a' ) )?.focus();
			} else if ( focusBack ) {
				toggle.focus();
			}
		};
		toggle.addEventListener( 'click', () => setOpen( list.hidden ) );
		list.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' ) {
				setOpen( false, true );
			}
		} );
		toggle.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' && ! list.hidden ) {
				setOpen( false );
			}
		} );
		document.addEventListener( 'click', ( event ) => {
			if ( ! list.hidden && ! event.target.closest( '.am-lang' ) ) {
				setOpen( false );
			}
		} );
		header.querySelector( '.am-lang' ).addEventListener( 'focusout', ( event ) => {
			if ( ! list.hidden && ! event.currentTarget.contains( event.relatedTarget ) ) {
				setOpen( false );
			}
		} );
	}
}
