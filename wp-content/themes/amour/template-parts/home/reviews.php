<?php
/**
 * Guest reviews on the home page (board Main-v2-b, "reviews"; HANDOFF, sections 5 and 6.9).
 * A huge "Guest reviews" heading that moves with the scroll, the Booking.com score and link, then a strip of
 * review cards that drifts on its own (34 px/s, right to left) while the page stands still.
 * It stops under the pointer, a finger, keyboard focus and the Pause button (WCAG 2.2.2); with reduced motion
 * it stands and scrolls by hand. Logic: assets/js/home/reviews.js. Reviews: Reviews in the admin.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_reviews = amour_reviews();
if ( ! $amour_reviews ) {
	return;
}
$amour_title   = (string) amour_page_field( 'home_reviews_title' );
$amour_score   = trim( (string) amour_option( 'amour_booking_score' ) );
$amour_count   = amour_or_placeholder( (string) amour_option( 'amour_booking_reviews' ), 'number' );
$amour_all_url = (string) amour_option( 'amour_booking_url' );
$amour_all     = __( 'Read all reviews on Booking.com', 'amour' );
?>
<section id="reviews" class="am-reviews" data-reviews aria-labelledby="am-reviews-title">
	<h2 id="am-reviews-title" class="sr-only">
		<?php
		echo esc_html(
			$amour_score
				/* translators: 1: heading of the block, 2: score out of 10. */
				? sprintf( __( '%1$s: %2$s out of 10 on Booking.com', 'amour' ), $amour_title, $amour_score )
				: $amour_title
		);
		?>
	</h2>

	<div class="am-reviews__strip" data-reviews-strip aria-hidden="true">
		<?php for ( $amour_i = 0; $amour_i < 8; $amour_i++ ) : ?>
			<span class="am-reviews__word"><?php echo esc_html( $amour_title ); ?>
				<?php if ( $amour_score ) : ?>
					<span class="am-reviews__badge"><?php echo esc_html( $amour_score ); ?></span>
				<?php endif; ?>
			</span>
		<?php endfor; ?>
	</div>

	<div class="am-reviews__bar">
		<?php if ( $amour_score ) : ?>
			<p class="am-reviews__score">
				<?php
				printf(
					/* translators: 1: score out of 10, 2: number of reviews. */
					wp_kses( __( '<strong>%1$s out of 10</strong> on Booking.com · %2$s reviews', 'amour' ), array( 'strong' => array() ) ),
					esc_html( $amour_score ),
					esc_html( $amour_count )
				);
				?>
			</p>
		<?php endif; ?>
		<div class="am-reviews__actions">
			<?php if ( $amour_all_url ) : ?>
				<a class="am-reviews__all" href="<?php echo esc_url( $amour_all_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $amour_all ); ?></a>
			<?php elseif ( '' !== amour_or_placeholder( '', 'x' ) ) : ?>
				<span class="am-reviews__all"><?php echo esc_html( '[' . $amour_all . ']' ); ?></span>
			<?php endif; ?>
			<button type="button" class="am-reviews__pause" data-reviews-pause
				data-pause="<?php esc_attr_e( 'Pause reviews', 'amour' ); ?>" data-play="<?php esc_attr_e( 'Play reviews', 'amour' ); ?>"><?php esc_html_e( 'Pause reviews', 'amour' ); ?></button>
		</div>
	</div>

	<?php // Focusable, so the keyboard can stop the strip and move it (arrows), or scroll it when it stands. ?>
	<div class="am-reviews__scroller" data-reviews-scroller tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Review cards: arrow keys move them', 'amour' ); ?>">
		<ul class="am-reviews__track" data-reviews-track>
			<?php foreach ( array( false, true ) as $amour_copy ) : ?>
				<?php foreach ( $amour_reviews as $amour_review ) : ?>
					<?php // The second set only closes the loop: hidden from screen readers and the keyboard. ?>
					<li class="am-review<?php echo $amour_copy ? ' is-copy' : ''; ?>" <?php echo $amour_copy ? 'aria-hidden="true" inert' : ''; ?>>
						<div class="am-review__top">
							<p class="am-review__head">
								<?php if ( $amour_review['source'] ) : ?>
									<a class="am-review__source" href="<?php echo esc_url( $amour_review['source'] ); ?>" target="_blank" rel="noopener">Booking.com</a>
								<?php else : ?>
									<span class="am-review__source">Booking.com</span>
								<?php endif; ?>
								<span class="am-review__score"><strong><?php echo esc_html( $amour_review['score'] ); ?></strong> / 10</span>
							</p>
							<blockquote class="am-review__text am-review__fade">“<?php echo esc_html( $amour_review['text'] ); ?>”</blockquote>
						</div>
						<p class="am-review__who am-review__fade">
							<span class="am-review__initial" aria-hidden="true"><?php echo esc_html( $amour_review['initial'] ); ?></span>
							<span class="am-review__names">
								<span class="am-review__name"><?php echo esc_html( $amour_review['who'] ); ?></span>
								<span class="am-review__when"><?php echo esc_html( $amour_review['when'] ); ?></span>
							</span>
						</p>
					</li>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
