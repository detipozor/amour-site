<?php
/**
 * Contact (board Contact; HANDOFF, sections 6 "Contact" and 8 "Формы"): "Find us in Almyrida" with the hosts,
 * the contacts and the Apokoronas map; Getting here; Write to us — the form (assets/js/contact.js checks it and
 * sends it in place; without the script the page reloads with the thank-you line).
 * Texts: the page (Contact page); contacts: Hotel settings; the message goes to the public email there.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
$amour_f       = static fn( $name ) => amour_page_field( 'ct_' . $name );
$amour_c       = amour_contacts();
$amour_reply   = amour_or_placeholder( (string) $amour_f( 'reply' ), '24 hours' );
$amour_hosts   = array_values( array_filter( (array) $amour_f( 'hosts' ), static fn( $h ) => ! empty( $h['name'] ) ) );
$amour_names   = $amour_hosts ? wp_list_pluck( $amour_hosts, 'name' ) : array_fill( 0, 2, amour_or_placeholder( '', 'Name' ) );
$amour_routes  = array_values( array_filter( (array) $amour_f( 'routes' ), static fn( $r ) => ! empty( $r['from'] ) ) );
$amour_wa_raw  = (string) amour_option( 'amour_whatsapp' );
$amour_tel_raw = (string) amour_option( 'amour_phone' );
$amour_mail    = (string) amour_option( 'amour_email' );
$amour_maps    = (string) amour_option( 'amour_maps_url' );
$amour_sent    = isset( $_GET['sent'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only shows a line.
$amour_failed  = isset( $_GET['error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only shows a line.

// Rows of the contact list: what, the text, the link (empty for a placeholder or plain text).
$amour_rows = array_filter(
	array(
		array( __( 'WhatsApp', 'amour' ), amour_or_placeholder( $amour_wa_raw, '+30 …' ), $amour_c['whatsapp']['url'] ?? '' ),
		array( __( 'Phone', 'amour' ), amour_or_placeholder( $amour_tel_raw, '+30 …' ), $amour_c['phone']['url'] ?? '' ),
		array( __( 'Email', 'amour' ), amour_or_placeholder( $amour_mail, 'hello@…' ), $amour_c['email']['url'] ?? '' ),
		array( __( 'Address', 'amour' ), amour_or_placeholder( (string) amour_option( 'amour_address' ), 'Street, Almyrida 730 08, Apokoronas, Crete' ), '' ),
		array( __( 'Hosts on site', 'amour' ), amour_or_placeholder( (string) $amour_f( 'hours' ), 'from … to …' ), '' ),
	),
	static fn( $row ) => '' !== $row[1]
);

// The Apokoronas map: a window on the relief of Crete (2400 × 1029) from 368, 176.4, 448 × 313.6 wide.
$amour_pin  = static fn( $lon, $lat ) => sprintf( 'left: %.2f%%; top: %.2f%%', ( $lon - 23.86 ) / 0.56 * 100, ( 35.62 - $lat ) / 0.32 * 100 );
$amour_pins = array(
	array( __( 'Amour Relax Hotel', 'amour' ), 24.172, 35.447, 'row-reverse', 'main' ),
	/* translators: the villa is about 4 minutes by car from the hotel. */
	array( __( 'Villa Eudora · 4 min', 'amour' ), 24.180, 35.454, 'row', 'villa' ),
	array( __( 'Chania', 'amour' ), 24.018, 35.513, 'row-reverse', 'town' ),
	array( __( 'Chania airport', 'amour' ), 24.149, 35.532, 'row', 'town' ),
);
?>
<main id="main" tabindex="-1" class="am-contact">

	<section class="am-pg-open am-ct-top" aria-labelledby="am-ct-title">
		<div class="am-pg-in am-ct-top__grid">
			<div class="am-ct-top__text">
				<nav class="am-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'amour' ); ?>">
					<a href="<?php echo esc_url( amour_home_url() ); ?>"><?php esc_html_e( 'Home', 'amour' ); ?></a> · <span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
				</nav>
				<h1 id="am-ct-title"><?php echo esc_html( $amour_f( 'title' ) ? $amour_f( 'title' ) : get_the_title() ); ?></h1>

				<div class="am-ct-hosts">
					<span class="am-ct-hosts__pics" aria-hidden="true">
						<?php for ( $amour_i = 0; $amour_i < 2; $amour_i++ ) : ?>
							<span class="am-ct-hosts__pic">
								<?php
								if ( ! empty( $amour_hosts[ $amour_i ]['photo'] ) ) {
									echo wp_get_attachment_image( (int) $amour_hosts[ $amour_i ]['photo'], 'thumbnail', false, array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
								} else {
									echo esc_html( amour_or_placeholder( '', 'photo' ) );
								}
								?>
							</span>
						<?php endfor; ?>
					</span>
					<span class="am-ct-hosts__text">
						<?php /* translators: 1: first host, 2: second host. */ ?>
						<strong><?php echo esc_html( sprintf( __( '%1$s and %2$s, your hosts', 'amour' ), $amour_names[0], $amour_names[1] ?? '' ) ); ?></strong>
						<?php if ( $amour_f( 'hosts_line' ) ) : ?>
							<span><?php echo esc_html( $amour_f( 'hosts_line' ) ); ?></span>
						<?php endif; ?>
					</span>
				</div>

				<?php if ( $amour_f( 'text' ) ) : ?>
					<p><?php echo esc_html( $amour_f( 'text' ) ); ?></p>
				<?php endif; ?>

				<dl class="am-ct-list">
					<?php foreach ( $amour_rows as $amour_row ) : ?>
						<div>
							<dt><?php echo esc_html( $amour_row[0] ); ?></dt>
							<dd>
								<?php if ( $amour_row[2] ) : ?>
									<a href="<?php echo esc_url( $amour_row[2] ); ?>"><?php echo esc_html( $amour_row[1] ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $amour_row[1] ); ?>
								<?php endif; ?>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>

				<div class="am-ct-top__actions">
					<?php if ( $amour_c['whatsapp']['url'] ?? '' ) : ?>
						<a class="am-btn" href="<?php echo esc_url( $amour_c['whatsapp']['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Write on WhatsApp', 'amour' ); ?></a>
					<?php endif; ?>
					<?php if ( $amour_maps ) : ?>
						<a class="am-btn am-btn--ghost" href="<?php echo esc_url( $amour_maps ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open in Google Maps', 'amour' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="am-ct-map" data-tone="light">
				<img class="am-ct-map__img" src="<?php echo esc_url( get_theme_file_uri( 'assets/maps/crete-relief.svg' ) ); ?>" alt="<?php esc_attr_e( 'Relief map of Apokoronas: the hotel and the villa in Almyrida, Chania and the airport to the north-west', 'amour' ); ?>" width="2400" height="1029" loading="lazy" decoding="async">
				<?php foreach ( $amour_pins as $amour_p ) : ?>
					<span class="am-ct-pin am-ct-pin--<?php echo esc_attr( $amour_p[4] ); ?>" data-side="<?php echo esc_attr( $amour_p[3] ); ?>" style="<?php echo esc_attr( $amour_pin( $amour_p[1], $amour_p[2] ) ); ?>" aria-hidden="true">
						<span class="am-ct-pin__dot"></span><span class="am-ct-pin__label"><?php echo esc_html( $amour_p[0] ); ?></span>
					</span>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( $amour_routes ) : ?>
		<section class="am-ct-routes" aria-labelledby="am-ct-routes">
			<div class="am-pg-in">
				<h2 id="am-ct-routes" class="am-ct-h2"><?php echo esc_html( $amour_f( 'routes_title' ) ); ?></h2>
				<div class="am-ct-routes__grid">
					<?php foreach ( $amour_routes as $amour_r ) : ?>
						<div class="am-ct-route">
							<p class="am-eyebrow"><?php echo esc_html( $amour_r['by'] ); ?></p>
							<h3><?php echo esc_html( $amour_r['from'] ); ?></h3>
							<p><?php echo esc_html( $amour_r['text'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="am-ct-write" id="write" aria-labelledby="am-ct-write">
		<div class="am-pg-in am-ct-write__grid">
			<div class="am-ct-write__text">
				<h2 id="am-ct-write" class="am-ct-h2"><?php echo esc_html( $amour_f( 'form_title' ) ); ?></h2>
				<p><?php echo esc_html( str_replace( '{reply}', $amour_reply, (string) $amour_f( 'form_text' ) ) ); ?></p>
			</div>
			<form class="am-form" data-contact-form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
				<input type="hidden" name="action" value="amour_contact">
				<?php wp_nonce_field( 'amour_contact', 'amour_contact_nonce', false ); ?>
				<?php // A field for robots: people never see it. ?>
				<p class="am-form__trap" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

				<p class="am-form__field">
					<label for="ct-name"><?php esc_html_e( 'Your name', 'amour' ); ?></label>
					<input id="ct-name" type="text" name="name" autocomplete="name" data-required="name" aria-describedby="ct-name-error">
					<span class="am-form__error" id="ct-name-error" hidden><?php esc_html_e( 'Please add your name.', 'amour' ); ?></span>
				</p>
				<p class="am-form__field">
					<label for="ct-email"><?php esc_html_e( 'Email', 'amour' ); ?></label>
					<input id="ct-email" type="email" name="email" autocomplete="email" data-required="email" aria-describedby="ct-email-error">
					<span class="am-form__error" id="ct-email-error" hidden><?php esc_html_e( 'Please add an email like name@example.com, so we can reply.', 'amour' ); ?></span>
				</p>
				<p class="am-form__field">
					<label for="ct-dates"><?php esc_html_e( 'Dates', 'amour' ); ?></label>
					<input id="ct-dates" type="text" name="dates" autocomplete="off" placeholder="<?php esc_attr_e( 'e.g. 14 – 21 May', 'amour' ); ?>">
				</p>
				<p class="am-form__field">
					<label for="ct-guests"><?php esc_html_e( 'Guests', 'amour' ); ?></label>
					<input id="ct-guests" type="text" name="guests" autocomplete="off" placeholder="<?php esc_attr_e( 'e.g. 2 adults', 'amour' ); ?>">
				</p>
				<p class="am-form__field am-form__field--wide">
					<label for="ct-message"><?php esc_html_e( 'Message', 'amour' ); ?></label>
					<textarea id="ct-message" name="message" rows="4" data-required="message" aria-describedby="ct-message-error"></textarea>
					<span class="am-form__error" id="ct-message-error" hidden><?php esc_html_e( 'Please write a few words.', 'amour' ); ?></span>
				</p>
				<div class="am-form__end">
					<p class="am-form__status" role="status" data-contact-status>
						<?php if ( $amour_sent ) : ?>
							<?php /* translators: %s: how soon the hosts reply, for example 24 hours. */ ?>
							<?php echo esc_html( sprintf( __( 'Thank you. We reply by email within %s.', 'amour' ), $amour_reply ) ); ?>
						<?php elseif ( $amour_failed ) : ?>
							<?php esc_html_e( 'The message did not go through. Please check the fields, or write to us on WhatsApp.', 'amour' ); ?>
						<?php endif; ?>
					</p>
					<?php /* translators: %s: how soon the hosts reply, for example 24 hours. */ ?>
					<button type="submit" class="am-btn" data-sent="<?php echo esc_attr( sprintf( __( 'Thank you. We reply by email within %s.', 'amour' ), $amour_reply ) ); ?>" data-failed="<?php esc_attr_e( 'The message did not go through. Please write to us on WhatsApp.', 'amour' ); ?>"><?php esc_html_e( 'Send', 'amour' ); ?></button>
				</div>
				<p class="am-form__privacy">
					<?php /* translators: %s: link to the privacy policy. */ ?>
					<?php echo wp_kses( sprintf( __( 'We use your details only to answer you. %s', 'amour' ), '<a href="' . esc_url( amour_page_url( 'privacy-policy' ) ) . '">' . esc_html__( 'Privacy policy', 'amour' ) . '</a>' ), array( 'a' => array( 'href' => array() ) ) ); ?>
				</p>
			</form>
		</div>
	</section>
</main>
<?php
get_footer();
