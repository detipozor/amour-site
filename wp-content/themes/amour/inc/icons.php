<?php
/**
 * Icons from the design system (design/ds/components/bundle.js, Amour.Icon): 48 × 48 grid, filled outlines.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Outline data: icon name => list of path outlines.
 */
const AMOUR_ICONS = array(
	'arrow-right'  => array(
		'M7,26 L39,26 A2,2 0 0 0 39,22 L7,22 A2,2 0 0 0 7,26 Z',
		'M27.62,15.45 L38.12,25.45 A2,2 0 0 0 40.88,22.55 L30.38,12.55 A2,2 0 0 0 27.62,15.45 Z',
		'M30.38,35.45 L40.88,25.45 A2,2 0 0 0 38.12,22.55 L27.62,32.55 A2,2 0 0 0 30.38,35.45 Z',
	),
	'chevron-down' => array(
		'M9.59,19.41 L22.59,32.41 A2,2 0 0 0 25.41,29.59 L12.41,16.59 A2,2 0 0 0 9.59,19.41 Z',
		'M35.59,16.59 L22.59,29.59 A2,2 0 0 0 25.41,32.41 L38.41,19.41 A2,2 0 0 0 35.59,16.59 Z',
	),
	'close'        => array(
		'M10.59,13.41 L34.59,37.41 A2,2 0 0 0 37.41,34.59 L13.41,10.59 A2,2 0 0 0 10.59,13.41 Z',
		'M34.59,10.59 L10.59,34.59 A2,2 0 0 0 13.41,37.41 L37.41,13.41 A2,2 0 0 0 34.59,10.59 Z',
	),
	'menu'         => array(
		'M7,18 L41,18 A2,2 0 0 0 41,14 L7,14 A2,2 0 0 0 7,18 Z',
		'M7,26 L41,26 A2,2 0 0 0 41,22 L7,22 A2,2 0 0 0 7,26 Z',
		'M7,34 L41,34 A2,2 0 0 0 41,30 L7,30 A2,2 0 0 0 7,34 Z',
	),
	'plus'         => array(
		'M22,10 L22,38 A2,2 0 0 0 26,38 L26,10 A2,2 0 0 0 22,10 Z',
		'M10,26 L38,26 A2,2 0 0 0 38,22 L10,22 A2,2 0 0 0 10,26 Z',
	),
	'minus'        => array(
		'M10,26 L38,26 A2,2 0 0 0 38,22 L10,22 A2,2 0 0 0 10,26 Z',
	),
	'check'        => array(
		'M7.59,26.41 L18.09,36.91 A2,2 0 0 0 20.91,34.09 L10.41,23.59 A2,2 0 0 0 7.59,26.41 Z',
		'M20.95,36.88 L40.45,16.38 A2,2 0 0 0 37.55,13.62 L18.05,34.12 A2,2 0 0 0 20.95,36.88 Z',
	),
);

/**
 * Icon markup. Decorative by default; pass a title when the icon is the only label of a control.
 *
 * @param string $name  Icon name (see AMOUR_ICONS).
 * @param int    $size  Size in pixels.
 * @param string $title Accessible name, or empty for a decorative icon.
 * @return string SVG markup.
 */
function amour_icon( $name, $size = 24, $title = '' ) {
	if ( ! isset( AMOUR_ICONS[ $name ] ) ) {
		return '';
	}

	$paths = '';
	foreach ( AMOUR_ICONS[ $name ] as $outline ) {
		$paths .= '<path fill-rule="evenodd" d="' . esc_attr( $outline ) . '"/>';
	}

	$a11y = $title
		? ' role="img" aria-label="' . esc_attr( $title ) . '"'
		: ' aria-hidden="true" focusable="false"';

	return sprintf(
		'<svg class="am-icon am-icon--%1$s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="%2$d" height="%2$d" fill="currentColor"%3$s>%4$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$a11y,
		$paths
	);
}

/**
 * Names of all icons, for the styleguide.
 *
 * @return string[]
 */
function amour_icon_names() {
	return array_keys( AMOUR_ICONS );
}
