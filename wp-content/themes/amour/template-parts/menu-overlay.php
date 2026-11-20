<?php
/**
 * Full-screen menu (board Menu-list): page names only. On a desktop a photo in an arch on the left changes
 * with the name under the pointer or the keyboard focus; on a phone the names are a plain list.
 * Items, photos and lines come from Appearance → Menus (location "Main menu"), contacts and networks from Hotel settings.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_locations = get_nav_menu_locations();
$amour_items     = ! empty( $amour_locations['primary'] ) ? wp_get_nav_menu_items( $amour_locations['primary'] ) : array();
$amour_items     = $amour_items ? array_values( $amour_items ) : array();
$amour_queried   = (int) get_queried_object_id();
$amour_languages = amour_languages();
$amour_contacts  = amour_contacts();
$amour_socials   = amour_socials( true );
$amour_field     = static fn( $name, $item_id ) => function_exists( 'get_field' ) ? get_field( $name, $item_id ) : null;
$amour_first     = $amour_items ? (string) $amour_field( 'amour_menu_line', $amour_items[0]->ID ) : '';
?>
<div id="am-menu" class="am-menu" data-theme="amour" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'amour' ); ?>" hidden>
	<div class="am-menu__head">
		<button type="button" class="am-menu__close" data-menu-close>
			<?php echo amour_icon( 'close', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_icon(). ?>
			<span class="am-menu__close-label"><?php esc_html_e( 'Close', 'amour' ); ?></span>
		</button>
		<a class="am-menu__logo" href="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Amour Relax Hotel, home', 'amour' ); ?>">
			<?php echo amour_logo( 'amour-horizontal' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?>
		</a>
		<a class="am-btn am-btn--on-photo am-menu__book" href="<?php echo esc_url( amour_page_url( 'book' ) ); ?>"><?php esc_html_e( 'Book', 'amour' ); ?></a>
	</div>

	<div class="am-menu__stage">
		<div class="am-menu__pic" aria-hidden="true">
			<div class="am-menu__arch">
				<?php foreach ( $amour_items as $amour_index => $amour_item ) : ?>
					<?php
					$amour_photo = (int) $amour_field( 'amour_menu_photo', $amour_item->ID );
					if ( $amour_photo ) {
						echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
							$amour_photo,
							'large',
							false,
							array(
								'class'      => 0 === $amour_index ? 'is-active' : '',
								'alt'        => '',
								'sizes'      => '(max-width: 760px) 1px, 374px',
								'loading'    => 'lazy',
								'decoding'   => 'async',
								'data-index' => $amour_index,
							)
						);
					}
					?>
				<?php endforeach; ?>
			</div>
			<p class="am-menu__line"><?php echo esc_html( $amour_first ); ?></p>
		</div>

		<nav class="am-menu__nav" aria-label="<?php esc_attr_e( 'Main', 'amour' ); ?>">
			<ul>
				<?php foreach ( $amour_items as $amour_index => $amour_item ) : ?>
					<li style="--i: <?php echo (int) $amour_index; ?>">
						<a href="<?php echo esc_url( $amour_item->url ); ?>"
							data-index="<?php echo (int) $amour_index; ?>"
							data-line="<?php echo esc_attr( (string) $amour_field( 'amour_menu_line', $amour_item->ID ) ); ?>"
							<?php echo 0 === $amour_index ? ' class="is-active"' : ''; ?>
							<?php echo ( 'post_type' === $amour_item->type && (int) $amour_item->object_id === $amour_queried ) ? ' aria-current="page"' : ''; ?>>
							<?php echo esc_html( $amour_item->title ); ?><span class="am-menu__dot" aria-hidden="true"></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</div>

	<div class="am-menu__foot">
		<?php if ( count( $amour_languages ) > 1 ) : ?>
			<ul class="am-menu__langs" aria-label="<?php esc_attr_e( 'Language', 'amour' ); ?>">
				<?php foreach ( $amour_languages as $amour_language ) : ?>
					<li><a href="<?php echo esc_url( $amour_language['url'] ); ?>" lang="<?php echo esc_attr( $amour_language['locale'] ); ?>" hreflang="<?php echo esc_attr( $amour_language['locale'] ); ?>"<?php echo $amour_language['current'] ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $amour_language['code'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $amour_contacts ) : ?>
			<ul class="am-menu__contacts">
				<?php foreach ( $amour_contacts as $amour_contact ) : ?>
					<li>
						<?php if ( $amour_contact['url'] ) : ?>
							<a href="<?php echo esc_url( $amour_contact['url'], array( 'tel', 'mailto', 'https' ) ); ?>"><?php echo esc_html( $amour_contact['label'] ); ?></a>
						<?php else : ?>
							<span><?php echo esc_html( $amour_contact['label'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $amour_socials ) : ?>
			<ul class="am-menu__social">
				<?php foreach ( $amour_socials as $amour_social ) : ?>
					<li><a href="<?php echo esc_url( $amour_social['url'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $amour_social['label'] ); ?><span class="sr-only"> <?php esc_html_e( '(opens in a new tab)', 'amour' ); ?></span></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</div>
