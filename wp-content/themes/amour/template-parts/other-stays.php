<?php
/**
 * "Other ways to stay" on the room pages (room_template.html; HANDOFF, section 6): the other rooms and the villa,
 * three cards with a photo, the name and two facts. The villa page shows the rooms under its own heading.
 * Cards: the Card box of each room and of the villa in the admin.
 *
 * @package Amour
 *
 * $args:
 *   title (string) Heading. Default "Other ways to stay".
 *   all   (string) The link to the Rooms page. Default "All rooms and the villa".
 */

defined( 'ABSPATH' ) || exit;

$amour_here  = array_map( 'intval', amour_core_ids_or_self( get_queried_object_id() ) );
$amour_cards = array_values(
	array_filter(
		amour_cards(),
		static fn( $card ) => ! in_array( $card['id'], $amour_here, true )
	)
);
if ( ! $amour_cards ) {
	return;
}
$amour_title = ! empty( $args['title'] ) ? $args['title'] : __( 'Other ways to stay', 'amour' );
$amour_all   = ! empty( $args['all'] ) ? $args['all'] : __( 'All rooms and the villa', 'amour' );
?>
<section class="am-others" data-others aria-labelledby="am-others-title">
	<div class="am-others__in">
		<div class="am-others__head">
			<h2 id="am-others-title" class="am-others__title"><?php echo esc_html( $amour_title ); ?></h2>
			<a class="am-others__all" href="<?php echo esc_url( amour_page_url( 'rooms' ) ); ?>"><?php echo esc_html( $amour_all ); ?> <span aria-hidden="true">→</span></a>
		</div>
		<ul class="am-others__grid" data-dock-avoid>
			<?php foreach ( array_slice( $amour_cards, 0, 3 ) as $amour_i => $amour_card ) : ?>
				<li class="am-other" style="--i: <?php echo (int) $amour_i; ?>">
					<a class="am-other__link" href="<?php echo esc_url( $amour_card['url'] ); ?>" aria-label="<?php echo esc_attr( $amour_card['label'] ); ?>">
						<span class="am-other__pic">
							<?php
							if ( $amour_card['photo'] ) {
								echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
									$amour_card['photo'],
									'large',
									false,
									array(
										'alt'      => '',
										'sizes'    => '(max-width: 760px) 80vw, 400px',
										'loading'  => 'lazy',
										'decoding' => 'async',
									)
								);
							}
							?>
						</span>
						<span class="am-other__row">
							<span class="am-other__name"><?php echo esc_html( $amour_card['name'] ); ?></span>
							<span class="am-other__facts" aria-hidden="true">
								<?php foreach ( array_slice( $amour_card['facts'], 0, 2 ) as $amour_fact ) : ?>
									<span class="am-chip">
										<?php if ( ! empty( $amour_fact['icon'] ) && isset( AMOUR_FACT_ICONS[ $amour_fact['icon'] ] ) ) : ?>
											<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="<?php echo esc_attr( AMOUR_FACT_ICONS[ $amour_fact['icon'] ] ); ?>"/></svg>
										<?php endif; ?>
										<?php echo esc_html( $amour_fact['text'] ); ?>
									</span>
								<?php endforeach; ?>
							</span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
