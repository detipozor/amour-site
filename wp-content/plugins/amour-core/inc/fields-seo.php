<?php
/**
 * Search and sharing (HANDOFF, section 13): each page's title and description for Google and its picture for
 * sharing; in Hotel settings the picture every page shares by default and the old addresses that lead to new pages.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the search and sharing field groups.
 */
function amour_register_seo_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_seo',
			'title'                 => __( 'Search and sharing', 'amour' ),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			'position'              => 'side',
			'menu_order'            => 30,
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'          => 'field_amour_seo_title',
					'name'         => 'seo_title',
					'label'        => __( 'Title in Google and the browser tab', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'Empty: the page title · Amour Relax Hotel.', 'amour' ),
				),
				array(
					'key'          => 'field_amour_seo_description',
					'name'         => 'seo_description',
					'label'        => __( 'Description in Google', 'amour' ),
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => __( 'One or two sentences, up to about 155 characters. Also shown when the page is shared.', 'amour' ),
				),
				array(
					'key'           => 'field_amour_seo_image',
					'name'          => 'seo_image',
					'label'         => __( 'Picture when shared', 'amour' ),
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'thumbnail',
					'instructions'  => __( 'Cut to 1200 × 630 for Facebook, WhatsApp and others. Empty: the room or villa card photo, or the picture in Hotel settings.', 'amour' ),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_search',
			'title'                 => __( 'Search and sharing', 'amour' ),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'amour-hotel',
					),
				),
			),
			'menu_order'            => 10,
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'           => 'field_amour_share_image',
					'name'          => 'amour_share_image',
					'label'         => __( 'Picture when a page is shared', 'amour' ),
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'thumbnail',
					'instructions'  => __( 'For pages without their own. Cut to 1200 × 630.', 'amour' ),
				),
				array(
					'key'          => 'field_amour_redirects',
					'name'         => 'amour_redirects',
					'label'        => __( 'Old addresses', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Add an address', 'amour' ),
					'instructions' => __( 'Addresses of the old site that Google still knows (Search Console), and where they lead now. A permanent redirect (301).', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'         => 'field_amour_redirect_from',
							'name'        => 'from',
							'label'       => __( 'Old address', 'amour' ),
							'type'        => 'text',
							'placeholder' => '/checkout-page/',
						),
						array(
							'key'         => 'field_amour_redirect_to',
							'name'        => 'to',
							'label'       => __( 'Leads to', 'amour' ),
							'type'        => 'text',
							'placeholder' => '/book/',
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_seo_fields' );
