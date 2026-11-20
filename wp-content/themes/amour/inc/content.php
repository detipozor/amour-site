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

/**
 * A field of the current page. A translation without its own value shows the default-language value,
 * so a German page is never empty while its texts are being written.
 *
 * @param string   $name    Field name.
 * @param int|null $post_id Page id, the current page by default.
 * @return mixed
 */
function amour_page_field( $name, $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	$post_id = $post_id ? $post_id : get_queried_object_id();
	$value   = get_field( $name, $post_id );
	if ( ( null === $value || '' === $value || false === $value ) && function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
		$source = pll_get_post( $post_id, pll_default_language() );
		if ( $source && (int) $source !== (int) $post_id ) {
			$value = get_field( $name, $source );
		}
	}
	return $value;
}

/**
 * Stroke icons of the card facts (board Main-v2, IC).
 */
const AMOUR_FACT_ICONS = array(
	'area'   => 'M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5',
	'guests' => 'M12 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM5 20.5v-1A5.5 5.5 0 0 1 10.5 14h3a5.5 5.5 0 0 1 5.5 5.5v1',
	'bed'    => 'M3 19V6M3 15h18v4M21 15v-2a3 3 0 0 0-3-3h-8v5M6.5 12.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z',
	'water'  => 'M3 9c2 0 2-1.5 4.5-1.5S10 9 12 9s2-1.5 4.5-1.5S19 9 21 9M3 15c2 0 2-1.5 4.5-1.5S10 15 12 15s2-1.5 4.5-1.5S19 15 21 15',
);

/**
 * Cards of the rooms (pages under /rooms/, in menu order) and of the villa, in the current language.
 *
 * @return array<int, array<string, mixed>>
 */
function amour_cards() {
	$rooms = get_page_by_path( 'rooms' );
	$pages = array();
	if ( $rooms ) {
		$parent = function_exists( 'pll_get_post' ) && pll_get_post( $rooms->ID ) ? pll_get_post( $rooms->ID ) : $rooms->ID;
		$pages  = get_pages(
			array(
				'parent'      => $parent,
				'sort_column' => 'menu_order,post_title',
			)
		);
	}
	$villa = get_page_by_path( 'villa-eudora' );
	if ( $villa ) {
		$villa_id = function_exists( 'pll_get_post' ) && pll_get_post( $villa->ID ) ? pll_get_post( $villa->ID ) : $villa->ID;
		$pages[]  = get_post( $villa_id );
	}

	$cards = array();
	foreach ( $pages as $page ) {
		$is_villa = $villa && in_array( (int) $page->ID, array_map( 'intval', amour_core_ids_or_self( $villa->ID ) ), true );
		$name     = (string) amour_page_field( 'card_name', $page->ID );
		$facts    = array_values( array_filter( (array) amour_page_field( 'card_facts', $page->ID ), static fn( $f ) => ! empty( $f['text'] ) ) );
		$price    = amour_page_field( 'price_from', $page->ID );
		$price    = $is_villa ? '' : amour_or_placeholder( null === $price || '' === $price ? '' : (string) $price, 'price' );
		$say      = array_merge( wp_list_pluck( $facts, 'text' ), $price ? array( sprintf( /* translators: %s: price, e.g. 95 */ __( 'from €%s a night', 'amour' ), $price ) ) : array() );

		$cards[] = array(
			'url'   => get_permalink( $page ),
			'name'  => $name ? $name : get_the_title( $page ),
			'facts' => $facts,
			'price' => $price,
			'label' => ( $name ? $name : get_the_title( $page ) ) . ( $say ? ', ' . implode( ', ', $say ) : '' ),
			'photo' => (int) amour_page_field( 'card_photo', $page->ID ),
			'hover' => (int) amour_page_field( 'card_photo_hover', $page->ID ),
			'villa' => $is_villa,
		);
	}
	return $cards;
}

/**
 * A page id with its translations (or just the id without Polylang).
 *
 * @param int $id Page id.
 * @return int[]
 */
function amour_core_ids_or_self( $id ) {
	$ids = array( (int) $id );
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$ids = array_merge( $ids, array_map( 'intval', array_values( pll_get_post_translations( $id ) ) ) );
	}
	return $ids;
}

