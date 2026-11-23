<?php
/**
 * Fields of the Court and pool page (board Estate; HANDOFF, section 6 "Court and pool"): the first screen with the
 * court video, the court from day to night, the pool in three points, the estate from above with six places,
 * the closing line. The outlines of the six places are drawn on the aerial photo, so they live in the theme
 * (template-parts/estate-plan.php); their names, texts and photos are here, in the same order.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the Court and pool field group.
 */
function amour_register_estate_fields() {
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
		amour_core_page_ids( 'court-and-pool' )
	);
	if ( ! $location ) {
		return;
	}
	$tab   = static fn( $key, $label ) => array(
		'key'       => 'field_amour_estate_tab_' . $key,
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);
	$field = static fn( $key, $label, $type = 'text', $extra = array() ) => array_merge(
		array(
			'key'   => 'field_amour_estate_' . $key,
			'name'  => 'estate_' . $key,
			'label' => $label,
			'type'  => $type,
		),
		'textarea' === $type ? array( 'rows' => 2 ) : array(),
		in_array( $type, array( 'image', 'file' ), true ) ? array( 'return_format' => 'id' ) : array(),
		$extra
	);
	$rows  = static fn( $key, $label, $min, $max, $subs ) => array(
		'key'          => 'field_amour_estate_' . $key,
		'name'         => 'estate_' . $key,
		'label'        => $label,
		'type'         => 'repeater',
		'layout'       => 'block',
		'min'          => $min,
		'max'          => $max,
		'button_label' => __( 'Add', 'amour' ),
		'sub_fields'   => array_map(
			static fn( $sub ) => array_merge(
				array(
					'key'   => 'field_amour_estate_' . $key . '_' . $sub[0],
					'name'  => $sub[0],
					'label' => $sub[1],
					'type'  => $sub[2],
				),
				'image' === $sub[2] ? array(
					'return_format' => 'id',
					'preview_size'  => 'thumbnail',
				) : array(),
				'textarea' === $sub[2] ? array( 'rows' => 2 ) : array()
			),
			$subs
		),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_estate',
			'title'                 => __( 'Court and pool', 'amour' ),
			'location'              => $location,
			'position'              => 'acf_after_title',
			'hide_on_screen'        => array( 'the_content' ),
			'instruction_placement' => 'field',
			'fields'                => array(
				$tab( 'hero', __( 'First screen', 'amour' ) ),
				$field( 'title', __( 'Heading', 'amour' ) ),
				$field( 'text', __( 'Text', 'amour' ), 'textarea' ),
				$field( 'facts', __( 'Line of facts', 'amour' ), 'text', array( 'instructions' => __( 'For example Free · floodlit · rackets to borrow · 07:00–23:00.', 'amour' ) ) ),
				$field( 'video', __( 'Video of the court (MP4)', 'amour' ), 'file', array( 'mime_types' => 'mp4' ) ),
				$field( 'poster', __( 'Photo shown before the video and with reduced motion', 'amour' ), 'image' ),
				$field( 'video_label', __( 'What the video shows (for screen readers)', 'amour' ) ),

				$tab( 'court', __( 'The court', 'amour' ) ),
				$field( 'court_label', __( 'Label (capitals)', 'amour' ) ),
				$field( 'court_title', __( 'Heading: day line, then night line', 'amour' ), 'textarea' ),
				$field( 'court_text', __( 'Text in the glass card', 'amour' ), 'textarea' ),
				$field( 'court_day', __( 'Photo by day', 'amour' ), 'image' ),
				$field( 'court_night', __( 'Photo at night', 'amour' ), 'image' ),

				$tab( 'pool', __( 'The pool', 'amour' ) ),
				$field( 'pool_label', __( 'Label (capitals)', 'amour' ) ),
				$field( 'pool_title', __( 'Heading', 'amour' ) ),
				$field( 'pool_text', __( 'Text', 'amour' ), 'textarea' ),
				$rows(
					'pool_points',
					__( 'Three points, each with its photo', 'amour' ),
					3,
					3,
					array(
						array( 'title', __( 'Title', 'amour' ), 'text' ),
						array( 'text', __( 'Text', 'amour' ), 'textarea' ),
						array( 'photo', __( 'Photo', 'amour' ), 'image' ),
					)
				),

				$tab( 'plan', __( 'From above', 'amour' ) ),
				$field( 'plan_label', __( 'Label (capitals)', 'amour' ) ),
				$field( 'plan_title', __( 'Heading', 'amour' ) ),
				$field( 'plan_text', __( 'Text', 'amour' ), 'textarea' ),
				$field( 'plan_photo', __( 'Aerial photo', 'amour' ), 'image', array( 'instructions' => __( 'The outlines of the places are drawn on this photo: change it only together with the outlines.', 'amour' ) ) ),
				$rows(
					'places',
					__( 'Places on the photo, in this order: pool, tennis court, daybeds, barbecue, lawns, olive grove', 'amour' ),
					6,
					6,
					array(
						array( 'name', __( 'Name', 'amour' ), 'text' ),
						array( 'text', __( 'Text', 'amour' ), 'textarea' ),
						array( 'photo', __( 'Photo (optional)', 'amour' ), 'image' ),
					)
				),

				$tab( 'end', __( 'Closing', 'amour' ) ),
				$field( 'end_label', __( 'Label (capitals)', 'amour' ) ),
				$field( 'end_title', __( 'Heading', 'amour' ) ),
				$field( 'end_text', __( 'Text', 'amour' ), 'textarea' ),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_estate_fields' );
