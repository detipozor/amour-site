<?php
/**
 * Styleguide: tokens, type, buttons, icons and logos on one page, to check them against the boards.
 * A private page (only visible after logging in), created by `wp amour seed`. Not part of the site.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

wp_enqueue_style( 'amour-styleguide', get_theme_file_uri( 'assets/css/styleguide.css' ), array( 'amour-button' ), AMOUR_VERSION );

$amour_swatches = array(
	'--bg'             => 'Page (limestone)',
	'--surface'        => 'Cards, fields (paper)',
	'--text'           => 'Headings, text',
	'--text-2'         => 'Secondary text',
	'--text-muted'     => 'Captions',
	'--accent'         => 'Buttons, links, labels',
	'--accent-hover'   => 'Button hover',
	'--deep'           => 'Dark blocks, footer',
	'--accent-soft'    => 'Badges',
	'--line'           => 'Lines, borders',
	'--on-deep-accent' => 'Labels on dark blocks',
);

get_header();
?>
<main id="main" tabindex="-1" class="sg">
	<div class="am-container am-sec-s">
		<p class="am-eyebrow">Design system</p>
		<h1><?php esc_html_e( 'Styleguide', 'amour' ); ?></h1>
		<p class="am-text-2 sg-lead">Tokens, type, buttons, icons and logos of the theme, side by side with the boards. This page is private: visitors see a 404.</p>
	</div>

	<?php foreach ( array( 'amour', 'eudora' ) as $amour_house ) : ?>
		<section class="sg-house" data-theme="<?php echo esc_attr( $amour_house ); ?>" aria-labelledby="sg-colours-<?php echo esc_attr( $amour_house ); ?>">
			<div class="am-container am-sec-s">
				<h2 id="sg-colours-<?php echo esc_attr( $amour_house ); ?>" class="sg-h2"><?php echo esc_html( 'amour' === $amour_house ? 'Colours: Amour' : 'Colours: Villa Eudora' ); ?></h2>
				<ul class="sg-swatches">
					<?php foreach ( $amour_swatches as $amour_token => $amour_use ) : ?>
						<li>
							<span class="sg-swatch" style="background: var(<?php echo esc_attr( $amour_token ); ?>)"></span>
							<code><?php echo esc_html( $amour_token ); ?></code>
							<span class="am-muted"><?php echo esc_html( $amour_use ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<h3 class="sg-h3">Buttons</h3>
				<div class="sg-row">
					<a class="am-btn" href="#main">Check dates</a>
					<a class="am-btn am-btn--ghost" href="#main">View room</a>
					<a class="am-btn am-btn--small" href="#main">Book</a>
					<button class="am-btn" type="button" disabled>Not available</button>
				</div>

				<div class="am-deep sg-deep">
					<p class="am-eyebrow am-eyebrow--light">On a dark block</p>
					<p class="am-phrase">Nine rooms among olive groves</p>
					<div class="sg-row">
						<a class="am-btn am-btn--on-photo" href="#main">Book</a>
						<a class="am-btn" href="#main">Check dates</a>
					</div>
				</div>

				<h3 class="sg-h3">Logos</h3>
				<div class="sg-logos">
					<?php if ( 'amour' === $amour_house ) : ?>
						<div class="sg-logo-tile"><?php echo amour_logo( 'amour-horizontal', 'Amour Relax Hotel' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?></div>
						<div class="sg-logo-tile am-deep"><?php echo amour_logo( 'amour-horizontal', 'Amour Relax Hotel', 'sg-on-deep' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?></div>
						<div class="sg-logo-tile am-deep"><?php echo amour_logo( 'amour-main', 'Amour Relax Hotel', 'sg-tall sg-tamarisk' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?></div>
						<div class="sg-logo-tile am-deep sg-wide"><?php echo amour_logo( 'amour-word', '', 'sg-word sg-on-deep' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?></div>
						<div class="sg-logo-tile"><?php echo amour_logo( 'olive', '', 'sg-tall' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?></div>
					<?php else : ?>
						<div class="sg-logo-tile"><?php echo amour_logo( 'eudora-horizontal', 'Villa Eudora' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?></div>
						<div class="sg-logo-tile am-deep"><?php echo amour_logo( 'eudora-horizontal', 'Villa Eudora', 'sg-on-deep' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?></div>
						<div class="sg-logo-tile am-deep sg-wide"><?php echo amour_logo( 'eudora-word', '', 'sg-word sg-on-deep' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?></div>
						<div class="sg-logo-tile"><?php echo amour_logo( 'jug', '', 'sg-tall' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_logo(). ?></div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endforeach; ?>

	<section class="am-container am-sec-s" aria-labelledby="sg-type">
		<h2 id="sg-type" class="sg-h2">Type</h2>
		<p class="sg-note am-muted">Desktop / phone: H1 72 / 40, H2 44 / 30, H3 24 / 20, phrase 76 / 44, text 18 / 16, label 13 (HANDOFF, section 3).</p>
		<dl class="sg-type">
			<dt>H1</dt>
			<dd><p class="am-h1">Stay like you live here</p></dd>
			<dt>H2</dt>
			<dd><p class="am-h2">Quiet days by the water</p></dd>
			<dt>H3</dt>
			<dd><p class="am-h3">Superior Queen Room</p></dd>
			<dt>Label</dt>
			<dd><p class="am-eyebrow">Room 01</p></dd>
			<dt>Text</dt>
			<dd><p>Every room has a kitchen and a terrace. Cook, eat outside, keep your own hours.</p></dd>
			<dt>Secondary</dt>
			<dd><p class="am-text-2">The beach at Almyrida is 1.2 km away, about 15 minutes on foot.</p></dd>
			<dt>Link</dt>
			<dd><a class="am-link" href="#main">Open in Google Maps <?php echo amour_icon( 'arrow-right', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_icon(). ?></a></dd>
		</dl>
	</section>

	<section class="am-container am-sec-s" aria-labelledby="sg-icons">
		<h2 id="sg-icons" class="sg-h2">Icons</h2>
		<ul class="sg-icons">
			<?php foreach ( amour_icon_names() as $amour_icon_name ) : ?>
				<li><?php echo amour_icon( $amour_icon_name, 32 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amour_icon(). ?><code><?php echo esc_html( $amour_icon_name ); ?></code></li>
			<?php endforeach; ?>
		</ul>
	</section>
</main>
<?php
get_footer();
