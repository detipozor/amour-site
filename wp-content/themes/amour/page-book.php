<?php
/**
 * Book your stay (board Book; HANDOFF, section 7): the steps, the search (Arrive, Leave, Guests, our own calendar)
 * and the rooms and the villa free for the dates, each with the price for the whole stay, the cancellation rule
 * next to it and Select. Steps 3 Details and 4 Payment are the booking engine's: Select hands the guest over
 * (inc/booking.php, assets/js/book.js). Fields: the page (Booking page), each room page and the villa
 * (On the booking page), Hotel settings → Booking.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
$amour_units  = amour_booking_units();
$amour_config = amour_booking_config( $amour_units );
$amour_terms  = amour_booking_terms_line();
$amour_wa     = amour_contacts()['whatsapp']['url'] ?? '';
$amour_steps  = array( __( 'Dates', 'amour' ), __( 'Room', 'amour' ), __( 'Details', 'amour' ), __( 'Payment', 'amour' ) );
$amour_max    = $amour_units ? max( array_column( $amour_units, 'guests' ) ) : 2;
?>
<main id="main" tabindex="-1" class="am-book" data-book data-book-config="<?php echo esc_attr( wp_json_encode( $amour_config ) ); ?>">

	<section class="am-pg-open am-bk-top" aria-labelledby="am-bk-title">
		<div class="am-pg-in am-bk-top__in">
			<nav class="am-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'amour' ); ?>">
				<a href="<?php echo esc_url( amour_home_url() ); ?>"><?php esc_html_e( 'Home', 'amour' ); ?></a> · <span aria-current="page"><?php esc_html_e( 'Book', 'amour' ); ?></span>
			</nav>
			<h1 id="am-bk-title"><?php the_title(); ?></h1>
			<?php if ( amour_page_field( 'bk_lead' ) ) : ?>
				<p class="am-bk-top__lead"><?php echo esc_html( amour_page_field( 'bk_lead' ) ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="am-bk" id="book" aria-labelledby="am-bk-title">
		<div class="am-pg-in am-bk__in">
			<ol class="am-bk-steps" aria-label="<?php esc_attr_e( 'Steps', 'amour' ); ?>">
				<?php foreach ( $amour_steps as $amour_i => $amour_step ) : ?>
					<li data-bk-step<?php echo 0 === $amour_i ? ' aria-current="step"' : ''; ?>><?php echo esc_html( ( $amour_i + 1 ) . ' ' . $amour_step ); ?></li>
				<?php endforeach; ?>
			</ol>

			<div class="am-bk-dates">
				<div class="am-bk-search" data-bk-search>
					<button type="button" class="am-bk-field" data-bk-open="arr" aria-expanded="false" aria-controls="am-bk-cal">
						<span class="am-bk-field__label"><?php esc_html_e( 'Arrive', 'amour' ); ?></span>
						<span class="am-bk-field__value" data-bk-value="arr"><?php esc_html_e( 'Add date', 'amour' ); ?></span>
					</button>
					<button type="button" class="am-bk-field" data-bk-open="dep" aria-expanded="false" aria-controls="am-bk-cal">
						<span class="am-bk-field__label"><?php esc_html_e( 'Leave', 'amour' ); ?></span>
						<span class="am-bk-field__value" data-bk-value="dep"><?php esc_html_e( 'Add date', 'amour' ); ?></span>
					</button>
					<div class="am-bk-field am-bk-guests" role="group" aria-labelledby="am-bk-guests">
						<span class="am-bk-guests__text">
							<span class="am-bk-field__label" id="am-bk-guests"><?php esc_html_e( 'Guests', 'amour' ); ?></span>
							<output class="am-bk-field__value" data-bk-guests data-max="<?php echo (int) $amour_max; ?>" aria-live="polite"><?php echo esc_html( sprintf( /* translators: %d: number of guests. */ _n( '%d adult', '%d adults', 2, 'amour' ), 2 ) ); ?></output>
						</span>
						<span class="am-bk-guests__buttons">
							<button type="button" class="am-bk-round" data-bk-guest="-1" aria-label="<?php esc_attr_e( 'Fewer guests', 'amour' ); ?>">−</button>
							<button type="button" class="am-bk-round" data-bk-guest="1" aria-label="<?php esc_attr_e( 'More guests', 'amour' ); ?>">+</button>
						</span>
					</div>
					<button type="button" class="am-btn am-bk-go" data-bk-go><?php esc_html_e( 'Search', 'amour' ); ?></button>

					<div class="am-bk-cal" id="am-bk-cal" role="dialog" aria-label="<?php esc_attr_e( 'Choose your dates', 'amour' ); ?>" data-bk-cal hidden>
						<div class="am-bk-cal__head">
							<button type="button" class="am-bk-round am-bk-round--plain" data-bk-month="-1" aria-label="<?php esc_attr_e( 'Previous month', 'amour' ); ?>">←</button>
							<p class="am-bk-cal__month" data-bk-month-name aria-live="polite"></p>
							<button type="button" class="am-bk-round am-bk-round--plain" data-bk-month="1" aria-label="<?php esc_attr_e( 'Next month', 'amour' ); ?>">→</button>
						</div>
						<div class="am-bk-cal__week" aria-hidden="true">
							<?php
							// Monday first, as in Greece and Germany.
							foreach ( array( 1, 2, 3, 4, 5, 6, 0 ) as $amour_d ) {
								echo '<span>' . esc_html( mb_substr( $GLOBALS['wp_locale']->get_weekday_abbrev( $GLOBALS['wp_locale']->get_weekday( $amour_d ) ), 0, 2 ) ) . '</span>';
							}
							?>
						</div>
						<div class="am-bk-cal__days" data-bk-days></div>
						<p class="am-bk-cal__hint" data-bk-cal-hint></p>
					</div>
				</div>
				<noscript><p class="am-bk-note"><a href="<?php echo esc_url( amour_page_url( 'contacts' ) ); ?>"><?php esc_html_e( 'The search needs JavaScript. Write to us and we book for you.', 'amour' ); ?></a></p></noscript>

				<?php // Before the search the hint, after it what was found; one line, read out when it changes. ?>
				<p class="am-bk-note is-hint" data-bk-note data-hint="<?php echo esc_attr( (string) amour_page_field( 'bk_hint' ) ); ?>" role="status"><?php echo esc_html( (string) amour_page_field( 'bk_hint' ) ); ?></p>

				<ul class="am-bk-units" data-bk-units hidden>
					<?php foreach ( $amour_units as $amour_u ) : ?>
						<li class="am-bk-unit" data-bk-unit="<?php echo (int) $amour_u['id']; ?>" hidden>
							<span class="am-bk-unit__pic">
								<?php
								echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
									$amour_u['photo'],
									'large',
									false,
									array(
										'sizes'    => '(max-width: 760px) 92vw, 520px',
										'loading'  => 'lazy',
										'decoding' => 'async',
									)
								);
								?>
							</span>
							<div class="am-bk-unit__text">
								<div class="am-bk-unit__head">
									<h2 class="am-bk-unit__name" id="am-bk-unit-<?php echo (int) $amour_u['id']; ?>"><?php echo esc_html( $amour_u['name'] ); ?></h2>
									<?php foreach ( $amour_u['facts'] as $amour_fact ) : ?>
										<span class="am-bk-pill"><?php echo esc_html( $amour_fact ); ?></span>
									<?php endforeach; ?>
								</div>
								<?php if ( $amour_u['text'] ) : ?>
									<p class="am-bk-unit__desc"><?php echo esc_html( $amour_u['text'] ); ?></p>
								<?php endif; ?>
								<p class="am-bk-unit__stay" data-bk-stay></p>
								<p class="am-bk-unit__total" data-bk-total></p>
								<p class="am-bk-unit__terms"><?php echo esc_html( $amour_terms ); ?></p>
								<div class="am-bk-unit__go">
									<button type="button" class="am-btn" data-bk-select aria-describedby="am-bk-unit-<?php echo (int) $amour_u['id']; ?>"><?php esc_html_e( 'Select', 'amour' ); ?></button>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php if ( 'mock' === $amour_config['provider'] ) : ?>
				<p class="am-bk-test" data-bk-test hidden>[<?php esc_html_e( 'Test booking engine: the prices are the price from × nights; Select opens the thank-you page as if paid', 'amour' ); ?>]</p>
			<?php endif; ?>

			<p class="am-bk-help">
				<?php esc_html_e( 'Questions?', 'amour' ); ?>
				<?php if ( $amour_wa ) : ?>
					<a href="<?php echo esc_url( $amour_wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Write on WhatsApp', 'amour' ); ?></a>
				<?php else : ?>
					<a href="<?php echo esc_url( amour_page_url( 'contacts' ) ); ?>"><?php esc_html_e( 'Write on WhatsApp', 'amour' ); ?></a>
				<?php endif; ?>
				· <a href="<?php echo esc_url( amour_page_url( 'booking-terms' ) ); ?>"><?php esc_html_e( 'Booking terms', 'amour' ); ?></a>
			</p>
		</div>
	</section>
</main>
<?php
get_footer();
