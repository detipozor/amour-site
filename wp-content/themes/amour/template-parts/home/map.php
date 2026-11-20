<?php
/**
 * The map on the home page (board Main-v2-b, "Home map"; HANDOFF, section 6.8). Pinned full screen for 2300 px:
 * an opening — the places part, the camera drops through the clouds onto the island — then four views:
 * the whole of Crete, the hotel, the villa, day trips (point at or tap a place to see the road there).
 * Tabs at the bottom jump between the views; the card at the bottom right tells about the view or the place.
 * Phone: closer views, short labels, the card without a photo. Not pinned with reduced motion: the tabs switch views.
 * Logic: assets/js/home/map.js. Places: Places in the admin; texts: Home → Map.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_places = amour_map_places();
$amour_views  = array_values( (array) amour_page_field( 'home_map_views' ) );
if ( ! $amour_places || count( $amour_views ) < 4 ) {
	return;
}
$amour_intro_label  = (string) amour_page_field( 'home_map_intro_label' );
$amour_intro_title  = (string) amour_page_field( 'home_map_intro_title' );
$amour_intro_photos = array_values( array_filter( (array) amour_page_field( 'home_map_intro_photos' ), static fn( $p ) => ! empty( $p['photo'] ) ) );
$amour_trips        = array_values( wp_list_filter( $amour_places, array( 'kind' => 'trip' ) ) );
$amour_link         = static fn( $link ) => is_array( $link ) ? array( (string) ( $link['title'] ?? '' ), (string) ( $link['url'] ?? '' ) ) : array( '', '' );

// Everything the script needs to change the card and the pins, in one block of data.
$amour_data      = array(
	'views'   => array_map(
		static function ( $view ) use ( $amour_link ) {
			list( $label, $url ) = $amour_link( $view['link'] ?? null );
			return array(
				'eyebrow'   => (string) $view['eyebrow'],
				'title'     => (string) $view['title'],
				'meta'      => (string) $view['meta'],
				'text'      => (string) $view['text'],
				'textPhone' => (string) ( $view['text_phone'] ?? '' ),
				'link'      => $label,
				'href'      => $url,
				'photo'     => (int) ( $view['photo'] ?? 0 ),
			);
		},
		$amour_views
	),
	'trips'   => array_map(
		static fn( $trip ) => array(
			'title' => $trip['name'],
			'meta'  => $trip['meta'],
			'text'  => $trip['text'],
			'photo' => $trip['photo'],
		),
		$amour_trips
	),
	'strings' => array(
		'trip'     => __( 'Day trip', 'amour' ),
		'tripLink' => __( 'Open the Crete guide', 'amour' ),
		'tripHref' => amour_page_url( 'crete-guide' ),
		'noPhoto'  => amour_or_placeholder( '', 'Photo to come' ),
	),
);
$amour_photo_ids = array_values( array_unique( array_filter( array_merge( wp_list_pluck( $amour_data['views'], 'photo' ), wp_list_pluck( $amour_data['trips'], 'photo' ) ) ) ) );
$amour_first     = $amour_data['views'][0];
$amour_map_alt   = __( 'Relief map of Crete with Amour Relax Hotel and Villa Eudora in Almyrida and day trips around them', 'amour' );
?>
<section id="guide" class="am-map" data-map aria-labelledby="am-map-title">
	<h2 id="am-map-title" class="sr-only"><?php esc_html_e( 'Where we are: the hotel, the villa and day trips on a map of Crete', 'amour' ); ?></h2>
	<div class="am-map__pin" data-tone="light">
		<div class="am-map__frame" data-map-frame>

			<div class="am-map__canvas" data-map-canvas>
				<img class="am-map__img" src="<?php echo esc_url( get_theme_file_uri( 'assets/maps/crete-relief.svg' ) ); ?>" alt="<?php echo esc_attr( $amour_map_alt ); ?>" width="2400" height="1029" loading="lazy" decoding="async" draggable="false">

				<svg class="am-map__roads" viewBox="0 0 2400 1029" preserveAspectRatio="none" aria-hidden="true" focusable="false">
					<?php foreach ( $amour_trips as $amour_i => $amour_trip ) : ?>
						<?php if ( $amour_trip['route'] ) : ?>
							<path class="am-road" d="<?php echo esc_attr( $amour_trip['route'] ); ?>"/>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php foreach ( $amour_trips as $amour_i => $amour_trip ) : ?>
						<?php if ( $amour_trip['route'] ) : ?>
							<path class="am-road-on am-road-on--case" data-road="<?php echo (int) $amour_i; ?>" pathLength="1" d="<?php echo esc_attr( $amour_trip['route'] ); ?>"/>
							<path class="am-road-on" data-road="<?php echo (int) $amour_i; ?>" pathLength="1" d="<?php echo esc_attr( $amour_trip['route'] ); ?>"/>
						<?php endif; ?>
					<?php endforeach; ?>
				</svg>

				<?php foreach ( $amour_places as $amour_place ) : ?>
					<?php if ( 'far' === $amour_place['kind'] ) : ?>
						<span class="am-far" data-far style="left: <?php echo esc_attr( $amour_place['x'] ); ?>%; top: <?php echo esc_attr( $amour_place['y'] ); ?>%" aria-hidden="true">
							<span class="am-far__dot"></span><span class="am-far__label"><?php echo esc_html( $amour_place['label'] ); ?></span>
						</span>
					<?php else : ?>
						<?php
						// Day trips are buttons (point, focus or tap: the road and the card); the hotel and the villa are labels.
						// The name is the visible label (WCAG 2.5.3); the card tells the rest and is read out when it changes.
						$amour_trip_i = array_search( $amour_place['id'], wp_list_pluck( $amour_trips, 'id' ), true );
						$amour_tag    = false !== $amour_trip_i ? 'button' : 'span';
						?>
						<<?php echo esc_attr( $amour_tag ); ?> <?php echo 'button' === $amour_tag ? 'type="button"' : ''; ?> class="am-pin am-pin--<?php echo esc_attr( $amour_place['kind'] ); ?>"
							data-kind="<?php echo esc_attr( $amour_place['kind'] ); ?>"
							<?php echo false !== $amour_trip_i ? 'data-trip="' . (int) $amour_trip_i . '"' : ''; ?>
							data-dir="<?php echo esc_attr( $amour_place['dir'] ); ?>" data-dir-phone="<?php echo esc_attr( $amour_place['dir_phone'] ); ?>"
							data-label="<?php echo esc_attr( $amour_place['label'] ); ?>" data-short="<?php echo esc_attr( $amour_place['short'] ); ?>"
							style="left: <?php echo esc_attr( $amour_place['x'] ); ?>%; top: <?php echo esc_attr( $amour_place['y'] ); ?>%">
							<span class="am-pin__dot"></span><span class="am-pin__label"><?php echo esc_html( $amour_place['label'] ); ?></span>
						</<?php echo esc_attr( $amour_tag ); ?>>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<div class="am-clouds am-clouds--flight" data-map-clouds aria-hidden="true"><span></span><span></span><span></span></div>

			<div class="am-map__intro" data-map-intro aria-hidden="true">
				<?php foreach ( array_slice( $amour_intro_photos, 0, 6 ) as $amour_i => $amour_photo ) : ?>
					<figure class="am-map__fig" data-fig="<?php echo (int) $amour_i; ?>">
						<span class="am-map__fig-pic">
							<?php
							echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
								(int) $amour_photo['photo'],
								'medium_large',
								false,
								array(
									'alt'      => '',
									'sizes'    => '(max-width: 760px) 42vw, 280px',
									'loading'  => 'lazy',
									'decoding' => 'async',
								)
							);
							?>
						</span>
						<?php if ( ! empty( $amour_photo['caption'] ) ) : ?>
							<figcaption><?php echo esc_html( $amour_photo['caption'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
				<div class="am-clouds am-clouds--intro"><span></span><span></span><span></span></div>
				<div class="am-map__intro-text">
					<?php if ( $amour_intro_label ) : ?>
						<p class="am-eyebrow"><?php echo esc_html( $amour_intro_label ); ?></p>
					<?php endif; ?>
					<p class="am-map__intro-title"><?php echo esc_html( $amour_intro_title ); ?></p>
				</div>
			</div>

			<div class="am-map__card" data-map-card aria-live="polite">
				<div class="am-map__card-pics" data-card-pics>
					<?php foreach ( $amour_photo_ids as $amour_photo_id ) : ?>
						<?php
						echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
							$amour_photo_id,
							'medium_large',
							false,
							array(
								'data-photo' => $amour_photo_id,
								'sizes'      => '368px',
								'loading'    => 'lazy',
								'decoding'   => 'async',
							)
						);
						?>
					<?php endforeach; ?>
					<p class="am-map__card-nophoto" data-card-nophoto><?php echo esc_html( $amour_data['strings']['noPhoto'] ); ?></p>
				</div>
				<div class="am-map__card-body" data-card-body>
					<p class="am-eyebrow" data-card="eyebrow"><?php echo esc_html( $amour_first['eyebrow'] ); ?></p>
					<h3 class="am-h3 am-map__card-title" data-card="title"><?php echo esc_html( $amour_first['title'] ); ?></h3>
					<p class="am-map__card-meta" data-card="meta"><?php echo esc_html( $amour_first['meta'] ); ?></p>
					<p class="am-map__card-text" data-card="text"><?php echo esc_html( $amour_first['text'] ); ?></p>
					<a class="am-map__card-link" data-card="link" href="<?php echo esc_url( $amour_first['href'] ); ?>"><?php echo esc_html( $amour_first['link'] ); ?></a>
				</div>
			</div>

			<div class="am-map__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Map views', 'amour' ); ?>">
				<?php foreach ( array_slice( $amour_data['views'], 1, 3, true ) as $amour_k => $amour_view ) : ?>
					<button type="button" role="tab" class="am-map__tab" data-step="<?php echo (int) $amour_k; ?>" aria-selected="false">
						<?php echo esc_html( (string) $amour_views[ $amour_k ]['tab'] ); ?><span class="am-map__tab-fill" aria-hidden="true"></span>
					</button>
				<?php endforeach; ?>
			</div>
			<p class="am-map__hint" data-map-hint aria-hidden="true"><?php esc_html_e( 'Scroll to come closer', 'amour' ); ?></p>
		</div>
	</div>
	<script type="application/json" data-map-data><?php echo wp_json_encode( $amour_data ); ?></script>
</section>
