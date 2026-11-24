<?php
/**
 * Questions and house rules (board FAQ; HANDOFF, section 6 "FAQ"): the topics on the left (on a phone a row of
 * chips), the questions as an accordion (the first one open), the small print, then "Still wondering about
 * something?" with WhatsApp and email (on a phone the buttons full width). The topic in view is marked (faq.js).
 * Questions: Questions in the admin; texts: the page.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
$amour_f        = static fn( $name ) => amour_page_field( 'faq_' . $name );
$amour_topics   = amour_faq();
$amour_contacts = amour_contacts();
?>
<main id="main" tabindex="-1" class="am-faq" data-faq>

	<section class="am-pg-open" aria-labelledby="am-faq-title">
		<div class="am-pg-in am-pg-open__grid">
			<div class="am-pg-open__left">
				<nav class="am-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'amour' ); ?>">
					<a href="<?php echo esc_url( amour_home_url() ); ?>"><?php esc_html_e( 'Home', 'amour' ); ?></a> · <span aria-current="page"><?php esc_html_e( 'Questions', 'amour' ); ?></span>
				</nav>
				<h1 id="am-faq-title"><?php echo esc_html( $amour_f( 'title' ) ? $amour_f( 'title' ) : get_the_title() ); ?></h1>
			</div>
			<?php if ( $amour_f( 'text' ) ) : ?>
				<p class="am-pg-open__text"><?php echo esc_html( $amour_f( 'text' ) ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $amour_topics ) : ?>
		<section class="am-faq-body">
			<div class="am-pg-in am-faq-body__grid">
				<nav class="am-faq-nav" aria-label="<?php esc_attr_e( 'Topics', 'amour' ); ?>">
					<?php foreach ( $amour_topics as $amour_i => $amour_topic ) : ?>
						<a class="am-faq-nav__link<?php echo 0 === $amour_i ? ' is-on' : ''; ?>" href="#<?php echo esc_attr( $amour_topic['slug'] ); ?>" data-faq-link="<?php echo esc_attr( $amour_topic['slug'] ); ?>"><?php echo esc_html( $amour_topic['name'] ); ?></a>
					<?php endforeach; ?>
				</nav>
				<div class="am-faq-list">
					<?php foreach ( $amour_topics as $amour_i => $amour_topic ) : ?>
						<div class="am-faq-topic" id="<?php echo esc_attr( $amour_topic['slug'] ); ?>" data-faq-topic>
							<h2 class="am-faq-topic__title"><?php echo esc_html( $amour_topic['name'] ); ?></h2>
							<?php foreach ( $amour_topic['questions'] as $amour_q => $amour_question ) : ?>
								<details class="am-faq-q"<?php echo 0 === $amour_i && 0 === $amour_q ? ' open' : ''; ?>>
									<summary><span><?php echo esc_html( $amour_question['q'] ); ?></span><span class="am-faq-q__plus" aria-hidden="true">+</span></summary>
									<p><?php echo esc_html( $amour_question['a'] ); ?></p>
								</details>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
					<?php if ( $amour_f( 'note' ) ) : ?>
						<p class="am-faq-note"><?php echo esc_html( $amour_f( 'note' ) ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $amour_f( 'cta' ) ) : ?>
		<section class="am-faq-cta" aria-labelledby="am-faq-cta">
			<div class="am-pg-in am-faq-cta__in">
				<h2 id="am-faq-cta" class="am-faq-cta__title"><?php echo esc_html( $amour_f( 'cta' ) ); ?></h2>
				<div class="am-faq-cta__actions">
					<?php if ( ( $amour_contacts['whatsapp']['url'] ?? '' ) ) : ?>
						<a class="am-btn" href="<?php echo esc_url( ( $amour_contacts['whatsapp']['url'] ?? '' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Write on WhatsApp', 'amour' ); ?></a>
					<?php else : ?>
						<a class="am-btn" href="<?php echo esc_url( amour_page_url( 'contacts' ) ); ?>"><?php esc_html_e( 'Write on WhatsApp', 'amour' ); ?></a>
					<?php endif; ?>
					<?php if ( ( $amour_contacts['email']['url'] ?? '' ) ) : ?>
						<a class="am-btn am-btn--ghost" href="<?php echo esc_url( ( $amour_contacts['email']['url'] ?? '' ) ); ?>"><?php esc_html_e( 'Email us', 'amour' ); ?></a>
					<?php else : ?>
						<a class="am-btn am-btn--ghost" href="<?php echo esc_url( amour_page_url( 'contacts' ) . '#write' ); ?>"><?php esc_html_e( 'Email us', 'amour' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
