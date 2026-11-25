<?php
/**
 * The booking (HANDOFF, section 7; board Book): what the search on the booking page offers and how it hands the
 * guest to the booking engine. Steps 1 Dates and 2 Room are ours; 3 Details and 4 Payment are the engine's.
 * The search runs in assets/js/book.js behind a BookingProvider (assets/js/booking-provider.js): the engine
 * address from Hotel settings when it is there, the test engine on a test copy until then.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * The rooms and the villa as the search shows them, in the order of the cards.
 *
 * @return array<int, array<string, mixed>>
 */
function amour_booking_units() {
	$units = array();
	foreach ( amour_cards() as $card ) {
		$f     = static fn( $name ) => amour_page_field( $name, $card['id'] );
		$price = $f( 'price_from' );
		$facts = array_values( array_filter( array_map( 'trim', explode( '·', (string) $f( 'book_facts' ) ) ) ) );

		$seasons = array();
		if ( $card['villa'] ) {
			foreach ( (array) $f( 'villa_seasons' ) as $season ) {
				if ( ! empty( $season['first'] ) && ! empty( $season['last'] ) && '' !== (string) $season['price'] ) {
					$seasons[] = array(
						'first' => amour_booking_day( $season['first'] ),
						'last'  => amour_booking_day( $season['last'] ),
						'price' => (float) $season['price'],
					);
				}
			}
		}

		$units[] = array(
			'id'      => $card['id'],
			'name'    => get_the_title( $card['id'] ),
			'facts'   => $facts,
			'text'    => (string) $f( 'book_text' ),
			'photo'   => (int) $f( 'book_photo' ) ? (int) $f( 'book_photo' ) : $card['photo'],
			'guests'  => max( 1, (int) $f( 'max_guests' ) ),
			'price'   => null === $price || '' === $price ? null : (float) $price,
			'seasons' => $seasons,
			'min'     => $card['villa'] ? max( 1, (int) $f( 'villa_min_nights' ) ) : 1,
			'code'    => (string) $f( 'room_engine_code' ),
			'villa'   => $card['villa'],
		);
	}
	return $units;
}

/**
 * A date from the admin (Ymd) as YYYY-MM-DD, the form the search and the engine use.
 *
 * @param string $ymd Date as Ymd.
 * @return string
 */
function amour_booking_day( $ymd ) {
	$ymd = preg_replace( '/\D+/', '', (string) $ymd );
	return 8 === strlen( $ymd ) ? substr( $ymd, 0, 4 ) . '-' . substr( $ymd, 4, 2 ) . '-' . substr( $ymd, 6, 2 ) : '';
}

/**
 * The cancellation line next to every price: "Free cancellation until 14 days before arrival · climate fee paid at
 * the hotel". Without the number a test copy shows a placeholder and the live site keeps only the fee.
 *
 * @return string
 */
function amour_booking_terms_line() {
	$days = (string) amour_option( 'amour_cancel_days' );
	$days = amour_or_placeholder( $days, '14' );
	if ( '' === $days ) {
		return __( 'Climate fee paid at the hotel', 'amour' );
	}
	/* translators: %s: number of days. */
	return sprintf( __( 'Free cancellation until %s days before arrival · climate fee paid at the hotel', 'amour' ), $days );
}

/**
 * What the booking script needs: which engine, the season, the words it writes, the rooms and the villa.
 *
 * @param array<int, array<string, mixed>> $units The units from amour_booking_units().
 * @return array<string, mixed>
 */
function amour_booking_config( $units ) {
	global $wp_locale;
	$engine = trim( (string) amour_option( 'amour_engine_url' ) );
	$test   = 'production' !== wp_get_environment_type();

	$months = array();
	$short  = array();
	for ( $m = 1; $m <= 12; $m++ ) {
		$months[] = $wp_locale->get_month( $m );
		$short[]  = $wp_locale->get_month_abbrev( $wp_locale->get_month( $m ) );
	}
	$weekdays = array();
	for ( $d = 0; $d <= 6; $d++ ) {
		$weekdays[] = $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( $d ) );
	}

	return array(
		'provider' => $engine ? 'link' : ( $test ? 'mock' : 'contact' ),
		'engine'   => $engine,
		'contact'  => amour_page_url( 'contacts' ),
		'thanks'   => amour_page_url( 'thank-you' ),
		'season'   => array(
			'first' => amour_booking_day( (string) amour_option( 'amour_season_first' ) ),
			'last'  => amour_booking_day( (string) amour_option( 'amour_season_last' ) ),
		),
		'locale'   => str_replace( '_', '-', get_locale() ),
		'price'    => $test ? '[price]' : '',
		'months'   => $months,
		'short'    => $short,
		'weekdays' => $weekdays,
		'text'     => array(
			/* translators: a day in the calendar: 1: weekday, 2: day, 3: month, e.g. Mon 1 Mar. */
			'day'     => _x( '%1$s %2$s %3$s', 'calendar day', 'amour' ),
			/* translators: a date: 1: day, 2: month, e.g. 1 March. */
			'date'    => _x( '%1$s %2$s', 'date', 'amour' ),
			'addDate' => __( 'Add date', 'amour' ),
			/* translators: %d: number of nights. */
			'night'   => __( '%d night', 'amour' ),
			/* translators: %d: number of nights. */
			'nights'  => __( '%d nights', 'amour' ),
			/* translators: %d: number of guests. */
			'adult'   => __( '%d adult', 'amour' ),
			/* translators: %d: number of guests. */
			'adults'  => __( '%d adults', 'amour' ),
			/* translators: 1: arrival day, 2: departure day, 3: nights and guests, e.g. 7 nights · 2 adults. */
			'free'    => __( 'Free for %1$s – %2$s, %3$s.', 'amour' ),
			'many'    => __( 'For more than two guests: the villa, or several rooms booked one by one.', 'amour' ),
			'none'    => __( 'Nothing free for these dates. Try other dates or write to us.', 'amour' ),
			/* translators: 1: first night of the season, 2: last night, e.g. 1 March and 31 October 2027. */
			'open'    => __( 'We are open from %1$s to %2$s.', 'amour' ),
			'leave'   => __( 'Now the day you leave.', 'amour' ),
			'closed'  => __( 'closed', 'amour' ),
			/* translators: %s: the total, e.g. €1,190. */
			'paid'    => __( '%s by card', 'amour' ),
		),
		'units'    => array_map(
			static fn( $u ) => array(
				'id'      => $u['id'],
				'name'    => $u['name'],
				'guests'  => $u['guests'],
				'price'   => $u['price'],
				'seasons' => $u['seasons'],
				'min'     => $u['min'],
				'code'    => $u['code'],
				'villa'   => $u['villa'],
			),
			$units
		),
	);
}
