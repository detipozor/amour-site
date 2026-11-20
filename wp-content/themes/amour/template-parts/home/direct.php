<?php
/**
 * Book direct on the home page (board Main-v2-b, "cta"; HANDOFF, section 6.10): a full-screen photo of late sun
 * on a limestone wall, and over it the label, the heading, a line and the button to the date search.
 * The text comes up softly once the block is in view (assets/js/home/direct.js). Texts: Home → Book direct.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_title = (string) amour_page_field( 'home_direct_title' );
if ( '' === $amour_title ) {
	return;
}
$amour_label = (string) amour_page_field( 'home_direct_label' );
$amour_text  = (string) amour_page_field( 'home_direct_text' );
$amour_link  = amour_page_field( 'home_direct_link' );
$amour_photo = (int) amour_page_field( 'home_direct_photo' );
?>
<section class="am-direct" data-direct data-tone="light" aria-labelledby="am-direct-title">
	<?php
	if ( $amour_photo ) {
		echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
			$amour_photo,
			'full',
			false,
			array(
				'class'    => 'am-direct__photo',
				'sizes'    => '100vw',
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
	}
	?>
	<div class="am-direct__text" data-direct-text>
		<?php if ( $amour_label ) : ?>
			<p class="am-eyebrow"><?php echo esc_html( $amour_label ); ?></p>
		<?php endif; ?>
		<h2 id="am-direct-title" class="am-direct__title"><?php echo esc_html( $amour_title ); ?></h2>
		<?php if ( $amour_text ) : ?>
			<p class="am-direct__line"><?php echo esc_html( $amour_text ); ?></p>
		<?php endif; ?>
		<?php if ( is_array( $amour_link ) && ! empty( $amour_link['url'] ) ) : ?>
			<a class="am-btn" href="<?php echo esc_url( $amour_link['url'] ); ?>"><?php echo esc_html( $amour_link['title'] ); ?></a>
		<?php endif; ?>
	</div>
</section>
