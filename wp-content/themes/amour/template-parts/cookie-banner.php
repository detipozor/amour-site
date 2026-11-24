<?php
/**
 * The cookie banner (HANDOFF, section 14; CookieBanner of the design system): the text from Hotel settings with
 * the link to the cookie policy, Settings with two choices (analytics, advertising), Reject all and Accept all,
 * equally visible (decision 24.11). It shows by itself only when there is a Tag Manager container to agree to;
 * the Cookie settings button on the Cookies page opens it any time (assets/js/consent.js).
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

$amour_gtm  = strtoupper( trim( (string) amour_option( 'amour_gtm_id' ) ) );
$amour_gtm  = preg_match( '/^GTM-[A-Z0-9]+$/', $amour_gtm ) ? $amour_gtm : '';
$amour_text = (string) amour_option( 'amour_cookie_text' );
$amour_kind = array(
	'analytics' => array( __( 'Analytics', 'amour' ), __( 'Which pages help guests plan their stay (Google Analytics).', 'amour' ) ),
	'ads'       => array( __( 'Advertising', 'amour' ), __( 'Our ads on Facebook and Instagram remember your visit (Meta Pixel).', 'amour' ) ),
);
?>
<div class="am-cookie" role="dialog" aria-label="<?php esc_attr_e( 'Cookie settings', 'amour' ); ?>" data-consent data-gtm="<?php echo esc_attr( $amour_gtm ); ?>" hidden>
	<p class="am-cookie__text">
		<?php echo esc_html( $amour_text ); ?>
		<a href="<?php echo esc_url( amour_page_url( 'cookies' ) ); ?>"><?php esc_html_e( 'Cookie policy', 'amour' ); ?></a>
	</p>
	<div class="am-cookie__choices" id="am-cookie-choices" data-consent-choices hidden>
		<?php foreach ( $amour_kind as $amour_key => $amour_choice ) : ?>
			<label class="am-cookie__choice">
				<input type="checkbox" data-consent-kind="<?php echo esc_attr( $amour_key ); ?>">
				<span><span class="am-cookie__name"><?php echo esc_html( $amour_choice[0] ); ?></span> <?php echo esc_html( $amour_choice[1] ); ?></span>
			</label>
		<?php endforeach; ?>
	</div>
	<div class="am-cookie__actions">
		<button type="button" class="am-btn am-btn--ghost" data-consent-settings aria-expanded="false" aria-controls="am-cookie-choices"><?php esc_html_e( 'Settings', 'amour' ); ?></button>
		<button type="button" class="am-btn am-btn--ghost" data-consent-save hidden><?php esc_html_e( 'Save choices', 'amour' ); ?></button>
		<button type="button" class="am-btn" data-consent-all="false"><?php esc_html_e( 'Reject all', 'amour' ); ?></button>
		<button type="button" class="am-btn" data-consent-all="true"><?php esc_html_e( 'Accept all', 'amour' ); ?></button>
	</div>
</div>
