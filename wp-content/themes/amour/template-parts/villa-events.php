<?php
/**
 * Weddings and gatherings on the villa page (gen_villa.py, section #events; HANDOFF, section 6: "Long tables,
 * long evenings": праздники и свадьбы, «Plan a gathering», WhatsApp). The bay at dusk, the heading line by line,
 * the arch with the dinner at sunset; it all comes up once in view (assets/js/villa-page.js).
 * The address #gatherings is where "Weddings and gatherings" links lead (home page, villa footer).
 * Fields: the villa page → Villa → Weddings and gatherings; WhatsApp: Hotel settings.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_f     = static fn( $name ) => amour_page_field( $name );
$amour_title = trim( (string) $amour_f( 'villa_events_title' ) );
if ( '' === $amour_title ) {
	return;
}
$amour_lines    = preg_split( '/\R/', $amour_title );
$amour_button   = $amour_f( 'villa_events_button' );
$amour_whatsapp = amour_contacts()['whatsapp'];
$amour_bg       = (int) $amour_f( 'villa_events_bg' );
$amour_photo    = (int) $amour_f( 'villa_events_photo' );
?>
<section id="gatherings" class="am-villa-events" data-villa-events data-tone="dark" data-dock-avoid aria-labelledby="am-villa-events">
	<?php
	if ( $amour_bg ) {
		echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
			$amour_bg,
			'full',
			false,
			array(
				'class'    => 'am-villa-events__bg',
				'alt'      => '',
				'sizes'    => '100vw',
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
	}
	?>
	<div class="am-room__in am-villa-events__in">
		<div class="am-villa-events__text">
			<?php if ( $amour_f( 'villa_events_label' ) ) : ?>
				<p class="am-eyebrow am-villa-events__fade"><?php echo esc_html( $amour_f( 'villa_events_label' ) ); ?></p>
			<?php endif; ?>
			<h2 id="am-villa-events" class="am-villa-events__title">
				<?php foreach ( $amour_lines as $amour_i => $amour_line ) : ?>
					<span class="am-villa-events__ln" style="--i: <?php echo (int) $amour_i; ?>"><span><?php echo esc_html( $amour_line ); ?></span></span>
				<?php endforeach; ?>
			</h2>
			<?php if ( $amour_f( 'villa_events_text' ) ) : ?>
				<p class="am-villa-events__line am-villa-events__fade"><?php echo esc_html( $amour_f( 'villa_events_text' ) ); ?></p>
			<?php endif; ?>
			<div class="am-villa-events__actions am-villa-events__fade">
				<?php if ( is_array( $amour_button ) && ! empty( $amour_button['url'] ) ) : ?>
					<a class="am-btn am-villa-events__go" href="<?php echo esc_url( $amour_button['url'] ); ?>"><?php echo esc_html( $amour_button['title'] ); ?></a>
				<?php endif; ?>
				<?php if ( $amour_whatsapp['url'] ) : ?>
					<a class="am-villa-events__wa" href="<?php echo esc_url( $amour_whatsapp['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Write on WhatsApp', 'amour' ); ?></a>
				<?php elseif ( '' !== amour_or_placeholder( '', 'x' ) ) : ?>
					<span class="am-villa-events__wa"><?php echo esc_html( '[' . __( 'Write on WhatsApp', 'amour' ) . ']' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( $amour_photo ) : ?>
			<figure class="am-villa-events__fig">
				<span class="am-villa-events__arch">
					<?php
					echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
						$amour_photo,
						'large',
						false,
						array(
							'sizes'    => '(max-width: 760px) 92vw, 400px',
							'loading'  => 'lazy',
							'decoding' => 'async',
						)
					);
					?>
				</span>
				<?php if ( $amour_f( 'villa_events_caption' ) ) : ?>
					<figcaption class="am-villa-events__fade"><?php echo esc_html( $amour_f( 'villa_events_caption' ) ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>
	</div>
</section>
