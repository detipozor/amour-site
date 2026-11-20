<?php
/**
 * Card of a room type or the villa (HANDOFF, section 9: "Тип номера", "Вилла" — name, facts, price "from",
 * card photo and the photo for the hover). Shown on the home page now, on Rooms and "Other ways to stay" later.
 * The room pages are the pages under /rooms/; the villa is the page /villa-eudora/ (in every language).
 * The rest of the room and villa model comes with their pages (week 3).
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
 * Register the card field group.
 */
function amour_register_card_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

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
