<?php
/**
 * Brand: which house a page belongs to (Amour or Villa Eudora) and the logos from the package.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * The house of the current page: "eudora" on the villa page and below it, "amour" everywhere else.
 * Drives data-theme (palette), the header logo and the footer pattern.
 *
 * @return string "amour" or "eudora".
 */
function amour_brand() {
	$villa = get_page_by_path( 'villa-eudora' );
	if ( ! $villa || ! is_page() ) {
		return 'amour';
	}

	// The villa page in every language.
	$villa_ids = array( (int) $villa->ID );
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$villa_ids = array_merge( $villa_ids, array_map( 'intval', array_values( pll_get_post_translations( $villa->ID ) ) ) );
	}

	$page_id   = (int) get_queried_object_id();
	$ancestors = array_map( 'intval', get_post_ancestors( $page_id ) );

	return array_intersect( $villa_ids, array_merge( array( $page_id ), $ancestors ) ) ? 'eudora' : 'amour';
}

/**
 * Logo markup. The outlines come from the package (assets/brand, built by bin/build-brand.py);
 * the colour comes from the surrounding text colour, so it is always a token.
 *
 * @param string $name  File name in assets/brand without .svg: amour-horizontal, amour-main, amour-word,
 *                      eudora-horizontal, eudora-word, olive, jug.
 * @param string $label Accessible name, or empty for a decorative logo.
 * @param string $classes Extra class names.
 * @return string SVG markup.
 */
function amour_logo( $name, $label = '', $classes = '' ) {
	static $view_boxes = array();

	$file = get_theme_file_path( 'assets/brand/' . $name . '.svg' );
	if ( ! isset( $view_boxes[ $name ] ) ) {
		$view_boxes[ $name ] = '';
		if ( is_readable( $file ) ) {
			// Only the opening tag is needed for the frame; the outlines stay in the file the browser caches.
			$head = (string) file_get_contents( $file, false, null, 0, 300 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
			if ( preg_match( '/viewBox="([^"]+)"/', $head, $match ) ) {
				$view_boxes[ $name ] = $match[1];
			}
		}
	}
	if ( ! $view_boxes[ $name ] ) {
		return '';
	}

	$a11y = $label
		? ' role="img" aria-label="' . esc_attr( $label ) . '"'
		: ' aria-hidden="true" focusable="false"';

	$href = get_theme_file_uri( 'assets/brand/' . $name . '.svg' ) . '?v=' . AMOUR_VERSION . '#logo';

	return sprintf(
		'<svg class="am-logo am-logo--%1$s %2$s" xmlns="http://www.w3.org/2000/svg" viewBox="%3$s" fill="currentColor"%4$s><use href="%5$s"/></svg>',
		esc_attr( $name ),
		esc_attr( $classes ),
		esc_attr( $view_boxes[ $name ] ),
		$a11y,
		esc_url( $href )
	);
}

/**
 * Favicons from the package, unless a site icon is set in the admin (Appearance → Customize → Site Identity).
 */
function amour_favicons() {
	if ( has_site_icon() ) {
		return;
	}
	$dir = get_theme_file_uri( 'assets/brand/favicon/' . amour_brand() . '/' );
	printf( '<link rel="icon" type="image/png" sizes="32x32" href="%s">' . "\n", esc_url( $dir . 'favicon_32.png' ) );
	printf( '<link rel="icon" type="image/png" sizes="16x16" href="%s">' . "\n", esc_url( $dir . 'favicon_16.png' ) );
	printf( '<link rel="apple-touch-icon" sizes="180x180" href="%s">' . "\n", esc_url( $dir . 'favicon_180.png' ) );
}
add_action( 'wp_head', 'amour_favicons', 5 );
