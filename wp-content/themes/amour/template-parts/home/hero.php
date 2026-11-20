<?php
/**
 * First screen (board Main-v2, HANDOFF section 6.1): the drone video in an arch, the big AMOUR, the line
 * next to it (the page heading) and Check dates. While the page scrolls the first 520 px the screen stays
 * pinned and the arch opens to the whole screen (assets/js/home/hero.js sets --p from 0 to 1).
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_line   = (string) amour_page_field( 'home_hero_line' );
$amour_note   = (string) amour_page_field( 'home_hero_note' );
$amour_poster = (int) amour_page_field( 'home_hero_poster' );
$amour_video  = (int) amour_page_field( 'home_hero_video' );
$amour_phone  = (int) amour_page_field( 'home_hero_video_phone' );
$amour_label  = (string) amour_page_field( 'home_hero_video_label' );
?>
<div class="am-hero" data-hero>
	<section class="am-hero__stage" aria-labelledby="am-hero-line">
		<div class="am-hero__side am-hero__side--left">
			<h1 id="am-hero-line" class="am-hero__line"><?php echo esc_html( amour_or_placeholder( $amour_line, 'Line next to the arch' ) ); ?></h1>
		</div>
		<div class="am-hero__side am-hero__side--right">
			<?php if ( $amour_note ) : ?>
				<p class="am-hero__note"><?php echo esc_html( $amour_note ); ?></p>
			<?php endif; ?>
			<a class="am-btn" href="#book"><?php esc_html_e( 'Check dates', 'amour' ); ?></a>
		</div>

		<div class="am-hero__arch">
			<?php
			if ( $amour_poster ) {
				// The poster is the largest picture of the first screen: no lazy loading, high priority.
				echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
					$amour_poster,
					'full',
					false,
					array(
						'class'         => 'am-hero__poster',
						'alt'           => $amour_label,
						'sizes'         => '100vw',
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'decoding'      => 'async',
					)
				);
			}
			?>
			<?php if ( $amour_video ) : ?>
				<video class="am-hero__video" muted loop playsinline preload="none" aria-hidden="true"
					data-src="<?php echo esc_url( (string) wp_get_attachment_url( $amour_video ) ); ?>"
					<?php echo $amour_phone ? 'data-src-phone="' . esc_url( (string) wp_get_attachment_url( $amour_phone ) ) . '"' : ''; ?>></video>
			<?php endif; ?>
			<div class="am-hero__scrim" aria-hidden="true"></div>
		</div>

		<?php if ( $amour_video ) : ?>
			<button type="button" class="am-hero__pause" data-hero-pause aria-label="<?php esc_attr_e( 'Pause video', 'amour' ); ?>" data-label-play="<?php esc_attr_e( 'Play video', 'amour' ); ?>" data-label-pause="<?php esc_attr_e( 'Pause video', 'amour' ); ?>" hidden>
				<svg class="am-hero__pause-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M8 5v14M16 5v14" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" fill="none"/></svg>
				<svg class="am-hero__play-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg>
			</button>
		<?php endif; ?>

		<?php
		echo amour_logo( 'amour-word', '', 'am-hero__word am-hero__word--dark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo().
		echo amour_logo( 'amour-word', '', 'am-hero__word am-hero__word--light' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo().
		?>
	</section>
</div>
