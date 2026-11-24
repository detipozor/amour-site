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
			'id'    => (int) $page->ID,
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
 * Places on a map (plugin amour-core, type amour_place), in their order, in the current language.
 *
 * @param string $map Which map: on_home (the home page), on_rooms (the room pages) or on_villa (the villa page).
 * @return array<int, array<string, mixed>>
 */
function amour_map_places( $map = 'on_home' ) {
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
		if ( ! $f( $map ) ) {
			continue;
		}
		$places[] = array(
			'id'              => $id,
			'kind'            => (string) $f( 'kind' ),
			'name'            => get_the_title( $id ),
			'label'           => (string) $f( 'label' ),
			'short'           => (string) $f( 'short' ),
			'x'               => (float) $f( 'x' ),
			'y'               => (float) $f( 'y' ),
			'dir'             => (string) $f( 'dir' ) ? (string) $f( 'dir' ) : 'row',
			'dir_phone'       => (string) $f( 'dir_phone' ) ? (string) $f( 'dir_phone' ) : ( (string) $f( 'dir' ) ? (string) $f( 'dir' ) : 'row' ),
			'meta'            => (string) $f( 'meta' ),
			'text'            => (string) $f( 'text' ),
			'photo'           => (int) $f( 'photo' ),
			'route'           => (string) $f( 'route' ),
			'villa'           => (string) $f( 'villa_label' ),
			'guide_dir'       => (string) $f( 'guide_dir' ),
			'guide_dir_phone' => (string) $f( 'guide_dir_phone' ),
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

/**
 * Is this a room page (a page under /rooms/, in any language)?
 *
 * @param int|null $id Page id; the current page by default.
 * @return bool
 */
function amour_is_room( $id = null ) {
	$id = $id ? (int) $id : ( is_page() ? get_queried_object_id() : 0 );
	if ( ! $id ) {
		return false;
	}
	$source = function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) && pll_get_post( $id, pll_default_language() ) ? pll_get_post( $id, pll_default_language() ) : $id;
	$parent = get_post_parent( $source );
	return $parent && 'rooms' === $parent->post_name;
}

/**
 * Everything a room page shows (board Room-*, design/generators/room_template.html), from the fields of the page.
 * The photo stream is laid out here, as on the board: upright photos 34% wide, wide ones 52%, left and right in
 * turn with small indents; a photo moves up beside the previous one when the two fit side by side.
 *
 * @param int|null $id Page id; the current page by default.
 * @return array<string, mixed>
 */
function amour_room( $id = null ) {
	$id     = $id ? (int) $id : get_queried_object_id();
	$f      = static fn( $name ) => amour_page_field( $name, $id );
	$price  = $f( 'price_from' );
	$photos = array_values( array_filter( (array) $f( 'room_gallery' ), static fn( $p ) => ! empty( $p['photo'] ) ) );

	$figs = array();
	$prev = null;
	foreach ( $photos as $i => $photo ) {
		$meta  = wp_get_attachment_metadata( (int) $photo['photo'] );
		$shape = $photo['shape'] ?? 'auto';
		if ( 'auto' === $shape || '' === $shape ) {
			$shape = ! empty( $meta['height'] ) && ! empty( $meta['width'] ) && $meta['height'] > $meta['width'] ? 'upright' : 'wide';
		}
		$width  = 'upright' === $shape ? 34 : 52;
		$indent = array( 0, 0, 6, 4, 2, 8, 0 )[ $i % 7 ];
		$reach  = $width + $indent;
		$beside = null !== $prev && $prev + $reach + 4 <= 100;
		$prev   = $reach;
		$figs[] = array(
			'photo'   => (int) $photo['photo'],
			'caption' => (string) ( $photo['caption'] ?? '' ),
			'shape'   => $shape,
			'side'    => $i % 2 ? 'right' : 'left',
			'width'   => $width,
			'indent'  => $indent,
			'top'     => 0 === $i ? 'first' : ( $beside ? 'beside' : 'below' ),
		);
	}

	$facts = array_values(
		array_filter(
			array_map(
				static fn( $row ) => array(
					'label' => trim( (string) ( $row['label'] ?? '' ) ),
					'value' => trim( (string) ( $row['value'] ?? '' ) ),
				),
				(array) $f( 'room_facts' )
			),
			static fn( $row ) => '' !== $row['label']
		)
	);

	// The rules for every room close the list of all details; the villa has its own, among its details.
	$villa = amour_is_villa( $id );
	$stay  = array();
	foreach ( $villa ? array() : array(
		/* translators: %s: time, for example 14:00. */
		'amour_checkin'  => array( __( 'Check-in', 'amour' ), __( 'From %s', 'amour' ) ),
		/* translators: %s: time, for example 11:00. */
		'amour_checkout' => array( __( 'Check-out', 'amour' ), __( 'By %s', 'amour' ) ),
	) as $key => $words ) {
		$time = trim( (string) amour_option( $key ) );
		if ( '' !== $time ) {
			$stay[] = array(
				'label' => $words[0],
				'value' => sprintf( $words[1], $time ),
			);
		}
	}
	foreach ( $villa ? array() : (array) amour_option( 'amour_stay_rules' ) as $rule ) {
		if ( ! empty( $rule['label'] ) ) {
			$stay[] = array(
				'label' => (string) $rule['label'],
				'value' => (string) ( $rule['value'] ?? '' ),
			);
		}
	}

	return array(
		'name'        => (string) ( $f( 'room_name' ) ? $f( 'room_name' ) : ( $f( 'card_name' ) ? $f( 'card_name' ) : get_the_title( $id ) ) ),
		'kicker'      => (string) $f( 'room_kicker' ),
		'meta'        => (string) $f( 'room_meta' ),
		'cue'         => (string) $f( 'room_cue' ),
		'figs'        => $figs,
		'title'       => (string) $f( 'room_details_title' ),
		'desc_label'  => (string) $f( 'room_desc_label' ),
		'description' => (string) $f( 'room_description' ),
		'key'         => array_slice( $facts, 0, 6 ),
		'more'        => array_merge( array_slice( $facts, 6 ), $stay ),
		'price'       => amour_or_placeholder( is_numeric( $price ) ? number_format_i18n( (float) $price ) : '', 'price' ),
		'villa'       => $villa,
	);
}

/**
 * The location block of the room and villa pages (room_template.html, "Almyrida, Crete"): the card from
 * Hotel settings and the places marked for this map, the main place (the hotel, or the villa on its page) first.
 *
 * @param string $page Which page: rooms or villa.
 * @return array<string, mixed>
 */
function amour_location( $page = 'rooms' ) {
	$villa  = 'villa' === $page;
	$main   = $villa ? 'villa' : 'hotel';
	$places = amour_map_places( $villa ? 'on_villa' : 'on_rooms' );
	foreach ( $places as &$place ) {
		$place['main'] = $main === $place['kind'];
		if ( $villa && '' !== $place['villa'] ) {
			$place['label'] = $place['villa'];
		}
	}
	unset( $place );
	usort( $places, static fn( $a, $b ) => (int) ! $a['main'] <=> (int) ! $b['main'] );

	$rows = $villa ? amour_page_field( 'villa_near' ) : amour_option( 'amour_location_near' );
	$near = array();
	foreach ( (array) $rows as $row ) {
		if ( ! empty( $row['place'] ) ) {
			$near[] = array(
				'place' => (string) $row['place'],
				'value' => (string) ( $row['value'] ?? '' ),
			);
		}
	}
	return array(
		'title'  => (string) amour_option( 'amour_location_title' ),
		'text'   => (string) amour_option( 'amour_location_text' ),
		'near'   => $near,
		'maps'   => (string) amour_option( 'amour_maps_url' ),
		'places' => $places,
		'alt'    => $villa
			? __( 'Relief map of Apokoronas: the villa and the hotel above Almyrida bay, Chania and the airport to the north-west', 'amour' )
			: __( 'Relief map of Apokoronas: the hotel above Almyrida bay, Chania and the airport to the north-west', 'amour' ),
	);
}

/**
 * Is this one of the site's pages, by its address in the default language (any translation of it counts)?
 *
 * @param string $path Page path in the default language, for example rooms.
 * @return bool
 */
function amour_is_page( $path ) {
	if ( ! is_page() ) {
		return false;
	}
	$page = get_page_by_path( $path );
	return $page && in_array( get_queried_object_id(), array_map( 'intval', amour_core_ids_or_self( $page->ID ) ), true );
}

/**
 * The rooms on the Rooms page (board Rooms): each room page's "On the Rooms page" fields, its first photo,
 * its price, in menu order.
 *
 * @return array<int, array<string, mixed>>
 */
function amour_rooms_list() {
	$list = array();
	foreach ( wp_list_filter( amour_cards(), array( 'villa' => false ) ) as $card ) {
		$f      = static fn( $name ) => amour_page_field( $name, $card['id'] );
		$photos = array_values( array_filter( (array) $f( 'room_gallery' ), static fn( $p ) => ! empty( $p['photo'] ) ) );
		$facts  = array_values( array_filter( (array) $f( 'list_facts' ), static fn( $row ) => ! empty( $row['label'] ) ) );
		$name   = (string) $f( 'list_name' );
		$list[] = array(
			'url'   => $card['url'],
			'name'  => $name ? $name : get_the_title( $card['id'] ),
			'text'  => (string) $f( 'list_text' ),
			'facts' => $facts,
			'photo' => $photos ? (int) $photos[0]['photo'] : $card['photo'],
			'price' => $card['price'],
		);
	}
	return $list;
}

/**
 * Is this the villa page (in any language)?
 *
 * @param int|null $id Page id; the current page by default.
 * @return bool
 */
function amour_is_villa( $id = null ) {
	$id    = $id ? (int) $id : ( is_page() ? get_queried_object_id() : 0 );
	$villa = get_page_by_path( 'villa-eudora' );
	return $id && $villa && in_array( $id, array_map( 'intval', amour_core_ids_or_self( $villa->ID ) ), true );
}

/**
 * The Crete guide (board Guide): the places on the big map, and the Days out cards with their small road maps.
 * Drive times come from each place's "Distance and time" (for example 41 km · 1 hr by car), written short for
 * the card (1 h); the small map frames the road from the hotel with some room around it.
 *
 * @return array{pins: array<int, array<string, mixed>>, days: array<int, array<string, mixed>>, hotel: ?array<string, mixed>}
 */
function amour_guide() {
	$places = amour_map_places();
	$hotel  = wp_list_filter( $places, array( 'kind' => 'hotel' ) );
	$hotel  = $hotel ? reset( $hotel ) : null;
	$villa  = wp_list_filter( $places, array( 'kind' => 'villa' ) );
	$villa  = $villa ? reset( $villa ) : null;
	$pins   = array();
	$days   = array();
	foreach ( $places as $place ) {
		if ( 'villa' === $place['kind'] ) {
			continue;
		}
		if ( 'hotel' === $place['kind'] && $villa ) {
			$place['label'] = $place['label'] . ' · ' . $villa['label'];
			$place['short'] = __( 'Amour · Villa Eudora', 'amour' );
		}
		$pins[] = $place;
		if ( 'trip' !== $place['kind'] ) {
			continue;
		}

		// "41 km · 1 hr by car" → 1 h, 41 km by car; "65 km · 1 hr 30 min to Xyloskalo" → 1 h 30, 65 km to Xyloskalo.
		$parts = array_map( 'trim', explode( '·', $place['meta'] ) );
		$where = str_contains( $place['meta'], 'Xyloskalo' ) ? __( 'to Xyloskalo', 'amour' ) : __( 'by car', 'amour' );
		$time  = preg_replace( array( '/ by car$/', '/ to Xyloskalo$/', '/ hr (\d+) min/', '/ hr$/' ), array( '', '', ' h $1', ' h' ), $parts[1] ?? '' );

		// The small map: the road's box, as a 4:3 view that a 4:5 card cuts to its middle, with room around.
		$mini = null;
		if ( $place['route'] && preg_match_all( '/-?\d+(?:\.\d+)?/', $place['route'], $nums ) ) {
			$xs   = array_map( 'floatval', array_values( array_filter( $nums[0], static fn( $k ) => 0 === $k % 2, ARRAY_FILTER_USE_KEY ) ) );
			$ys   = array_map( 'floatval', array_values( array_filter( $nums[0], static fn( $k ) => 1 === $k % 2, ARRAY_FILTER_USE_KEY ) ) );
			$h    = max( max( $ys ) - min( $ys ), ( max( $xs ) - min( $xs ) ) * 5 / 4 ) * 1.35;
			$w    = $h * 4 / 3;
			$cx   = ( min( $xs ) + max( $xs ) ) / 2;
			$cy   = ( min( $ys ) + max( $ys ) ) / 2;
			$mini = array(
				'vb'    => sprintf( '%.1f %.1f %.1f %.1f', $cx - $w / 2, $cy - $h / 2, $w, $h ),
				'u'     => round( $w / 250, 3 ), // one "unit" of line width at this size
				'start' => array( $xs[0], $ys[0] ),
				'end'   => array( end( $xs ), end( $ys ) ),
			);
		}
		$days[] = array(
			'name'  => $place['name'],
			'time'  => $time,
			'km'    => trim( ( $parts[0] ?? '' ) . ' ' . $where ),
			'note'  => (string) amour_page_field( 'note', $place['id'] ),
			'photo' => $place['photo'],
			'route' => $place['route'],
			'mini'  => $mini,
		);
	}
	return array(
		'pins'  => $pins,
		'days'  => $days,
		'hotel' => $hotel,
	);
}
