<?php
/**
 * Places (HANDOFF, section 9: "Место" — for the map on the home page and the Crete guide): name, short name,
 * position on the relief map, drive time, text, photo, Google Maps link, the road there, shown on the home page
 * and on the map of the room and villa pages ("Almyrida, Crete").
 * The hotel, the villa and far labels (Heraklion) are places too, marked by their kind.
 * Places have no pages of their own; the guide (week 4) lists them.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the place type.
 */
function amour_register_places() {
	register_post_type(
		'amour_place',
		array(
			'labels'        => array(
				'name'          => __( 'Places', 'amour' ),
				'singular_name' => __( 'Place', 'amour' ),
				'add_new_item'  => __( 'Add a place', 'amour' ),
				'edit_item'     => __( 'Edit place', 'amour' ),
				'menu_name'     => __( 'Places', 'amour' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-location',
			'menu_position' => 21,
			'supports'      => array( 'title', 'page-attributes' ),
			'hierarchical'  => false,
		)
	);
}
add_action( 'init', 'amour_register_places' );

/**
 * Places are translated with Polylang like pages.
 *
 * @param array $types Post types Polylang translates.
 * @return array
 */
function amour_places_translatable( $types ) {
	$types['amour_place'] = 'amour_place';
	return $types;
}
add_filter( 'pll_get_post_types', 'amour_places_translatable' );

/**
 * Fields of a place.
 */
function amour_register_place_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_place',
			'title'                 => __( 'Place', 'amour' ),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'amour_place',
					),
				),
			),
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'           => 'field_amour_place_kind',
					'name'          => 'kind',
					'label'         => __( 'Kind', 'amour' ),
					'type'          => 'select',
					'choices'       => array(
						'trip'  => __( 'Day trip', 'amour' ),
						'hotel' => __( 'The hotel', 'amour' ),
						'villa' => __( 'The villa', 'amour' ),
						'far'   => __( 'Far label only (for example Heraklion airport)', 'amour' ),
						'near'  => __( 'Label on the room or villa map only (a beach, the airport)', 'amour' ),
					),
					'default_value' => 'trip',
				),
				array(
					'key'          => 'field_amour_place_label',
					'name'         => 'label',
					'label'        => __( 'Label on the map', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'Name and drive time, for example Chania · 40 min. Times from Google Maps.', 'amour' ),
				),
				array(
					'key'   => 'field_amour_place_short',
					'name'  => 'short',
					'label' => __( 'Short label (phone)', 'amour' ),
					'type'  => 'text',
				),
				array(
					'key'          => 'field_amour_place_x',
					'name'         => 'x',
					'label'        => __( 'Across the map, %', 'amour' ),
					'type'         => 'number',
					'step'         => 0.01,
					'instructions' => __( 'Position on the relief map of Crete: 0 is the left edge, 100 the right.', 'amour' ),
				),
				array(
					'key'   => 'field_amour_place_y',
					'name'  => 'y',
					'label' => __( 'Down the map, %', 'amour' ),
					'type'  => 'number',
					'step'  => 0.01,
				),
				array(
					'key'     => 'field_amour_place_dir',
					'name'    => 'dir',
					'label'   => __( 'Label side', 'amour' ),
					'type'    => 'select',
					'choices' => array(
						'row'         => __( 'Right of the dot', 'amour' ),
						'row-reverse' => __( 'Left of the dot', 'amour' ),
					),
				),
				array(
					'key'     => 'field_amour_place_dir_phone',
					'name'    => 'dir_phone',
					'label'   => __( 'Label side on a phone', 'amour' ),
					'type'    => 'select',
					'choices' => array(
						'row'         => __( 'Right of the dot', 'amour' ),
						'row-reverse' => __( 'Left of the dot', 'amour' ),
					),
				),
				array(
					'key'          => 'field_amour_place_guide_dir',
					'name'         => 'guide_dir',
					'label'        => __( 'Label side on the guide map (if different)', 'amour' ),
					'type'         => 'select',
					'allow_null'   => 1,
					'choices'      => array(
						'row'         => __( 'Right of the dot', 'amour' ),
						'row-reverse' => __( 'Left of the dot', 'amour' ),
					),
					'instructions' => __( 'The guide shows the whole island, so a label may sit on the other side than on the home page.', 'amour' ),
				),
				array(
					'key'        => 'field_amour_place_guide_dir_phone',
					'name'       => 'guide_dir_phone',
					'label'      => __( 'Label side on the guide map, on a tablet or phone (if different)', 'amour' ),
					'type'       => 'select',
					'allow_null' => 1,
					'choices'    => array(
						'row'         => __( 'Right of the dot', 'amour' ),
						'row-reverse' => __( 'Left of the dot', 'amour' ),
					),
				),
				array(
					'key'          => 'field_amour_place_meta',
					'name'         => 'meta',
					'label'        => __( 'Distance and time', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'For example 23 km · 40 min by car.', 'amour' ),
				),
				array(
					'key'          => 'field_amour_place_note',
					'name'         => 'note',
					'label'        => __( 'A few words for the card in the guide', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'For example go early, little shade.', 'amour' ),
				),
				array(
					'key'   => 'field_amour_place_text',
					'name'  => 'text',
					'label' => __( 'Text', 'amour' ),
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'           => 'field_amour_place_photo',
					'name'          => 'photo',
					'label'         => __( 'Photo', 'amour' ),
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'thumbnail',
				),
				array(
					'key'   => 'field_amour_place_maps',
					'name'  => 'maps_url',
					'label' => __( 'Google Maps link', 'amour' ),
					'type'  => 'url',
				),
				array(
					'key'          => 'field_amour_place_route',
					'name'         => 'route',
					'label'        => __( 'Road from the hotel (SVG path on the relief map)', 'amour' ),
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => __( 'Drawn by the designer; leave as it is unless the map changes.', 'amour' ),
				),
				array(
					'key'           => 'field_amour_place_home',
					'name'          => 'on_home',
					'label'         => __( 'On the home page map', 'amour' ),
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				),
				array(
					'key'           => 'field_amour_place_rooms',
					'name'          => 'on_rooms',
					'label'         => __( 'On the map of the room pages', 'amour' ),
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 0,
				),
				array(
					'key'           => 'field_amour_place_villa',
					'name'          => 'on_villa',
					'label'         => __( 'On the map of the villa page', 'amour' ),
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 0,
				),
				array(
					'key'          => 'field_amour_place_villa_label',
					'name'         => 'villa_label',
					'label'        => __( 'Label on the villa map, if different', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'For example Amour Relax Hotel · 4 min.', 'amour' ),
				),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_place_fields' );
