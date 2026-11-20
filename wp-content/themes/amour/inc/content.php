<?php
/**
 * Content from the admin: "Hotel" settings, page addresses in the current language, languages.
 * Templates never hold these values themselves (CLAUDE.md: texts and settings come from the admin).
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * A value from the "Hotel" settings page (plugin amour-core, Secure Custom Fields).
 *
 * @param string $key Field name.
 * @return mixed Value, or null when the plugin is off.
 */
function amour_option( $key ) {
	return function_exists( 'get_field' ) ? get_field( $key, 'option' ) : null;
}

/**
 * Empty fields: the test copy shows the placeholder in square brackets, so it is clear what is missing;
 * the live site shows nothing (decision 19.11, HANDOFF section 19).
 *
 * @param mixed  $value       Field value.
 * @param string $placeholder Placeholder text without brackets, e.g. "Phone".
 * @return string Value, "[Placeholder]" or "".
 */
function amour_or_placeholder( $value, $placeholder ) {
	if ( is_string( $value ) && '' !== trim( $value ) ) {
		return trim( $value );
	}
	return 'production' === wp_get_environment_type() ? '' : '[' . $placeholder . ']';
}

/**
 * Contacts for guests as links: phone, WhatsApp, email. Empty ones become placeholders (or disappear on the live site).
 *
 * @return array<string, array{label: string, url: string}> Keyed by phone, whatsapp, email; url is empty for placeholders.
 */
function amour_contacts() {
	$phone    = (string) amour_option( 'amour_phone' );
	$whatsapp = preg_replace( '/\D+/', '', (string) amour_option( 'amour_whatsapp' ) );
	$email    = (string) amour_option( 'amour_email' );

	$contacts = array(
		'phone'    => array(
			'label' => amour_or_placeholder( $phone, 'Phone' ),
			'url'   => $phone ? 'tel:' . preg_replace( '/[^\d+]+/', '', $phone ) : '',
		),
		'whatsapp' => array(
			'label' => $whatsapp ? 'WhatsApp' : amour_or_placeholder( '', 'WhatsApp' ),
			'url'   => $whatsapp ? 'https://wa.me/' . $whatsapp : '',
		),
		'email'    => array(
			'label' => amour_or_placeholder( $email, 'Email' ),
			'url'   => $email ? 'mailto:' . antispambot( $email ) : '',
		),
	);

	return array_filter( $contacts, static fn( $contact ) => '' !== $contact['label'] );
}

/**
 * Social links from the settings.
 *
 * @param bool $menu_only Only the ones marked "Also in the menu".
 * @return array<int, array{label: string, url: string}>
 */
function amour_socials( $menu_only = false ) {
	$rows = (array) amour_option( 'amour_social' );
	$out  = array();
	foreach ( $rows as $row ) {
		if ( empty( $row['label'] ) || empty( $row['url'] ) || ( $menu_only && empty( $row['in_menu'] ) ) ) {
			continue;
		}
		$out[] = array(
			'label' => $row['label'],
			'url'   => $row['url'],
		);
	}
	return $out;
}

/**
 * Address of a page by its English slug, in the language of the current page when a translation exists.
 *
 * @param string $path Page path, e.g. "book" or "rooms/standard-queen-room".
 * @return string URL.
 */
function amour_page_url( $path ) {
	$page = get_page_by_path( $path );
	if ( ! $page ) {
		return home_url( '/' . trailingslashit( $path ) );
	}
	$id = $page->ID;
	if ( function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $id );
		$id         = $translated ? $translated : $id;
	}
	return get_permalink( $id );
}

/**
 * Home of the current house: the hotel home page, or the villa page on the villa (HANDOFF, section 5).
 *
 * @return string URL.
 */
function amour_home_url() {
	if ( 'eudora' === amour_brand() ) {
		return amour_page_url( 'villa-eudora' );
	}
	return function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
}

/**
 * Site languages with the address of this page in each (or that language's home page).
 *
 * @return array<int, array{code: string, name: string, locale: string, url: string, current: bool}>
 */
function amour_languages() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return array();
	}
	$raw = pll_the_languages(
		array(
			'raw'                    => 1,
			'hide_if_no_translation' => 0,
			'force_home'             => 0,
		)
	);
	$out = array();
	foreach ( (array) $raw as $language ) {
		$out[] = array(
			'code'    => strtoupper( $language['slug'] ),
			'name'    => $language['name'],
			'locale'  => str_replace( '_', '-', $language['locale'] ),
			'url'     => $language['url'],
			'current' => ! empty( $language['current_lang'] ),
		);
	}
	return $out;
}

