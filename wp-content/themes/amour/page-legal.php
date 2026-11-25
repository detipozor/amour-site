<?php
/**
 * A legal page: Privacy, Cookies or Booking terms (board Legal; HANDOFF, sections 6 "Legal" and 9 "Юридические
 * страницы"). The text is an ordinary page in the editor; this template adds the switch between the three
 * documents and the contents on the side, built from the text's Heading 2s (hidden on a phone).
 * The texts are drafts until the lawyer checks them.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

// The text, with an id on every Heading 2 (the contents link to them). A German page without its own text shows
// the English one, marked as English and with a line that says so (decision 25.11: the legal texts are translated
// after the lawyer has checked the English ones; HANDOFF section 19).
$amour_text    = get_the_content();
$amour_english = false;
if ( '' === trim( $amour_text ) && function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
	$amour_en = (int) pll_get_post( get_the_ID(), pll_default_language() );
	if ( $amour_en && get_the_ID() !== $amour_en ) {
		$amour_text    = (string) get_post_field( 'post_content', $amour_en );
		$amour_english = '' !== trim( $amour_text );
	}
}
$amour_lang     = $amour_english ? ' lang="en"' : '';
$amour_html     = apply_filters( 'the_content', $amour_text ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter.
$amour_sections = array();
$amour_html     = preg_replace_callback(
	'/<h2([^>]*)>(.*?)<\/h2>/s',
	static function ( $m ) use ( &$amour_sections ) {
		$id               = preg_match( '/\sid="([^"]+)"/', $m[1], $found ) ? $found[1] : sanitize_title( wp_strip_all_tags( $m[2] ) );
		$amour_sections[] = array(
			'id'    => $id,
			'title' => wp_strip_all_tags( $m[2] ),
		);
		$attrs            = preg_match( '/\sid="/', $m[1] ) ? $m[1] : $m[1] . ' id="' . esc_attr( $id ) . '"';
		return '<h2' . $attrs . '>' . $m[2] . '</h2>';
	},
	(string) $amour_html
);

// The switch between the documents, named by each page's own title in the current language.
$amour_docs = array();
foreach ( array( 'privacy-policy', 'cookies', 'booking-terms' ) as $amour_slug ) {
	$amour_page = get_page_by_path( $amour_slug );
	if ( ! $amour_page ) {
		continue;
	}
	$amour_id = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $amour_page->ID ) : 0;
	$amour_id = $amour_id ? $amour_id : $amour_page->ID;

	$amour_docs[ $amour_slug ] = get_the_title( $amour_id );
}
$amour_updated = (string) amour_page_field( 'lg_updated' );
$amour_privacy = (string) amour_option( 'amour_privacy_email' );
?>
<main id="main" tabindex="-1" class="am-legal">

	<section class="am-pg-open am-lg-top" aria-labelledby="am-lg-title">
		<div class="am-pg-in am-lg-top__in">
			<nav class="am-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'amour' ); ?>">
				<a href="<?php echo esc_url( amour_home_url() ); ?>"><?php esc_html_e( 'Home', 'amour' ); ?></a> · <span aria-current="page"><?php the_title(); ?></span>
			</nav>
			<h1 id="am-lg-title"><?php the_title(); ?></h1>
			<?php if ( $amour_english ) : ?>
				<p class="am-lg-top__lang"><?php esc_html_e( 'This page is only in English for now.', 'amour' ); ?></p>
			<?php endif; ?>
			<?php if ( amour_page_field( 'lg_lead' ) ) : ?>
				<p class="am-lg-top__lead"<?php echo $amour_lang; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a fixed attribute. ?>><?php echo esc_html( amour_page_field( 'lg_lead' ) ); ?></p>
			<?php endif; ?>
			<p class="am-lg-top__date">
				<?php
				/* translators: %s: date. */
				echo esc_html( sprintf( __( 'Last updated %s', 'amour' ), $amour_updated ? date_i18n( _x( 'j F Y', 'date format, see php.net/date', 'amour' ), strtotime( $amour_updated ) ) : amour_or_placeholder( '', 'date' ) ) );
				// A test copy says the text is a draft; the lawyer checks it before launch.
				echo esc_html( 'production' !== wp_get_environment_type() ? ' · [' . __( 'Draft: the lawyer checks it before launch', 'amour' ) . ']' : '' );
				?>
			</p>
			<nav class="am-lg-docs" aria-label="<?php esc_attr_e( 'Documents', 'amour' ); ?>">
				<?php foreach ( $amour_docs as $amour_slug => $amour_label ) : ?>
					<?php $amour_current = amour_is_page( $amour_slug ); ?>
					<a class="am-lg-docs__link<?php echo $amour_current ? ' is-on' : ''; ?>" href="<?php echo esc_url( amour_page_url( $amour_slug ) ); ?>"<?php echo $amour_current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $amour_label ); ?></a>
				<?php endforeach; ?>
			</nav>
			<?php if ( amour_is_page( 'cookies' ) ) : ?>
				<?php // Change or take back the choice as easily as it was given (decision 24.11): reopens the banner. ?>
				<p class="am-lg-top__consent"><button type="button" class="am-btn am-btn--ghost" data-consent-open><?php esc_html_e( 'Cookie settings', 'amour' ); ?></button></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="am-lg-body">
		<div class="am-pg-in am-lg-body__grid">
			<?php if ( $amour_sections ) : ?>
				<nav class="am-lg-toc" aria-label="<?php esc_attr_e( 'On this page', 'amour' ); ?>"<?php echo $amour_lang; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a fixed attribute. ?>>
					<?php foreach ( $amour_sections as $amour_s ) : ?>
						<a href="#<?php echo esc_attr( $amour_s['id'] ); ?>"><?php echo esc_html( $amour_s['title'] ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
			<article class="am-lg-text">
				<div<?php echo $amour_lang; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a fixed attribute. ?>>
					<?php echo $amour_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the page's own content, filtered by the_content. ?>
				</div>
				<p class="am-lg-ask">
					<?php
					$amour_write = $amour_privacy ? '<a href="mailto:' . esc_attr( antispambot( $amour_privacy ) ) . '">' . esc_html( antispambot( $amour_privacy ) ) . '</a>' : esc_html( amour_or_placeholder( '', 'privacy email' ) );
					echo wp_kses(
						/* translators: 1: email address, 2: link to the contact page. */
						sprintf( __( 'Questions about this page? Write to %1$s or %2$s.', 'amour' ), $amour_write, '<a href="' . esc_url( amour_page_url( 'contacts' ) ) . '">' . esc_html__( 'contact us', 'amour' ) . '</a>' ),
						array( 'a' => array( 'href' => array() ) )
					);
					?>
				</p>
			</article>
		</div>
	</section>
</main>
<?php
get_footer();
