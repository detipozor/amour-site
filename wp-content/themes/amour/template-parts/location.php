<?php
/**
 * The location block of the room and villa pages (design/generators/room_template.html, "Almyrida, Crete";
 * HANDOFF, section 6: "Карта окрестностей с временем в пути и карточкой «Almyrida, Crete»").
 * The whole island first; pinned for 760 px, the camera comes down to Almyrida with the hotel always in view,
 * then the road to Chania and the nearby places appear. Without pinning (reduced motion, short screens) it stands
 * close in. Logic: assets/js/location.js. Card: Hotel settings → Location card; places: Places → "On the map of
 * the room and villa pages".
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_loc   = amour_location();
$amour_hotel = wp_list_filter( $amour_loc['places'], array( 'kind' => 'hotel' ) );
$amour_hotel = $amour_hotel ? reset( $amour_hotel ) : null;
if ( ! $amour_hotel || '' === $amour_loc['title'] ) {
	return;
}
?>
<section class="am-loc" data-loc data-hotel-x="<?php echo esc_attr( $amour_hotel['x'] ); ?>" data-hotel-y="<?php echo esc_attr( $amour_hotel['y'] ); ?>" aria-labelledby="am-loc-title">
	<div class="am-loc__pin" data-tone="light" data-dock-avoid>
		<div class="am-loc__canvas" data-loc-canvas>
			<img class="am-loc__img" src="<?php echo esc_url( get_theme_file_uri( 'assets/maps/crete-relief.svg' ) ); ?>" alt="<?php esc_attr_e( 'Relief map of Apokoronas: the hotel above Almyrida bay, Chania and the airport to the north-west', 'amour' ); ?>" width="2400" height="1029" loading="lazy" decoding="async" draggable="false">
			<svg class="am-loc__roads" viewBox="0 0 2400 1029" preserveAspectRatio="none" aria-hidden="true" focusable="false">
				<?php foreach ( $amour_loc['places'] as $amour_place ) : ?>
					<?php if ( $amour_place['route'] ) : ?>
						<path class="am-loc__road am-loc__road--case" d="<?php echo esc_attr( $amour_place['route'] ); ?>"/>
						<path class="am-loc__road" d="<?php echo esc_attr( $amour_place['route'] ); ?>"/>
					<?php endif; ?>
				<?php endforeach; ?>
			</svg>
			<?php foreach ( $amour_loc['places'] as $amour_place ) : ?>
				<?php
				// The hotel's label to the left, day trips to the right, the rest as set for the place; a phone has
				// short labels and its own sides, so the labels stay clear of each other.
				$amour_side  = 'hotel' === $amour_place['kind'] ? 'row-reverse' : ( 'near' === $amour_place['kind'] ? $amour_place['dir'] : 'row' );
				$amour_phone = 'near' === $amour_place['kind'] ? $amour_place['dir_phone'] : $amour_side;
				$amour_short = $amour_place['short'] ? $amour_place['short'] : $amour_place['label'];
				?>
				<span class="am-loc__spot<?php echo 'hotel' === $amour_place['kind'] ? ' is-main' : ''; ?>" data-side="<?php echo esc_attr( $amour_side ); ?>" data-side-phone="<?php echo esc_attr( $amour_phone ); ?>" style="left: <?php echo esc_attr( $amour_place['x'] ); ?>%; top: <?php echo esc_attr( $amour_place['y'] ); ?>%" aria-hidden="true">
					<span class="am-loc__dot"></span><span class="am-loc__label"><span class="am-loc__long"><?php echo esc_html( $amour_place['label'] ); ?></span><span class="am-loc__short"><?php echo esc_html( $amour_short ); ?></span></span>
				</span>
			<?php endforeach; ?>
		</div>

		<p class="am-loc__hint" aria-hidden="true"><?php esc_html_e( 'Scroll to come closer', 'amour' ); ?></p>

		<div class="am-loc__card" data-loc-card>
			<p class="am-eyebrow"><?php esc_html_e( 'Location', 'amour' ); ?></p>
			<h2 id="am-loc-title" class="am-loc__title"><?php echo esc_html( $amour_loc['title'] ); ?></h2>
			<?php if ( $amour_loc['text'] ) : ?>
				<p class="am-loc__text"><?php echo esc_html( $amour_loc['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( $amour_loc['near'] ) : ?>
				<dl class="am-loc__near">
					<?php foreach ( $amour_loc['near'] as $amour_near ) : ?>
						<div>
							<dt><?php echo esc_html( $amour_near['place'] ); ?></dt>
							<dd><?php echo esc_html( $amour_near['value'] ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
			<?php if ( $amour_loc['maps'] ) : ?>
				<a class="am-loc__maps" href="<?php echo esc_url( $amour_loc['maps'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open in Google Maps', 'amour' ); ?> <span aria-hidden="true">↗</span></a>
			<?php elseif ( '' !== amour_or_placeholder( '', 'x' ) ) : ?>
				<span class="am-loc__maps"><?php echo esc_html( '[' . __( 'Open in Google Maps', 'amour' ) . ']' ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</section>
