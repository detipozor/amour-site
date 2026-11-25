<?php
/**
 * Texts that Polylang (free) keeps once for all languages, in the page language (decision 25.11, HANDOFF section 19):
 * the texts of Hotel settings guests read (the place, the stay rules, the location card, the cookie banner) and the
 * descriptions of the photos (alt text). They are translated as Polylang strings: Languages → Translations, groups
 * "Amour: Hotel settings" and "Amour: Photo descriptions". The seed (wp amour seed) fills the German ones from
 * data/de.json.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * The texts of Hotel settings that guests read, as [field name => long text?]; repeaters list their rows' text fields.
 */
const AMOUR_TRANSLATED_OPTIONS = array(
	'amour_place'          => false,
	'amour_stay_rules'     => false,
	'amour_location_title' => false,
	'amour_location_text'  => true,
	'amour_location_near'  => false,
	'amour_cookie_text'    => true,
);

/**
 * The English texts of those settings, one by one (the rows of a repeater too).
 *
 * @return array<int, array{name: string, text: string, multiline: bool}>
 */
function amour_option_strings() {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}
	$strings = array();
	$add     = static function ( $name, $value, $multiline ) use ( &$strings, &$add ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $part ) {
				$add( $name, $part, $multiline );
			}
		} elseif ( is_string( $value ) && '' !== trim( $value ) && ! preg_match( '/^(https?:|#|\/)/', $value ) ) {
			$strings[] = array(
				'name'      => $name,
				'text'      => $value,
				'multiline' => $multiline,
			);
		}
	};
	foreach ( AMOUR_TRANSLATED_OPTIONS as $name => $multiline ) {
		$add( $name, get_field( $name, 'option', false ), $multiline );
	}
	return $strings;
}

/**
 * The descriptions of the photos (alt text of the media library), one by one.
 *
 * @return array<int, string>
 */
function amour_photo_alts() {
	$alts = array();
	foreach ( get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	) as $id ) {
		$alt = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
		if ( '' !== $alt ) {
			$alts[] = $alt;
		}
	}
	return array_values( array_unique( $alts ) );
}

/**
 * List the texts in Languages → Translations, so the German ones can be edited there.
 */
function amour_register_option_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	foreach ( amour_option_strings() as $string ) {
		pll_register_string( $string['name'], $string['text'], 'Amour: Hotel settings', $string['multiline'] );
	}
	foreach ( amour_photo_alts() as $alt ) {
		pll_register_string( 'Photo', $alt, 'Amour: Photo descriptions' );
	}
}
add_action( 'admin_init', 'amour_register_option_strings' );

/**
 * On a page in another language, a text field of Hotel settings shows its translation (or the English text if
 * there is none yet).
 *
 * @param mixed      $value   Field value.
 * @param int|string $post_id Where the value comes from; "option" or "options" for the settings.
 * @param array      $field   Field.
 * @return mixed
 */
function amour_translate_option_text( $value, $post_id, $field ) {
	if ( ! is_string( $value ) || '' === $value || is_admin() || ! function_exists( 'pll__' ) ) {
		return $value;
	}
	if ( ! in_array( $post_id, array( 'option', 'options' ), true ) || ! in_array( $field['type'] ?? '', array( 'text', 'textarea' ), true ) ) {
		return $value;
	}
	return pll__( $value );
}
add_filter( 'acf/format_value', 'amour_translate_option_text', 20, 3 );

/**
 * On a page in another language, a photo's description is its translation (or the English one if there is none yet).
 *
 * @param array<string, string> $attr Image attributes.
 * @return array<string, string>
 */
function amour_translate_photo_alt( $attr ) {
	if ( ! empty( $attr['alt'] ) && ! is_admin() && function_exists( 'pll__' ) ) {
		$attr['alt'] = pll__( $attr['alt'] );
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'amour_translate_photo_alt' );
