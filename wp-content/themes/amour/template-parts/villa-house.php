<?php
/**
 * The villa page after its details (design/generators/gen_villa.py, EXTRA_A; boards Villa-v2, Villa-v2-b):
 * Five bedrooms — five arched cards, a row that scrolls with a finger on a phone;
 * How the house works — a drawing of the house; pointing at, focusing or tapping a floor lights it up.
 * When the architect's plan is in the admin, it replaces the drawing. Logic: assets/js/villa-page.js.
 * Fields: the villa page → Villa (Bedrooms, Floor by floor).
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_f      = static fn( $name ) => amour_page_field( $name );
$amour_beds   = array_values( array_filter( (array) $amour_f( 'villa_bedrooms' ), static fn( $b ) => ! empty( $b['name'] ) ) );
$amour_floors = array_values( array_filter( (array) $amour_f( 'villa_floors' ), static fn( $b ) => ! empty( $b['name'] ) ) );
$amour_plan   = (int) $amour_f( 'villa_plan' );
$amour_zone   = static fn( $i ) => 'data-zone="' . (int) $i . '"';
?>
<?php if ( $amour_beds ) : ?>
	<section class="am-villa-beds" data-villa-beds aria-labelledby="am-villa-beds">
		<div class="am-room__in">
			<div class="am-villa-beds__head">
				<h2 id="am-villa-beds" class="am-room__giant am-villa-beds__title"><?php echo esc_html( $amour_f( 'villa_beds_title' ) ); ?></h2>
				<?php if ( $amour_f( 'villa_beds_text' ) ) : ?>
					<p class="am-villa-beds__text"><?php echo esc_html( $amour_f( 'villa_beds_text' ) ); ?></p>
				<?php endif; ?>
			</div>
			<ul class="am-villa-beds__list" data-dock-avoid data-villa-beds-list aria-label="<?php esc_attr_e( 'Bedrooms', 'amour' ); ?>">
				<?php foreach ( $amour_beds as $amour_i => $amour_bed ) : ?>
					<li class="am-villa-bed" style="--i: <?php echo (int) $amour_i; ?>">
						<span class="am-villa-bed__arch">
							<?php
							if ( ! empty( $amour_bed['photo'] ) ) {
								echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
									(int) $amour_bed['photo'],
									'medium_large',
									false,
									array(
										'sizes'    => '(max-width: 760px) 64vw, 240px',
										'loading'  => 'lazy',
										'decoding' => 'async',
									)
								);
							}
							?>
						</span>
						<h3 class="am-villa-bed__name"><?php echo esc_html( $amour_bed['name'] ); ?></h3>
						<?php if ( ! empty( $amour_bed['text'] ) ) : ?>
							<p class="am-villa-bed__text"><?php echo esc_html( $amour_bed['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
<?php endif; ?>

<?php if ( count( $amour_floors ) === 3 ) : ?>
	<section class="am-villa-floors" data-villa-floors data-on="0" aria-labelledby="am-villa-floors">
		<div class="am-room__in am-villa-floors__in" data-dock-avoid>
			<figure class="am-villa-floors__fig">
				<div class="am-villa-floors__pic">
					<?php if ( $amour_plan ) : ?>
						<?php
						echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
							$amour_plan,
							'large',
							false,
							array(
								'sizes'    => '(max-width: 760px) 92vw, 700px',
								'loading'  => 'lazy',
								'decoding' => 'async',
							)
						);
						?>
					<?php else : ?>
						<svg class="am-house" viewBox="0 0 720 520" role="img" aria-label="<?php esc_attr_e( 'Drawing of the villa: ground floor, upper floor and the garden with the pool', 'amour' ); ?>">
							<rect class="am-house__sky" x="0" y="0" width="720" height="520"/>
							<g class="am-house__waves"><path d="M0 470 Q30 464 60 470 T120 470 T180 470 T240 470 T300 470 T360 470 T420 470 T480 470 T540 470 T600 470 T660 470 T720 470"/><path d="M0 490 Q30 484 60 490 T120 490 T180 490 T240 490 T300 490 T360 490 T420 490 T480 490 T540 490 T600 490 T660 490 T720 490"/></g>
							<rect class="am-house__ground" x="0" y="430" width="720" height="22"/>
							<rect class="am-house__garden" <?php echo $amour_zone( 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- an integer. ?> x="440" y="300" width="270" height="130" rx="10"/>
							<rect class="am-house__pool" x="474" y="404" width="210" height="26" rx="3"/>
							<path class="am-house__ripple" d="M486 416h40M552 418h50M630 414h34"/>
							<path class="am-house__line am-house__line--table" d="M478 372h112"/>
							<g class="am-house__chairs"><circle cx="490" cy="384" r="4"/><circle cx="512" cy="384" r="4"/><circle cx="534" cy="384" r="4"/><circle cx="556" cy="384" r="4"/><circle cx="578" cy="384" r="4"/></g>
							<path class="am-house__line am-house__line--legs" d="M484 376v28M584 376v28"/>
							<g class="am-house__tree"><path d="M646 430v-54"/><ellipse cx="646" cy="360" rx="34" ry="26"/><ellipse class="am-house__leaf" cx="628" cy="350" rx="18" ry="14"/></g>
							<g class="am-house__tree"><path d="M46 430v-64"/><ellipse cx="46" cy="352" rx="36" ry="28"/><ellipse class="am-house__leaf" cx="62" cy="342" rx="18" ry="14"/></g>
							<rect class="am-house__floor" <?php echo $amour_zone( 0 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- an integer. ?> x="96" y="290" width="330" height="140"/>
							<rect class="am-house__floor" <?php echo $amour_zone( 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- an integer. ?> x="132" y="170" width="258" height="120"/>
							<path class="am-house__line am-house__line--roof" d="M122 170h278M86 290h350"/>
							<g class="am-house__windows"><path d="M158 268v-38a14 14 0 0 1 28 0v38z"/><path d="M212 268v-38a14 14 0 0 1 28 0v38z"/><path d="M282 268v-38a14 14 0 0 1 28 0v38z"/><path d="M336 268v-38a14 14 0 0 1 28 0v38z"/><path d="M126 410v-56a16 16 0 0 1 32 0v56z"/><path d="M188 430v-80a24 24 0 0 1 48 0v80z"/><path d="M268 410v-56a16 16 0 0 1 32 0v56z"/><path d="M330 410v-56a16 16 0 0 1 32 0v56z"/></g>
							<text class="am-house__label" <?php echo $amour_zone( 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- an integer. ?> x="261" y="203" text-anchor="middle"><?php echo esc_html( mb_strtoupper( $amour_floors[1]['name'] ) ); ?></text>
							<text class="am-house__label" <?php echo $amour_zone( 0 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- an integer. ?> x="261" y="322" text-anchor="middle"><?php echo esc_html( mb_strtoupper( $amour_floors[0]['name'] ) ); ?></text>
							<text class="am-house__label" <?php echo $amour_zone( 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- an integer. ?> x="575" y="330" text-anchor="middle"><?php echo esc_html( mb_strtoupper( $amour_floors[2]['name'] ) ); ?></text>
						</svg>
					<?php endif; ?>
				</div>
				<?php if ( $amour_f( 'villa_floors_caption' ) ) : ?>
					<figcaption><?php echo esc_html( $amour_f( 'villa_floors_caption' ) ); ?></figcaption>
				<?php endif; ?>
			</figure>
			<div class="am-villa-floors__text">
				<?php if ( $amour_f( 'villa_floors_label' ) ) : ?>
					<p class="am-eyebrow"><?php echo esc_html( $amour_f( 'villa_floors_label' ) ); ?></p>
				<?php endif; ?>
				<h2 id="am-villa-floors" class="am-villa-floors__title"><?php echo esc_html( $amour_f( 'villa_floors_title' ) ); ?></h2>
				<div class="am-villa-floors__list">
					<?php foreach ( $amour_floors as $amour_i => $amour_floor ) : ?>
						<button type="button" class="am-villa-floor" data-floor="<?php echo (int) $amour_i; ?>" aria-pressed="<?php echo 0 === $amour_i ? 'true' : 'false'; ?>">
							<span class="am-villa-floor__name"><?php echo esc_html( $amour_floor['name'] ); ?></span>
							<span class="am-villa-floor__text"><?php echo esc_html( $amour_floor['text'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>
