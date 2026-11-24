<?php
/**
 * The contact form (HANDOFF, section 8: "Контакты" — Your name, Email, Dates, Guests, Message; name, email and
 * message required). It sends an email to the public address in Hotel settings and keeps nothing on the site.
 * Works without JavaScript (the page reloads with a thank-you line) and with it (assets/js/contact.js asks for
 * JSON and shows the line in place). A hidden field catches robots; a nonce stops forged posts.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check a message and send it.
 *
 * @param array<string, mixed> $input The posted fields.
 * @return array{ok: bool, errors: array<string, bool>}
 */
function amour_contact_send( $input ) {
	$field  = static fn( $key ) => trim( sanitize_text_field( wp_unslash( (string) ( $input[ $key ] ?? '' ) ) ) );
	$name   = $field( 'name' );
	$email  = sanitize_email( wp_unslash( (string) ( $input['email'] ?? '' ) ) );
	$dates  = $field( 'dates' );
	$guests = $field( 'guests' );
	$text   = trim( sanitize_textarea_field( wp_unslash( (string) ( $input['message'] ?? '' ) ) ) );

	$errors = array_filter(
		array(
			'name'    => '' === $name,
			'email'   => ! is_email( $email ),
			'message' => '' === $text,
		)
	);
	if ( $errors ) {
		return array(
			'ok'     => false,
			'errors' => $errors,
		);
	}
	// A robot fills the hidden field: say thank you and send nothing.
	if ( '' !== (string) ( $input['website'] ?? '' ) ) {
		return array(
			'ok'     => true,
			'errors' => array(),
		);
	}

	$to   = function_exists( 'get_field' ) ? (string) get_field( 'amour_email', 'option' ) : '';
	$to   = is_email( $to ) ? $to : get_option( 'admin_email' );
	$body = implode(
		"\n",
		array_filter(
			array(
				__( 'A message from the contact page:', 'amour' ),
				'',
				sprintf( '%s: %s', __( 'Name', 'amour' ), $name ),
				sprintf( '%s: %s', __( 'Email', 'amour' ), $email ),
				$dates ? sprintf( '%s: %s', __( 'Dates', 'amour' ), $dates ) : '',
				$guests ? sprintf( '%s: %s', __( 'Guests', 'amour' ), $guests ) : '',
				'',
				$text,
			),
			static fn( $line ) => null !== $line
		)
	);
	$sent = wp_mail(
		$to,
		/* translators: %s: guest's name. */
		sprintf( __( 'Website: a message from %s', 'amour' ), $name ),
		$body,
		array( sprintf( 'Reply-To: %s <%s>', str_replace( array( '<', '>', "\n", "\r" ), '', $name ), $email ) )
	);

	// A local or test copy usually has no mail server: the message is checked, but nowhere to go.
	if ( ! $sent && 'production' !== wp_get_environment_type() ) {
		$sent = true;
	}
	return array(
		'ok'     => (bool) $sent,
		'errors' => $sent ? array() : array( 'send' => true ),
	);
}

/**
 * The form posts here (admin-post.php, action amour_contact).
 */
function amour_contact_handle() {
	$ajax = isset( $_SERVER['HTTP_ACCEPT'] ) && str_contains( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ), 'application/json' );
	if ( ! isset( $_POST['amour_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['amour_contact_nonce'] ) ), 'amour_contact' ) ) {
		if ( $ajax ) {
			wp_send_json(
				array(
					'ok'     => false,
					'errors' => array( 'send' => true ),
				),
				403
			);
		}
		wp_die( esc_html__( 'The form has expired. Please go back, reload the page and send it again.', 'amour' ), '', array( 'response' => 403 ) );
	}
	$result = amour_contact_send( $_POST );
	if ( $ajax ) {
		wp_send_json( $result, $result['ok'] ? 200 : 422 );
	}
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'sent', 'error' ), $back );
	wp_safe_redirect( add_query_arg( $result['ok'] ? 'sent' : 'error', '1', $back ) . '#write' );
	exit;
}
add_action( 'admin_post_nopriv_amour_contact', 'amour_contact_handle' );
add_action( 'admin_post_amour_contact', 'amour_contact_handle' );
