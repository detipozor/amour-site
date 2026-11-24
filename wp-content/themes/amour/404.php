<?php
/**
 * Page not found (board NotFound; HANDOFF, section 6 "404"): "This path ends here" with the photo in our arch
 * and the ways back: home, Rooms, Villa Eudora, Crete guide. Photo: Hotel settings → the error page.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
$amour_photo = (int) amour_option( 'amour_404_photo' );
?>
<main id="main" tabindex="-1" class="am-notfound">
	<section class="am-pg-open am-nf" aria-labelledby="am-nf-title">
		<div class="am-pg-in am-nf__grid">
			<?php if ( $amour_photo ) : ?>
				<span class="am-arch am-nf__arch">
					<?php echo wp_get_attachment_image( $amour_photo, 'large', false, array( 'sizes' => '(max-width: 760px) 70vw, 380px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes. ?>
				</span>
			<?php endif; ?>
			<div class="am-nf__text">
				<p class="am-eyebrow"><?php esc_html_e( 'Error 404', 'amour' ); ?></p>
				<h1 id="am-nf-title"><?php esc_html_e( 'This path ends here', 'amour' ); ?></h1>
				<p><?php esc_html_e( 'The page you were looking for has moved or never existed. Everything else is where we left it.', 'amour' ); ?></p>
				<div class="am-nf__actions">
					<a class="am-btn" href="<?php echo esc_url( amour_home_url() ); ?>"><?php esc_html_e( 'Back to home', 'amour' ); ?></a>
					<a class="am-btn am-btn--ghost" href="<?php echo esc_url( amour_page_url( 'rooms' ) ); ?>"><?php esc_html_e( 'Rooms', 'amour' ); ?></a>
					<a class="am-btn am-btn--ghost" href="<?php echo esc_url( amour_page_url( 'villa-eudora' ) ); ?>"><?php esc_html_e( 'Villa Eudora', 'amour' ); ?></a>
					<a class="am-btn am-btn--ghost" href="<?php echo esc_url( amour_page_url( 'crete-guide' ) ); ?>"><?php esc_html_e( 'Crete guide', 'amour' ); ?></a>
				</div>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
