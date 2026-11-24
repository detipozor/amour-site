<?php
/**
 * Thank you, after a booking (board Thanks; HANDOFF, section 6 "Thanks"): "Thank you, [name]", the summary of
 * the booking and three next steps. The name and the summary come from the booking engine (week 5); until then
 * assets/js/thanks.js reads what the booking page leaves in sessionStorage "amour-booking", and a test copy shows
 * placeholders. Not for search engines (amour_noindex_thanks()). Fields: the page (Thank-you page).
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
$amour_f       = static fn( $name ) => amour_page_field( 'th_' . $name );
$amour_next    = array_values( array_filter( (array) $amour_f( 'next' ), static fn( $n ) => ! empty( $n['title'] ) ) );
$amour_wa      = amour_contacts()['whatsapp']['url'] ?? '';
$amour_summary = array(
	'number' => array( __( 'Booking number', 'amour' ), amour_or_placeholder( '', 'number' ) ),
	'stay'   => array( __( 'Stay', 'amour' ), amour_or_placeholder( '', 'dates · nights' ) ),
	'room'   => array( __( 'Room', 'amour' ), amour_or_placeholder( '', 'room' ) ),
	'guests' => array( __( 'Guests', 'amour' ), amour_or_placeholder( '', 'guests' ) ),
	'paid'   => array( __( 'Paid', 'amour' ), amour_or_placeholder( '', 'amount' ) ),
	/* translators: %s: the climate resilience fee for one night, paid at the hotel. */
	'fee'    => array( __( 'At the hotel', 'amour' ), amour_or_placeholder( (string) amour_option( 'amour_climate_fee' ), '€…' ), __( 'Climate fee %s a night', 'amour' ) ),
);
// On the live site a row without a value waits hidden until the booking fills it (assets/js/thanks.js); the fee
// from Hotel settings alone does not open the summary.
$amour_any = (bool) array_filter( array_column( array_diff_key( $amour_summary, array( 'fee' => 1 ) ), 1 ) );
// Add to calendar: the stay as an all-day event from the arrival to the departure day (assets/js/thanks.js).
$amour_cal = array(
	'title' => get_bloginfo( 'name' ),
	'place' => (string) amour_option( 'amour_address' ),
	/* translators: 1: check-in time, 2: check-out time. */
	'note'  => sprintf( __( 'Check-in from %1$s, check-out by %2$s.', 'amour' ), (string) amour_option( 'amour_checkin' ), (string) amour_option( 'amour_checkout' ) ),
);
?>
<main id="main" tabindex="-1" class="am-thanks" data-thanks>

	<section class="am-pg-open am-th-top" aria-labelledby="am-th-title">
		<div class="am-pg-in am-th-top__grid">
			<div class="am-th-top__text">
				<?php if ( $amour_f( 'label' ) ) : ?>
					<p class="am-eyebrow"><?php echo esc_html( $amour_f( 'label' ) ); ?></p>
				<?php endif; ?>
				<?php /* translators: %s: guest's first name. */ ?>
				<h1 id="am-th-title" data-thanks-title data-with-name="<?php echo esc_attr( __( 'Thank you, %s', 'amour' ) ); ?>"><?php echo esc_html( sprintf( __( 'Thank you, %s', 'amour' ), amour_or_placeholder( '', 'name' ) ) ); ?></h1>
				<?php if ( $amour_f( 'text' ) ) : ?>
					<p class="am-th-top__lead"><?php echo esc_html( $amour_f( 'text' ) ); ?></p>
				<?php endif; ?>
				<dl class="am-th-summary"<?php echo $amour_any ? '' : ' hidden'; ?>>
					<?php foreach ( $amour_summary as $amour_key => $amour_row ) : ?>
						<?php $amour_with = $amour_row[2] ?? ''; ?>
						<div<?php echo '' === $amour_row[1] ? ' hidden' : ''; ?>>
							<dt><?php echo esc_html( $amour_row[0] ); ?></dt>
							<dd data-booking="<?php echo esc_attr( $amour_key ); ?>"<?php echo $amour_with ? ' data-with="' . esc_attr( $amour_with ) . '"' : ''; ?>><?php echo esc_html( $amour_with && '' !== $amour_row[1] ? sprintf( $amour_with, $amour_row[1] ) : $amour_row[1] ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</div>
			<?php if ( $amour_f( 'photo' ) ) : ?>
				<span class="am-arch am-th-top__arch">
					<?php echo wp_get_attachment_image( (int) $amour_f( 'photo' ), 'large', false, array( 'sizes' => '(max-width: 760px) 92vw, 480px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes. ?>
				</span>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $amour_next ) : ?>
		<section class="am-th-next" aria-labelledby="am-th-next">
			<div class="am-pg-in">
				<h2 id="am-th-next" class="am-ct-h2"><?php echo esc_html( $amour_f( 'next_title' ) ); ?></h2>
				<ol class="am-th-next__list">
					<?php foreach ( $amour_next as $amour_i => $amour_n ) : ?>
						<li class="am-ct-route">
							<span class="am-th-next__n" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $amour_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<h3><?php echo esc_html( $amour_n['title'] ); ?></h3>
							<p><?php echo esc_html( $amour_n['text'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
				<div class="am-th-next__actions">
					<?php if ( $amour_wa ) : ?>
						<a class="am-btn" href="<?php echo esc_url( $amour_wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Write on WhatsApp', 'amour' ); ?></a>
					<?php endif; ?>
					<a class="am-btn am-btn--ghost" href="<?php echo esc_url( amour_page_url( 'crete-guide' ) ); ?>"><?php esc_html_e( 'Plan your days', 'amour' ); ?></a>
					<button type="button" class="am-btn am-btn--ghost" data-thanks-calendar data-event="<?php echo esc_attr( wp_json_encode( $amour_cal ) ); ?>" hidden><?php esc_html_e( 'Add to calendar', 'amour' ); ?></button>
				</div>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