/**
 * Places on the home map (plugin amour-core, type amour_place), in their order, in the current language.
 *
 * @return array<int, array<string, mixed>>
 */
function amour_map_places() {
	if ( ! post_type_exists( 'amour_place' ) ) {
		return array();
	}
	$posts  = get_posts(
		array(
			'post_type'      => 'amour_place',
			'posts_per_page' => 50,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'lang'           => function_exists( 'pll_current_language' ) ? pll_default_language() : '',
		)
	);
	$places = array();
	foreach ( $posts as $post ) {
		$id = function_exists( 'pll_get_post' ) && pll_get_post( $post->ID ) ? pll_get_post( $post->ID ) : $post->ID;
		$f  = static fn( $name ) => amour_page_field( $name, $id );
		if ( ! $f( 'on_home' ) ) {
			continue;
		}
		$places[] = array(
			'id'        => $id,
			'kind'      => (string) $f( 'kind' ),
			'name'      => get_the_title( $id ),
			'label'     => (string) $f( 'label' ),
			'short'     => (string) $f( 'short' ),
			'x'         => (float) $f( 'x' ),
			'y'         => (float) $f( 'y' ),
			'dir'       => (string) $f( 'dir' ) ? (string) $f( 'dir' ) : 'row',
			'dir_phone' => (string) $f( 'dir_phone' ) ? (string) $f( 'dir_phone' ) : ( (string) $f( 'dir' ) ? (string) $f( 'dir' ) : 'row' ),
			'meta'      => (string) $f( 'meta' ),
			'text'      => (string) $f( 'text' ),
			'photo'     => (int) $f( 'photo' ),
			'route'     => (string) $f( 'route' ),
		);
	}
	return $places;
}

/**
 * Reviews for the home page (Reviews in the admin, in their order). Reviews stay in the language they were
 * written in, so both languages show the same ones; the room name follows the page language.
 * A review still in square brackets (the board's placeholder) never reaches the live site.
 *
 * @return array<int, array{name: string, initial: string, who: string, when: string, text: string, score: string, source: string}>
 */
function amour_reviews() {
	if ( ! post_type_exists( 'amour_review' ) || ! function_exists( 'get_field' ) ) {
		return array();
	}
	$live    = 'production' === wp_get_environment_type();
	$joined  = static fn( $parts ) => implode( ' · ', array_filter( $parts, static fn( $p ) => '' !== $p ) );
	$reviews = array();
	$posts   = get_posts(
		array(
			'post_type'      => 'amour_review',
			'posts_per_page' => 30,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	foreach ( $posts as $post ) {
		$text = trim( (string) get_field( 'text', $post->ID ) );
		if ( '' === $text || ! get_field( 'on_home', $post->ID ) || ( $live && str_starts_with( $text, '[' ) ) ) {
			continue;
		}
		$name  = amour_or_placeholder( get_the_title( $post ), 'Name' );
		$room  = (int) get_field( 'room', $post->ID, false );
		$room  = $room && function_exists( 'pll_get_post' ) && pll_get_post( $room ) ? pll_get_post( $room ) : $room;
		$month = (string) get_field( 'month', $post->ID );
		$month = $month ? date_i18n( 'F Y', strtotime( $month ) ) : '';
		$score = get_field( 'score', $post->ID );
		$first = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );

		$reviews[] = array(
			'name'    => $name,
			'initial' => '' === $first || '[' === $first ? '·' : $first,
			'who'     => $joined( array( $name, amour_or_placeholder( (string) get_field( 'country', $post->ID ), 'Country' ) ) ),
			'when'    => $joined( array( amour_or_placeholder( $month, 'Month Year' ), $room ? get_the_title( $room ) : '' ) ),
			'text'    => $text,
			'score'   => is_numeric( $score ) ? rtrim( rtrim( number_format( (float) $score, 1, '.', '' ), '0' ), '.' ) : amour_or_placeholder( '', 'Score' ),
			'source'  => (string) get_field( 'source', $post->ID ),
		);
	}
	return $reviews;
}
