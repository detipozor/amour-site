<?php
/**
 * Plugin Name: Amour Core
 * Description: Content model and site tools for Amour Relax Hotel and Villa Eudora: settings, content types, local seed data.
 * Version: 0.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.2
 * Text Domain: amour
 * License: Proprietary
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

define( 'AMOUR_CORE_DIR', __DIR__ );

require_once AMOUR_CORE_DIR . '/inc/fields.php';
require_once AMOUR_CORE_DIR . '/inc/fields-home.php';
require_once AMOUR_CORE_DIR . '/inc/fields-rooms.php';
require_once AMOUR_CORE_DIR . '/inc/fields-villa.php';
require_once AMOUR_CORE_DIR . '/inc/fields-estate.php';
require_once AMOUR_CORE_DIR . '/inc/fields-guide.php';
require_once AMOUR_CORE_DIR . '/inc/places.php';
require_once AMOUR_CORE_DIR . '/inc/reviews.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once AMOUR_CORE_DIR . '/inc/class-amour-seed-command.php';
	WP_CLI::add_command( 'amour seed', 'Amour_Seed_Command' );
}