/**
 * A footer column from a menu location: the menu name is the column title (Appearance → Menus).
 *
 * @param string $location Menu location.
 * @return array{title: string, links: array<int, array{label: string, url: string}>}|null
 */
function amour_menu_column( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return null;
	}
	$menu  = wp_get_nav_menu_object( $locations[ $location ] );
	$items = $menu ? wp_get_nav_menu_items( $menu->term_id ) : array();
	if ( ! $items ) {
		return null;
	}
	return array(
		'title' => $menu->name,
		'links' => array_map(
			static fn( $item ) => array(
				'label' => $item->title,
				'url'   => $item->url,
			),
			$items
		),
	);
}

/**
 * Everything the footer shows for a house (boards About and Villa-v2-b; HANDOFF, section 5 and decision 19.11):
 * two menu columns plus Contact, networks, two legal lines.
 *
 * Greek letters are not in Instrument Sans, so the registry numbers are written GEMI and VAT (AFM) on the site.
 *
 * @param string $brand "amour" or "eudora".
 * @return array<string, mixed>
 */
function amour_footer_data( $brand ) {
	$o      = static fn( $key ) => (string) amour_option( $key );
	$joined = static fn( array $parts ) => implode( ' · ', array_filter( $parts, static fn( $part ) => '' !== $part ) );
	$label  = static fn( $prefix, $value ) => '' === $value ? '' : $prefix . ' ' . $value;
	$villa  = 'eudora' === $brand;

	$contacts = amour_contacts();
	$contact  = array();
	foreach ( array( 'phone', 'email' ) as $key ) {
		if ( isset( $contacts[ $key ] ) ) {
			$contact[] = $contacts[ $key ];
		}
	}
	$place = amour_or_placeholder( $o( 'amour_place' ), 'Place' );
	if ( '' !== $place ) {
		$contact[] = array(
			'label' => $place,
			'url'   => amour_page_url( 'contacts' ),
		);
	}

	if ( $villa ) {
		$social = array();
		foreach ( (array) amour_option( 'amour_villa_social' ) as $row ) {
			if ( ! empty( $row['label'] ) && ! empty( $row['url'] ) ) {
				$social[] = array(
					'label' => $row['label'],
					'url'   => $row['url'],
				);
			}
		}
		if ( ! $social && '' !== amour_or_placeholder( '', 'Villa Instagram' ) ) {
			$social[] = array(
				'label' => amour_or_placeholder( '', 'Villa Instagram' ),
				'url'   => '',
			);
		}
		$score = $o( 'amour_villa_booking_score' );
		if ( '' !== $score ) {
			$social[] = array(
				'label' => 'Booking.com · ' . $score,
				'url'   => $o( 'amour_villa_booking_url' ),
			);
		}

		$line_1 = $joined(
			array(
				'Villa Eudora',
				amour_or_placeholder( $o( 'amour_villa_company' ), 'Owner company' ),
				amour_or_placeholder( $o( 'amour_villa_address' ), 'Address' ),
				$label( __( 'Villa licence', 'amour' ), amour_or_placeholder( $o( 'amour_mhte_villa' ), 'MHTE or AMA number' ) ),
			)
		);
		$line_2 = $joined(
			array(
				$label( 'GEMI', amour_or_placeholder( $o( 'amour_villa_gemi' ), 'GEMI number' ) ),
				$label( 'VAT (AFM)', amour_or_placeholder( $o( 'amour_villa_afm' ), 'AFM number' ) ),
			)
		);
	} else {
		$social = amour_socials();
		$line_1 = $joined(
			array(
				amour_or_placeholder( $o( 'amour_company' ), 'Company name' ),
				amour_or_placeholder( $o( 'amour_address' ), 'Address' ),
				$label( 'MHTE', amour_or_placeholder( $o( 'amour_mhte_hotel' ), 'MHTE number' ) ),
			)
		);
		$line_2 = $joined(
			array(
				$label( 'GEMI', amour_or_placeholder( $o( 'amour_gemi' ), 'GEMI number' ) ),
				$label( 'VAT (AFM)', amour_or_placeholder( $o( 'amour_afm' ), 'AFM number' ) ),
				$label( __( 'Villa Eudora licence', 'amour' ), amour_or_placeholder( $o( 'amour_mhte_villa' ), 'MHTE or AMA number' ) ),
			)
		);
	}

	return array(
		'columns' => array_values(
			array_filter(
				array(
					amour_menu_column( $villa ? 'footer-villa' : 'footer-stay' ),
					amour_menu_column( $villa ? 'footer-around' : 'footer-discover' ),
				)
			)
		),
		'contact' => $contact,
		'social'  => $social,
		'legal'   => array_values( array_filter( array( $line_1, $line_2 ) ) ),
	);
}
