<?php
/**
 * The villa on the home page (board Main-v2-b, "vh2"; HANDOFF, section 6.7): it takes over where the rooms
 * grid left the villa card — an arch in the middle with "Room for" and "everyone" on both sides. Pinned for
 * 1100 px of scroll: the background turns from limestone to foam, the words part and fade, the arch opens to
 * the whole screen, then one line, a button and the facts come up (assets/js/home/villa.js).
 * Reduced motion: no pinning, the open photo with its text.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_cards = wp_list_filter( amour_cards(), array( 'villa' => true ) );
$amour_card  = $amour_cards ? reset( $amour_cards ) : null;
if ( ! $amour_card || ! $amour_card['photo'] ) {
	return;
}
$amour_words    = array_pad( preg_split( '/\R/', trim( (string) amour_page_field( 'home_villa_words' ) ) ), 2, '' );
$amour_captions = array_pad( preg_split( '/\R/', trim( (string) amour_page_field( 'home_villa_captions' ) ) ), 2, '' );
$amour_title    = (string) amour_page_field( 'home_villa_title' );
$amour_meta     = (string) amour_page_field( 'home_villa_meta' );
$amour_button   = (string) amour_page_field( 'home_villa_button' );
$amour_link     = (string) amour_page_field( 'home_villa_link' );
?>
<section id="villa" class="am-villa" data-home-villa data-theme="eudora" data-brand="eudora" aria-labelledby="am-villa-title">
	<div class="am-villa__pin" data-villa-pin data-tone="light">
		<h2 id="am-villa-title" class="sr-only"><?php echo esc_html( trim( 'Villa Eudora: ' . implode( ' ', array_filter( $amour_words ) ) ) ); ?></h2>
		<p class="am-vword am-vword--l" data-vword="l" aria-hidden="true"><?php echo esc_html( $amour_words[0] ); ?></p>
		<p class="am-vword am-vword--r" data-vword="r" aria-hidden="true"><?php echo esc_html( $amour_words[1] ); ?></p>
		<p class="am-vcap am-vcap--l" data-vcap="l" aria-hidden="true"><?php echo esc_html( $amour_captions[0] ); ?></p>
		<p class="am-vcap am-vcap--r" data-vcap="r" aria-hidden="true"><?php echo esc_html( $amour_captions[1] ); ?></p>

		<div class="am-villa__arch" data-villa-arch>
			<?php
			echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
				$amour_card['photo'],
				'full',
				false,
				array(
					'sizes'    => '100vw',
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			);
			?>
			<div class="am-villa__shade" aria-hidden="true"></div>
		</div>

		<div class="am-villa__text" data-villa-text>
			<div class="am-villa__main">
				<?php if ( $amour_title ) : ?>
					<p class="am-villa__title"><?php echo wp_kses( $amour_title, array( 'br' => array() ) ); ?></p>
				<?php endif; ?>
				<div class="am-villa__actions">
					<a class="am-btn am-btn--limestone" href="<?php echo esc_url( $amour_card['url'] ); ?>"><?php echo esc_html( $amour_button ? $amour_button : __( 'Discover the villa', 'amour' ) ); ?></a>
					<?php if ( $amour_link ) : ?>
						<a class="am-villa__link" href="<?php echo esc_url( $amour_card['url'] . '#gatherings' ); ?>"><?php echo esc_html( $amour_link ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( $amour_meta ) : ?>
				<p class="am-villa__meta"><?php echo esc_html( $amour_meta ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
