<?php
/**
 * Fields of the Crete guide (board Guide; HANDOFF, section 6 "Crete guide"): the opening, Almyrida bay,
 * the Days out band and Getting here and around. The map and the day trips come from Places.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the guide field group.
 */
function amour_register_guide_fields() {
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
		amour_core_page_ids( 'crete-guide' )
	);
	if ( ! $location ) {
		return;
	}
	$tab   = static fn( $key, $label ) => array(
		'key'       => 'field_amour_guide_tab_' . $key,
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);
	$field = static fn( $key, $label, $type = 'text' ) => array_merge(
		array(
			'key'   => 'field_amour_guide_' . $key,
			'name'  => 'guide_' . $key,
			'label' => $label,
			'type'  => $type,
		),
		'textarea' === $type ? array( 'rows' => 2 ) : array(),
		'image' === $type ? array(
			'return_format' => 'id',
			'preview_size'  => 'thumbnail',
		) : array()
	);
	$pairs = static fn( $key, $label, $first, $second ) => array(
		'key'          => 'field_amour_guide_' . $key,
		'name'         => 'guide_' . $key,
		'label'        => $label,
		'type'         => 'repeater',
		'layout'       => 'table',
		'button_label' => __( 'Add', 'amour' ),
		'sub_fields'   => array(
			array(
				'key'   => 'field_amour_guide_' . $key . '_a',
				'name'  => 'a',
				'label' => $first,
				'type'  => 'text',
			),
			array(
				'key'   => 'field_amour_guide_' . $key . '_b',
				'name'  => 'b',
				'label' => $second,
				'type'  => 'textarea',
				'rows'  => 2,
			),
		),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_guide',
			'title'                 => __( 'Crete guide', 'amour' ),
			'location'              => $location,
			'position'              => 'acf_after_title',
			'hide_on_screen'        => array( 'the_content' ),
			'instruction_placement' => 'field',
			'fields'                => array(
				$tab( 'intro', __( 'Opening', 'amour' ) ),
				$field( 'title', __( 'Heading', 'amour' ) ),
				$field( 'text', __( 'Text next to it', 'amour' ), 'textarea' ),
				array(
					'key'     => 'field_amour_guide_note',
					'label'   => __( 'The map', 'amour' ),
					'type'    => 'message',
					'message' => __( 'The map and the Days out cards show the places from Places (on the home page map): their names, drive times, texts, photos, roads and the short note for the card.', 'amour' ),
				),

				$tab( 'bay', __( 'Almyrida bay', 'amour' ) ),
				$field( 'bay_label', __( 'Label (capitals)', 'amour' ) ),
				$field( 'bay_title', __( 'Heading', 'amour' ) ),
				$field( 'bay_text', __( 'Text', 'amour' ), 'textarea' ),
				$field( 'bay_short', __( 'Shorter text for a phone', 'amour' ), 'textarea' ),
				$field( 'bay_photo', __( 'Photo', 'amour' ), 'image' ),
				$pairs( 'bay_facts', __( 'Distances (table)', 'amour' ), __( 'Place', 'amour' ), __( 'How far', 'amour' ) ),
				$pairs( 'bay_figures', __( 'Two figures on a phone', 'amour' ), __( 'Figure', 'amour' ), __( 'What it means', 'amour' ) ),

				$tab( 'days', __( 'Days out', 'amour' ) ),
				$field( 'days_title', __( 'Big word on the photo', 'amour' ) ),
				$field( 'days_text', __( 'Text', 'amour' ), 'textarea' ),
				$field( 'days_photo', __( 'Photo', 'amour' ), 'image' ),

				$tab( 'getting', __( 'Getting here', 'amour' ) ),
				$field( 'getting_title', __( 'Heading', 'amour' ) ),
				$pairs( 'getting', __( 'Ways to come', 'amour' ), __( 'Title', 'amour' ), __( 'Text', 'amour' ) ),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_guide_fields' );
