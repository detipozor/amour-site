<?php
/**
 * Fields of the booking (HANDOFF, sections 7 and 9; board Book): the season, the cancellation rule, the climate fee
 * and the booking engine in Hotel settings; how each room and the villa shows on the booking page; the villa's
 * prices by season; the texts of the booking page.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the booking field groups.
 */
function amour_register_booking_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_booking',
			'title'                 => __( 'Booking (the booking page, the price line, the thank-you page)', 'amour' ),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'amour-hotel',
					),
				),
			),
			'menu_order'            => 8,
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'            => 'field_amour_season_first',
					'name'           => 'amour_season_first',
					'label'          => __( 'First night of the season', 'amour' ),
					'type'           => 'date_picker',
					'display_format' => 'j F Y',
					'return_format'  => 'Ymd',
					'instructions'   => __( 'The booking calendar opens on this night.', 'amour' ),
				),
				array(
					'key'            => 'field_amour_season_last',
					'name'           => 'amour_season_last',
					'label'          => __( 'Last night of the season', 'amour' ),
					'type'           => 'date_picker',
					'display_format' => 'j F Y',
					'return_format'  => 'Ymd',
					'instructions'   => __( 'Guests can leave the morning after it.', 'amour' ),
				),
				array(
					'key'          => 'field_amour_cancel_days',
					'name'         => 'amour_cancel_days',
					'label'        => __( 'Free cancellation until … days before arrival', 'amour' ),
					'type'         => 'number',
					'min'          => 0,
					'instructions' => __( 'Shown next to every price on the booking page.', 'amour' ),
				),
				array(
					'key'          => 'field_amour_climate_fee',
					'name'         => 'amour_climate_fee',
					'label'        => __( 'Climate fee a night', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'For example €1.50. Paid at the hotel; shown on the thank-you page.', 'amour' ),
				),
				array(
					'key'          => 'field_amour_engine_url',
					'name'         => 'amour_engine_url',
					'label'        => __( 'Booking engine address', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'From HotelAvailabilities. Write {arrive} and {leave} (YYYY-MM-DD), {guests} and {room} (the room code on each room page) where the dates, guests and room go. Empty: a test copy uses the test engine; the live site sends guests to the contact page.', 'amour' ),
				),
			),
		)
	);

	$units = function_exists( 'amour_room_locations' ) ? amour_room_locations() : array();
	if ( $units ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_amour_book_unit',
				'title'                 => __( 'On the booking page', 'amour' ),
				'location'              => $units,
				'position'              => 'side',
				'menu_order'            => 1,
				'instruction_placement' => 'field',
				'fields'                => array(
					array(
						'key'          => 'field_amour_max_guests',
						'name'         => 'max_guests',
						'label'        => __( 'Guests at most', 'amour' ),
						'type'         => 'number',
						'min'          => 1,
						'instructions' => __( 'The search shows this room only for this many guests or fewer.', 'amour' ),
					),
					array(
						'key'          => 'field_amour_book_facts',
						'name'         => 'book_facts',
						'label'        => __( 'Two facts', 'amour' ),
						'type'         => 'text',
						'instructions' => __( 'Divided by ·, for example 20 m² · 2 guests.', 'amour' ),
					),
					array(
						'key'   => 'field_amour_book_text',
						'name'  => 'book_text',
						'label' => __( 'One line about it', 'amour' ),
						'type'  => 'textarea',
						'rows'  => 2,
					),
					array(
						'key'           => 'field_amour_book_photo',
						'name'          => 'book_photo',
						'label'         => __( 'Photo (landscape, 3:2)', 'amour' ),
						'type'          => 'image',
						'return_format' => 'id',
						'preview_size'  => 'thumbnail',
					),
				),
			)
		);
	}

	$villa = function_exists( 'amour_core_page_location' ) ? amour_core_page_location( 'villa-eudora' ) : array();
	if ( $villa ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_amour_villa_prices',
				'title'                 => __( 'Villa prices', 'amour' ),
				'location'              => $villa,
				'menu_order'            => 20,
				'instruction_placement' => 'field',
				'fields'                => array(
					array(
						'key'          => 'field_amour_villa_seasons',
						'name'         => 'villa_seasons',
						'label'        => __( 'Price a night by season', 'amour' ),
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add a season', 'amour' ),
						'instructions' => __( 'Until the booking engine gives the prices, the booking page counts the villa by these.', 'amour' ),
						'sub_fields'   => array(
							array(
								'key'            => 'field_amour_villa_season_first',
								'name'           => 'first',
								'label'          => __( 'First night', 'amour' ),
								'type'           => 'date_picker',
								'display_format' => 'j F Y',
								'return_format'  => 'Ymd',
							),
							array(
								'key'            => 'field_amour_villa_season_last',
								'name'           => 'last',
								'label'          => __( 'Last night', 'amour' ),
								'type'           => 'date_picker',
								'display_format' => 'j F Y',
								'return_format'  => 'Ymd',
							),
							array(
								'key'   => 'field_amour_villa_season_price',
								'name'  => 'price',
								'label' => __( '€ a night', 'amour' ),
								'type'  => 'number',
								'min'   => 0,
							),
						),
					),
					array(
						'key'          => 'field_amour_villa_min_nights',
						'name'         => 'villa_min_nights',
						'label'        => __( 'Nights at least', 'amour' ),
						'type'         => 'number',
						'min'          => 1,
						'instructions' => __( 'Shorter stays do not show the villa in the search.', 'amour' ),
					),
				),
			)
		);
	}

	$book = function_exists( 'amour_core_page_location' ) ? amour_core_page_location( 'book' ) : array();
	if ( $book ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_amour_book_page',
				'title'                 => __( 'Booking page', 'amour' ),
				'location'              => $book,
				'position'              => 'acf_after_title',
				'hide_on_screen'        => array( 'the_content' ),
				'instruction_placement' => 'field',
				'fields'                => array(
					array(
						'key'   => 'field_amour_bk_lead',
						'name'  => 'bk_lead',
						'label' => __( 'Line under the heading', 'amour' ),
						'type'  => 'textarea',
						'rows'  => 2,
					),
					array(
						'key'          => 'field_amour_bk_hint',
						'name'         => 'bk_hint',
						'label'        => __( 'Line before the search', 'amour' ),
						'type'         => 'textarea',
						'rows'         => 2,
						'instructions' => __( 'The rooms, the villa, their prices and the season are on their own pages and in Hotel settings → Booking.', 'amour' ),
					),
				),
			)
		);
	}
}
add_action( 'acf/init', 'amour_register_booking_fields' );
