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
