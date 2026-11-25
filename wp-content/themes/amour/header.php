<?php
/**
 * Document head, the page curtain and the site header (HANDOFF, section 5: header, menu).
 *
 * Header: Menu on the left (only the icon on a phone), the logo in the middle, language and Book on the right.
 * No bar behind them; the logo turns light over photos and dark blocks and steps aside while reading down.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_brand     = amour_brand();
$amour_languages = amour_languages();
$amour_current   = wp_list_filter( $amour_languages, array( 'current' => true ) );
$amour_current   = $amour_current ? reset( $amour_current ) : null;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'amour' ); ?></a>

<div class="am-curtain" aria-hidden="true">
	<?php
	// The logo of this page's house is needed at once (the arriving curtain), the other one only on the way out.
	echo amour_logo( 'amour-horizontal', '', 'am-curtain__logo am-curtain__logo--amour', 'eudora' === $amour_brand ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo().
	echo amour_logo( 'eudora-horizontal', '', 'am-curtain__logo am-curtain__logo--eudora', 'amour' === $amour_brand ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo().
	?>
</div>

<header class="am-header" data-brand="<?php echo esc_attr( $amour_brand ); ?>" data-home-brand="<?php echo esc_attr( $amour_brand ); ?>" data-villa="<?php echo esc_url( amour_page_url( 'villa-eudora' ) ); ?>">
	<button type="button" class="am-header__pill am-header__menu" aria-expanded="false" aria-controls="am-menu">
		<svg class="am-header__menu-icon" width="18" height="12" viewBox="0 0 18 12" aria-hidden="true" focusable="false"><path d="M0 1h18M0 6h18M0 11h18" fill="none" stroke="currentColor" stroke-width="2"/></svg>
		<span class="am-header__menu-label"><?php esc_html_e( 'Menu', 'amour' ); ?></span>
	</button>

	<a class="am-header__logo" href="<?php echo esc_url( amour_home_url() ); ?>" aria-label="<?php echo esc_attr( 'eudora' === $amour_brand ? __( 'Villa Eudora, home', 'amour' ) : __( 'Amour Relax Hotel, home', 'amour' ) ); ?>">
		<?php
		echo amour_logo( 'amour-horizontal', '', '', 'eudora' === $amour_brand ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo().
		echo amour_logo( 'eudora-horizontal', '', '', 'amour' === $amour_brand ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo().
		?>
	</a>

	<div class="am-header__right">
		<?php if ( $amour_current && count( $amour_languages ) > 1 ) : ?>
			<div class="am-lang">
				<button type="button" class="am-header__pill am-lang__toggle" aria-expanded="false" aria-controls="am-lang-list"
					aria-label="<?php /* translators: %s: current language code, e.g. EN */ echo esc_attr( sprintf( __( '%s, choose language', 'amour' ), $amour_current['code'] ) ); ?>">
					<?php echo esc_html( $amour_current['code'] ); ?>
				</button>
				<ul id="am-lang-list" class="am-lang__list" hidden>
					<?php foreach ( $amour_languages as $amour_language ) : ?>
						<li>
							<a href="<?php echo esc_url( $amour_language['url'] ); ?>" lang="<?php echo esc_attr( $amour_language['locale'] ); ?>" hreflang="<?php echo esc_attr( $amour_language['locale'] ); ?>"<?php echo $amour_language['current'] ? ' aria-current="true"' : ''; ?>>
								<?php echo esc_html( $amour_language['name'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<a class="am-btn am-header__book" href="<?php echo esc_url( amour_page_url( 'book' ) ); ?>"><?php esc_html_e( 'Book', 'amour' ); ?></a>
	</div>
</header>

<?php get_template_part( 'template-parts/menu-overlay' ); ?>
