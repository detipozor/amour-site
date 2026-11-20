<?php
/**
 * The estate (HANDOFF, section 6.4): the photo grows from half the width to the full screen, its rounded
 * top flattens and the heading rises, all tied to the scroll (assets/js/home/estate.js sets --r).
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_label = (string) amour_page_field( 'home_estate_label' );
$amour_title = (string) amour_page_field( 'home_estate_title' );
$amour_photo = (int) amour_page_field( 'home_estate_photo' );
if ( '' === $amour_title && ! $amour_photo ) {
	return;
}
?>
<section class="am-estate" data-estate aria-labelledby="am-estate-title">
	<div class="am-estate__in">
		<div class="am-estate__head">
			<?php if ( $amour_label ) : ?>
				<p class="am-eyebrow"><?php echo esc_html( $amour_label ); ?></p>
			<?php endif; ?>
			<h2 id="am-estate-title" class="am-h1"><?php echo esc_html( $amour_title ); ?></h2>
		</div>
		<?php if ( $amour_photo ) : ?>
			<div class="am-estate__photo">
				<?php
				echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
					$amour_photo,
					'full',
					false,
					array(
						'sizes'    => '100vw',
						'loading'  => 'lazy',
						'decoding' => 'async',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
