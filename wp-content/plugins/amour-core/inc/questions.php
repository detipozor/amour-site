<?php
/**
 * Questions for the FAQ page (HANDOFF, section 9: "Вопрос" — question, answer, topic, order).
 * A question's title is the question; its topic (Booking and payment, Arrival and stay…) groups it on the page.
 * Topics have their own order. Both are translated with Polylang.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the question type and the topic taxonomy.
 */
function amour_register_questions() {
	register_post_type(
		'amour_question',
		array(
			'labels'        => array(
				'name'          => __( 'Questions', 'amour' ),
				'singular_name' => __( 'Question', 'amour' ),
				'add_new_item'  => __( 'Add a question', 'amour' ),
				'edit_item'     => __( 'Edit question', 'amour' ),
				'menu_name'     => __( 'Questions', 'amour' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-editor-help',
			'menu_position' => 23,
			'supports'      => array( 'title', 'page-attributes' ),
			'hierarchical'  => false,
		)
	);
	register_taxonomy(
		'amour_topic',
		'amour_question',
		array(
			'labels'            => array(
				'name'          => __( 'Topics', 'amour' ),
				'singular_name' => __( 'Topic', 'amour' ),
				'add_new_item'  => __( 'Add a topic', 'amour' ),
				'menu_name'     => __( 'Topics', 'amour' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true, // a list of checkboxes in the editor: one topic per question
		)
	);
}
add_action( 'init', 'amour_register_questions' );

/**
 * Questions and topics are translated with Polylang.
 *
 * @param array $types Post types or taxonomies Polylang translates.
 * @return array
 */
function amour_questions_translatable( $types ) {
	$types['amour_question'] = 'amour_question';
	return $types;
}
add_filter( 'pll_get_post_types', 'amour_questions_translatable' );

/**
 * Topics are translated with Polylang.
 *
 * @param array $taxonomies Taxonomies Polylang translates.
 * @return array
 */
function amour_topics_translatable( $taxonomies ) {
	$taxonomies['amour_topic'] = 'amour_topic';
	return $taxonomies;
}
add_filter( 'pll_get_taxonomies', 'amour_topics_translatable' );

/**
 * The question's title field says what it is.
 *
 * @param string  $text Placeholder of the title field.
 * @param WP_Post $post Post being edited.
 * @return string
 */
function amour_question_title_placeholder( $text, $post ) {
	return 'amour_question' === $post->post_type ? __( 'The question, as a guest would ask it', 'amour' ) : $text;
}
add_filter( 'enter_title_here', 'amour_question_title_placeholder', 10, 2 );

/**
 * The answer, and the order of a topic.
 */
function amour_register_question_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group(
		array(
			'key'      => 'group_amour_question',
			'title'    => __( 'Answer', 'amour' ),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'amour_question',
					),
				),
			),
			'fields'   => array(
				array(
					'key'          => 'field_amour_question_answer',
					'name'         => 'answer',
					'label'        => __( 'Answer', 'amour' ),
					'type'         => 'textarea',
					'rows'         => 4,
					'instructions' => __( 'Plain and warm, as the hosts would say it. The order of questions inside a topic: Page attributes → Order.', 'amour' ),
				),
			),
		)
	);
	acf_add_local_field_group(
		array(
			'key'      => 'group_amour_topic',
			'title'    => __( 'Topic', 'amour' ),
			'location' => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'amour_topic',
					),
				),
			),
			'fields'   => array(
				array(
					'key'          => 'field_amour_topic_order',
					'name'         => 'order',
					'label'        => __( 'Order on the page', 'amour' ),
					'type'         => 'number',
					'instructions' => __( '1 comes first.', 'amour' ),
				),
			),
		)
	);
}
add_action( 'acf/init', 'amour_register_question_fields' );
