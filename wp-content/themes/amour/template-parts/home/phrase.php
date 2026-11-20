<?php
/**
 * The phrase on the wine block (HANDOFF, section 6.3): the words light up one by one as the page scrolls,
 * from 18% to full; the block grows from a framed card to the full width (assets/js/home/phrase.js).
 * With reduced motion the whole phrase is there at once.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_phrase = trim( (string) amour_page_field( 'home_phrase' ) );
if ( '' === $amour_phrase ) {
	return;
}
$amour_words = preg_split( '/\s+/', $amour_phrase );
?>
<section class="am-phrase" data-phrase data-tone="dark">
	<div class="am-phrase__in">
		<p class="am-phrase__text" data-axe-motion style="--n: <?php echo count( $amour_words ); ?>">
			<?php foreach ( $amour_words as $amour_i => $amour_word ) : ?>
				<span style="--i: <?php echo (int) $amour_i; ?>"><?php echo esc_html( $amour_word ); ?> </span>
			<?php endforeach; ?>
		</p>
	</div>
</section>
