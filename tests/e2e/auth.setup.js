import { test as setup, expect } from '@playwright/test';
import { AUTH_FILE } from './auth.js';

/** Local wp-env login (the default for a copy on your own computer); other copies pass their own. */
const USER = process.env.WP_USER || 'admin';
const PASS = process.env.WP_PASS || 'password';

/*
 * Log in once and share the session with the tests that need it. Logging the same user in from
 * several tests at once makes WordPress drop some of the sessions (they share one list of tokens).
 */
setup( 'log in to WordPress', async ( { page } ) => {
	await page.goto( '/wp-login.php' );
	await page.fill( '#user_login', USER );
	await page.fill( '#user_pass', PASS );
	await page.click( '#wp-submit' );
	await page.waitForURL( ( url ) => url.pathname.startsWith( '/wp-admin' ) );
	await expect( page.locator( '#wpadminbar' ) ).toBeVisible();
	await page.context().storageState( { path: AUTH_FILE } );
} );
