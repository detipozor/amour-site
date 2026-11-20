<?php
/**
 * Site footer (HANDOFF, section 5; boards About and Villa-v2-b; motion from design/css/amour-motion.css).
 * A field of olive pictograms (at the villa: the sea lines) with a slow diagonal wave and brighter olives
 * around the cursor; columns from the menus, Contact, networks, two legal lines; the big word at the bottom.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_brand  = amour_brand();
$amour_villa  = 'eudora' === $amour_brand;
$amour_footer = amour_footer_data( $amour_brand );
$amour_legal  = get_nav_menu_locations()['legal'] ?? 0;
$amour_legal  = $amour_legal ? wp_get_nav_menu_items( $amour_legal ) : array();

/**
 * One link, or plain text when there is no address (a placeholder on the test copy).
 *
 * @param array{label: string, url: string} $link Link.
 */
$amour_link = static function ( $link ) {
	if ( $link['url'] ) {
		printf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $link['url'], array( 'http', 'https', 'tel', 'mailto' ) ),
			esc_html( $link['label'] )
		);
	} else {
		printf( '<span>%s</span>', esc_html( $link['label'] ) );
	}
};
?>
<footer class="am-footer<?php echo $amour_villa ? ' am-footer--villa' : ''; ?>" data-tone="dark">
	<?php if ( $amour_villa ) : ?>
		<div class="am-footer__field am-footer__field--waves" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/patterns/waves.svg' ) ); ?>" alt="" width="1440" height="760" loading="lazy" decoding="async"></div>
		<div class="am-footer__field am-footer__field--waves am-footer__lens" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/patterns/waves.svg' ) ); ?>" alt="" width="1440" height="760" loading="lazy" decoding="async"></div>
	<?php else : ?>
		<?php foreach ( array( '', ' am-footer__lens' ) as $amour_layer ) : ?>
			<div class="am-footer__field<?php echo esc_attr( $amour_layer ); ?>" aria-hidden="true">
				<?php for ( $amour_i = 0; $amour_i < 96; $amour_i++ ) : ?>
					<span style="--d: <?php echo esc_attr( number_format( ( ( $amour_i % 16 ) + floor( $amour_i / 16 ) * 1.4 ) * 0.12, 2, '.', '' ) ); ?>s"></span>
				<?php endfor; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<div class="am-footer__in">
		<a class="am-footer__brand" href="<?php echo esc_url( amour_home_url() ); ?>" aria-label="<?php echo esc_attr( $amour_villa ? __( 'Villa Eudora, home', 'amour' ) : __( 'Amour Relax Hotel, home', 'amour' ) ); ?>">
			<?php echo $amour_villa ? amour_logo( 'eudora-horizontal' ) : amour_logo( 'amour-main' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?>
		</a>

		<div class="am-footer__cols">
			<?php foreach ( $amour_footer['columns'] as $amour_index => $amour_column ) : ?>
				<nav class="am-footer__col" aria-labelledby="am-footer-col-<?php echo (int) $amour_index; ?>">
					<p class="am-eyebrow am-eyebrow--light" id="am-footer-col-<?php echo (int) $amour_index; ?>"><?php echo esc_html( $amour_column['title'] ); ?></p>
					<ul>
						<?php foreach ( $amour_column['links'] as $amour_item ) : ?>
							<li><?php $amour_link( $amour_item ); ?></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endforeach; ?>

			<?php if ( $amour_footer['contact'] ) : ?>
				<div class="am-footer__col">
					<p class="am-eyebrow am-eyebrow--light"><?php esc_html_e( 'Contact', 'amour' ); ?></p>
					<ul>
						<?php foreach ( $amour_footer['contact'] as $amour_item ) : ?>
							<li><?php $amour_link( $amour_item ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $amour_footer['social'] ) : ?>
			<ul class="am-footer__social">
				<?php foreach ( $amour_footer['social'] as $amour_item ) : ?>
					<li>
						<?php if ( $amour_item['url'] ) : ?>
							<a href="<?php echo esc_url( $amour_item['url'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $amour_item['label'] ); ?><span class="sr-only"> <?php esc_html_e( '(opens in a new tab)', 'amour' ); ?></span></a>
						<?php else : ?>
							<span><?php echo esc_html( $amour_item['label'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<div class="am-footer__legal">
			<div class="am-footer__legal-text">
				<?php foreach ( $amour_footer['legal'] as $amour_index => $amour_line ) : ?>
					<p<?php echo $amour_index ? ' class="am-footer__legal-small"' : ''; ?>><?php echo esc_html( $amour_line ); ?></p>
				<?php endforeach; ?>
			</div>
			<?php if ( $amour_legal ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Legal', 'amour' ); ?>">
					<ul>
						<?php foreach ( $amour_legal as $amour_item ) : ?>
							<li><a href="<?php echo esc_url( $amour_item->url ); ?>"><?php echo esc_html( $amour_item->title ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</div>

	<?php echo amour_logo( $amour_villa ? 'eudora-word' : 'amour-word', '', 'am-footer__word' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?>
</footer>
<?php wp_footer(); ?>
</body>
</html>
