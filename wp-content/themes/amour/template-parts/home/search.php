<?php
/**
 * Date search (HANDOFF, section 5): Arrive, Leave, Guests and Check dates. They are links to the booking
 * page, which opens with its calendar ready (sessionStorage amour-cal=arr, assets/js/date-search.js).
 * No browser date fields: they speak the browser's language, not the page's.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_book = amour_page_url( 'book' );
$amour_cell = static function ( $label, $value, $classes = '' ) use ( $amour_book ) {
	printf(
		'<a class="am-search__field %1$s" href="%2$s" data-amour-cal="arr"><span class="am-search__label">%3$s</span><span class="am-search__value">%4$s</span></a>',
		esc_attr( $classes ),
		esc_url( $amour_book ),
		esc_html( $label ),
		esc_html( $value )
	);
};
?>
<div class="am-search" id="book">
	<div class="am-search__in">
		<?php
		$amour_cell( __( 'Arrive', 'amour' ), __( 'Add date', 'amour' ) );
		$amour_cell( __( 'Leave', 'amour' ), __( 'Add date', 'amour' ) );
		$amour_cell( __( 'Guests', 'amour' ), __( '2 adults', 'amour' ), 'am-search__field--guests' );
		?>
		<a class="am-btn am-search__go" href="<?php echo esc_url( $amour_book ); ?>" data-amour-cal="arr"><?php esc_html_e( 'Check dates', 'amour' ); ?></a>
	</div>
</div>
