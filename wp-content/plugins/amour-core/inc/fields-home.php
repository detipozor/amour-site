<?php
/**
 * Home page fields (HANDOFF, section 9: "Главная" — block texts, A day at Amour frames, first screen photo and video).
 * Grows block by block with the home page (week 2); each tab is one block of the boards Main-v2 and Main-v2-b.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the home page field group.
 */
function amour_register_home_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$tab = static fn( $key, $label ) => array(
		'key'       => 'field_amour_home_tab_' . $key,
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_amour_home',
			'title'                 => __( 'Home page', 'amour' ),
			'location'              => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
			),
			'position'              => 'acf_after_title',
			'style'                 => 'default',
			'hide_on_screen'        => array( 'the_content' ),
			'instruction_placement' => 'field',
			'fields'                => array(
				$tab( 'hero', __( 'First screen', 'amour' ) ),
				array(
					'key'          => 'field_amour_home_hero_line',
					'name'         => 'home_hero_line',
					'label'        => __( 'Line next to the arch (the main heading of the page)', 'amour' ),
					'type'         => 'textarea',
					'rows'         => 2,
					'new_lines'    => '',
					'instructions' => __( 'One sentence. Search engines read it as the title of the home page.', 'amour' ),
				),
				array(
					'key'   => 'field_amour_home_hero_note',
					'name'  => 'home_hero_note',
					'label' => __( 'Note on the right, above Check dates', 'amour' ),
					'type'  => 'text',
				),
				array(
					'key'           => 'field_amour_home_hero_poster',
					'name'          => 'home_hero_poster',
					'label'         => __( 'Photo in the arch (shown before the video and with reduced motion)', 'amour' ),
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'medium',
				),
				array(
					'key'           => 'field_amour_home_hero_video',
					'name'          => 'home_hero_video',
					'label'         => __( 'Video in the arch (MP4, about 2.5 MB, 1080p)', 'amour' ),
					'type'          => 'file',
					'return_format' => 'id',
					'mime_types'    => 'mp4',
				),
				array(
					'key'           => 'field_amour_home_hero_video_phone',
					'name'          => 'home_hero_video_phone',
					'label'         => __( 'Lighter video for phones (MP4, 720p, optional)', 'amour' ),
					'type'          => 'file',
					'return_format' => 'id',
					'mime_types'    => 'mp4',
				),
				array(
					'key'          => 'field_amour_home_hero_video_label',
					'name'         => 'home_hero_video_label',
					'label'        => __( 'What the video shows (for screen readers)', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'Like the alt text of a photo.', 'amour' ),
				),

				$tab( 'phrase', __( 'Phrase', 'amour' ) ),
				array(
					'key'          => 'field_amour_home_phrase',
					'name'         => 'home_phrase',
					'label'        => __( 'Phrase on the wine block', 'amour' ),
					'type'         => 'textarea',
					'rows'         => 3,
					'new_lines'    => '',
					'instructions' => __( 'The words light up one by one as the visitor scrolls.', 'amour' ),
				),

				$tab( 'estate', __( 'The estate', 'amour' ) ),
				array(
					'key'       => 'field_amour_home_estate_label',
					'name'      => 'home_estate_label',
					'label'     => __( 'Label (capitals, up to 4 words)', 'amour' ),
					'type'      => 'text',
					'maxlength' => 40,
				),
				array(
					'key'   => 'field_amour_home_estate_title',
					'name'  => 'home_estate_title',
					'label' => __( 'Heading', 'amour' ),
					'type'  => 'text',
				),
				array(
					'key'           => 'field_amour_home_estate_photo',
					'name'          => 'home_estate_photo',
					'label'         => __( 'Photo that grows to the full width', 'amour' ),
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'medium',
				),

				$tab( 'day', __( 'A day at Amour', 'amour' ) ),
				array(
					'key'   => 'field_amour_home_day_title',
					'name'  => 'home_day_title',
					'label' => __( 'Heading', 'amour' ),
					'type'  => 'text',
				),
				array(
					'key'          => 'field_amour_home_day_frames',
					'name'         => 'home_day_frames',
					'label'        => __( 'Frames of the day, from morning to evening', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'block',
					'min'          => 2,
					'button_label' => __( 'Add a frame', 'amour' ),
					'instructions' => __( 'The frame in the middle is the colour arch; scrolling moves the strip one frame at a time.', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'           => 'field_amour_home_day_photo',
							'name'          => 'photo',
							'label'         => __( 'Photo (upright)', 'amour' ),
							'type'          => 'image',
							'return_format' => 'id',
							'preview_size'  => 'thumbnail',
							'required'      => 1,
						),
						array(
							'key'       => 'field_amour_home_day_label',
							'name'      => 'label',
							'label'     => __( 'Time of day (capitals, up to 4 words)', 'amour' ),
							'type'      => 'text',
							'maxlength' => 30,
						),
						array(
							'key'   => 'field_amour_home_day_line',
							'name'  => 'line',
							'label' => __( 'Line under the arch', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'           => 'field_amour_home_day_focus',
							'name'          => 'focus',
							'label'         => __( 'Focus of the photo in the arch', 'amour' ),
							'type'          => 'text',
							'default_value' => '50% 50%',
							'instructions'  => __( 'Across and down, for example 42% 50%. Moves the visible part of the photo.', 'amour' ),
						),
					),
				),

				$tab( 'rooms', __( 'Rooms', 'amour' ) ),
				array(
					'key'          => 'field_amour_home_rooms_title',
					'name'         => 'home_rooms_title',
					'label'        => __( 'Heading of the rooms grid', 'amour' ),
					'type'         => 'textarea',
					'rows'         => 2,
					'new_lines'    => '',
					'instructions' => __( 'Each line is a line of the heading. The cards come from the room pages and the villa page (their "Card" fields).', 'amour' ),
				),

				$tab( 'villa', __( 'Villa', 'amour' ) ),
				array(
					'key'          => 'field_amour_home_villa_words',
					'name'         => 'home_villa_words',
					'label'        => __( 'Two words around the arch', 'amour' ),
					'type'         => 'textarea',
					'rows'         => 2,
					'new_lines'    => '',
					'instructions' => __( 'First line on the left (or above on a phone), second on the right (below).', 'amour' ),
				),
				array(
					'key'          => 'field_amour_home_villa_captions',
					'name'         => 'home_villa_captions',
					'label'        => __( 'Two labels under the words (desktop)', 'amour' ),
					'type'         => 'textarea',
					'rows'         => 2,
					'new_lines'    => '',
					'instructions' => __( 'Capitals, up to 4 words each. First line on the left, second on the right.', 'amour' ),
				),
				array(
					'key'       => 'field_amour_home_villa_title',
					'name'      => 'home_villa_title',
					'label'     => __( 'Line over the open photo', 'amour' ),
					'type'      => 'textarea',
					'rows'      => 2,
					'new_lines' => 'br',
				),
				array(
					'key'   => 'field_amour_home_villa_meta',
					'name'  => 'home_villa_meta',
					'label' => __( 'Facts in one line (bottom right)', 'amour' ),
					'type'  => 'text',
				),
				array(
					'key'   => 'field_amour_home_villa_button',
					'name'  => 'home_villa_button',
					'label' => __( 'Button to the villa page', 'amour' ),
					'type'  => 'text',
				),
				array(
					'key'          => 'field_amour_home_villa_link',
					'name'         => 'home_villa_link',
					'label'        => __( 'Second link: gatherings on the villa page', 'amour' ),
					'type'         => 'text',
					'instructions' => __( 'Leads to the gatherings block of the villa page.', 'amour' ),
				),

				$tab( 'map', __( 'Map', 'amour' ) ),
				array(
					'key'       => 'field_amour_home_map_intro_label',
					'name'      => 'home_map_intro_label',
					'label'     => __( 'Opening: label (capitals, up to 4 words)', 'amour' ),
					'type'      => 'text',
					'maxlength' => 40,
				),
				array(
					'key'   => 'field_amour_home_map_intro_title',
					'name'  => 'home_map_intro_title',
					'label' => __( 'Opening: heading', 'amour' ),
					'type'  => 'text',
				),
				array(
					'key'          => 'field_amour_home_map_intro_photos',
					'name'         => 'home_map_intro_photos',
					'label'        => __( 'Opening: photos that part before the camera drops onto the island', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'table',
					'max'          => 6,
					'button_label' => __( 'Add a photo', 'amour' ),
					'instructions' => __( 'Up to 6 (a phone shows the first 4). Their places on the screen follow the design.', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'           => 'field_amour_home_map_intro_photo',
							'name'          => 'photo',
							'label'         => __( 'Photo', 'amour' ),
							'type'          => 'image',
							'return_format' => 'id',
							'preview_size'  => 'thumbnail',
						),
						array(
							'key'   => 'field_amour_home_map_intro_caption',
							'name'  => 'caption',
							'label' => __( 'Place name under it (or empty)', 'amour' ),
							'type'  => 'text',
						),
					),
				),
				array(
					'key'          => 'field_amour_home_map_views',
					'name'         => 'home_map_views',
					'label'        => __( 'The four views: whole Crete, the hotel, the villa, day trips', 'amour' ),
					'type'         => 'repeater',
					'layout'       => 'block',
					'min'          => 4,
					'max'          => 4,
					'instructions' => __( 'The card at the bottom right and the tab of each view. The first view has no tab.', 'amour' ),
					'sub_fields'   => array(
						array(
							'key'   => 'field_amour_home_map_view_tab',
							'name'  => 'tab',
							'label' => __( 'Tab', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'   => 'field_amour_home_map_view_eyebrow',
							'name'  => 'eyebrow',
							'label' => __( 'Label', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'   => 'field_amour_home_map_view_title',
							'name'  => 'title',
							'label' => __( 'Heading', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'   => 'field_amour_home_map_view_meta',
							'name'  => 'meta',
							'label' => __( 'Line under the heading', 'amour' ),
							'type'  => 'text',
						),
						array(
							'key'   => 'field_amour_home_map_view_text',
							'name'  => 'text',
							'label' => __( 'Text', 'amour' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'key'   => 'field_amour_home_map_view_text_phone',
							'name'  => 'text_phone',
							'label' => __( 'Text on a phone (if different)', 'amour' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'key'   => 'field_amour_home_map_view_link',
							'name'  => 'link',
							'label' => __( 'Link', 'amour' ),
							'type'  => 'link',
						),
						array(
							'key'           => 'field_amour_home_map_view_photo',
							'name'          => 'photo',
							'label'         => __( 'Photo in the card (optional)', 'amour' ),
							'type'          => 'image',
							'return_format' => 'id',
							'preview_size'  => 'thumbnail',
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_home_fields' );
