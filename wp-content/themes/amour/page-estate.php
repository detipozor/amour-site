<?php
/**
 * Court and pool (board Estate; HANDOFF, section 6 "Court and pool"). Used for the page /court-and-pool/ in every
 * language (amour_room_template()).
 *
 * 1. First screen: the court video, "Game, set, sunset", the facts, Check dates and a court slot on WhatsApp.
 *    It stays while the court rises over it and darkens under it.
 * 2. The court, pinned for 1000 px: the serve in the low sun turns into the lit court at night.
 * 3. The pool: three points, pinned for 3 × 460 px; each brings its photo up from the bottom.
 * 4. The estate from above: six places on the aerial photo (template-parts/estate-plan.php).
 * 5. Pack a swimsuit, leave the racket.
 * Logic: assets/js/estate.js. Fields: the page in the admin (Court and pool).
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
$amour_f      = static fn( $name ) => amour_page_field( 'estate_' . $name );
$amour_video  = (int) $amour_f( 'video' );
$amour_poster = (int) $amour_f( 'poster' );
$amour_court  = array_pad( preg_split( '/\R/', trim( (string) $amour_f( 'court_title' ) ) ), 2, '' );
$amour_points = array_values( array_filter( (array) $amour_f( 'pool_points' ), static fn( $p ) => ! empty( $p['title'] ) ) );
$amour_wa     = amour_contacts()['whatsapp'];
$amour_img    = static fn( $id, $classes, $sizes = '100vw', $alt = null ) => $id ? wp_get_attachment_image(
	$id,
	'full',
	false,
	array_merge(
		array(
			'class'    => $classes,
			'sizes'    => $sizes,
			'loading'  => 'lazy',
			'decoding' => 'async',
		),
		null === $alt ? array() : array( 'alt' => $alt )
	)
) : '';
?>
<main id="main" tabindex="-1" class="am-estate" data-estate>

	<div class="am-es-stack">
		<section class="am-es-hero" data-es-hero data-tone="dark" aria-labelledby="am-es-title">
			<div class="am-es-hero__frame">
				<?php
				if ( $amour_poster ) {
					echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
						$amour_poster,
						'full',
						false,
						array(
							'class'         => 'am-es-hero__poster',
							'alt'           => (string) $amour_f( 'video_label' ),
							'sizes'         => '100vw',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
						)
					);
				}
				?>
				<?php if ( $amour_video ) : ?>
					<video class="am-es-hero__video" muted loop playsinline preload="none" aria-hidden="true" data-src="<?php echo esc_url( (string) wp_get_attachment_url( $amour_video ) ); ?>"></video>
				<?php endif; ?>
				<div class="am-es-hero__shade" aria-hidden="true"></div>
				<div class="am-es-hero__dim" aria-hidden="true"></div>
				<div class="am-es-hero__text">
					<div class="am-es-hero__left">
						<nav class="am-crumbs am-crumbs--light" aria-label="<?php esc_attr_e( 'Breadcrumb', 'amour' ); ?>">
							<a href="<?php echo esc_url( amour_home_url() ); ?>"><?php esc_html_e( 'Home', 'amour' ); ?></a> · <span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
						</nav>
						<h1 id="am-es-title" class="am-es-hero__title"><?php echo esc_html( $amour_f( 'title' ) ? $amour_f( 'title' ) : get_the_title() ); ?></h1>
					</div>
					<div class="am-es-hero__right">
						<?php if ( $amour_f( 'text' ) ) : ?>
							<p><?php echo esc_html( $amour_f( 'text' ) ); ?></p>
						<?php endif; ?>
						<?php if ( $amour_f( 'facts' ) ) : ?>
							<p class="am-es-hero__facts"><?php echo esc_html( $amour_f( 'facts' ) ); ?></p>
						<?php endif; ?>
						<div class="am-es-hero__actions">
							<a class="am-btn" href="<?php echo esc_url( amour_page_url( 'book' ) ); ?>" data-amour-cal="arr"><?php esc_html_e( 'Check dates', 'amour' ); ?></a>
							<?php if ( $amour_wa['url'] ) : ?>
								<a class="am-es-hero__wa" href="<?php echo esc_url( $amour_wa['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Book a court slot on WhatsApp', 'amour' ); ?></a>
							<?php elseif ( '' !== amour_or_placeholder( '', 'x' ) ) : ?>
								<span class="am-es-hero__wa"><?php echo esc_html( '[' . __( 'Book a court slot on WhatsApp', 'amour' ) . ']' ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php if ( $amour_video ) : ?>
				<button type="button" class="am-es-hero__pause" data-es-pause aria-label="<?php esc_attr_e( 'Pause video', 'amour' ); ?>" data-label-play="<?php esc_attr_e( 'Play video', 'amour' ); ?>" data-label-pause="<?php esc_attr_e( 'Pause video', 'amour' ); ?>" hidden>
					<svg class="am-es-hero__pause-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M8 5v14M16 5v14" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" fill="none"/></svg>
					<svg class="am-es-hero__play-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg>
				</button>
			<?php endif; ?>
		</section>

		<section class="am-es-court" data-es-court aria-labelledby="am-es-court">
			<div class="am-es-court__pin" data-tone="dark">
				<?php
				echo $amour_img( (int) $amour_f( 'court_day' ), 'am-es-court__img am-es-court__img--day' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
				echo $amour_img( (int) $amour_f( 'court_night' ), 'am-es-court__img am-es-court__img--night' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
				?>
				<div class="am-es-court__shade" aria-hidden="true"></div>
				<div class="am-es-court__head">
					<?php if ( $amour_f( 'court_label' ) ) : ?>
						<p class="am-eyebrow am-eyebrow--light"><?php echo esc_html( $amour_f( 'court_label' ) ); ?></p>
					<?php endif; ?>
					<h2 id="am-es-court" class="am-es-court__title">
						<span class="am-es-court__line am-es-court__line--day"><?php echo esc_html( $amour_court[0] ); ?></span>
						<span class="am-es-court__line am-es-court__line--night"><?php echo esc_html( $amour_court[1] ); ?></span>
					</h2>
				</div>
				<?php if ( $amour_f( 'court_text' ) ) : ?>
					<div class="am-es-court__card"><p><?php echo esc_html( $amour_f( 'court_text' ) ); ?></p></div>
				<?php endif; ?>
				<div class="am-es-court__bar" aria-hidden="true">
					<span><?php esc_html_e( 'Day', 'amour' ); ?></span><span class="am-es-court__track"><span class="am-es-court__dot"></span></span><span><?php esc_html_e( 'Night', 'amour' ); ?></span>
				</div>
			</div>
		</section>
	</div>

	<?php if ( $amour_points ) : ?>
		<section class="am-es-pool" data-es-pool aria-labelledby="am-es-pool">
			<div class="am-es-pool__intro am-es-enter">
				<?php if ( $amour_f( 'pool_label' ) ) : ?>
					<p class="am-eyebrow"><?php echo esc_html( $amour_f( 'pool_label' ) ); ?></p>
				<?php endif; ?>
				<h2 id="am-es-pool" class="am-es-pool__title"><?php echo esc_html( $amour_f( 'pool_title' ) ); ?></h2>
				<?php if ( $amour_f( 'pool_text' ) ) : ?>
					<p class="am-es-pool__text"><?php echo esc_html( $amour_f( 'pool_text' ) ); ?></p>
				<?php endif; ?>
			</div>
			<div class="am-es-pool__pin" data-es-pool-pin data-step="0">
				<div class="am-es-pool__stage" data-hdr="split">
					<ol class="am-es-pool__points">
						<?php foreach ( $amour_points as $amour_i => $amour_point ) : ?>
							<li class="am-es-point" data-point="<?php echo (int) $amour_i; ?>">
								<span class="am-es-point__n"><?php echo esc_html( str_pad( (string) ( $amour_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<span class="am-es-point__body">
									<span class="am-es-point__title"><?php echo esc_html( $amour_point['title'] ); ?></span>
									<span class="am-es-point__text"><?php echo esc_html( $amour_point['text'] ); ?></span>
								</span>
							</li>
						<?php endforeach; ?>
					</ol>
					<div class="am-es-pool__photo">
						<?php foreach ( $amour_points as $amour_i => $amour_point ) : ?>
							<?php echo $amour_img( (int) $amour_point['photo'], 'am-es-pool__img am-es-pool__img--' . (int) $amour_i, '(max-width: 760px) 100vw, 50vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes. ?>
						<?php endforeach; ?>
						<div class="am-es-pool__shade" aria-hidden="true"></div>
						<p class="am-es-pool__count" aria-hidden="true"><span data-es-count>01</span><span>/ <?php echo esc_html( str_pad( (string) count( $amour_points ), 2, '0', STR_PAD_LEFT ) ); ?></span></p>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/estate-plan' ); ?>

	<?php if ( $amour_f( 'end_title' ) ) : ?>
		<section class="am-es-end am-es-enter" aria-labelledby="am-es-end">
			<?php if ( $amour_f( 'end_label' ) ) : ?>
				<p class="am-eyebrow"><?php echo esc_html( $amour_f( 'end_label' ) ); ?></p>
			<?php endif; ?>
			<h2 id="am-es-end" class="am-es-end__title"><?php echo esc_html( $amour_f( 'end_title' ) ); ?></h2>
			<?php if ( $amour_f( 'end_text' ) ) : ?>
				<p class="am-es-end__text"><?php echo esc_html( $amour_f( 'end_text' ) ); ?></p>
			<?php endif; ?>
			<div class="am-es-end__actions">
				<a class="am-btn" href="<?php echo esc_url( amour_page_url( 'rooms' ) ); ?>"><?php esc_html_e( 'See the rooms', 'amour' ); ?></a>
				<a class="am-btn am-btn--ghost" href="<?php echo esc_url( amour_page_url( 'villa-eudora' ) ); ?>"><?php esc_html_e( 'Villa Eudora', 'amour' ); ?></a>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
