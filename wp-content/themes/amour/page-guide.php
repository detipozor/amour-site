<?php
/**
 * The Crete guide (board Guide; HANDOFF, sections 5 "Карта Крита" and 6 "Crete guide"). Used for the page
 * /crete-guide/ in every language (amour_room_template()).
 *
 * 1. Opening: "Your home base for Crete".
 * 2. The map: the whole island in a frame; a click or tap opens it on the whole screen, where it zooms with the
 *    wheel or two fingers (up to 3×, on a phone 6×) and moves with a drag. A place flies the map to it (through
 *    clouds from the whole island) and opens its card; pointing at a place draws the road there.
 * 3. Almyrida bay: on foot from the hotel. On a phone the photo in our arch and two figures.
 * 4. Days out: a band, then the places as cards: the road from the hotel draws itself in, then the card turns
 *    to the photo and the drive time; pointing at it or focusing it turns it back to the road.
 * 5. Getting here and around.
 * Logic: assets/js/guide.js. Fields: the page (Crete guide) and Places.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
$amour_f      = static fn( $name ) => amour_page_field( 'guide_' . $name );
$amour_guide  = amour_guide();
$amour_pairs  = static fn( $name ) => array_values( array_filter( (array) $amour_f( $name ), static fn( $r ) => ! empty( $r['a'] ) ) );
$amour_coast  = get_theme_file_uri( 'assets/maps/crete-coast.svg' ) . '?v=' . AMOUR_VERSION . '#coast';
$amour_photos = array_values( array_unique( array_filter( wp_list_pluck( $amour_guide['pins'], 'photo' ) ) ) );
?>
<main id="main" tabindex="-1" class="am-guide" data-guide>

	<section class="am-gd-open" aria-labelledby="am-gd-title">
		<div class="am-gd-in am-gd-open__grid">
			<div class="am-gd-open__left">
				<nav class="am-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'amour' ); ?>">
					<a href="<?php echo esc_url( amour_home_url() ); ?>"><?php esc_html_e( 'Home', 'amour' ); ?></a> · <span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
				</nav>
				<h1 id="am-gd-title"><?php echo esc_html( $amour_f( 'title' ) ? $amour_f( 'title' ) : get_the_title() ); ?></h1>
			</div>
			<?php if ( $amour_f( 'text' ) ) : ?>
				<p class="am-gd-open__text"><?php echo esc_html( $amour_f( 'text' ) ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $amour_guide['pins'] ) : ?>
		<div class="am-gd-mapwrap">
			<section id="map" class="am-gd-map" data-gd-map data-tone="light" aria-label="<?php esc_attr_e( 'Map of Crete with day trips from the hotel', 'amour' ); ?>">
				<div class="am-gd-map__vp" data-gd-vp>
					<div class="am-gd-map__plane" data-gd-plane>
						<img class="am-gd-map__img" src="<?php echo esc_url( get_theme_file_uri( 'assets/maps/crete-relief.svg' ) ); ?>" alt="<?php esc_attr_e( 'Relief map of Crete with the hotel in Almyrida and day trips around it', 'amour' ); ?>" width="2400" height="1029" draggable="false">
						<svg class="am-gd-map__roads" viewBox="0 0 2400 1029" preserveAspectRatio="none" aria-hidden="true" focusable="false">
							<?php foreach ( $amour_guide['pins'] as $amour_i => $amour_pin ) : ?>
								<?php if ( $amour_pin['route'] ) : ?>
									<path class="am-gd-road" d="<?php echo esc_attr( $amour_pin['route'] ); ?>"/>
								<?php endif; ?>
							<?php endforeach; ?>
							<?php foreach ( $amour_guide['pins'] as $amour_i => $amour_pin ) : ?>
								<?php if ( $amour_pin['route'] ) : ?>
									<path class="am-gd-road-on am-gd-road-on--case" data-road="<?php echo (int) $amour_i; ?>" pathLength="1" d="<?php echo esc_attr( $amour_pin['route'] ); ?>"/>
									<path class="am-gd-road-on" data-road="<?php echo (int) $amour_i; ?>" pathLength="1" d="<?php echo esc_attr( $amour_pin['route'] ); ?>"/>
								<?php endif; ?>
							<?php endforeach; ?>
						</svg>
						<?php foreach ( $amour_guide['pins'] as $amour_i => $amour_pin ) : ?>
							<?php if ( 'far' === $amour_pin['kind'] ) : ?>
								<span class="am-gd-far" style="left: <?php echo esc_attr( $amour_pin['x'] ); ?>%; top: <?php echo esc_attr( $amour_pin['y'] ); ?>%" aria-hidden="true"><span class="am-gd-far__dot"></span><span class="am-gd-far__label"><?php echo esc_html( $amour_pin['label'] ); ?></span></span>
							<?php else : ?>
								<button type="button" class="am-gd-pin<?php echo 'hotel' === $amour_pin['kind'] ? ' am-gd-pin--hotel' : ''; ?>" data-i="<?php echo (int) $amour_i; ?>"
									data-x="<?php echo esc_attr( $amour_pin['x'] ); ?>" data-y="<?php echo esc_attr( $amour_pin['y'] ); ?>"
									data-side="<?php echo esc_attr( $amour_pin['guide_dir'] ? $amour_pin['guide_dir'] : $amour_pin['dir'] ); ?>" data-side-phone="<?php echo esc_attr( $amour_pin['guide_dir_phone'] ? $amour_pin['guide_dir_phone'] : $amour_pin['dir_phone'] ); ?>"
									style="left: <?php echo esc_attr( $amour_pin['x'] ); ?>%; top: <?php echo esc_attr( $amour_pin['y'] ); ?>%">
									<span class="am-gd-pin__dot" aria-hidden="true"></span><span class="am-gd-pin__label"><span class="am-gd-pin__long"><?php echo esc_html( $amour_pin['label'] ); ?></span><span class="am-gd-pin__short"><?php echo esc_html( $amour_pin['short'] ? $amour_pin['short'] : $amour_pin['label'] ); ?></span></span>
								</button>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="am-gd-clouds" data-gd-clouds aria-hidden="true"><span></span><span></span><span></span></div>
				</div>

				<div class="am-gd-card" data-gd-card aria-hidden="true">
					<div class="am-gd-card__pics">
						<?php foreach ( $amour_photos as $amour_photo ) : ?>
							<?php
							echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
								(int) $amour_photo,
								'medium_large',
								false,
								array(
									'data-photo' => (int) $amour_photo,
									'sizes'      => '(max-width: 760px) 88px, 360px',
									'loading'    => 'lazy',
									'decoding'   => 'async',
								)
							);
							?>
						<?php endforeach; ?>
					</div>
					<div class="am-gd-card__body">
						<h2 class="am-h3 am-gd-card__name" data-gd-card-name></h2>
						<p class="am-gd-card__meta" data-gd-card-meta></p>
						<p class="am-gd-card__text" data-gd-card-text></p>
					</div>
				</div>

				<button type="button" class="am-gd-btn am-gd-btn--open" data-gd-open><?php esc_html_e( 'Open the map', 'amour' ); ?></button>
				<button type="button" class="am-gd-btn am-gd-btn--close" data-gd-close aria-label="<?php esc_attr_e( 'Close the map', 'amour' ); ?>"><span aria-hidden="true">✕</span></button>
				<p class="am-gd-hint" aria-hidden="true">
					<span class="am-gd-hint__desk"><?php esc_html_e( 'Scroll to zoom, drag to move', 'amour' ); ?></span>
					<span class="am-gd-hint__phone"><?php esc_html_e( 'Pinch to zoom, tap a place', 'amour' ); ?></span>
				</p>
				<script type="application/json" data-gd-data>
				<?php
				echo wp_json_encode(
					array_map(
						static fn( $p ) => array(
							'name'  => 'hotel' === $p['kind'] ? $p['label'] : $p['name'],
							'meta'  => $p['meta'],
							'text'  => $p['text'],
							'photo' => $p['photo'],
						),
						$amour_guide['pins']
					)
				);
				?>
																</script>
			</section>
		</div>
	<?php endif; ?>

	<?php if ( $amour_f( 'bay_title' ) ) : ?>
		<section class="am-gd-bay" aria-labelledby="am-gd-bay">
			<div class="am-gd-in am-gd-bay__grid">
				<div class="am-gd-bay__pic">
					<?php
					echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
						(int) $amour_f( 'bay_photo' ),
						'large',
						false,
						array(
							'sizes'    => '(max-width: 760px) 92vw, 760px',
							'loading'  => 'lazy',
							'decoding' => 'async',
						)
					);
					?>
					<p class="am-gd-bay__over" aria-hidden="true"><span class="am-eyebrow am-eyebrow--light"><?php echo esc_html( $amour_f( 'bay_label' ) ); ?></span><span class="am-gd-bay__over-title"><?php echo esc_html( $amour_f( 'bay_title' ) ); ?></span></p>
				</div>
				<div class="am-gd-bay__card">
					<p class="am-eyebrow"><?php echo esc_html( $amour_f( 'bay_label' ) ); ?></p>
					<h2 id="am-gd-bay" class="am-gd-bay__title"><?php echo esc_html( $amour_f( 'bay_title' ) ); ?></h2>
					<p class="am-gd-bay__text"><?php echo esc_html( $amour_f( 'bay_text' ) ); ?></p>
					<?php if ( $amour_f( 'bay_short' ) ) : ?>
						<p class="am-gd-bay__short"><?php echo esc_html( $amour_f( 'bay_short' ) ); ?></p>
					<?php endif; ?>
					<?php if ( $amour_pairs( 'bay_facts' ) ) : ?>
						<dl class="am-gd-bay__facts">
							<?php foreach ( $amour_pairs( 'bay_facts' ) as $amour_row ) : ?>
								<div><dt><?php echo esc_html( $amour_row['a'] ); ?></dt><dd><?php echo esc_html( $amour_row['b'] ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
					<?php if ( $amour_pairs( 'bay_figures' ) ) : ?>
						<div class="am-gd-bay__figures">
							<?php foreach ( $amour_pairs( 'bay_figures' ) as $amour_row ) : ?>
								<p><span class="am-gd-bay__figure"><?php echo esc_html( $amour_row['a'] ); ?></span><span><?php echo esc_html( $amour_row['b'] ); ?></span></p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $amour_guide['days'] ) : ?>
		<section class="am-gd-band" data-tone="dark" aria-hidden="true">
			<?php
			echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
				(int) $amour_f( 'days_photo' ),
				'full',
				false,
				array(
					'class'    => 'am-gd-band__img',
					'alt'      => '',
					'sizes'    => '100vw',
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			);
			?>
			<p class="am-gd-band__title"><?php echo esc_html( $amour_f( 'days_title' ) ); ?></p>
			<p class="am-gd-band__text"><?php echo esc_html( $amour_f( 'days_text' ) ); ?></p>
		</section>

		<section id="days" class="am-gd-days" aria-labelledby="am-gd-days">
			<div class="am-gd-in">
				<h2 id="am-gd-days" class="sr-only"><?php echo esc_html( $amour_f( 'days_title' ) ); ?></h2>
				<ul class="am-gd-days__list" data-gd-days>
					<?php foreach ( $amour_guide['days'] as $amour_i => $amour_day ) : ?>
						<?php $amour_m = $amour_day['mini']; ?>
						<li class="am-day" style="--i: <?php echo (int) ( $amour_i % 3 ); ?>">
							<?php /* translators: 1: place, 2: drive time, 3: distance. */ ?>
							<div class="am-day__flip" tabindex="0" role="group" aria-label="<?php echo esc_attr( sprintf( __( '%1$s: %2$s, %3$s. Point at it or focus it to see the road from the hotel.', 'amour' ), $amour_day['name'], $amour_day['time'], $amour_day['km'] ) ); ?>">
								<div class="am-day__inner">
									<div class="am-day__face am-day__face--map" aria-hidden="true">
										<?php if ( $amour_m ) : ?>
											<svg class="am-day__map" viewBox="<?php echo esc_attr( $amour_m['vb'] ); ?>" preserveAspectRatio="xMidYMid slice" style="--u: <?php echo esc_attr( $amour_m['u'] ); ?>" focusable="false">
												<rect class="am-day__sea" x="-100" y="-100" width="2600" height="1300"/>
												<use class="am-day__coast" href="<?php echo esc_url( $amour_coast ); ?>"/>
												<path class="am-day__road am-day__road--case" d="<?php echo esc_attr( $amour_day['route'] ); ?>"/>
												<path class="am-day__road" pathLength="1" d="<?php echo esc_attr( $amour_day['route'] ); ?>"/>
												<circle class="am-day__home" cx="<?php echo esc_attr( $amour_m['start'][0] ); ?>" cy="<?php echo esc_attr( $amour_m['start'][1] ); ?>"/>
												<circle class="am-day__there" cx="<?php echo esc_attr( $amour_m['end'][0] ); ?>" cy="<?php echo esc_attr( $amour_m['end'][1] ); ?>"/>
											</svg>
										<?php endif; ?>
										<span class="am-day__tag"><?php esc_html_e( 'The road from the hotel', 'amour' ); ?></span>
									</div>
									<div class="am-day__face am-day__face--photo<?php echo $amour_day['photo'] ? '' : ' is-empty'; ?>">
										<?php
										if ( $amour_day['photo'] ) {
											echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
												(int) $amour_day['photo'],
												'medium_large',
												false,
												array(
													'sizes'    => '(max-width: 760px) 78vw, 400px',
													'loading'  => 'lazy',
													'decoding' => 'async',
												)
											);
										}
										?>
										<span class="am-day__info" aria-hidden="true">
											<span class="am-day__time"><?php echo esc_html( $amour_day['time'] ); ?></span>
											<span class="am-day__km"><?php echo esc_html( $amour_day['km'] . ( $amour_day['note'] ? ' · ' . $amour_day['note'] : '' ) ); ?></span>
										</span>
									</div>
								</div>
							</div>
							<h3 class="am-day__name"><?php echo esc_html( $amour_day['name'] ); ?></h3>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $amour_pairs( 'getting' ) ) : ?>
		<section class="am-gd-getting" aria-labelledby="am-gd-getting">
			<div class="am-gd-in am-gd-getting__grid">
				<h2 id="am-gd-getting" class="am-gd-getting__title"><?php echo esc_html( $amour_f( 'getting_title' ) ); ?></h2>
				<div class="am-gd-getting__list">
					<?php foreach ( $amour_pairs( 'getting' ) as $amour_row ) : ?>
						<div>
							<h3><?php echo esc_html( $amour_row['a'] ); ?></h3>
							<p><?php echo esc_html( $amour_row['b'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
