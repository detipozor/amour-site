<?php
/**
 * Reviews (HANDOFF, section 9: "Отзыв"): guest name, country, month and year, room, the text word for word,
 * the score and a link to the review. Only real reviews from Booking.com, never made up (CLAUDE.md).
 * Reviews stay in the language they were written in, so they are not translated: both languages show them.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the review type.
 */
function amour_register_reviews() {
	register_post_type(
		'amour_review',
		array(
			'labels'        => array(
				'name'          => __( 'Reviews', 'amour' ),
				'singular_name' => __( 'Review', 'amour' ),
				'add_new_item'  => __( 'Add a review', 'amour' ),
				'edit_item'     => __( 'Edit review', 'amour' ),
				'menu_name'     => __( 'Reviews', 'amour' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-format-quote',
			'menu_position' => 22,
			'supports'      => array( 'title', 'page-attributes' ),
			'hierarchical'  => false,
		)
	);
}
add_action( 'init', 'amour_register_reviews' );

/**
 * The title of a review is the guest's name.
 *
 * @param string  $text Placeholder of the title field.
 * @param WP_Post $post Post being edited.
 * @return string
 */
function amour_review_title_placeholder( $text, $post ) {
	return 'amour_review' === $post->post_type ? __( 'Guest name, as on Booking.com', 'amour' ) : $text;
}
add_filter( 'enter_title_here', 'amour_review_title_placeholder', 10, 2 );

/**
 * Fields of a review.
 */
function amour_register_review_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_review',
			'title'                 => __( 'Review', 'amour' ),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'amour_review',
					),
				),
			),
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'          => 'field_amour_review_text',
					'name'         => 'text',
					'label'        => __( 'Review, word for word', 'amour' ),
					'type'         => 'textarea',
					'rows'         => 4,
					'instructions' => __( 'Copy it from Booking.com exactly as the guest wrote it; do not shorten or correct it.', 'amour' ),
				),
				array(
					'key'   => 'field_amour_review_country',
					'name'  => 'country',
					'label' => __( 'Country', 'amour' ),
					'type'  => 'text',
				),
				array(
					'key'            => 'field_amour_review_month',
					'name'           => 'month',
					'label'          => __( 'Month of the stay', 'amour' ),
					'type'           => 'date_picker',
					'display_format' => 'F Y',
					'return_format'  => 'Ymd',
					'instructions'   => __( 'Any day of that month: the site shows the month and the year.', 'amour' ),
				),
				array(
					'key'           => 'field_amour_review_room',
					'name'          => 'room',
					'label'         => __( 'Room or villa', 'amour' ),
					'type'          => 'post_object',
					'post_type'     => array( 'page' ),
					'return_format' => 'id',
					'allow_null'    => 1,
				),
				array(
					'key'   => 'field_amour_review_score',
					'name'  => 'score',
					'label' => __( 'Score out of 10', 'amour' ),
					'type'  => 'number',
					'min'   => 1,
					'max'   => 10,
					'step'  => 0.1,
				),
				array(
					'key'          => 'field_amour_review_source',
					'name'         => 'source',
					'label'        => __( 'Link to the review', 'amour' ),
					'type'         => 'url',
					'instructions' => __( 'Where the review can be read on Booking.com.', 'amour' ),
				),
				array(
					'key'           => 'field_amour_review_home',
					'name'          => 'on_home',
					'label'         => __( 'On the home page', 'amour' ),
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_review_fields' );
