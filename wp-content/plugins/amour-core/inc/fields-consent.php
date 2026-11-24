<?php
/**
 * Analytics and the cookie banner (HANDOFF, section 14): the Google Tag Manager container (GA4 and Meta Pixel are
 * set up inside it) and the banner's text. Nothing loads before the guest agrees; without a container nothing
 * tracks, so the banner does not ask.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the analytics field group on Hotel settings.
 */
function amour_register_consent_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_consent',
			'title'                 => __( 'Analytics and the cookie banner', 'amour' ),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'amour-hotel',
					),
				),
			),
			'menu_order'            => 9,
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'          => 'field_amour_gtm_id',
					'name'         => 'amour_gtm_id',
					'label'        => __( 'Google Tag Manager container', 'amour' ),
					'type'         => 'text',
					'placeholder'  => 'GTM-XXXXXXX',
					'instructions' => __( 'From the company account (question 30). Google Analytics 4 and the Meta Pixel are set up inside Tag Manager. It loads only after a guest agrees; while this is empty nothing tracks and the banner does not ask.', 'amour' ),
				),
				array(
					'key'          => 'field_amour_cookie_text',
					'name'         => 'amour_cookie_text',
					'label'        => __( 'Banner text', 'amour' ),
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => __( 'A link to the cookie policy follows it. Every new service also goes into the table on the Cookies page.', 'amour' ),
				),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_consent_fields' );
