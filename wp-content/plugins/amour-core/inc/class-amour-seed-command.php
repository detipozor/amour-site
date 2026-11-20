<?php
/**
 * WP-CLI command that fills a fresh WordPress with the site structure:
 * pages at the addresses from HANDOFF section 2, menus, languages and photos.
 *
 * Safe to run again: it finds what already exists and only adds what is missing.
 * For local copies, previews and the test copy only; never run it on the live site.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seed the site structure.
 */
class Amour_Seed_Command {

	/**
	 * Pages: slug => [ title, parent slug ]. Addresses from HANDOFF, section 2.
	 *
	 * @var array<string, array{0: string, 1: string}>
	 */
	const PAGES = array(
		'home'                => array( 'Home', '' ),
		'rooms'               => array( 'Rooms', '' ),
		'standard-queen-room' => array( 'Standard Queen Room', 'rooms' ),
		'deluxe-queen-room'   => array( 'Deluxe Queen Room', 'rooms' ),
		'superior-queen-room' => array( 'Superior Queen Room', 'rooms' ),
		'villa-eudora'        => array( 'Villa Eudora', '' ),
		'court-and-pool'      => array( 'Court and pool', '' ),
		'crete-guide'         => array( 'Crete guide', '' ),
		'questions'           => array( 'Questions and house rules', '' ),
		'contacts'            => array( 'Contact', '' ),
		'book'                => array( 'Book your stay', '' ),
		'about-us'            => array( 'About us', '' ),
		'privacy-policy'      => array( 'Privacy policy', '' ),
		'cookies'             => array( 'Cookies', '' ),
		'booking-terms'       => array( 'Booking terms', '' ),
		'thank-you'           => array( 'Thank you', '' ),
	);

	/**
	 * Menus: location => [ menu name, items ]. Item: [ label, page slug or "#anchor" on the home page ].
	 * Labels from the Menu-list board and the footer on the boards.
	 *
	 * @var array<string, array{0: string, 1: array<int, array{0: string, 1: string}>}>
	 */
	const MENUS = array(
		'primary'         => array(
			'Main',
			array(
				array( 'Rooms', 'rooms' ),
				array( 'Villa Eudora', 'villa-eudora' ),
				array( 'A day at Amour', '#a-day-at-amour' ),
				array( 'Court and pool', 'court-and-pool' ),
				array( 'Crete guide', 'crete-guide' ),
				array( 'About us', 'about-us' ),
				array( 'Contact', 'contacts' ),
			),
		),
		'footer-stay'     => array(
			'Footer: Stay',
			array(
				array( 'Rooms', 'rooms' ),
				array( 'Villa Eudora', 'villa-eudora' ),
				array( 'Court and pool', 'court-and-pool' ),
			),
		),
		'footer-discover' => array(
			'Footer: Discover',
			array(
				array( 'Crete guide', 'crete-guide' ),
				array( 'About us', 'about-us' ),
				array( 'FAQ', 'questions' ),
			),
		),
		'legal'           => array(
			'Footer: legal links',
			array(
				array( 'Privacy', 'privacy-policy' ),
				array( 'Cookies', 'cookies' ),
				array( 'Terms', 'booking-terms' ),
			),
		),
	);

	/**
	 * Fill the site with pages, menus, languages and photos.
	 *
	 * ## OPTIONS
	 *
	 * [--media=<dir>]
	 * : Folder with the photos from assets/images; manifest.csv is read from its parent folder.
	 *
	 * ## EXAMPLES
	 *
	 *     wp amour seed --media=/var/www/html/amour-assets/images
	 *
	 * @param array $args       Positional arguments (unused).
	 * @param array $assoc_args Named arguments.
	 */
	public function __invoke( $args, $assoc_args ) {
		if ( 'production' === wp_get_environment_type() ) {
			WP_CLI::error( 'This command fills a site with structure and test content. It does not run on production.' );
		}

		$this->theme();
		$this->settings();
		$this->remove_default_content();
		$ids = $this->pages();
		$this->front_page( $ids['home'] );
		$this->languages( $ids );
		$this->menus( $ids );

		if ( ! empty( $assoc_args['media'] ) ) {
			$this->media( rtrim( $assoc_args['media'], '/' ) );
		}

		flush_rewrite_rules( false );
		WP_CLI::success( 'Site structure is in place.' );
	}

	/**
	 * Switch to the Amour theme (menu locations are stored per theme, so this goes first).
	 */
	private function theme() {
		if ( 'amour' === get_stylesheet() ) {
			return;
		}
		if ( ! wp_get_theme( 'amour' )->exists() ) {
			WP_CLI::error( 'The amour theme is not installed.' );
		}
		switch_theme( 'amour' );
		WP_CLI::log( 'Theme Amour' );
	}

