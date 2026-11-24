<?php
/**
 * Search and sharing (HANDOFF, section 13): the title of every page as on the boards ("Rooms · Amour Relax Hotel"),
 * its description, the Open Graph picture cut to 1200 × 630, schema.org Hotel on the home page and the rooms and
 * LodgingBusiness on the villa, noindex on a test copy, the sitemap and the old addresses (301).
 * Canonical addresses and hreflang come from WordPress and Polylang. Only real values go out: an empty field or a
 * placeholder in square brackets is left out.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * The picture for sharing, cut to 1200 × 630 (a larger photo; smaller ones are shared whole).
 */
function amour_seo_sizes() {
	add_image_size( 'amour-share', 1200, 630, true );
}
add_action( 'after_setup_theme', 'amour_seo_sizes' );

/**
 * A value worth giving to Google: not empty and not a placeholder.
 *
 * @param mixed $value Value.
 * @return string The value, or ''.
 */
function amour_seo_real( $value ) {
	$value = is_scalar( $value ) ? trim( (string) $value ) : '';
	return '' === $value || str_starts_with( $value, '[' ) ? '' : $value;
}

/**
 * The page the head speaks for: the current page, or the front page.
 *
 * @return int Page id, or 0.
 */
function amour_seo_page() {
	if ( is_front_page() ) {
		$front = (int) get_option( 'page_on_front' );
		return function_exists( 'pll_get_post' ) && pll_get_post( $front ) ? (int) pll_get_post( $front ) : $front;
	}
	return is_page() ? get_queried_object_id() : 0;
}

/**
 * The separator of the boards: "Rooms · Amour Relax Hotel".
 *
 * @return string
 */
function amour_seo_separator() {
	return '·';
}
add_filter( 'document_title_separator', 'amour_seo_separator' );

/**
 * A page's own title from the admin, when it has one ("Amour Relax Hotel · Almyrida, Crete" on the home page).
 *
 * @param string $title Title WordPress would make; '' lets it make one.
 * @return string
 */
function amour_seo_title( $title ) {
	$id  = amour_seo_page();
	$own = $id ? amour_seo_real( amour_page_field( 'seo_title', $id ) ) : '';
	return $own ? $own : $title;
}
add_filter( 'pre_get_document_title', 'amour_seo_title' );

/**
 * The home page without a title of its own: the site name and where it is, not the tagline.
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function amour_seo_title_parts( $parts ) {
	if ( is_front_page() ) {
		unset( $parts['tagline'] );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'amour_seo_title_parts' );

/**
 * The picture a page shares: its own, the card photo of a room or the villa, or the one in Hotel settings.
 *
 * @param int $id Page id.
 * @return array{url: string, width: int, height: int, alt: string}|null
 */
function amour_seo_image( $id ) {
	$photo = $id ? (int) amour_page_field( 'seo_image', $id ) : 0;
	if ( ! $photo && $id && ( amour_is_room( $id ) || amour_is_villa( $id ) ) ) {
		$photo = (int) amour_page_field( 'card_photo', $id );
	}
	$photo = $photo ? $photo : (int) amour_option( 'amour_share_image' );
	$image = $photo ? wp_get_attachment_image_src( $photo, 'amour-share' ) : false;
	if ( ! $image ) {
		return null;
	}
	return array(
		'url'    => $image[0],
		'width'  => (int) $image[1],
		'height' => (int) $image[2],
		'alt'    => (string) get_post_meta( $photo, '_wp_attachment_image_alt', true ),
	);
}

/**
 * The description, Open Graph and the schema.org data in the head.
 */
