<?php
/**
 * About (board About; HANDOFF, section 6 "About"): the heading with the photo in our arch, the philosophy that
 * lights up word by word as it rises (like the phrase on the home page; assets/js/about.js), three photos
 * at different heights, and "Stay like you live here". Fields: the page (About page).
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
$amour_f       = static fn( $name ) => amour_page_field( 'ab_' . $name );
$amour_words   = preg_split( '/\s+/', trim( (string) $amour_f( 'phrase' ) ) );
$amour_gallery = array_values( array_filter( (array) $amour_f( 'gallery' ), static fn( $g ) => ! empty( $g['photo'] ) ) );
?>
<main id="main" tabindex="-1" class="am-about">

	<section class="am-pg-open am-ab-top" aria-labelledby="am-ab-title">
		<div class="am-pg-in am-ab-top__grid">
			<div class="am-ab-top__text">
				<nav class="am-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'amour' ); ?>">
					<a href="<?php echo esc_url( amour_home_url() ); ?>"><?php esc_html_e( 'Home', 'amour' ); ?></a> · <span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
				</nav>
				<h1 id="am-ab-title" class="am-ab-top__title"><?php echo esc_html( $amour_f( 'title' ) ? $amour_f( 'title' ) : get_the_title() ); ?></h1>
				<?php if ( $amour_f( 'text' ) ) : ?>
					<p class="am-ab-top__lead"><?php echo esc_html( $amour_f( 'text' ) ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $amour_f( 'photo' ) ) : ?>
				<span class="am-arch am-ab-top__arch">
					<?php
					echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
						(int) $amour_f( 'photo' ),
						'large',
						false,
						array(
							'sizes'         => '(max-width: 760px) 92vw, 480px',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
						)
					);
					?>
				</span>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $amour_f( 'phrase' ) ) : ?>
		<section class="am-ab-phil" data-ab-phrase aria-labelledby="am-ab-phil">
			<div class="am-ab-phil__in">
				<h2 id="am-ab-phil" class="am-eyebrow"><?php echo esc_html( $amour_f( 'label' ) ); ?></h2>
				<p class="am-ab-phil__text" data-axe-motion style="--n: <?php echo count( $amour_words ); ?>">
					<?php foreach ( $amour_words as $amour_i => $amour_word ) : ?>
						<span style="--i: <?php echo (int) $amour_i; ?>"><?php echo esc_html( $amour_word ); ?></span>
					<?php endforeach; ?>
				</p>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $amour_gallery ) : ?>
		<section class="am-ab-gal" aria-label="<?php esc_attr_e( 'Photos of the hotel', 'amour' ); ?>">
			<div class="am-pg-in am-ab-gal__grid">
				<?php foreach ( $amour_gallery as $amour_i => $amour_g ) : ?>
					<figure class="am-ab-fig am-ab-fig--<?php echo (int) $amour_i; ?>">
						<span class="am-ab-fig__pic">
							<?php
							echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
								(int) $amour_g['photo'],
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
						<figcaption><span><?php echo esc_html( $amour_g['caption'] ); ?></span><span><?php echo (int) $amour_i + 1; ?> / <?php echo count( $amour_gallery ); ?></span></figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $amour_f( 'cta' ) ) : ?>
		<section class="am-ab-end" aria-labelledby="am-ab-end">
			<div class="am-pg-in am-ab-end__box" data-tone="dark">
				<h2 id="am-ab-end" class="am-ab-end__title"><?php echo esc_html( $amour_f( 'cta' ) ); ?></h2>
				<div class="am-ab-end__actions">
					<a class="am-btn" href="<?php echo esc_url( amour_page_url( 'book' ) ); ?>"><?php esc_html_e( 'Book your stay', 'amour' ); ?></a>
					<a class="am-btn am-btn--on-photo" href="<?php echo esc_url( amour_page_url( 'rooms' ) ); ?>"><?php esc_html_e( 'See the rooms', 'amour' ); ?></a>
				</div>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
