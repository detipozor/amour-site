/**
 * Full-screen menu (board Menu-list, motion from design/prototype-shell/app.js).
 * - Opens from the Menu button; Close and Esc close it and return focus to Menu (HANDOFF, section 12).
 * - The rest of the page is inert while it is open, so the keyboard stays inside.
 * - Desktop: the photo in the arch follows the name under the pointer or the keyboard focus.
 * - Going to another page: the names lift away and the menu dissolves over the new page (transitions.js).
 */

import { softGo, reduceMotion } from '@amour/transitions';

const menu = document.getElementById( 'am-menu' );
const button = document.querySelector( '.am-header__menu' );

if ( menu && button ) {
	const root = document.documentElement;
	const stage = menu.querySelector( '.am-menu__stage' );
	const pics = [ ...menu.querySelectorAll( '.am-menu__arch img' ) ];
	const links = [ ...menu.querySelectorAll( '.am-menu__nav a' ) ];
	const line = menu.querySelector( '.am-menu__line' );
	const ROW = 72;
	const NAV_TOP = 190;
	let hideTimer = 0;

	/* The 1440 × 900 board scales down to fit the window; header and footer keep their place at the edges */
	const fit = () => {
		const scale = Math.min( 1.1, window.innerWidth / 1440, ( window.innerHeight - 40 ) / 860 );
		stage.style.setProperty( '--fit', scale.toFixed( 4 ) );
	};

	/* The arch stands next to the active name, within the board's limits */
	const setActive = ( index ) => {
		links.forEach( ( link, i ) => link.classList.toggle( 'is-active', i === index ) );
		pics.forEach( ( pic ) => pic.classList.toggle( 'is-active', Number( pic.dataset.index ) === index ) );
		const top = Math.max( 110, Math.min( 297, NAV_TOP + index * ROW + ROW / 2 - 226 ) );
		stage.style.setProperty( '--arch-y', `${ top }px` );
		if ( line ) {
			line.textContent = links[ index ]?.dataset.line || '';
		}
	};

	links.forEach( ( link, i ) => {
		link.addEventListener( 'mouseenter', () => setActive( i ) );
		link.addEventListener( 'focus', () => setActive( i ) );
	} );

	const outside = () => [ ...document.body.children ].filter( ( el ) => el !== menu && ! el.matches( 'script, .am-curtain, #wpadminbar' ) );

	const open = () => {
		clearTimeout( hideTimer );
		fit();
		menu.hidden = false;
		void menu.offsetWidth; // let the closed state paint first, so the panel can drop in
		menu.classList.add( 'is-open' );
		root.classList.add( 'menu-open' );
		button.setAttribute( 'aria-expanded', 'true' );
		outside().forEach( ( el ) => ( el.inert = true ) );
		const current = links.findIndex( ( link ) => link.hasAttribute( 'aria-current' ) );
		setActive( current > -1 ? current : 0 );
		setTimeout( () => ( links[ 0 ] || menu.querySelector( '[data-menu-close]' ) ).focus( { preventScroll: true } ), reduceMotion() ? 0 : 400 );
	};

	const close = ( instant = false ) => {
		if ( menu.hidden ) {
			return;
		}
		menu.classList.remove( 'is-open', 'is-leaving' );
		root.classList.remove( 'menu-open' );
		button.setAttribute( 'aria-expanded', 'false' );
		outside().forEach( ( el ) => ( el.inert = false ) );
		const hide = () => {
			if ( ! menu.classList.contains( 'is-open' ) ) {
				menu.hidden = true;
			}
		};
		if ( instant || reduceMotion() ) {
			hide();
		} else {
			hideTimer = setTimeout( hide, 760 );
		}
		button.focus( { preventScroll: true } );
	};

	button.addEventListener( 'click', open );
	menu.querySelector( '[data-menu-close]' ).addEventListener( 'click', () => close() );
	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && ! menu.hidden ) {
			close();
		}
	} );
	window.addEventListener( 'resize', () => ! menu.hidden && fit() );

	/* Links in the menu: another page — soft dissolve; this page with an anchor — close and scroll */
	menu.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( '.am-menu__nav a' );
		if ( ! link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
			return;
		}
		const url = new URL( link.href, window.location.href );
		const samePage = url.origin === window.location.origin && url.pathname === window.location.pathname;
		if ( samePage && url.hash ) {
			event.preventDefault();
			close( true );
			document.getElementById( url.hash.slice( 1 ) )?.scrollIntoView( { behavior: reduceMotion() ? 'auto' : 'smooth' } );
			return;
		}
		if ( ! reduceMotion() ) {
			event.preventDefault();
			menu.classList.add( 'is-leaving' );
			softGo( url.href );
		}
	} );

	/* Back from the next page (page cache): show the page, not the menu */
	window.addEventListener( 'pageshow', ( event ) => event.persisted && close( true ) );
}
