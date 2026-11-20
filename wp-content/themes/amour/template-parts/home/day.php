<?php
/**
 * A day at Amour (board Main-v2; HANDOFF, section 6.5): a strip of arches from morning to evening, the one in
 * the middle in colour, the rest quiet (grey and a little sepia). Desktop: the block is pinned and the scroll
 * moves the strip one frame at a time on a soft curve (assets/js/home/day.js). Phone and reduced motion:
 * a strip that scrolls with a finger, the neighbours peek at the edges (docs/12, section 10).
 *
 * The strip repeats the frames on both sides (three before, three after), so the first and last frames have
 * neighbours too; the repeats are hidden from screen readers.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_title  = (string) amour_page_field( 'home_day_title' );
$amour_frames = array_values( array_filter( (array) amour_page_field( 'home_day_frames' ), static fn( $f ) => ! empty( $f['photo'] ) ) );
$amour_count  = count( $amour_frames );
if ( $amour_count < 2 ) {
	return;
}
?>
<section id="a-day-at-amour" class="am-day" data-day aria-labelledby="am-day-title" style="--nf: <?php echo (int) $amour_count; ?>">
	<div class="am-day__stage">
		<div class="am-day__head">
			<h2 id="am-day-title" class="am-day__title"><?php echo esc_html( $amour_title ); ?></h2>
		</div>

		<div class="am-day__strip" data-day-strip>
			<?php for ( $amour_k = -3; $amour_k <= $amour_count + 2; $amour_k++ ) : ?>
				<?php
				$amour_own   = $amour_k >= 0 && $amour_k < $amour_count;
				$amour_frame = $amour_frames[ ( ( $amour_k % $amour_count ) + $amour_count ) % $amour_count ];
				$amour_alt   = $amour_own ? (string) get_post_meta( (int) $amour_frame['photo'], '_wp_attachment_image_alt', true ) : '';
				?>
				<figure class="am-day__card<?php echo $amour_own ? '' : ' am-day__card--repeat'; ?>" data-k="<?php echo (int) $amour_k; ?>"<?php echo $amour_own ? '' : ' aria-hidden="true"'; ?>>
					<?php
					echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
						(int) $amour_frame['photo'],
						'large',
						false,
						array(
							'alt'      => $amour_alt,
							'sizes'    => '(max-width: 760px) 78vw, 420px',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'style'    => 'object-position: ' . esc_attr( ! empty( $amour_frame['focus'] ) ? $amour_frame['focus'] : '50% 50%' ),
						)
					);
					?>
				</figure>
			<?php endfor; ?>
		</div>

		<div class="am-day__caps" data-axe-motion>
			<?php foreach ( $amour_frames as $amour_i => $amour_frame ) : ?>
				<div class="am-day__cap<?php echo 0 === $amour_i ? ' is-active' : ''; ?>" data-i="<?php echo (int) $amour_i; ?>">
					<?php if ( ! empty( $amour_frame['label'] ) ) : ?>
						<p class="am-eyebrow"><?php echo esc_html( $amour_frame['label'] ); ?></p>
					<?php endif; ?>
					<p class="am-day__line"><?php echo esc_html( (string) $amour_frame['line'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
