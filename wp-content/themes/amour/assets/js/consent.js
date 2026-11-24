/**
 * Cookie consent (HANDOFF, section 14): Google Consent Mode v2 in its basic form. Tag Manager, with Google
 * Analytics 4 and the Meta Pixel inside it, loads only after the guest agrees to analytics or advertising; before
 * that nothing from outside the site loads. The choice stays in this browser for a year (localStorage
 * "amour-consent": { analytics, ads, at }), then the banner asks again.
 *
 * The banner (template-parts/cookie-banner.php) shows by itself when there is a container to agree to and no choice
 * yet. Any [data-consent-open] (Cookie settings on the Cookies page) opens it to change the choice.
 * While it is open the page keeps the focused element above it (scroll-padding), so the keyboard never ends up
 * behind the banner (WCAG 2.4.11).
 */

const KEY = 'amour-consent';
const YEAR = 365 * 86400000;

window.dataLayer = window.dataLayer || [];

/**
 * Consent Mode reads the arguments object itself, the way Google's gtag() snippet pushes it.
 */
function gtag() {
	window.dataLayer.push( arguments );
}

const read = () => {
	try {
		const choice = JSON.parse( window.localStorage.getItem( KEY ) || 'null' );
		return choice && Date.now() - choice.at < YEAR ? choice : null;
	} catch {
		return null;
	}
};

const write = ( choice ) => {
	try {
		window.localStorage.setItem( KEY, JSON.stringify( choice ) );
	} catch {
		// Storage blocked: the choice holds for this page only.
	}
};

const signals = ( choice ) => {
	const ads = choice.ads ? 'granted' : 'denied';
	return { analytics_storage: choice.analytics ? 'granted' : 'denied', ad_storage: ads, ad_user_data: ads, ad_personalization: ads };
};

let defaulted = false;
let started = false;

/**
 * Tell Consent Mode the choice and, when the guest agreed to anything, load Tag Manager once.
 *
 * @param {Object} choice    { analytics, ads }.
 * @param {string} container The Tag Manager container, GTM-…, or ''.
 */
const apply = ( choice, container ) => {
	if ( ! defaulted ) {
		gtag( 'consent', 'default', { ...signals( {} ), functionality_storage: 'granted', security_storage: 'granted', wait_for_update: 500 } );
		defaulted = true;
	}
	gtag( 'consent', 'update', signals( choice ) );
	if ( ( choice.analytics || choice.ads ) && container && ! started ) {
		started = true;
		window.dataLayer.push( { 'gtm.start': Date.now(), event: 'gtm.js' } );
		const script = document.createElement( 'script' );
		script.async = true;
		script.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent( container );
		document.head.append( script );
	}
};

const banner = document.querySelector( '[data-consent]' );

if ( banner ) {
	const container = banner.dataset.gtm || '';
	const boxes = [ ...banner.querySelectorAll( '[data-consent-kind]' ) ];
	const choices = banner.querySelector( '[data-consent-choices]' );
	const settings = banner.querySelector( '[data-consent-settings]' );
	const save = banner.querySelector( '[data-consent-save]' );
	let opener = null;

	const fit = () => {
		document.documentElement.style.scrollPaddingBottom = banner.hidden ? '' : banner.offsetHeight + 32 + 'px';
	};

	const unfold = ( open ) => {
		choices.hidden = ! open;
		save.hidden = ! open;
		settings.hidden = open;
		settings.setAttribute( 'aria-expanded', String( open ) );
		fit();
	};

	const show = ( from ) => {
		opener = from || null;
		const choice = read() || {};
		boxes.forEach( ( box ) => ( box.checked = Boolean( choice[ box.dataset.consentKind ] ) ) );
		unfold( false );
		banner.hidden = false;
		fit();
		if ( opener ) {
			settings.focus();
		}
	};

	const hide = () => {
		banner.hidden = true;
		fit();
		if ( opener ) {
			opener.focus();
			opener = null;
		}
	};

	const decide = ( analytics, ads ) => {
		const choice = { analytics, ads, at: Date.now() };
		write( choice );
		apply( choice, container );
		hide();
	};

	settings.addEventListener( 'click', () => {
		unfold( true );
		boxes[ 0 ].focus();
	} );
	save.addEventListener( 'click', () => {
		const checked = ( kind ) => boxes.some( ( box ) => box.dataset.consentKind === kind && box.checked );
		decide( checked( 'analytics' ), checked( 'ads' ) );
	} );
	banner.querySelectorAll( '[data-consent-all]' ).forEach( ( button ) => {
		button.addEventListener( 'click', () => {
			const all = button.dataset.consentAll === 'true';
			decide( all, all );
		} );
	} );
	// Opened from Cookie settings, Esc closes it without a new choice.
	banner.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && opener ) {
			hide();
		}
	} );
	document.addEventListener( 'click', ( event ) => {
		const open = event.target.closest?.( '[data-consent-open]' );
		if ( open ) {
			show( open );
		}
	} );
	window.addEventListener( 'resize', fit );

	const stored = read();
	if ( stored ) {
		apply( stored, container );
	} else if ( container ) {
		show();
	}
}