	/**
	 * Basic settings: pretty addresses, site name, time zone.
	 */
	private function settings() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		update_option( 'blogname', 'Amour Relax Hotel' );
		update_option( 'blogdescription', 'A nine-room hotel in Almyrida, Crete' );
		update_option( 'timezone_string', 'Europe/Athens' );
		update_option( 'default_comment_status', 'closed' );
		update_option( 'default_ping_status', 'closed' );
	}

	/**
	 * Remove the sample post and page that come with WordPress.
	 */
	private function remove_default_content() {
		$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $hello ) {
			wp_delete_post( $hello->ID, true );
		}
		$sample = get_page_by_path( 'sample-page' );
		if ( $sample ) {
			wp_delete_post( $sample->ID, true );
		}
	}

	/**
	 * Create the pages that are missing; publish the default privacy policy draft.
	 *
	 * @return array<string, int> Page ids by slug.
	 */
	private function pages() {
		$ids = array();
		foreach ( self::PAGES as $slug => $page ) {
			list( $title, $parent ) = $page;
			$path                   = $parent ? $parent . '/' . $slug : $slug;
			$existing               = get_page_by_path( $path );

			if ( $existing ) {
				if ( 'publish' !== $existing->post_status ) {
					wp_update_post(
						array(
							'ID'          => $existing->ID,
							'post_status' => 'publish',
							'post_title'  => $title,
						)
					);
				}
				$ids[ $slug ] = $existing->ID;
				continue;
			}

			$ids[ $slug ] = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $title,
					'post_name'   => $slug,
					'post_parent' => $parent ? $ids[ $parent ] : 0,
				),
				true
			);

			if ( is_wp_error( $ids[ $slug ] ) ) {
				WP_CLI::error( 'Could not create the page ' . $path . ': ' . $ids[ $slug ]->get_error_message() );
			}
			WP_CLI::log( 'Page /' . $path . '/' );
		}
		return $ids;
	}

	/**
	 * Show the Home page at the root address.
	 *
	 * @param int $home_id Home page id.
	 */
	private function front_page( $home_id ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
		update_option( 'page_for_posts', 0 );
		update_option( 'wp_page_for_privacy_policy', (int) get_page_by_path( 'privacy-policy' )->ID );
	}

	/**
	 * Polylang: English by default at /, German at /de/ (HANDOFF, section 1).
	 * German pages other than the home page come with the content model (week 5).
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function languages( $ids ) {
		if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_set_post_language' ) ) {
			WP_CLI::warning( 'Polylang is not active: languages skipped.' );
			return;
		}

		$model = PLL()->model;
		$known = wp_list_pluck( $this->language_list( $model ), 'slug' );

		$languages = array(
			array(
				'name'       => 'English',
				'slug'       => 'en',
				'locale'     => 'en_US',
				'rtl'        => false,
				'term_group' => 0,
				'flag'       => 'gb',
			),
			array(
				'name'       => 'Deutsch',
				'slug'       => 'de',
				'locale'     => 'de_DE',
				'rtl'        => false,
				'term_group' => 1,
				'flag'       => 'de',
			),
		);

		foreach ( $languages as $language ) {
			if ( in_array( $language['slug'], $known, true ) ) {
				continue;
			}
			$result = $this->add_language( $model, $language );
			if ( is_wp_error( $result ) ) {
				WP_CLI::error( 'Polylang: ' . $result->get_error_message() );
			}
			WP_CLI::log( 'Language ' . $language['name'] );
		}

		$model->clean_languages_cache();

		// Addresses: /de/… for German, no prefix for English; the German home page lives at /de/
		// (redirect_lang: the front page address is the language code, not the page name); no redirects by browser language.
		$this->polylang_options(
			array(
				'default_lang'  => 'en',
				'force_lang'    => 1,
				'hide_default'  => true,
				'rewrite'       => true,
				'redirect_lang' => true,
				'browser'       => false,
			)
		);

		foreach ( $ids as $id ) {
			if ( ! pll_get_post_language( $id ) ) {
				pll_set_post_language( $id, 'en' );
			}
		}

		// German home page, so that /de/ answers.
		$de_home = pll_get_post( $ids['home'], 'de' );
		if ( ! $de_home ) {
			$de_home = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => 'Startseite',
					'post_name'   => 'startseite',
				)
			);
			pll_set_post_language( $de_home, 'de' );
			pll_save_post_translations(
				array(
					'en' => $ids['home'],
					'de' => $de_home,
				)
			);
			WP_CLI::log( 'Page /de/' );
		}

		// Polylang caches each language with its home page. The cache was built before the German
		// home page existed, so without this /de/ shows a post list instead of the page on a fresh site.
		$model->clean_languages_cache();
	}

	/**
	 * Polylang languages across Polylang versions (3.7+ keeps them in $model->languages).
	 *
	 * @param object $model Polylang model.
	 * @return array
	 */
	private function language_list( $model ) {
		if ( isset( $model->languages ) && is_object( $model->languages ) && method_exists( $model->languages, 'get_list' ) ) {
			return $model->languages->get_list();
		}
		return $model->get_languages_list();
	}

	/**
	 * Save Polylang settings across Polylang versions (3.7+ has an options object that saves itself).
	 *
	 * @param array<string, mixed> $settings Settings by key.
	 */
	private function polylang_options( $settings ) {
		$options = PLL()->options;
		if ( is_object( $options ) && method_exists( $options, 'set' ) ) {
			foreach ( $settings as $key => $value ) {
				$result = $options->set( $key, $value );
				if ( is_wp_error( $result ) && $result->has_errors() ) {
					WP_CLI::warning( 'Polylang option ' . $key . ': ' . $result->get_error_message() );
				}
			}
			if ( method_exists( $options, 'save' ) ) {
				$options->save();
			}
			return;
		}
		update_option( 'polylang', array_merge( (array) get_option( 'polylang', array() ), $settings ) );
	}

	/**
	 * Add a Polylang language across Polylang versions.
	 *
	 * @param object $model    Polylang model.
	 * @param array  $language Language arguments.
	 * @return true|WP_Error
	 */
	private function add_language( $model, $language ) {
		if ( isset( $model->languages ) && is_object( $model->languages ) && method_exists( $model->languages, 'add' ) ) {
			return $model->languages->add( $language );
		}
		if ( method_exists( $model, 'add_language' ) ) {
			return $model->add_language( $language );
		}
		return new WP_Error( 'amour_polylang', 'This Polylang version has no known way to add a language.' );
	}

	/**
	 * Build the menus again from the list above and assign them to the theme locations.
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function menus( $ids ) {
		$locations = get_theme_mod( 'nav_menu_locations', array() );

		foreach ( self::MENUS as $location => $menu ) {
			list( $name, $items ) = $menu;

			$existing = wp_get_nav_menu_object( $name );
			if ( $existing ) {
				wp_delete_nav_menu( $existing->term_id );
			}
			$menu_id = wp_create_nav_menu( $name );

			foreach ( $items as $position => $item ) {
				list( $label, $target ) = $item;
				$data                   = array(
					'menu-item-title'    => $label,
					'menu-item-status'   => 'publish',
					'menu-item-position' => $position + 1,
				);
				if ( str_starts_with( $target, '#' ) ) {
					$data['menu-item-type'] = 'custom';
					$data['menu-item-url']  = home_url( '/' . $target );
				} else {
					$data['menu-item-type']      = 'post_type';
					$data['menu-item-object']    = 'page';
					$data['menu-item-object-id'] = $ids[ $target ];
				}
				wp_update_nav_menu_item( $menu_id, 0, $data );
			}

			$locations[ $location ] = $menu_id;
			WP_CLI::log( 'Menu ' . $name );
		}

		set_theme_mod( 'nav_menu_locations', $locations );

		// Polylang keeps menu locations per language in its own settings; the menus above are the English ones.
		// German menus come with the German pages (week 5).
		if ( function_exists( 'PLL' ) ) {
			$nav_menus                     = (array) ( PLL()->options['nav_menus'] ?? array() );
			$nav_menus[ get_stylesheet() ] = array_map(
				static fn( $menu_id ) => array( 'en' => $menu_id ),
				$locations
			);
			$this->polylang_options( array( 'nav_menus' => $nav_menus ) );
		}
	}

	/**
	 * Import the photos (WebP and JPG) with alt text and notes from assets/manifest.csv.
	 * Logos, maps and patterns are theme files, videos come with the home page.
	 *
	 * @param string $dir Folder with the photos.
	 */
	private function media( $dir ) {
		$manifest = dirname( $dir ) . '/manifest.csv';
		if ( ! is_readable( $manifest ) ) {
			WP_CLI::warning( 'No manifest at ' . $manifest . ': photos skipped.' );
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$rows   = array_map( 'str_getcsv', file( $manifest, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) );
		$header = array_shift( $rows );
		$added  = 0;

		foreach ( $rows as $cells ) {
			if ( count( $cells ) !== count( $header ) ) {
				continue;
			}
			$row = array_combine( $header, $cells );
			if ( ! in_array( $row['type'], array( 'webp', 'jpg' ), true ) ) {
				continue;
			}

			$name = basename( $row['file'] );
			$file = $dir . '/' . $name;
			if ( ! is_readable( $file ) || $this->attachment_by_source( $name ) ) {
				continue;
			}

			$tmp = wp_tempnam( $name );
			copy( $file, $tmp );
			$id = media_handle_sideload(
				array(
					'name'     => $name,
					'tmp_name' => $tmp,
				),
				0,
				null,
				array( 'post_content' => $row['note'] )
			);
			if ( is_wp_error( $id ) ) {
				WP_CLI::warning( $name . ': ' . $id->get_error_message() );
				continue;
			}

			// The manifest can list several alts for one file (one per use); the first one is the general one.
			$alt = trim( explode( ' | ', $row['alt_en'] )[0] );
			update_post_meta( $id, '_wp_attachment_image_alt', $alt );
			update_post_meta( $id, '_amour_source', $name );
			update_post_meta( $id, '_amour_blob', $row['blob_id'] );
			++$added;
		}

		WP_CLI::log( 'Photos added: ' . $added );
	}

	/**
	 * Find a photo imported earlier from the same file.
	 *
	 * @param string $name File name.
	 * @return int Attachment id or 0.
	 */
	private function attachment_by_source( $name ) {
		$found = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_amour_source', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-off CLI seed.
				'meta_value'     => $name, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- one-off CLI seed.
			)
		);
		return $found ? (int) $found[0] : 0;
	}
}
