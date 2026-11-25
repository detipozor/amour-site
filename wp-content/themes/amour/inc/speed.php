<?php
/**
 * Speed (HANDOFF, section 13): LCP up to 2.5 s on a phone on 4G, theme JS up to about 150 KB.
 * The font preload is in functions.php, the video loads after the page (assets/js/home/hero.js),
 * the logos are thinned by bin/build-brand.py.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

// WordPress emoji: two scripts on every page that turn emoji into pictures. The site has no emoji in its
// texts, and browsers draw them on their own.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'emoji_svg_url', '__return_false' );


/**
 * Styles WordPress adds for the block editor's presets (colours, gradients, font sizes) and classic buttons:
 * about 10 KB in every page. The theme takes its colours and sizes from tokens.css, so they are not used.
 * Pages written with blocks (the legal pages) keep the block styles, the table among them.
 * WordPress adds some of them late, in the footer, and moves them up into <head>: hence the second hook.
 */
function amour_drop_core_styles() {
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
	if ( ! is_singular() || ! has_blocks( get_queried_object_id() ) ) {
		wp_dequeue_style( 'wp-block-library' );
	}
}
add_action( 'wp_enqueue_scripts', 'amour_drop_core_styles', 100 );
add_action( 'wp_footer', 'amour_drop_core_styles', 2 );
