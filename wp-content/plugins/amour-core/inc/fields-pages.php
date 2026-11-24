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

	$about = amour_core_page_location( 'about-us' );
	if ( $about ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_amour_about',
				'title'                 => __( 'About page', 'amour' ),
				'location'              => $about,
				'position'              => 'acf_after_title',
				'hide_on_screen'        => array( 'the_content' ),
				'instruction_placement' => 'field',
				'fields'                => array(
					amour_core_field( 'ab', 'title', __( 'Heading', 'amour' ) ),
					amour_core_field( 'ab', 'text', __( 'Text under it', 'amour' ), 'textarea' ),
					amour_core_field( 'ab', 'photo', __( 'Photo in the arch (upright)', 'amour' ), 'image' ),
					amour_core_field( 'ab', 'label', __( 'Label of the philosophy (capitals)', 'amour' ) ),
					amour_core_field( 'ab', 'phrase', __( 'Philosophy (lights up word by word)', 'amour' ), 'textarea', array( 'rows' => 3 ) ),
					array(
						'key'          => 'field_amour_ab_gallery',
						'name'         => 'ab_gallery',
						'label'        => __( 'Three photos', 'amour' ),
						'type'         => 'repeater',
						'layout'       => 'table',
						'max'          => 3,
						'button_label' => __( 'Add a photo', 'amour' ),
						'sub_fields'   => array(
							array(
								'key'           => 'field_amour_ab_gallery_photo',
								'name'          => 'photo',
								'label'         => __( 'Photo (upright)', 'amour' ),
								'type'          => 'image',
								'return_format' => 'id',
								'preview_size'  => 'thumbnail',
							),
							array(
								'key'   => 'field_amour_ab_gallery_caption',
								'name'  => 'caption',
								'label' => __( 'Caption', 'amour' ),
								'type'  => 'text',
							),
						),
					),
					amour_core_field( 'ab', 'cta', __( 'Heading of the box at the bottom', 'amour' ) ),
				),
			)
		);
	}

	$legal = array_merge( amour_core_page_location( 'privacy-policy' ), amour_core_page_location( 'cookies' ), amour_core_page_location( 'booking-terms' ) );
	if ( $legal ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_amour_legal',
				'title'                 => __( 'Legal page', 'amour' ),
				'location'              => $legal,
				'position'              => 'acf_after_title',
				'instruction_placement' => 'field',
				'fields'                => array(
					amour_core_field( 'lg', 'lead', __( 'One line under the heading', 'amour' ), 'textarea' ),
					amour_core_field(
						'lg',
						'updated',
						__( 'Last updated', 'amour' ),
						'date_picker',
						array(
							'display_format' => 'j F Y',
							'return_format'  => 'Ymd',
							'instructions'   => __( 'The text is below, in the editor. Each heading (Heading 2) becomes a line of the contents on the side.', 'amour' ),
						)
					),
				),
			)
		);
	}

	$thanks = amour_core_page_location( 'thank-you' );
	if ( $thanks ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_amour_thanks',
				'title'                 => __( 'Thank-you page', 'amour' ),
				'location'              => $thanks,
				'position'              => 'acf_after_title',
				'hide_on_screen'        => array( 'the_content' ),
				'instruction_placement' => 'field',
				'fields'                => array(
					amour_core_field( 'th', 'label', __( 'Label (capitals)', 'amour' ) ),
					amour_core_field( 'th', 'text', __( 'Text under "Thank you, …"', 'amour' ), 'textarea', array( 'instructions' => __( 'The name and the booking summary come from the booking engine.', 'amour' ) ) ),
					amour_core_field( 'th', 'photo', __( 'Photo in the arch (upright)', 'amour' ), 'image' ),
					amour_core_field( 'th', 'next_title', __( 'Heading of the next steps', 'amour' ) ),
					array(
						'key'          => 'field_amour_th_next',
						'name'         => 'th_next',
						'label'        => __( 'Next steps', 'amour' ),
						'type'         => 'repeater',
						'layout'       => 'table',
						'max'          => 3,
						'button_label' => __( 'Add a step', 'amour' ),
						'sub_fields'   => array(
							array(
								'key'   => 'field_amour_th_step_title',
								'name'  => 'title',
								'label' => __( 'Title', 'amour' ),
								'type'  => 'text',
							),
							array(
								'key'   => 'field_amour_th_step_text',
								'name'  => 'text',
								'label' => __( 'Text', 'amour' ),
								'type'  => 'textarea',
								'rows'  => 2,
							),
						),
					),
				),
			)
		);
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_more',
			'title'                 => __( 'Privacy contact and the error page', 'amour' ),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'amour-hotel',
					),
				),
			),
			'menu_order'            => 7,
			'instruction_placement' => 'field',
			'fields'                => array(
				array(
					'key'          => 'field_amour_privacy_email',
					'name'         => 'amour_privacy_email',
					'label'        => __( 'Email for questions about personal data', 'amour' ),
					'type'         => 'email',
					'instructions' => __( 'Shown at the bottom of the legal pages.', 'amour' ),
				),
				array(
					'key'           => 'field_amour_404_photo',
					'name'          => 'amour_404_photo',
					'label'         => __( 'Photo on the "page not found" page (upright)', 'amour' ),
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'thumbnail',
				),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_page_fields' );
