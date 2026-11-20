<?php
/**
 * Amour theme setup.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

define( 'AMOUR_VERSION', '0.1.0' );

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
 * Styles and scripts.
 */
function amour_assets() {
	wp_enqueue_style( 'amour-base', get_theme_file_uri( 'assets/css/base.css' ), array(), AMOUR_VERSION );
}
add_action( 'wp_enqueue_scripts', 'amour_assets' );
