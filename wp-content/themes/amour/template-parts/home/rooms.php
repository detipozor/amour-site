<?php
/**
 * Rooms grid (board Main-v2; HANDOFF, section 6.6): "Stay like you live here", the three room types and the villa.
 * Desktop: two columns, a second photo and a slight zoom under the pointer. Phone: one column of tall photos
 * that drift with the scroll, the facts on their own line (amour-soft.css, phone rules).
 * Cards come from the room pages and the villa page (their "Card" fields).
 *
 * Then the grid holds (pinned) and the villa card flies to the middle of the screen, the words "Room for" and
 * "everyone" come in on both sides, and the villa block (template-parts/home/villa.php) takes over from there
 * (assets/js/home/villa.js). Not pinned with reduced motion.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_cards = amour_cards();
if ( ! $amour_cards ) {
	return;
}
$amour_title  = preg_split( '/\R/', trim( (string) amour_page_field( 'home_rooms_title' ) ) );
$amour_rooms  = count( array_filter( $amour_cards, static fn( $c ) => ! $c['villa'] ) );
$amour_villas = count( $amour_cards ) - $amour_rooms;

// Board layout: left column cards 1 and 3, right column 2 and 4; the first and the last are tall.
$amour_columns = array( array(), array() );
foreach ( $amour_cards as $amour_i => $amour_card ) {
	$amour_card['tall']              = 0 === $amour_i || count( $amour_cards ) - 1 === $amour_i;
	$amour_columns[ $amour_i % 2 ][] = $amour_card;
}
?>
<section id="rooms" class="am-rooms" data-rooms aria-labelledby="am-rooms-title">
	<div class="am-rooms__pin" data-rooms-pin>
	<div class="am-rooms__in">
		<div class="am-rooms__head">
			<p class="am-rooms__counts">
				<span class="am-chip"><span class="am-chip__n"><?php echo (int) $amour_rooms; ?></span><?php echo esc_html( _n( 'Room', 'Rooms', $amour_rooms, 'amour' ) ); ?></span>
				<?php if ( $amour_villas ) : ?>
					<span class="am-chip"><span class="am-chip__n"><?php echo (int) $amour_villas; ?></span><?php esc_html_e( 'Villa', 'amour' ); ?></span>
				<?php endif; ?>
			</p>
			<h2 id="am-rooms-title" class="am-rooms__title">
				<?php foreach ( array_filter( $amour_title ) as $amour_line ) : ?>
					<span class="am-rooms__ln"><?php echo esc_html( $amour_line ); ?></span>
				<?php endforeach; ?>
			</h2>
		</div>

		<div class="am-rooms__grid">
			<?php foreach ( $amour_columns as $amour_column ) : ?>
				<div class="am-rooms__col">
					<?php foreach ( $amour_column as $amour_card ) : ?>
						<a class="am-unit<?php echo $amour_card['villa'] ? ' am-unit--villa' : ''; ?><?php echo $amour_card['tall'] ? ' am-unit--tall' : ''; ?>" href="<?php echo esc_url( $amour_card['url'] ); ?>"
							data-theme="<?php echo $amour_card['villa'] ? 'eudora' : 'amour'; ?>"<?php echo $amour_card['villa'] ? ' data-brand="eudora"' : ''; ?>
							aria-label="<?php echo esc_attr( $amour_card['label'] ); ?>">
							<span class="am-unit__pic">
								<?php
								if ( $amour_card['photo'] ) {
									echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
										$amour_card['photo'],
										'large',
										false,
										array(
											'class'    => 'am-unit__img',
											'sizes'    => '(max-width: 760px) 92vw, 620px',
											'loading'  => 'lazy',
											'decoding' => 'async',
											'data-parallax' => 'phone',
										)
									);
								}
								if ( $amour_card['hover'] ) {
									echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
										$amour_card['hover'],
										'large',
										false,
										array(
											'class'       => 'am-unit__alt',
											'alt'         => '',
											'aria-hidden' => 'true',
											'sizes'       => '620px',
											'loading'     => 'lazy',
											'decoding'    => 'async',
										)
									);
								}
								?>
							</span>
							<span class="am-unit__row">
								<span class="am-unit__name"><?php echo esc_html( $amour_card['name'] ); ?></span>
								<span class="am-unit__facts" aria-hidden="true">
									<?php foreach ( $amour_card['facts'] as $amour_fact ) : ?>
										<span class="am-pill">
											<?php if ( ! empty( $amour_fact['icon'] ) && isset( AMOUR_FACT_ICONS[ $amour_fact['icon'] ] ) ) : ?>
												<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="<?php echo esc_attr( AMOUR_FACT_ICONS[ $amour_fact['icon'] ] ); ?>"/></svg>
											<?php endif; ?>
											<?php echo esc_html( $amour_fact['text'] ); ?>
										</span>
									<?php endforeach; ?>
									<?php if ( $amour_card['price'] ) : ?>
										<span class="am-pill"><?php echo esc_html( sprintf( /* translators: %s: price, e.g. 95 */ __( 'from €%s', 'amour' ), $amour_card['price'] ) ); ?></span>
									<?php endif; ?>
								</span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<?php
	// The villa card on its way to the middle of the screen, and the words around it (decoration: the villa block says it in words).
	$amour_villa_card = wp_list_filter( $amour_cards, array( 'villa' => true ) );
	$amour_villa_card = $amour_villa_card ? reset( $amour_villa_card ) : null;
	$amour_words      = array_pad( preg_split( '/\R/', trim( (string) amour_page_field( 'home_villa_words' ) ) ), 2, '' );
	$amour_captions   = array_pad( preg_split( '/\R/', trim( (string) amour_page_field( 'home_villa_captions' ) ) ), 2, '' );
	?>
	<?php if ( $amour_villa_card && $amour_villa_card['photo'] ) : ?>
		<div class="am-rooms__fly" data-rooms-fly aria-hidden="true">
			<?php
			echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
				$amour_villa_card['photo'],
				'large',
				false,
				array(
					'alt'      => '',
					'sizes'    => '(max-width: 760px) 92vw, 620px',
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			);
			?>
		</div>
	<?php endif; ?>
	<p class="am-vword am-vword--l" data-vword="l" aria-hidden="true"><?php echo esc_html( $amour_words[0] ); ?></p>
	<p class="am-vword am-vword--r" data-vword="r" aria-hidden="true"><?php echo esc_html( $amour_words[1] ); ?></p>
	<p class="am-vcap am-vcap--l" data-vcap="l" aria-hidden="true"><?php echo esc_html( $amour_captions[0] ); ?></p>
	<p class="am-vcap am-vcap--r" data-vcap="r" aria-hidden="true"><?php echo esc_html( $amour_captions[1] ); ?></p>
	</div>
	<div class="am-rooms__tail" aria-hidden="true"></div>
</section>
