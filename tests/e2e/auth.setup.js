import { test as setup, expect } from '@playwright/test';
import { AUTH_FILE } from './auth.js';

/** Local wp-env login (the default for a copy on your own computer); other copies pass their own. */
const USER = process.env.WP_USER || 'admin';
const PASS = process.env.WP_PASS || 'password';

/*
 * Log in once and share the session with the tests that need it. Logging the same user in from
 * several tests at once makes WordPress drop some of the sessions (they share one list of tokens).
 * The form is posted directly: the login page clears its fields on a timer, which races with typing.
 */
setup( 'log in to WordPress', async ( { page } ) => {
	await page.goto( '/wp-login.php' ); // sets the test cookie WordPress checks for
	const response = await page.request.post( '/wp-login.php', {
		form: { log: USER, pwd: PASS, rememberme: 'forever', 'wp-submit': 'Log In', redirect_to: '/wp-admin/', testcookie: '1' },
	} );
	expect( response.ok() ).toBe( true );
	await page.goto( '/wp-admin/' );
	await expect( page.locator( '#wpadminbar' ) ).toBeVisible();
	await page.context().storageState( { path: AUTH_FILE } );
} );
