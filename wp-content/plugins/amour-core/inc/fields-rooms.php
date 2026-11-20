<?php
/**
 * Card of a room type or the villa (HANDOFF, section 9: "Тип номера", "Вилла" — name, facts, price "from",
 * card photo and the photo for the hover). Shown on the home page now, on Rooms and "Other ways to stay" later.
 * The room pages are the pages under /rooms/; the villa is the page /villa-eudora/ (in every language).
 * The page itself (first screen, photos, description, details): amour_register_room_fields() below.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ids of a page in every language.
 *
 * @param string $path Page path in the default language.
 * @return int[]
 */
function amour_core_page_ids( $path ) {
	$page = get_page_by_path( $path );
	if ( ! $page ) {
		return array();
	}
	$ids = array( (int) $page->ID );
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$ids = array_merge( $ids, array_map( 'intval', array_values( pll_get_post_translations( $page->ID ) ) ) );
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Where the room fields show: the pages under /rooms/ and the villa page, in every language.
 *
 * @return array<int, array<int, array<string, string>>> ACF location rules.
 */
function amour_room_locations() {
	$location = array();
	foreach ( amour_core_page_ids( 'rooms' ) as $id ) {
		$location[] = array(
			array(
				'param'    => 'page_parent',
				'operator' => '==',
				'value'    => (string) $id,
			),
		);
	}
	foreach ( amour_core_page_ids( 'villa-eudora' ) as $id ) {
		$location[] = array(
			array(
				'param'    => 'page',
				'operator' => '==',
				'value'    => (string) $id,
			),
		);
	}
	return $location;
}

/**
 * Register the card field group.
 */
function amour_register_card_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$location = amour_room_locations();
	if ( ! $location ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_card',
			'title'                 => __( 'Card (home page, Rooms, other ways to stay)', 'amour' ),
			'location'              => $location,
			'position'              => 'side',
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'          => 'field_amour_card_name',
					'name'         => 'card_name',
					'label'        => __( 'Short name', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'For example Standard, Deluxe, Villa Eudora.', 'amour' ),
				),
				array(
					'key'          => 'field_amour_card_facts',
					'name'         => 'card_facts',
					'label'        => __( 'Facts on the card (up to 3)', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'table',
					'max'          => 3,
					'button_label' => __( 'Add a fact', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'     => 'field_amour_card_fact_icon',
							'name'    => 'icon',
							'label'   => __( 'Icon', 'amour' ),
							'type'    => 'select',
							'choices' => array(
								'area'   => __( 'Area', 'amour' ),
								'guests' => __( 'Guests', 'amour' ),
								'bed'    => __( 'Bed or bedrooms', 'amour' ),
								'water'  => __( 'Water: view or pool', 'amour' ),
							),
						),
						array(
							'key'   => 'field_amour_card_fact_text',
							'name'  => 'text',
							'label' => __( 'Text', 'amour' ),
							'type'  => 'text',
						),
					),
				),
				array(
					'key'          => 'field_amour_price_from',
					'name'         => 'price_from',
					'label'        => __( 'Price from, € a night', 'amour' ),
					'type'         => 'number',
					'min'          => 0,
					'instructions' => __( 'The lowest price of the season. Shown as "from €… a night".', 'amour' ),
				),
				array(
					'key'           => 'field_amour_card_photo',
					'name'          => 'card_photo',
					'label'         => __( 'Card photo', 'amour' ),
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'thumbnail',
				),
				array(
					'key'           => 'field_amour_card_photo_hover',
					'name'          => 'card_photo_hover',
					'label'         => __( 'Second photo, shown when the pointer is on the card (desktop)', 'amour' ),
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'thumbnail',
				),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_card_fields' );

/**
 * Fields of a room page (HANDOFF, section 9: "Тип номера"; board Room-*, design/generators/room_template.html):
 * the wine first screen, the photos that rise over it, the description and the details.
 * The villa page uses the same fields and adds its own (week 3, villa).
 */
function amour_register_room_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$location = amour_room_locations();
	if ( ! $location ) {
		return;
	}
	$tab = static fn( $key, $label ) => array(
		'key'       => 'field_amour_room_tab_' . $key,
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_room',
			'title'                 => __( 'Room page', 'amour' ),
			'location'              => $location,
			'position'              => 'acf_after_title',
			'hide_on_screen'        => array( 'the_content' ),
			'instruction_placement' => 'field',
			'fields'                => array(
				$tab( 'screen', __( 'First screen', 'amour' ) ),
				array(
					'key'          => 'field_amour_room_kicker',
					'name'         => 'room_kicker',
					'label'        => __( 'Label above the name (capitals, up to 4 words)', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'For example Queen room · pool view. The big name is the short name of the card.', 'amour' ),
				),
				array(
					'key'          => 'field_amour_room_meta',
					'name'         => 'room_meta',
					'label'        => __( 'Line under the name', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'For example 32 m² · queen bed · kitchen · sitting corner · terrace.', 'amour' ),
				),
				array(
					'key'           => 'field_amour_room_cue',
					'name'          => 'room_cue',
					'label'         => __( 'Hint to scroll', 'amour' ),
					'type'          => 'text',
					'default_value' => 'Scroll to see the room',
				),
				$tab( 'photos', __( 'Photos', 'amour' ) ),
				array(
					'key'          => 'field_amour_room_gallery',
					'name'         => 'room_gallery',
					'label'        => __( 'Photos that rise over the name, in order', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Add a photo', 'amour' ),
					'instructions' => __( 'Upright photos come out narrower, wide ones wider; they take turns left and right.', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'           => 'field_amour_room_photo',
							'name'          => 'photo',
							'label'         => __( 'Photo', 'amour' ),
							'type'          => 'image',
							'return_format' => 'id',
							'preview_size'  => 'thumbnail',
						),
						array(
							'key'   => 'field_amour_room_photo_caption',
							'name'  => 'caption',
							'label' => __( 'Caption', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'           => 'field_amour_room_photo_shape',
							'name'          => 'shape',
							'label'         => __( 'Shape', 'amour' ),
							'type'          => 'select',
							'choices'       => array(
								'auto'    => __( 'As the photo', 'amour' ),
								'wide'    => __( 'Wide, 3:2', 'amour' ),
								'upright' => __( 'Upright, 3:4', 'amour' ),
							),
							'default_value' => 'auto',
						),
					),
				),
				$tab( 'details', __( 'Details', 'amour' ) ),
				array(
					'key'           => 'field_amour_room_details_title',
					'name'          => 'room_details_title',
					'label'         => __( 'Heading', 'amour' ),
					'type'          => 'text',
					'default_value' => 'Room details',
				),
				array(
					'key'           => 'field_amour_room_desc_label',
					'name'          => 'room_desc_label',
					'label'         => __( 'Label of the description (capitals)', 'amour' ),
					'type'          => 'text',
					'default_value' => 'The room',
				),
				array(
					'key'   => 'field_amour_room_description',
					'name'  => 'room_description',
					'label' => __( 'Description', 'amour' ),
					'type'  => 'textarea',
					'rows'  => 4,
				),
				array(
					'key'          => 'field_amour_room_facts',
					'name'         => 'room_facts',
					'label'        => __( 'Details', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Add a detail', 'amour' ),
					'instructions' => __( 'The first six are the key facts. The rest open with All details, followed by the rules for every room from Hotel settings.', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'   => 'field_amour_room_fact_label',
							'name'  => 'label',
							'label' => __( 'What', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'   => 'field_amour_room_fact_value',
							'name'  => 'value',
							'label' => __( 'Value', 'amour' ),
							'type'  => 'text',
						),
					),
				),
				$tab( 'booking', __( 'Booking', 'amour' ) ),
				array(
					'key'          => 'field_amour_room_engine_code',
					'name'         => 'room_engine_code',
					'label'        => __( 'Room code in the booking engine', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'Filled in when the booking engine is connected (week 5). The price "from" is in the card on the right.', 'amour' ),
				),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_room_fields' );
