<?php
/**
 * Admin fields (Secure Custom Fields): the "Hotel" settings page and the photo and line of each menu item.
 * HANDOFF, section 9. The rest of the content model (rooms, villa, places, reviews, FAQ, home) comes in week 5.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the settings page and the field groups.
 */
function amour_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page(
			array(
				'page_title' => __( 'Hotel settings', 'amour' ),
				'menu_title' => __( 'Hotel', 'amour' ),
				'menu_slug'  => 'amour-hotel',
				'capability' => 'edit_pages',
				'icon_url'   => 'dashicons-admin-home',
				'position'   => 3,
				'redirect'   => false,
			)
		);
	}

	$on_settings_page = array(
		array(
			array(
				'param'    => 'options_page',
				'operator' => '==',
				'value'    => 'amour-hotel',
			),
		),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_contacts',
			'title'                 => __( 'Contacts for guests', 'amour' ),
			'location'              => $on_settings_page,
			'menu_order'            => 0,
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'          => 'field_amour_phone',
					'name'         => 'amour_phone',
					'label'        => __( 'Phone', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'As guests should see it, with the country code: +30 …', 'amour' ),
				),
				array(
					'key'          => 'field_amour_whatsapp',
					'name'         => 'amour_whatsapp',
					'label'        => __( 'WhatsApp number', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'With the country code. Spaces are fine; the site makes a wa.me link from the digits.', 'amour' ),
				),
				array(
					'key'   => 'field_amour_email',
					'name'  => 'amour_email',
					'label' => __( 'Public email for guests', 'amour' ),
					'type'  => 'email',
				),
				array(
					'key'   => 'field_amour_address',
					'name'  => 'amour_address',
					'label' => __( 'Address', 'amour' ),
					'type'  => 'text',
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_company',
			'title'                 => __( 'Company details (footer and legal pages)', 'amour' ),
			'location'              => $on_settings_page,
			'menu_order'            => 1,
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'   => 'field_amour_company',
					'name'  => 'amour_company',
					'label' => __( 'Company name', 'amour' ),
					'type'  => 'text',
				),
				array(
					'key'   => 'field_amour_gemi',
					'name'  => 'amour_gemi',
					'label' => 'ΓΕΜΗ',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_amour_afm',
					'name'  => 'amour_afm',
					'label' => 'ΑΦΜ',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_amour_mhte_hotel',
					'name'  => 'amour_mhte_hotel',
					'label' => __( 'MHTE number of the hotel', 'amour' ),
					'type'  => 'text',
				),
				array(
					'key'   => 'field_amour_mhte_villa',
					'name'  => 'amour_mhte_villa',
					'label' => __( 'MHTE or AMA number of the villa', 'amour' ),
					'type'  => 'text',
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_social',
			'title'                 => __( 'Social networks', 'amour' ),
			'location'              => $on_settings_page,
			'menu_order'            => 2,
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'          => 'field_amour_social',
					'name'         => 'amour_social',
					'label'        => __( 'Links', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Add a network', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'      => 'field_amour_social_label',
							'name'     => 'label',
							'label'    => __( 'Name', 'amour' ),
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'      => 'field_amour_social_url',
							'name'     => 'url',
							'label'    => __( 'Address', 'amour' ),
							'type'     => 'url',
							'required' => 1,
						),
						array(
							'key'           => 'field_amour_social_in_menu',
							'name'          => 'in_menu',
							'label'         => __( 'Also in the menu', 'amour' ),
							'type'          => 'true_false',
							'ui'            => 1,
							'default_value' => 0,
						),
					),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_amour_menu_item',
			'title'    => __( 'Menu overlay', 'amour' ),
			'location' => array(
				array(
					array(
						'param'    => 'nav_menu_item',
						'operator' => '==',
						'value'    => 'all',
					),
				),
			),
			'fields'   => array(
				array(
					'key'           => 'field_amour_menu_photo',
					'name'          => 'amour_menu_photo',
					'label'         => __( 'Photo in the arch (main menu, desktop)', 'amour' ),
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'thumbnail',
				),
				array(
					'key'   => 'field_amour_menu_line',
					'name'  => 'amour_menu_line',
					'label' => __( 'Line under the photo', 'amour' ),
					'type'  => 'text',
				),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_fields' );
