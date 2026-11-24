<?php
/**
 * Fields of the smaller pages (boards FAQ and Contact; HANDOFF, section 6): their headings and texts.
 * The questions themselves are in Questions; the contacts (WhatsApp, phone, email, address) in Hotel settings.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Location rules for a page in every language.
 *
 * @param string $path Page path in the default language.
 * @return array<int, array<int, array<string, string>>>
 */
function amour_core_page_location( $path ) {
	return array_map(
		static fn( $id ) => array(
			array(
				'param'    => 'page',
				'operator' => '==',
				'value'    => (string) $id,
			),
		),
		amour_core_page_ids( $path )
	);
}

/**
 * A simple field.
 *
 * @param string $prefix Key prefix, for example faq.
 * @param string $key    Field key and name after the prefix.
 * @param string $label  Label.
 * @param string $type   Field type.
 * @param array  $extra  More settings.
 * @return array
 */
function amour_core_field( $prefix, $key, $label, $type = 'text', $extra = array() ) {
	return array_merge(
		array(
			'key'   => "field_amour_{$prefix}_{$key}",
			'name'  => "{$prefix}_{$key}",
			'label' => $label,
			'type'  => $type,
		),
		'textarea' === $type ? array( 'rows' => 2 ) : array(),
		'image' === $type ? array(
			'return_format' => 'id',
			'preview_size'  => 'thumbnail',
		) : array(),
		$extra
	);
}

/**
 * Register the FAQ and Contact field groups.
 */
function amour_register_page_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$faq = amour_core_page_location( 'questions' );
	if ( $faq ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_amour_faq',
				'title'                 => __( 'Questions page', 'amour' ),
				'location'              => $faq,
				'position'              => 'acf_after_title',
				'hide_on_screen'        => array( 'the_content' ),
				'instruction_placement' => 'field',
				'fields'                => array(
					amour_core_field( 'faq', 'title', __( 'Heading', 'amour' ) ),
					amour_core_field( 'faq', 'text', __( 'Text next to it', 'amour' ), 'textarea' ),
					amour_core_field( 'faq', 'note', __( 'Small print under the questions', 'amour' ), 'textarea' ),
					amour_core_field( 'faq', 'cta', __( 'Heading of the box at the bottom (WhatsApp and email)', 'amour' ) ),
					array(
						'key'     => 'field_amour_faq_where',
						'label'   => __( 'The questions', 'amour' ),
						'type'    => 'message',
						'message' => __( 'The questions and their topics are in Questions, in the menu on the left.', 'amour' ),
					),
				),
			)
		);
	}

	$contact = amour_core_page_location( 'contacts' );
	if ( $contact ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_amour_contact',
				'title'                 => __( 'Contact page', 'amour' ),
				'location'              => $contact,
				'position'              => 'acf_after_title',
				'hide_on_screen'        => array( 'the_content' ),
				'instruction_placement' => 'field',
				'fields'                => array(
					array(
						'key'       => 'field_amour_ct_tab_top',
						'label'     => __( 'Top', 'amour' ),
						'type'      => 'tab',
						'placement' => 'top',
					),
					amour_core_field( 'ct', 'title', __( 'Heading', 'amour' ) ),
					array(
						'key'          => 'field_amour_ct_hosts',
						'name'         => 'ct_hosts',
						'label'        => __( 'The hosts', 'amour' ),
						'type'         => 'repeater',
						'layout'       => 'table',
						'max'          => 2,
						'button_label' => __( 'Add a host', 'amour' ),
						'sub_fields'   => array(
							array(
								'key'   => 'field_amour_ct_host_name',
								'name'  => 'name',
								'label' => __( 'Name', 'amour' ),
								'type'  => 'text',
							),
							array(
								'key'           => 'field_amour_ct_host_photo',
								'name'          => 'photo',
								'label'         => __( 'Photo (square)', 'amour' ),
								'type'          => 'image',
								'return_format' => 'id',
								'preview_size'  => 'thumbnail',
							),
						),
					),
					amour_core_field( 'ct', 'hosts_line', __( 'Line under the hosts', 'amour' ), 'text', array( 'instructions' => __( 'For example On site every day · English, Greek, German.', 'amour' ) ) ),
					amour_core_field( 'ct', 'text', __( 'Text', 'amour' ), 'textarea' ),
					amour_core_field( 'ct', 'hours', __( 'Hosts on site (hours)', 'amour' ), 'text', array( 'instructions' => __( 'For example from 8:00 to 22:00.', 'amour' ) ) ),
					array(
						'key'       => 'field_amour_ct_tab_routes',
						'label'     => __( 'Getting here', 'amour' ),
						'type'      => 'tab',
						'placement' => 'top',
					),
					amour_core_field( 'ct', 'routes_title', __( 'Heading', 'amour' ) ),
					array(
						'key'          => 'field_amour_ct_routes',
						'name'         => 'ct_routes',
						'label'        => __( 'Ways to come', 'amour' ),
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add a way', 'amour' ),
						'sub_fields'   => array(
							array(
								'key'   => 'field_amour_ct_route_by',
								'name'  => 'by',
								'label' => __( 'By (capitals)', 'amour' ),
								'type'  => 'text',
							),
							array(
								'key'   => 'field_amour_ct_route_from',
								'name'  => 'from',
								'label' => __( 'From', 'amour' ),
								'type'  => 'text',
							),
							array(
								'key'   => 'field_amour_ct_route_text',
								'name'  => 'text',
								'label' => __( 'Text', 'amour' ),
								'type'  => 'textarea',
								'rows'  => 2,
							),
						),
					),
					array(
						'key'       => 'field_amour_ct_tab_form',
						'label'     => __( 'The form', 'amour' ),
						'type'      => 'tab',
						'placement' => 'top',
					),
					amour_core_field( 'ct', 'form_title', __( 'Heading', 'amour' ) ),
					amour_core_field( 'ct', 'form_text', __( 'Text', 'amour' ), 'textarea' ),
					amour_core_field( 'ct', 'reply', __( 'How soon you reply', 'amour' ), 'text', array( 'instructions' => __( 'For example 24 hours. Shown in the text and in the thank-you line. The message goes to the public email in Hotel settings.', 'amour' ) ) ),
				),
			)
		);
	}
}
add_action( 'acf/init', 'amour_register_page_fields' );
