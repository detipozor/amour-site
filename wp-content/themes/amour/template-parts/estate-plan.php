<?php
/**
 * The estate from above (board Estate, "plan"; HANDOFF, section 5 "План территории"): the aerial photo with six
 * places. Pointing at a name previews the place, a click or tap keeps it open (the rest of the photo dims and the
 * place is outlined), a click on the photo closes it. The card sits in the corner away from the place.
 * Phone: numbers on the photo, the names as chips under it and the text under them, at a fixed height.
 * Outlines: inc/estate-plan.php; texts and photos: Court and pool → From above. Logic: assets/js/estate.js.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_geo    = require get_theme_file_path( 'inc/estate-plan.php' );
$amour_places = array_values( (array) amour_page_field( 'estate_places' ) );
$amour_photo  = (int) amour_page_field( 'estate_plan_photo' );
if ( ! $amour_photo || count( $amour_places ) < count( $amour_geo ) ) {
	return;
}
// Plan units to % of the frame (it shows 60…2300 × 30…1485 of the photo).
$amour_at    = static fn( $x, $y ) => sprintf( 'left: %.2f%%; top: %.2f%%', ( $x - 60 ) / 2240 * 100, ( $y - 30 ) / 1455 * 100 );
$amour_vars  = static fn( $g ) => sprintf( '--dx: %.2f%%; --dy: %.2f%%; --px: %.2f%%; --py: %.2f%%', ( $g['x'] - 60 ) / 2240 * 100, ( $g['y'] - 30 ) / 1455 * 100, ( $g['px'] - 60 ) / 2240 * 100, ( $g['py'] - 30 ) / 1455 * 100 );
$amour_rooms = array( array( 640, 600 ), array( 1880, 470 ), array( 965, 428 ) );
?>
<section class="am-es-plan" data-es-plan data-tone="light" aria-labelledby="am-es-plan">
	<div class="am-es-plan__head am-es-enter">
		<div>
			<?php if ( amour_page_field( 'estate_plan_label' ) ) : ?>
				<p class="am-eyebrow"><?php echo esc_html( amour_page_field( 'estate_plan_label' ) ); ?></p>
			<?php endif; ?>
			<h2 id="am-es-plan" class="am-es-plan__title"><?php echo esc_html( amour_page_field( 'estate_plan_title' ) ); ?></h2>
		</div>
		<p class="am-es-plan__text"><?php echo esc_html( amour_page_field( 'estate_plan_text' ) ); ?></p>
	</div>

	<div class="am-es-plan__wrap">
		<div class="am-es-plan__box" data-es-plan-box data-on="-1">
			<div class="am-es-plan__layer" data-es-plan-layer>
				<svg class="am-es-plan__svg" viewBox="60 30 2240 1455" preserveAspectRatio="xMidYMid slice" role="img" aria-label="<?php esc_attr_e( 'The hotel from above: the tennis court in the middle, the pool and sun terrace above it, the daybeds between them, rooms on terraces on both sides, lawns, a barbecue corner and an olive grove below', 'amour' ); ?>">
					<defs>
						<?php // A mask, not a colour: white keeps the veil, black cuts the place out of it. ?>
						<mask id="am-es-focus" maskUnits="userSpaceOnUse" x="0" y="0" width="2400" height="1485">
							<rect x="0" y="0" width="2400" height="1485" fill="#fff"/>
							<?php foreach ( $amour_geo as $amour_i => $amour_g ) : ?>
								<path class="am-es-plan__hole" data-place="<?php echo (int) $amour_i; ?>" d="<?php echo esc_attr( $amour_g['path'] ); ?>" fill="#000"/>
							<?php endforeach; ?>
						</mask>
					</defs>
					<image href="<?php echo esc_url( (string) wp_get_attachment_image_url( $amour_photo, 'full' ) ); ?>" x="0" y="0" width="2400" height="1485" preserveAspectRatio="none" class="am-es-plan__aerial"/>
					<rect class="am-es-plan__veil" x="0" y="0" width="2400" height="1485" mask="url(#am-es-focus)"/>
					<g class="am-es-plan__outlines">
						<?php foreach ( $amour_geo as $amour_i => $amour_g ) : ?>
							<path class="am-es-plan__glow" data-place="<?php echo (int) $amour_i; ?>" d="<?php echo esc_attr( $amour_g['path'] ); ?>"/>
						<?php endforeach; ?>
						<?php foreach ( $amour_geo as $amour_i => $amour_g ) : ?>
							<path class="am-es-plan__line" data-place="<?php echo (int) $amour_i; ?>" d="<?php echo esc_attr( $amour_g['path'] ); ?>"/>
						<?php endforeach; ?>
					</g>
				</svg>
				<?php foreach ( $amour_rooms as $amour_r ) : ?>
					<span class="am-es-plan__rooms" style="<?php echo esc_attr( $amour_at( $amour_r[0], $amour_r[1] ) ); ?>" aria-hidden="true"><?php esc_html_e( 'Rooms', 'amour' ); ?></span>
				<?php endforeach; ?>
				<?php foreach ( $amour_geo as $amour_i => $amour_g ) : ?>
					<button type="button" class="am-es-spot am-es-spot--<?php echo esc_attr( $amour_g['key'] ); ?>" data-place="<?php echo (int) $amour_i; ?>" aria-pressed="false"
						style="<?php echo esc_attr( $amour_vars( $amour_g ) ); ?>">
						<?php // The name is what shows: the place's name, or on a phone its number (the name stays for screen readers). ?>
						<span class="am-es-spot__dot" aria-hidden="true"></span><span class="am-es-spot__n"><?php echo (int) $amour_i + 1; ?> </span><span class="am-es-spot__name"><?php echo esc_html( $amour_places[ $amour_i ]['name'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<?php foreach ( $amour_geo as $amour_i => $amour_g ) : ?>
				<?php
				$amour_place = $amour_places[ $amour_i ];
				$amour_right = ( $amour_g['cx'] - 60 ) / 2240 < 0.5; // the card goes to the side away from the place
				$amour_top   = ( $amour_g['cy'] - 30 ) / 1455 > 0.55;
				?>
				<div class="am-es-card" data-card="<?php echo (int) $amour_i; ?>" data-side="<?php echo esc_attr( ( $amour_top ? 'top' : 'bottom' ) . '-' . ( $amour_right ? 'right' : 'left' ) ); ?>" aria-hidden="true">
					<?php if ( ! empty( $amour_place['photo'] ) ) : ?>
						<span class="am-es-card__pic">
							<?php
							echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
								(int) $amour_place['photo'],
								'medium_large',
								false,
								array(
									'alt'      => '',
									'sizes'    => '300px',
									'loading'  => 'lazy',
									'decoding' => 'async',
								)
							);
							?>
						</span>
					<?php endif; ?>
					<span class="am-es-card__body">
						<span class="am-es-card__name"><?php echo esc_html( $amour_place['name'] ); ?></span>
						<span class="am-es-card__text"><?php echo esc_html( $amour_place['text'] ); ?></span>
					</span>
				</div>
			<?php endforeach; ?>
		</div>

		<?php // Phone: the names as chips, the text under them. Also what a screen reader hears when a place opens. ?>
		<div class="am-es-plan__phone">
			<div class="am-es-chips" role="group" aria-label="<?php esc_attr_e( 'Places on the photo', 'amour' ); ?>">
				<?php foreach ( $amour_places as $amour_i => $amour_place ) : ?>
					<?php if ( $amour_i < count( $amour_geo ) ) : ?>
						<button type="button" class="am-es-chip" data-place="<?php echo (int) $amour_i; ?>" aria-pressed="false"><span class="am-es-chip__n" aria-hidden="true"><?php echo (int) $amour_i + 1; ?></span><?php echo esc_html( $amour_place['name'] ); ?></button>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<p class="am-es-plan__say" data-es-say aria-live="polite"><span class="am-es-plan__say-name" data-es-say-name></span><span data-es-say-text></span></p>
	</div>
</section>
