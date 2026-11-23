<?php
/**
 * Fields of the villa page that a room does not have (HANDOFF, section 9: "Вилла"; boards Villa-v2, Villa-v2-b):
 * the bedrooms, how the house works floor by floor, weddings and gatherings, the villa's map.
 * The rest of the page (first screen, photos, the house, details, price) uses the room fields.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the villa field group.
 */
function amour_register_villa_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$location = array_map(
		static fn( $id ) => array(
			array(
				'param'    => 'page',
				'operator' => '==',
				'value'    => (string) $id,
			),
		),
		amour_core_page_ids( 'villa-eudora' )
	);
	if ( ! $location ) {
		return;
	}
	$tab   = static fn( $key, $label ) => array(
		'key'       => 'field_amour_villa_tab_' . $key,
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);
	$text  = static fn( $key, $name, $label, $type = 'text', $extra = array() ) => array_merge(
		array(
			'key'   => 'field_amour_villa_' . $key,
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		),
		'textarea' === $type ? array( 'rows' => 2 ) : array(),
		$extra
	);
	$image = static fn( $key, $name, $label ) => array(
		'key'           => 'field_amour_villa_' . $key,
		'name'          => $name,
		'label'         => $label,
		'type'          => 'image',
		'return_format' => 'id',
		'preview_size'  => 'thumbnail',
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_villa_page',
			'title'                 => __( 'Villa', 'amour' ),
			'location'              => $location,
			'menu_order'            => 1,
			'instruction_placement' => 'field',
			'fields'                => array(
				$tab( 'beds', __( 'Bedrooms', 'amour' ) ),
				$text( 'beds_title', 'villa_beds_title', __( 'Heading', 'amour' ) ),
				$text( 'beds_text', 'villa_beds_text', __( 'Text next to it', 'amour' ), 'textarea' ),
				array(
					'key'          => 'field_amour_villa_bedrooms',
					'name'         => 'villa_bedrooms',
					'label'        => __( 'Bedrooms', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Add a bedroom', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'   => 'field_amour_villa_bedroom_name',
							'name'  => 'name',
							'label' => __( 'Name', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'   => 'field_amour_villa_bedroom_text',
							'name'  => 'text',
							'label' => __( 'Bed, bathroom, view', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'           => 'field_amour_villa_bedroom_photo',
							'name'          => 'photo',
							'label'         => __( 'Photo (upright)', 'amour' ),
							'type'          => 'image',
							'return_format' => 'id',
							'preview_size'  => 'thumbnail',
						),
					),
				),

				$tab( 'floors', __( 'Floor by floor', 'amour' ) ),
				$text( 'floors_label', 'villa_floors_label', __( 'Label (capitals)', 'amour' ) ),
				$text( 'floors_title', 'villa_floors_title', __( 'Heading', 'amour' ) ),
				array(
					'key'          => 'field_amour_villa_floors',
					'name'         => 'villa_floors',
					'label'        => __( 'Ground floor, upper floor, outside', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'table',
					'min'          => 3,
					'max'          => 3,
					'instructions' => __( 'In this order: the drawing lights up the part you point at.', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'   => 'field_amour_villa_floor_name',
							'name'  => 'name',
							'label' => __( 'Name', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'   => 'field_amour_villa_floor_text',
							'name'  => 'text',
							'label' => __( 'What is there', 'amour' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
					),
				),
				$image( 'plan', 'villa_plan', __( 'Floor plan from the architect (replaces the drawing)', 'amour' ) ),
				$text( 'floors_caption', 'villa_floors_caption', __( 'Caption under the drawing', 'amour' ) ),

				$tab( 'events', __( 'Weddings and gatherings', 'amour' ) ),
				$text( 'events_label', 'villa_events_label', __( 'Label (capitals)', 'amour' ) ),
				$text( 'events_title', 'villa_events_title', __( 'Heading (each line on its own line)', 'amour' ), 'textarea' ),
				$text( 'events_text', 'villa_events_text', __( 'Text', 'amour' ), 'textarea' ),
				$text( 'events_button', 'villa_events_button', __( 'Button', 'amour' ), 'link', array( 'instructions' => __( 'For example Plan a gathering, to the contact page. "Write on WhatsApp" next to it uses the WhatsApp number from Hotel settings.', 'amour' ) ) ),
				$image( 'events_bg', 'villa_events_bg', __( 'Photo behind', 'amour' ) ),
				$image( 'events_photo', 'villa_events_photo', __( 'Photo in the arch (upright)', 'amour' ) ),
				$text( 'events_caption', 'villa_events_caption', __( 'Caption under the arch', 'amour' ) ),

				$tab( 'map', __( 'Map and other stays', 'amour' ) ),
				array(
					'key'          => 'field_amour_villa_near',
					'name'         => 'villa_near',
					'label'        => __( 'Nearby, with distance or drive time (the map card)', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Add a place', 'amour' ),
					'instructions' => __( 'The heading, the text and the Google Maps link come from Hotel settings → Location card.', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'   => 'field_amour_villa_near_place',
							'name'  => 'place',
							'label' => __( 'Place', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'   => 'field_amour_villa_near_value',
							'name'  => 'value',
							'label' => __( 'How far', 'amour' ),
							'type'  => 'text',
						),
					),
				),
				$text( 'others_title', 'villa_others_title', __( 'Heading above the hotel rooms', 'amour' ) ),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_villa_fields' );
