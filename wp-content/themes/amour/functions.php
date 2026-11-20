<?php
/**
 * Amour theme setup.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

define( 'AMOUR_VERSION', '0.3.0' );

require_once get_theme_file_path( 'inc/icons.php' );
require_once get_theme_file_path( 'inc/brand.php' );
require_once get_theme_file_path( 'inc/content.php' );

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
		'amour-tokens'      => 'assets/css/tokens.css',
		'amour-base'        => 'assets/css/base.css',
		'amour-button'      => 'assets/css/components/button.css',
		'amour-header'      => 'assets/css/components/header.css',
		'amour-menu'        => 'assets/css/components/menu.css',
		'amour-transitions' => 'assets/css/components/transitions.css',
	);
	$deps = array();
	foreach ( $css as $handle => $file ) {
		wp_enqueue_style( $handle, get_theme_file_uri( $file ), $deps, AMOUR_VERSION );
		$deps = array( $handle );
	}

	// JavaScript modules, one per block (HANDOFF, section 1). Imports use these ids, so each file loads once.
	wp_register_script_module( '@amour/transitions', get_theme_file_uri( 'assets/js/transitions.js' ), array(), AMOUR_VERSION );
	wp_register_script_module( '@amour/header', get_theme_file_uri( 'assets/js/header.js' ), array(), AMOUR_VERSION );
	wp_register_script_module( '@amour/menu', get_theme_file_uri( 'assets/js/menu.js' ), array( '@amour/transitions' ), AMOUR_VERSION );
	wp_enqueue_script_module( '@amour/transitions' );
	wp_enqueue_script_module( '@amour/header' );
	wp_enqueue_script_module( '@amour/menu' );
}
add_action( 'wp_enqueue_scripts', 'amour_assets' );

/**
 * The arriving half of a page change (assets/js/transitions.js) has to start before the first paint,
 * so a few lines go straight into <head>.
 */
function amour_arrival_script() {
	wp_print_inline_script_tag(
		"try{var k='amour-arrive',a=sessionStorage.getItem(k);sessionStorage.removeItem(k);" .
		"if((a==='curtain'||a==='soft')&&!matchMedia('(prefers-reduced-motion: reduce)').matches)" .
		"{document.documentElement.classList.add('is-arriving','is-arriving--'+a)}}catch(e){}"
	);
}
add_action( 'wp_head', 'amour_arrival_script', 0 );

/**
 * Start loading the next page while the pointer rests on a link (WordPress speculative loading),
 * so the page is ready when the curtain lifts.
 *
 * @param array|null $config Speculation rules configuration.
 * @return array|null
 */
function amour_speculation( $config ) {
	if ( is_array( $config ) ) {
		$config['mode']      = 'prefetch';
		$config['eagerness'] = 'moderate';
	}
	return $config;
}
add_filter( 'wp_speculation_rules_configuration', 'amour_speculation' );

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
