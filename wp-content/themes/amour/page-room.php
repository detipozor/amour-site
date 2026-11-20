<?php
/**
 * A room page (boards Room-standard, Room-deluxe, Room-superior; design/generators/room_template.html;
 * HANDOFF, section 6 "Страница номера"). Used for every page under /rooms/ (amour_room_template()).
 *
 * 1. The wine screen: the huge name stands while the photos rise over it, left and right in turn; each comes in
 *    as an arch and opens into a soft rectangle as it rises. Caption and count under each.
 * 2. The colour runs from wine into limestone as the details come up.
 * 3. Room details: the description, six key facts, the rest under All details.
 * 4. The price capsule at the bottom of the screen; it steps aside over the facts, buttons and the footer.
 * Logic: assets/js/room.js. Fields: the page in the admin (Room page, Card) and Hotel settings (stay rules).
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
$amour_room  = amour_room();
$amour_total = count( $amour_room['figs'] );
?>
<main id="main" tabindex="-1" class="am-room" data-room>

	<section class="am-room__stage" data-room-stage data-tone="dark" aria-labelledby="am-room-name">
		<?php // Comes up on load: axe checks it in its final state, in the reduced motion pass (tests/e2e/smoke.spec.js). ?>
		<div class="am-room__screen" data-axe-motion>
			<?php if ( $amour_room['kicker'] ) : ?>
				<p class="am-eyebrow am-room__kicker am-room__rise"><?php echo esc_html( $amour_room['kicker'] ); ?></p>
			<?php endif; ?>
			<h1 id="am-room-name" class="am-room__name am-room__rise"><?php echo esc_html( $amour_room['name'] ); ?></h1>
			<?php if ( $amour_room['meta'] ) : ?>
				<p class="am-room__meta am-room__rise"><?php echo esc_html( $amour_room['meta'] ); ?></p>
			<?php endif; ?>
			<?php if ( $amour_room['cue'] && $amour_total ) : ?>
				<span class="am-room__cue am-room__rise" aria-hidden="true"><i>↓</i><?php echo esc_html( $amour_room['cue'] ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( $amour_total ) : ?>
			<div class="am-room__stream">
				<?php foreach ( $amour_room['figs'] as $amour_i => $amour_fig ) : ?>
					<figure class="am-room__fig" data-room-fig data-side="<?php echo esc_attr( $amour_fig['side'] ); ?>" data-shape="<?php echo esc_attr( $amour_fig['shape'] ); ?>" data-top="<?php echo esc_attr( $amour_fig['top'] ); ?>"
						style="--w: <?php echo (int) $amour_fig['width']; ?>%; --indent: <?php echo (int) $amour_fig['indent']; ?>%">
						<div class="am-room__pic">
							<?php
							echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
								$amour_fig['photo'],
								'large',
								false,
								array(
									'sizes'    => 'upright' === $amour_fig['shape'] ? '(max-width: 760px) 72vw, 34vw' : '(max-width: 760px) 86vw, 52vw',
									'loading'  => $amour_i < 1 ? 'eager' : 'lazy',
									'decoding' => 'async',
								)
							);
							?>
						</div>
						<figcaption class="am-room__cap">
							<span><?php echo esc_html( $amour_fig['caption'] ); ?></span>
							<span class="am-room__count"><b><?php echo (int) $amour_i + 1; ?></b> / <?php echo (int) $amour_total; ?></span>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<section class="am-room__details" data-room-details data-tone="light" aria-labelledby="am-room-details">
		<div class="am-room__in">
			<h2 id="am-room-details" class="am-room__giant am-room__enter" data-dock-avoid><?php echo esc_html( $amour_room['title'] ); ?></h2>

			<?php if ( $amour_room['description'] ) : ?>
				<div class="am-room__row am-room__row--desc">
					<p class="am-room__label"><?php echo esc_html( $amour_room['desc_label'] ); ?></p>
					<p class="am-room__desc am-room__enter"><?php echo esc_html( $amour_room['description'] ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $amour_room['key'] ) : ?>
				<div class="am-room__row am-room__row--facts">
					<h3 class="am-room__label"><?php esc_html_e( 'Key facts', 'amour' ); ?></h3>
					<dl class="am-room__facts" data-dock-avoid>
						<?php foreach ( $amour_room['key'] as $amour_i => $amour_fact ) : ?>
							<div class="am-room__fact" style="--i: <?php echo (int) $amour_i; ?>">
								<dt><?php echo esc_html( $amour_fact['label'] ); ?></dt>
								<dd><?php echo esc_html( $amour_fact['value'] ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				</div>
			<?php endif; ?>

			<?php if ( $amour_room['more'] ) : ?>
				<div class="am-room__row">
					<button type="button" class="am-room__more" data-room-more aria-expanded="false" aria-controls="am-room-all" data-dock-avoid
						data-open="<?php esc_attr_e( 'Fewer details', 'amour' ); ?>"
						data-closed="<?php echo esc_attr( sprintf( /* translators: %d: number of further details. */ __( 'All details · %d', 'amour' ), count( $amour_room['more'] ) ) ); ?>">
						<span data-room-more-label><?php echo esc_html( sprintf( /* translators: %d: number of further details. */ __( 'All details · %d', 'amour' ), count( $amour_room['more'] ) ) ); ?></span>
						<span class="am-room__plus" aria-hidden="true">+</span>
					</button>
				</div>
				<div class="am-room__row am-room__row--all" id="am-room-all" hidden>
					<h3 class="am-room__label"><?php esc_html_e( 'All details', 'amour' ); ?></h3>
					<dl class="am-room__facts" data-dock-avoid>
						<?php foreach ( $amour_room['more'] as $amour_fact ) : ?>
							<div class="am-room__fact">
								<dt><?php echo esc_html( $amour_fact['label'] ); ?></dt>
								<dd><?php echo esc_html( $amour_fact['value'] ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<div class="am-dock" data-dock>
		<div class="am-dock__pill">
			<?php if ( '' !== $amour_room['price'] ) : ?>
				<?php /* translators: %s: price in euros. */ ?>
				<span class="am-dock__price"><?php echo esc_html( sprintf( __( 'From €%s a night', 'amour' ), $amour_room['price'] ) ); ?></span>
			<?php endif; ?>
			<a class="am-btn" href="<?php echo esc_url( amour_page_url( 'book' ) ); ?>"><?php esc_html_e( 'Check dates', 'amour' ); ?></a>
		</div>
	</div>
</main>
<?php
get_footer();