function amour_seo_head() {
	$id          = amour_seo_page();
	$description = $id ? amour_seo_real( amour_page_field( 'seo_description', $id ) ) : '';
	$url         = $id ? wp_get_canonical_url( $id ) : '';
	$url         = is_front_page() ? amour_home_url() : $url;
	$image       = amour_seo_image( $id );
	$locale      = get_locale();

	$tags = array(
		'og:type'      => 'website',
		'og:site_name' => get_bloginfo( 'name' ),
		'og:title'     => wp_get_document_title(),
		'og:url'       => is_404() ? '' : (string) $url,
		'og:locale'    => $locale,
	);
	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
		$tags['og:description'] = $description;
	}
	if ( $image ) {
		$tags['og:image']        = $image['url'];
		$tags['og:image:width']  = (string) $image['width'];
		$tags['og:image:height'] = (string) $image['height'];
		$tags['og:image:alt']    = $image['alt'];
	}
	foreach ( array_filter( $tags ) as $property => $content ) {
		printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $property ), esc_attr( $content ) );
	}
	foreach ( amour_languages() as $language ) {
		if ( ! $language['current'] ) {
			printf( '<meta property="og:locale:alternate" content="%s">' . "\n", esc_attr( str_replace( '-', '_', $language['locale'] ) ) );
		}
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

	$schema = amour_schema( $id, $description, $image );
	if ( $schema ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'amour_seo_head', 5 );

/**
 * The hotel for schema.org: only what is known for sure, no placeholders.
 *
 * @return array<string, mixed>
 */
function amour_schema_hotel() {
	$o     = static fn( $key ) => amour_seo_real( amour_option( $key ) );
	$image = amour_seo_image( 0 );
	$front = (int) get_option( 'page_on_front' );
	return array_filter(
		array(
			'@type'          => 'Hotel',
			'@id'            => home_url( '/#hotel' ),
			'name'           => get_bloginfo( 'name' ),
			'url'            => home_url( '/' ),
			'description'    => $front ? amour_seo_real( amour_page_field( 'seo_description', $front ) ) : '',
			'image'          => $image ? $image['url'] : '',
			'telephone'      => $o( 'amour_phone' ),
			'email'          => $o( 'amour_email' ),
			'address'        => array_filter(
				array(
					'@type'           => 'PostalAddress',
					'streetAddress'   => $o( 'amour_address' ),
					'addressLocality' => 'Almyrida',
					'addressRegion'   => 'Crete',
					'addressCountry'  => 'GR',
				)
			),
			'checkinTime'    => $o( 'amour_checkin' ),
			'checkoutTime'   => $o( 'amour_checkout' ),
			// The facts the brand manual confirms: nine rooms, each with a kitchen and a terrace, a pool, a court.
			'numberOfRooms'  => 9,
			'amenityFeature' => array_map(
				static fn( $name ) => array(
					'@type' => 'LocationFeatureSpecification',
					'name'  => $name,
					'value' => true,
				),
				array( 'Outdoor pool', 'Tennis court', 'Kitchen in every room', 'Terrace in every room' )
			),
		)
	);
}

/**
 * schema.org for this page: the Hotel on the home page and the Rooms page, the Hotel and the HotelRoom on a room
 * page, LodgingBusiness on the villa.
 *
 * @param int                   $id          Page id.
 * @param string                $description The page description.
 * @param array<string, mixed>|null $image   The page picture.
 * @return array<string, mixed>|null
 */
function amour_schema( $id, $description, $image ) {
	if ( is_front_page() || amour_is_page( 'rooms' ) ) {
		return array( '@context' => 'https://schema.org' ) + amour_schema_hotel();
	}
	if ( $id && amour_is_room( $id ) ) {
		$guests = (int) amour_page_field( 'max_guests', $id );
		$room   = array_filter(
			array(
				'@type'            => 'HotelRoom',
				'name'             => get_the_title( $id ),
				'url'              => wp_get_canonical_url( $id ),
				'description'      => $description ? $description : amour_seo_real( amour_page_field( 'list_text', $id ) ),
				'image'            => $image ? $image['url'] : '',
				'occupancy'        => $guests ? array(
					'@type'    => 'QuantitativeValue',
					'maxValue' => $guests,
				) : null,
				'containedInPlace' => array( '@id' => home_url( '/#hotel' ) ),
			)
		);
		return array(
			'@context' => 'https://schema.org',
			'@graph'   => array( amour_schema_hotel(), $room ),
		);
	}
	if ( $id && amour_is_villa( $id ) ) {
		$o = static fn( $key ) => amour_seo_real( amour_option( $key ) );
		return array_filter(
			array(
				'@context'       => 'https://schema.org',
				'@type'          => 'LodgingBusiness',
				'name'           => 'Villa Eudora',
				'url'            => wp_get_canonical_url( $id ),
				'description'    => $description,
				'image'          => $image ? $image['url'] : '',
				'telephone'      => $o( 'amour_phone' ),
				'address'        => array_filter(
					array(
						'@type'           => 'PostalAddress',
						'streetAddress'   => $o( 'amour_villa_address' ),
						'addressLocality' => 'Almyrida',
						'addressRegion'   => 'Crete',
						'addressCountry'  => 'GR',
					)
				),
				// Five bedrooms and its own pool (brand manual).
				'numberOfRooms'  => 5,
				'amenityFeature' => array(
					array(
						'@type' => 'LocationFeatureSpecification',
						'name'  => 'Private pool',
						'value' => true,
					),
				),
			)
		);
	}
	return null;
}

/**
 * A test copy is not for search engines (HANDOFF, section 13); on the live site only the thank-you page is not.
 *
 * @param array<string, bool> $robots Robots directives.
 * @return array<string, bool>
 */
function amour_seo_robots( $robots ) {
	if ( 'production' !== wp_get_environment_type() ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
		unset( $robots['max-image-preview'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'amour_seo_robots' );

/**
 * The sitemap lists the pages, without the thank-you page; no list of users.
 *
 * @param array<string, mixed> $args Query arguments.
 * @return array<string, mixed>
 */
function amour_sitemap_pages( $args ) {
	$thanks = get_page_by_path( 'thank-you' );
	if ( $thanks ) {
		$args['post__not_in'] = array_merge( (array) ( $args['post__not_in'] ?? array() ), amour_core_ids_or_self( $thanks->ID ) );
	}
	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'amour_sitemap_pages' );

/**
 * No sitemap of users.
 *
 * @param WP_Sitemaps_Provider|false $provider Provider.
 * @param string                     $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function amour_sitemap_no_users( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'amour_sitemap_no_users', 10, 2 );

/**
 * Old addresses from Hotel settings lead to the new pages with a permanent redirect (HANDOFF, section 2:
 * /checkout-page/ → /book/ and what Search Console still knows). Only an address that is not a page now.
 */
function amour_old_addresses() {
	if ( ! is_404() ) {
		return;
	}
	$request = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path    = trailingslashit( (string) wp_parse_url( $request, PHP_URL_PATH ) );
	foreach ( (array) amour_option( 'amour_redirects' ) as $row ) {
		$from = trailingslashit( '/' . ltrim( (string) wp_parse_url( trim( (string) ( $row['from'] ?? '' ) ), PHP_URL_PATH ), '/' ) );
		$to   = trim( (string) ( $row['to'] ?? '' ) );
		if ( '/' !== $from && $from === $path && '' !== $to ) {
			wp_safe_redirect( str_starts_with( $to, '/' ) ? home_url( $to ) : $to, 301, 'Amour' );
			exit;
		}
	}
}
add_action( 'template_redirect', 'amour_old_addresses', 1 );

// What WordPress adds to the head that no guest and no search engine needs.
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'rsd_link' );
