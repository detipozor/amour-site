<?php
/**
 * Amour theme setup.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

define( 'AMOUR_VERSION', '0.2.0' );

require_once get_theme_file_path( 'inc/icons.php' );
require_once get_theme_file_path( 'inc/brand.php' );

/**
 * Theme supports and menu locations.
 */
function amour_setup() {
	load_theme_textdomain( 'amour', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );

	register_nav_menus(
		array(
			'primary'         => __( 'Main menu (overlay)', 'amour' ),
			'footer-stay'     => __( 'Footer: Stay', 'amour' ),
			'footer-discover' => __( 'Footer: Discover', 'amour' ),
			'legal'           => __( 'Footer: legal links', 'amour' ),
		)
	);
}
add_action( 'after_setup_theme', 'amour_setup' );

/**
 * Styles and scripts. Plain CSS files, no build step, so the Playground preview runs straight from GitHub.
 */
function amour_assets() {
	$css  = array(
		'amour-tokens' => 'assets/css/tokens.css',
		'amour-base'   => 'assets/css/base.css',
		'amour-button' => 'assets/css/components/button.css',
	);
	$deps = array();
	foreach ( $css as $handle => $file ) {
		wp_enqueue_style( $handle, get_theme_file_uri( $file ), $deps, AMOUR_VERSION );
		$deps = array( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'amour_assets' );

/**
 * Preload the main text font (HANDOFF, section 13): Instrument Sans, Latin.
 */
function amour_preload_font() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( get_theme_file_uri( 'assets/fonts/InstrumentSans-latin.woff2' ) )
	);
}
add_action( 'wp_head', 'amour_preload_font', 1 );

/**
 * The house palette on the page: data-theme="amour" or "eudora" (HANDOFF, section 3).
 *
 * @param string $output Language attributes of the html element.
 * @return string
 */
function amour_html_theme( $output ) {
	if ( is_admin() || is_feed() ) {
		return $output;
	}
	return $output . ' data-theme="' . esc_attr( amour_brand() ) . '"';
}
add_filter( 'language_attributes', 'amour_html_theme' );
