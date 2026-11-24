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
	 * German addresses of the pages (decision 24.11, HANDOFF section 19). Polylang without Pro cannot give two
	 * languages the same address, so the German pages have German ones; the rooms keep theirs under /de/zimmer/.
	 * The German home page is /de/ (startseite, made in languages()).
	 *
	 * @var array<string, string>
	 */
	const GERMAN = array(
		'rooms'               => 'zimmer',
		'standard-queen-room' => 'standard-queen-room',
		'deluxe-queen-room'   => 'deluxe-queen-room',
		'superior-queen-room' => 'superior-queen-room',
		'villa-eudora'        => 'ferienvilla-eudora',
		'court-and-pool'      => 'tennisplatz-und-pool',
		'crete-guide'         => 'kreta-reisefuehrer',
		'questions'           => 'fragen',
		'contacts'            => 'kontakt',
		'book'                => 'buchen',
		'about-us'            => 'ueber-uns',
		'privacy-policy'      => 'datenschutz',
		'cookies'             => 'cookie-richtlinie',
		'booking-terms'       => 'buchungsbedingungen',
		'thank-you'           => 'danke',
	);

	/**
	 * Menus: location => [ menu name, items ]. Item: [ label, page slug or "#anchor" on the home page,
	 * photo id from the boards (/_blob/<id>, see assets/manifest.csv), line under the photo ].
	 * Labels, photos and lines from the Menu-list board; footer labels from the footer on the boards.
	 *
	 * @var array<string, array{0: string, 1: array<int, array<int, string>>}>
	 */
	const MENUS = array(
		'primary'         => array(
			'Main',
			array(
				array( 'Rooms', 'rooms', 'd727d557c1b81f70ef2da9c77753cec0', 'Nine rooms, each with its own kitchen and terrace.' ),
				array( 'Villa Eudora', 'villa-eudora', '5a3bd934ace28fc571185ce354dcf308', 'Five bedrooms and its own pool, four minutes from the hotel.' ),
				array( 'A day at Amour', '#a-day-at-amour', 'deb2647ed7b0e40eb233a905c0be42ac', 'The breakfast basket, the court at sunset, evenings on the terraces.' ),
				array( 'Court and pool', 'court-and-pool', '94833f4f7f37f38c5ee6ed7578db4a52', 'Stone terraces, a pool and our own court among olive groves.' ),
				array( 'Crete guide', 'crete-guide', '6388b6418cdb641f6bd8e7b2792479ef', 'Beaches, villages and gorges, with drive times from the hotel.' ),
				array( 'About us', 'about-us', '16e92c844577adb7ca466250469281b0', 'Who looks after you in Almyrida.' ),
				array( 'Contact', 'contacts', 'eb955df83df1fb9544e16d9e70a299c1', 'Directions, phone and WhatsApp.' ),
			),
		),
		// Footer columns: the menu name is the column title on the site (boards About and Villa-v2-b).
		'footer-stay'     => array(
			'Stay',
			array(
				array( 'Rooms', 'rooms' ),
				array( 'Villa Eudora', 'villa-eudora' ),
				array( 'Court and pool', 'court-and-pool' ),
			),
		),
		'footer-discover' => array(
			'Discover',
			array(
				array( 'Crete guide', 'crete-guide' ),
				array( 'About us', 'about-us' ),
				array( 'FAQ', 'questions' ),
			),
		),
		'footer-villa'    => array(
			'The villa',
			array(
				array( 'The house', 'villa-eudora#the-house' ),
				array( 'Weddings and gatherings', 'villa-eudora#gatherings' ),
				array( 'Check dates', 'book' ),
			),
		),
		'footer-around'   => array(
			'Around',
			array(
				array( 'Court and pool at the hotel', 'court-and-pool' ),
				array( 'Crete guide', 'crete-guide' ),
				array( 'Questions', 'questions' ),
				array( 'Amour Relax Hotel', 'home' ),
			),
		),
		'legal'           => array(
			'Legal',
			array(
				array( 'Privacy', 'privacy-policy' ),
				array( 'Cookies', 'cookies' ),
				array( 'Terms', 'booking-terms' ),
			),
		),
	);

	/**
	 * Menu names used by earlier versions of this command, removed on the next run.
	 */
	const OLD_MENUS = array( 'Footer: Stay', 'Footer: Discover', 'Footer: legal links' );

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
		$ids               = $this->pages();
		$ids['styleguide'] = $this->styleguide();
		$this->front_page( $ids['home'] );
		$this->languages( $ids );
		$this->german_pages( $ids );

		// Photos first: the menu items point at them.
		if ( ! empty( $assoc_args['media'] ) ) {
			$this->media( rtrim( $assoc_args['media'], '/' ) );
		}

		$this->menus( $ids );
		$this->hotel_settings();
		$this->home_content( $ids['home'] );
		$this->cards( $ids );
		$this->rooms( $ids );
		$this->villa( $ids['villa-eudora'] ?? 0 );
		$this->estate( $ids['court-and-pool'] ?? 0 );
		$this->guide( $ids['crete-guide'] ?? 0 );
		$this->faq_contact( $ids );
		$this->about_legal_thanks( $ids );
		$this->booking( $ids );
		$this->places();
		$this->home_map( $ids );
		$this->reviews( $ids );

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
	 * The styleguide page (theme template page-styleguide.php): private, so only logged-in editors see it.
	 *
	 * @return int Page id.
	 */
	private function styleguide() {
		$existing = get_page_by_path( 'styleguide' );
		if ( $existing ) {
			return $existing->ID;
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
				'post_title'  => 'Styleguide',
				'post_name'   => 'styleguide',
			)
		);
		WP_CLI::log( 'Page /styleguide/ (private)' );
		return $id;
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
	 * Polylang: English by default at /, German at /de/ (HANDOFF, section 1). The other German pages: german_pages().
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
	 * German pages (decision 24.11): each page gets its German translation at its German address (GERMAN above),
	 * with the English title until the German texts come (week 7). A German page without its own fields shows the
	 * English ones (amour_page_field()), and a legal page without its own text the English text (page-legal.php),
	 * so nothing is copied. Never changes a German page that exists.
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function german_pages( $ids ) {
		if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_get_post' ) ) {
			return;
		}
		foreach ( self::GERMAN as $slug => $german ) {
			$id = $ids[ $slug ] ?? 0;
			if ( ! $id || pll_get_post( $id, 'de' ) ) {
				continue;
			}
			$source = get_post( $id );
			$parent = $source->post_parent ? (int) pll_get_post( $source->post_parent, 'de' ) : 0;
			$de     = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $source->post_title,
					'post_name'   => $german,
					'post_parent' => $parent,
					'menu_order'  => $source->menu_order,
				),
				true
			);
			if ( is_wp_error( $de ) ) {
				WP_CLI::warning( 'German page ' . $german . ': ' . $de->get_error_message() );
				continue;
			}
			pll_set_post_language( $de, 'de' );
			pll_save_post_translations(
				array_merge(
					pll_get_post_translations( $id ),
					array(
						'en' => $id,
						'de' => $de,
					)
				)
			);
			WP_CLI::log( 'Page ' . wp_make_link_relative( get_permalink( $de ) ) );
		}
		PLL()->model->clean_languages_cache();
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
	 * Build the menus again from the list above and assign them to the theme locations: the English menus, and the
	 * German ones when the German pages are there (the same labels until the German texts, week 7; the links lead
	 * to the German pages). Polylang keeps the menu of each location per language.
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function menus( $ids ) {
		$locations = get_theme_mod( 'nav_menu_locations', array() );

		foreach ( self::OLD_MENUS as $old ) {
			$old_menu = wp_get_nav_menu_object( $old );
			if ( $old_menu ) {
				wp_delete_nav_menu( $old_menu->term_id );
			}
		}

		$languages = array( 'en' => '' );
		if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_home_url' ) && pll_get_post( $ids['home'], 'de' ) ) {
			$languages['de'] = ' · Deutsch';
		}

		$by_language = array();
		foreach ( $languages as $lang => $suffix ) {
			$page = static fn( $slug ) => 'en' === $lang ? (int) $ids[ $slug ] : (int) pll_get_post( $ids[ $slug ], $lang );
			$home = 'en' === $lang ? home_url( '/' ) : pll_home_url( $lang );

			foreach ( self::MENUS as $location => $menu ) {
				list( $name, $items ) = $menu;
				$name                .= $suffix;

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
						$data['menu-item-url']  = $home . $target;
					} elseif ( str_contains( $target, '#' ) ) {
						// A section of another page, e.g. villa-eudora#the-house (the section ids come with the page).
						list( $slug, $anchor )  = explode( '#', $target, 2 );
						$data['menu-item-type'] = 'custom';
						$data['menu-item-url']  = get_permalink( $page( $slug ) ) . '#' . $anchor;
					} else {
						if ( ! $page( $target ) ) {
							continue;
						}
						$data['menu-item-type']      = 'post_type';
						$data['menu-item-object']    = 'page';
						$data['menu-item-object-id'] = $page( $target );
					}
					$item_id = wp_update_nav_menu_item( $menu_id, 0, $data );

					// WordPress drops a label equal to the page's title and then shows whatever the page is called;
					// the pages get their full names further down (Cookies becomes Cookie policy). Keep the board's label.
					if ( 'post_type' === $data['menu-item-type'] && ! is_wp_error( $item_id ) ) {
						wp_update_post(
							array(
								'ID'         => $item_id,
								'post_title' => $label,
							)
						);
					}

					// Photo in the arch and the line under it (Menu-list board).
					if ( isset( $item[3] ) && function_exists( 'update_field' ) && ! is_wp_error( $item_id ) ) {
						$photo = $this->attachment_by_blob( $item[2] );
						if ( $photo ) {
							update_field( 'field_amour_menu_photo', $photo, $item_id );
						}
						update_field( 'field_amour_menu_line', $item[3], $item_id );
					}
				}

				$by_language[ $location ][ $lang ] = $menu_id;
				WP_CLI::log( 'Menu ' . $name );
			}
		}

		set_theme_mod( 'nav_menu_locations', array_merge( $locations, array_map( static fn( $menus ) => $menus['en'], $by_language ) ) );

		if ( function_exists( 'PLL' ) ) {
			$nav_menus                     = (array) ( PLL()->options['nav_menus'] ?? array() );
			$nav_menus[ get_stylesheet() ] = $by_language;
			$this->polylang_options( array( 'nav_menus' => $nav_menus ) );
		}
	}

	/**
	 * Import the photos (WebP and JPG) and videos (MP4) with alt text and notes from assets/manifest.csv.
	 * Logos, maps and patterns are theme files.
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
			if ( ! in_array( $row['type'], array( 'webp', 'jpg', 'mp4' ), true ) ) {
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
		return $this->attachment_by_meta( '_amour_source', $name );
	}

	/**
	 * Find a photo by its id on the boards (/_blob/<id>).
	 *
	 * @param string $blob Board id.
	 * @return int Attachment id or 0.
	 */
	private function attachment_by_blob( $blob ) {
		return $this->attachment_by_meta( '_amour_blob', $blob );
	}

	/**
	 * "Hotel" settings page: only what is already public — the social links of the old site and the Booking.com
	 * score (docs/09, section 2), the place name and the villa score from the boards. Phone, WhatsApp, email,
	 * company details and licences stay empty: they are filled in at the content stage. Never overwrites a value.
	 */
	private function hotel_settings() {
		if ( ! function_exists( 'update_field' ) ) {
			return;
		}

		$simple = array(
			'field_amour_place'               => array( 'amour_place', 'Almyrida, Crete' ),
			'field_amour_booking_score'       => array( 'amour_booking_score', '9.1' ),
			'field_amour_villa_booking_score' => array( 'amour_villa_booking_score', '9.5' ),
		);
		foreach ( $simple as $key => $field ) {
			if ( ! get_field( $field[0], 'option' ) ) {
				update_field( $key, $field[1], 'option' );
			}
		}

		if ( get_field( 'amour_social', 'option' ) ) {
			return;
		}
		update_field(
			'field_amour_social',
			array(
				array(
					'field_amour_social_label'   => 'Instagram',
					'field_amour_social_url'     => 'https://www.instagram.com/amourrelax/',
					'field_amour_social_in_menu' => 1,
				),
				array(
					'field_amour_social_label'   => 'Facebook',
					'field_amour_social_url'     => 'https://m.facebook.com/people/Amour-Relax-Hotel/61561312883178/',
					'field_amour_social_in_menu' => 1,
				),
				array(
					'field_amour_social_label'   => 'TikTok',
					'field_amour_social_url'     => 'https://www.tiktok.com/@amourrelax',
					'field_amour_social_in_menu' => 0,
				),
			),
			'option'
		);
		WP_CLI::log( 'Hotel settings: social links' );
	}

	/**
	 * Home page fields with the texts and pictures of the boards Main-v2 and Main-v2-b.
	 * Fills only empty fields, so edits in the admin survive a new run.
	 *
	 * @param int $home_id Home page id.
	 */
	private function home_content( $home_id ) {
		if ( ! function_exists( 'update_field' ) ) {
			return;
		}
		$values = array(
			'field_amour_home_hero_line'        => 'A nine-room hotel with its own court, among olive groves above Almyrida bay, Crete.',
			'field_amour_home_hero_note'        => 'Open from March to October.',
			'field_amour_home_hero_video_label' => 'Drone view of Amour Relax Hotel among olive groves above Almyrida bay',
			'field_amour_home_hero_poster'      => $this->attachment_by_blob( 'e9a2a1196453b8875bd0d496802f1fb9' ),
			'field_amour_home_hero_video'       => $this->attachment_by_blob( 'b0bf041382d31b29afb9ccc9eb79bcef' ),
			'field_amour_home_phrase'           => 'Nine rooms among olive groves. Your own kitchen, your own terrace, your own slow morning.',
			'field_amour_home_estate_label'     => 'The estate',
			'field_amour_home_estate_title'     => 'Stone terraces among olive groves',
			'field_amour_home_estate_photo'     => $this->attachment_by_blob( 'c7c14ed99aa8d9c4132d959c37050de9' ),
			'field_amour_home_day_title'        => 'A day at Amour',
			'field_amour_home_day_frames'       => $this->day_frames(),
			'field_amour_home_rooms_title'      => "Stay like\nyou live here",
			'field_amour_home_villa_words'      => "Room for\neveryone",
			'field_amour_home_villa_captions'   => "The villa next door\n4 minutes from the hotel",
			'field_amour_home_villa_title'      => "Five bedrooms,\none long table",
			'field_amour_home_villa_meta'       => 'Up to 11 guests · own pool · 256 m²',
			'field_amour_home_villa_button'     => 'Discover the villa',
			'field_amour_home_villa_link'       => 'Weddings and gatherings',
			'field_amour_home_reviews_title'    => 'Guest reviews',
			'field_amour_home_direct_label'     => 'Book direct',
			'field_amour_home_direct_title'     => 'Same price, straight to us',
			'field_amour_home_direct_text'      => 'The same price as on Booking.com, with the hosts a message away.',
			'field_amour_home_direct_link'      => array(
				'title'  => 'Check dates',
				'url'    => '#book',
				'target' => '',
			),
			'field_amour_home_direct_photo'     => $this->attachment_by_blob( '16e92c844577adb7ca466250469281b0' ),
		);
		foreach ( $values as $key => $value ) {
			$field = acf_get_field( $key );
			if ( $value && $field && ! get_field( $field['name'], $home_id, false ) ) {
				update_field( $key, $value, $home_id );
			}
		}
		WP_CLI::log( 'Home page: texts and pictures' );
	}

	/**
	 * A day at Amour: the five frames of the board Main-v2 (photo, time of day, line, focus).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function day_frames() {
		$frames = array(
			array( '5cb390922657fafc5eb5043328074546', 'Morning', 'A basket from the village bakery at your door, coffee on your own terrace.', '42% 50%' ),
			array( '16175242e07136a77226dd8cb574b487', 'Midday', 'Then the island: Seitan Limania, Arkadi, the White Mountains.', '50% 50%' ),
			array( 'deb2647ed7b0e40eb233a905c0be42ac', 'Golden hour', 'Back when the light turns gold. The court is yours.', '50% 45%' ),
			array( '3d4fe371301d6eb1243e6659e55b94e1', 'Sunset', 'The sky turns pink over the bay.', '50% 50%' ),
			array( '3e03be045ef749e0d13cdb0ef58aa776', 'Evening', 'The stone wall glows by the pool, and the wine is Cretan.', '50% 60%' ),
		);
		$rows   = array();
		foreach ( $frames as $frame ) {
			$photo = $this->attachment_by_blob( $frame[0] );
			if ( ! $photo ) {
				continue;
			}
			$rows[] = array(
				'field_amour_home_day_photo' => $photo,
				'field_amour_home_day_label' => $frame[1],
				'field_amour_home_day_line'  => $frame[2],
				'field_amour_home_day_focus' => $frame[3],
			);
		}
		return $rows;
	}

	/**
	 * Cards of the rooms and the villa, as on the board Main-v2 (rooms grid). Prices stay empty:
	 * they are filled in at the content stage, the site shows "from €[price]" until then.
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function cards( $ids ) {
		if ( ! function_exists( 'update_field' ) ) {
			return;
		}
		// The card fields attach to the pages under /rooms/ and the villa page. On a fresh site those pages are
		// created by this very run, after the fields were set up, so set them up again now that the pages exist.
		if ( function_exists( 'amour_register_card_fields' ) ) {
			amour_register_card_fields();
		}
		$fact  = static fn( $icon, $text ) => array(
			'field_amour_card_fact_icon' => $icon,
			'field_amour_card_fact_text' => $text,
		);
		$cards = array(
			'standard-queen-room' => array( 'Standard', array( $fact( 'area', '20 m²' ), $fact( 'guests', '2' ) ), '80e77fe1010b549279e89e9a35d5b698', '2b59c90b2750bdb3ac2476355da8d974' ),
			'deluxe-queen-room'   => array( 'Deluxe', array( $fact( 'area', '32 m²' ), $fact( 'water', 'Pool view' ) ), 'fd095be6630808380801bf1a11f4b2b1', 'd727d557c1b81f70ef2da9c77753cec0' ),
			'superior-queen-room' => array( 'Superior', array( $fact( 'water', 'Sea view' ), $fact( 'guests', '2' ) ), 'c52dc0b422d93e5648121425daaa19a4', '0b0dc5985e0129c7969b7aad231dd75f' ),
			'villa-eudora'        => array( 'Villa Eudora', array( $fact( 'bed', '5' ), $fact( 'guests', '11' ), $fact( 'water', 'Own pool' ) ), '8c07121482921ffe3be6f861ce5e82fe', '2aac80822b69d3836096e30d8c7de6da' ),
		);
		// Order of the rooms in lists and on the home page: Standard, Deluxe, Superior (Page attributes → Order).
		$order = 0;
		foreach ( array_keys( $cards ) as $slug ) {
			if ( ! empty( $ids[ $slug ] ) && 'villa-eudora' !== $slug && 0 === (int) get_post_field( 'menu_order', $ids[ $slug ] ) ) {
				wp_update_post(
					array(
						'ID'         => $ids[ $slug ],
						'menu_order' => ++$order,
					)
				);
			}
		}

		foreach ( $cards as $slug => $card ) {
			$id = $ids[ $slug ] ?? 0;
			if ( ! $id || get_field( 'card_name', $id, false ) ) {
				continue;
			}
			update_field( 'field_amour_card_name', $card[0], $id );
			update_field( 'field_amour_card_facts', $card[1], $id );
			update_field( 'field_amour_card_photo', $this->attachment_by_blob( $card[2] ), $id );
			update_field( 'field_amour_card_photo_hover', $this->attachment_by_blob( $card[3] ), $id );
		}
		WP_CLI::log( 'Cards: rooms and villa' );
	}

	/**
	 * Reviews: the five placeholders of the board Main-v2-b, in square brackets, until the real ones from
	 * Booking.com are copied in word for word. Never made up (CLAUDE.md, HANDOFF section 19).
	 * Runs only when there are no reviews yet.
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function reviews( $ids ) {
		if ( ! function_exists( 'update_field' ) || ! post_type_exists( 'amour_review' ) ) {
			return;
		}
		$any = get_posts(
			array(
				'post_type'      => 'amour_review',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( $any ) {
			return;
		}
		$reviews = array(
			array( 'deluxe-queen-room', '[A real review from Booking.com, word for word: about the court and an early game before breakfast.]' ),
			array( 'superior-queen-room', '[A real review, word for word: about the kitchen, the terrace and slow mornings.]' ),
			array( 'standard-queen-room', '[A real review, word for word: about the hosts and their tips for the area.]' ),
			array( 'deluxe-queen-room', '[A real review, word for word: about the walk down to Almyrida beach and the tavernas.]' ),
			array( 'villa-eudora', '[A real review from a family or group at Villa Eudora, word for word.]' ),
		);
		foreach ( $reviews as $i => $review ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'amour_review',
					'post_status' => 'publish',
					'post_title'  => '[Name]',
					'menu_order'  => $i + 1,
				)
			);
			update_field( 'field_amour_review_text', $review[1], $id );
			update_field( 'field_amour_review_room', $ids[ $review[0] ] ?? 0, $id );
			update_field( 'field_amour_review_home', 1, $id );
		}
		WP_CLI::log( 'Reviews: placeholders from the board' );
	}

	/**
	 * Room pages (data/rooms.json, from the boards Room-standard, Room-deluxe and Room-superior): label, line,
	 * photos with captions, description and details; and in Hotel settings the rules for every room and the
	 * location card of the room map.
	 * Fills only empty pages, so edits in the admin survive a new run.
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function rooms( $ids ) {
		if ( ! function_exists( 'update_field' ) ) {
			return;
		}
		if ( function_exists( 'amour_register_room_fields' ) ) {
			amour_register_room_fields(); // the room pages may have been created by this very run
		}
		$data = json_decode( (string) file_get_contents( AMOUR_CORE_DIR . '/data/rooms.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.

		if ( ! get_field( 'amour_location_title', 'option' ) ) {
			update_field( 'field_amour_location_title', 'Almyrida, Crete', 'option' );
			update_field( 'field_amour_location_text', 'Above the bay of Almyrida in Apokoronas, between Chania and Rethymno.', 'option' );
			update_field(
				'field_amour_location_near',
				array_map(
					static fn( $near ) => array(
						'field_amour_location_near_place' => $near[0],
						'field_amour_location_near_value' => $near[1],
					),
					array( array( 'Almyrida beach', '1.2 km on foot' ), array( 'Chania old town', '40 min' ), array( 'Rethymno', '55 min' ), array( 'Chania airport', '[… min]' ) )
				),
				'option'
			);
		}

		if ( ! get_field( 'amour_checkin', 'option' ) ) {
			update_field( 'field_amour_checkin', '14:00', 'option' );
			update_field( 'field_amour_checkout', '11:00', 'option' );
			update_field(
				'field_amour_stay_rules',
				array_map(
					static fn( $rule ) => array(
						'field_amour_stay_rule_label' => $rule['label'],
						'field_amour_stay_rule_value' => $rule['value'],
					),
					$data['stay']
				),
				'option'
			);
		}

		foreach ( $data['rooms'] as $slug => $room ) {
			$id = $ids[ $slug ] ?? 0;
			if ( $id && ! get_field( 'list_name', $id, false ) ) {
				update_field( 'field_amour_room_list_name', $room['list']['name'], $id );
				update_field( 'field_amour_room_list_text', $room['list']['text'], $id );
				update_field(
					'field_amour_room_list_facts',
					array_map(
						static fn( $fact ) => array(
							'field_amour_room_list_fact_label' => $fact['label'],
							'field_amour_room_list_fact_value' => $fact['value'],
						),
						$room['list']['facts']
					),
					$id
				);
			}
			if ( ! $id || get_field( 'room_kicker', $id, false ) ) {
				continue;
			}
			update_field( 'field_amour_room_kicker', $room['kicker'], $id );
			update_field( 'field_amour_room_meta', $room['meta'], $id );
			update_field( 'field_amour_room_cue', 'Scroll to see the room', $id );
			update_field( 'field_amour_room_details_title', 'Room details', $id );
			update_field( 'field_amour_room_desc_label', 'The room', $id );
			update_field( 'field_amour_room_description', $room['description'], $id );
			$photos = array();
			foreach ( $room['photos'] as $photo ) {
				$photo_id = $this->attachment_by_blob( $photo['blob'] );
				if ( $photo_id ) {
					$photos[] = array(
						'field_amour_room_photo'         => $photo_id,
						'field_amour_room_photo_caption' => $photo['caption'],
						'field_amour_room_photo_shape'   => $photo['shape'],
					);
				}
			}
			update_field( 'field_amour_room_gallery', $photos, $id );
			update_field(
				'field_amour_room_facts',
				array_map(
					static fn( $fact ) => array(
						'field_amour_room_fact_label' => $fact['label'],
						'field_amour_room_fact_value' => $fact['value'],
					),
					$room['facts']
				),
				$id
			);
		}
		$this->rooms_page( $ids['rooms'] ?? 0, $data['page'] );
		WP_CLI::log( 'Rooms: pages and stay rules' );
	}

	/**
	 * The Rooms page (board Rooms): opening, In every room, families and groups, Good to know. Only when empty.
	 *
	 * @param int                  $id   Rooms page id.
	 * @param array<string, mixed> $page Texts from data/rooms.json.
	 */
	private function rooms_page( $id, $page ) {
		if ( ! $id || get_field( 'rooms_title', $id, false ) ) {
			return;
		}
		if ( function_exists( 'amour_register_rooms_page_fields' ) ) {
			amour_register_rooms_page_fields();
		}
		$pairs  = static fn( $rows, $key ) => array_map(
			static fn( $row ) => array(
				$key . '_label' => $row['label'],
				$key . '_value' => $row['value'],
			),
			$rows
		);
		$values = array(
			'field_amour_rooms_title'          => $page['title'],
			'field_amour_rooms_text'           => $page['text'],
			'field_amour_rooms_every_title'    => $page['every_title'],
			'field_amour_rooms_every'          => array_map( static fn( $line ) => array( 'field_amour_rooms_every_text' => $line ), $page['every'] ),
			'field_amour_rooms_family_title'   => $page['family']['title'],
			'field_amour_rooms_family_text'    => $page['family']['text'],
			'field_amour_rooms_family_facts'   => $pairs( $page['family']['facts'], 'field_amour_rooms_family_fact' ),
			'field_amour_rooms_family_bg'      => $this->attachment_by_blob( $page['family']['bg'] ),
			'field_amour_rooms_family_photo'   => $this->attachment_by_blob( $page['family']['photo'] ),
			'field_amour_rooms_family_caption' => $page['family']['caption'],
			'field_amour_rooms_know_title'     => $page['know_title'],
			'field_amour_rooms_know'           => array_map(
				static fn( $note ) => array(
					'field_amour_rooms_know_label'  => $note['label'],
					'field_amour_rooms_know_figure' => $note['figure'],
					'field_amour_rooms_know_text'   => $note['text'],
				),
				$page['know']
			),
		);
		foreach ( $values as $key => $value ) {
			update_field( $key, $value, $id );
		}
	}

	/**
	 * The villa page (data/villa.json, from design/generators/gen_villa.py and the boards Villa-v2, Villa-v2-b):
	 * the room fields (first screen, photos, the house, details with the villa's own stay rules) and the villa's
	 * own (bedrooms, floor by floor, weddings and gatherings, the map card). Only when the page is empty.
	 * The bedroom photos are the board's stand-ins from the hotel rooms, until the villa bedrooms are shot.
	 *
	 * @param int $id Villa page id.
	 */
	private function villa( $id ) {
		if ( ! $id || ! function_exists( 'update_field' ) || get_field( 'villa_beds_title', $id, false ) ) {
			return;
		}
		if ( function_exists( 'amour_register_villa_fields' ) ) {
			amour_register_villa_fields();
		}
		$v      = json_decode( (string) file_get_contents( AMOUR_CORE_DIR . '/data/villa.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
		$rows   = static fn( $items, $map ) => array_map(
			static function ( $item ) use ( $map ) {
				$row = array();
				foreach ( $map as $key => $from ) {
					$row[ $key ] = is_callable( $from ) ? $from( $item ) : $item[ $from ];
				}
				return $row;
			},
			$items
		);
		$blob   = fn( $item ) => $this->attachment_by_blob( $item['blob'] );
		$values = array(
			'field_amour_room_name'            => 'Eudora',
			'field_amour_room_kicker'          => $v['kicker'],
			'field_amour_room_meta'            => $v['meta'],
			'field_amour_room_cue'             => $v['cue'],
			'field_amour_room_details_title'   => $v['details_title'],
			'field_amour_room_desc_label'      => $v['desc_label'],
			'field_amour_room_description'     => $v['description'],
			'field_amour_room_gallery'         => $rows(
				$v['photos'],
				array(
					'field_amour_room_photo'         => $blob,
					'field_amour_room_photo_caption' => 'caption',
					'field_amour_room_photo_shape'   => 'shape',
				)
			),
			'field_amour_room_facts'           => $rows(
				$v['facts'],
				array(
					'field_amour_room_fact_label' => 'label',
					'field_amour_room_fact_value' => 'value',
				)
			),
			'field_amour_villa_beds_title'     => $v['beds_title'],
			'field_amour_villa_beds_text'      => $v['beds_text'],
			'field_amour_villa_bedrooms'       => $rows(
				$v['bedrooms'],
				array(
					'field_amour_villa_bedroom_name'  => 'name',
					'field_amour_villa_bedroom_text'  => 'text',
					'field_amour_villa_bedroom_photo' => $blob,
				)
			),
			'field_amour_villa_floors_label'   => $v['floors_label'],
			'field_amour_villa_floors_title'   => $v['floors_title'],
			'field_amour_villa_floors'         => $rows(
				$v['floors'],
				array(
					'field_amour_villa_floor_name' => 'name',
					'field_amour_villa_floor_text' => 'text',
				)
			),
			'field_amour_villa_floors_caption' => $v['floors_caption'],
			'field_amour_villa_events_label'   => $v['events_label'],
			'field_amour_villa_events_title'   => $v['events_title'],
			'field_amour_villa_events_text'    => $v['events_text'],
			'field_amour_villa_events_button'  => array(
				'title'  => 'Plan a gathering',
				'url'    => get_permalink( get_page_by_path( 'contacts' ) ),
				'target' => '',
			),
			'field_amour_villa_events_bg'      => $this->attachment_by_blob( $v['events_bg'] ),
			'field_amour_villa_events_photo'   => $this->attachment_by_blob( $v['events_photo'] ),
			'field_amour_villa_events_caption' => $v['events_caption'],
			'field_amour_villa_near'           => $rows(
				$v['near'],
				array(
					'field_amour_villa_near_place' => 'place',
					'field_amour_villa_near_value' => 'value',
				)
			),
			'field_amour_villa_others_title'   => $v['others_title'],
		);
		foreach ( $values as $key => $value ) {
			update_field( $key, $value, $id );
		}
		WP_CLI::log( 'Villa: page' );
	}

	/**
	 * The Court and pool page (data/estate.json, from the board Estate). Only when the page is empty.
	 *
	 * @param int $id Court and pool page id.
	 */
	private function estate( $id ) {
		if ( ! $id || ! function_exists( 'update_field' ) || get_field( 'estate_title', $id, false ) ) {
			return;
		}
		if ( function_exists( 'amour_register_estate_fields' ) ) {
			amour_register_estate_fields();
		}
		$e     = json_decode( (string) file_get_contents( AMOUR_CORE_DIR . '/data/estate.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
		$photo = fn( $blob ) => $blob ? $this->attachment_by_blob( $blob ) : 0;
		$rows  = fn( $key, $items ) => array_map(
			static function ( $item ) use ( $key, $photo ) {
				$row = array();
				foreach ( $item as $name => $value ) {
					$row[ 'field_amour_estate_' . $key . '_' . $name ] = 'photo' === $name ? $photo( $value ) : $value;
				}
				return $row;
			},
			$items
		);
		foreach ( $e as $name => $value ) {
			if ( is_array( $value ) ) {
				$value = $rows( $name, $value );
			} elseif ( in_array( $name, array( 'video', 'poster', 'court_day', 'court_night', 'plan_photo' ), true ) ) {
				$value = $photo( $value );
			}
			update_field( 'field_amour_estate_' . $name, $value, $id );
		}
		WP_CLI::log( 'Court and pool: page' );
	}

	/**
	 * The Crete guide (data/guide.json, from the board Guide). Only when the page is empty.
	 *
	 * @param int $id Guide page id.
	 */
	private function guide( $id ) {
		if ( ! $id || ! function_exists( 'update_field' ) || get_field( 'guide_title', $id, false ) ) {
			return;
		}
		if ( function_exists( 'amour_register_guide_fields' ) ) {
			amour_register_guide_fields();
		}
		$g = json_decode( (string) file_get_contents( AMOUR_CORE_DIR . '/data/guide.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
		foreach ( $g as $name => $value ) {
			if ( is_array( $value ) ) {
				$value = array_map(
					static fn( $pair ) => array(
						'field_amour_guide_' . $name . '_a' => $pair[0],
						'field_amour_guide_' . $name . '_b' => $pair[1],
					),
					$value
				);
			} elseif ( str_ends_with( $name, '_photo' ) ) {
				$value = $this->attachment_by_blob( $value );
			}
			update_field( 'field_amour_guide_' . $name, $value, $id );
		}
		WP_CLI::log( 'Crete guide: page' );
	}

	/**
	 * The FAQ (topics and questions from the board FAQ) and the texts of the FAQ and Contact pages
	 * (data/pages.json). Questions only when there are none; page texts only when empty.
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function faq_contact( $ids ) {
		if ( ! function_exists( 'update_field' ) ) {
			return;
		}
		if ( function_exists( 'amour_register_page_fields' ) ) {
			amour_register_page_fields();
		}
		$data = json_decode( (string) file_get_contents( AMOUR_CORE_DIR . '/data/pages.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.

		$any = get_posts(
			array(
				'post_type'      => 'amour_question',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( ! $any && taxonomy_exists( 'amour_topic' ) ) {
			foreach ( $data['faq']['topics'] as $t => $topic ) {
				$term = term_exists( $topic['slug'], 'amour_topic' );
				$term = $term ? $term : wp_insert_term( $topic['name'], 'amour_topic', array( 'slug' => $topic['slug'] ) );
				if ( is_wp_error( $term ) ) {
					continue;
				}
				$term_id = (int) $term['term_id'];
				update_field( 'field_amour_topic_order', $t + 1, 'amour_topic_' . $term_id );
				if ( function_exists( 'pll_set_term_language' ) ) {
					pll_set_term_language( $term_id, 'en' );
				}
				foreach ( $topic['questions'] as $q => $question ) {
					$id = wp_insert_post(
						array(
							'post_type'   => 'amour_question',
							'post_status' => 'publish',
							'post_title'  => $question['q'],
							'menu_order'  => $q + 1,
						)
					);
					wp_set_object_terms( $id, $term_id, 'amour_topic' );
					update_field( 'field_amour_question_answer', $question['a'], $id );
					if ( function_exists( 'pll_set_post_language' ) ) {
						pll_set_post_language( $id, 'en' );
					}
				}
			}
			WP_CLI::log( 'Questions: from the board' );
		}

		$faq = $ids['questions'] ?? 0;
		if ( $faq && ! get_field( 'faq_title', $faq, false ) ) {
			foreach ( array( 'title', 'text', 'note', 'cta' ) as $key ) {
				update_field( 'field_amour_faq_' . $key, $data['faq'][ $key ], $faq );
			}
		}

		$contact = $ids['contacts'] ?? 0;
		$c       = $data['contact'];
		if ( $contact && ! get_field( 'ct_title', $contact, false ) ) {
			foreach ( array( 'title', 'hosts_line', 'text', 'routes_title', 'form_title', 'form_text' ) as $key ) {
				update_field( 'field_amour_ct_' . $key, $c[ $key ], $contact );
			}
			update_field(
				'field_amour_ct_routes',
				array_map(
					static fn( $r ) => array(
						'field_amour_ct_route_by'   => $r['by'],
						'field_amour_ct_route_from' => $r['from'],
						'field_amour_ct_route_text' => $r['text'],
					),
					$c['routes']
				),
				$contact
			);
		}
		WP_CLI::log( 'FAQ and Contact: pages' );
	}

	/**
	 * About, the three legal pages (the drafts of the board Legal, as headings and paragraphs in the editor, the
	 * cookie list as a table), the thank-you page and the photo of the error page. Only what is still empty.
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function about_legal_thanks( $ids ) {
		if ( ! function_exists( 'update_field' ) ) {
			return;
		}
		if ( function_exists( 'amour_register_page_fields' ) ) {
			amour_register_page_fields();
		}
		$data = json_decode( (string) file_get_contents( AMOUR_CORE_DIR . '/data/pages.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.

		$about = $ids['about-us'] ?? 0;
		$a     = $data['about'];
		if ( $about && ! get_field( 'ab_title', $about, false ) ) {
			foreach ( array( 'title', 'text', 'label', 'phrase', 'cta' ) as $key ) {
				update_field( 'field_amour_ab_' . $key, $a[ $key ], $about );
			}
			update_field( 'field_amour_ab_photo', $this->attachment_by_blob( $a['photo'] ), $about );
			update_field(
				'field_amour_ab_gallery',
				array_map(
					fn( $g ) => array(
						'field_amour_ab_gallery_photo'   => $this->attachment_by_blob( $g[0] ),
						'field_amour_ab_gallery_caption' => $g[1],
					),
					$a['gallery']
				),
				$about
			);
		}

		$docs  = json_decode( (string) file_get_contents( AMOUR_CORE_DIR . '/data/legal.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
		$pages = array(
			'privacy' => 'privacy-policy',
			'cookies' => 'cookies',
			'terms'   => 'booking-terms',
		);
		foreach ( $pages as $key => $slug ) {
			$id = $ids[ $slug ] ?? 0;
			if ( ! $id || get_field( 'lg_lead', $id, false ) ) {
				continue;
			}
			$blocks = '';
			foreach ( $docs[ $key ]['sections'] as $section ) {
				$blocks .= sprintf( "<!-- wp:heading {\"anchor\":\"%1\$s\"} -->\n<h2 class=\"wp-block-heading\" id=\"%1\$s\">%2\$s</h2>\n<!-- /wp:heading -->\n\n", esc_attr( $section['id'] ), esc_html( $section['h'] ) );
				foreach ( $section['ps'] as $paragraph ) {
					$blocks .= sprintf( "<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->\n\n", esc_html( $paragraph['t'] ) );
				}
				if ( $section['rows'] ) {
					$head    = array_shift( $section['rows'] );
					$cell    = static fn( $row, $tag ) => '<tr>' . implode( '', array_map( static fn( $k ) => "<{$tag}>" . esc_html( $row[ $k ] ) . "</{$tag}>", array( 'n', 'p', 'u', 'd' ) ) ) . '</tr>';
					$body    = implode( '', array_map( static fn( $row ) => $cell( $row, 'td' ), $section['rows'] ) );
					$blocks .= "<!-- wp:table -->\n<figure class=\"wp-block-table\"><table><thead>" . $cell( $head, 'th' ) . '</thead><tbody>' . $body . "</tbody></table></figure>\n<!-- /wp:table -->\n\n";
				}
			}
			wp_update_post(
				array(
					'ID'           => $id,
					'post_title'   => $docs[ $key ]['title'],
					'post_content' => trim( $blocks ),
				)
			);
			update_field( 'field_amour_lg_lead', $docs[ $key ]['lead'], $id );
		}

		$thanks = $ids['thank-you'] ?? 0;
		$t      = $data['thanks'];
		if ( $thanks && ! get_field( 'th_label', $thanks, false ) ) {
			foreach ( array( 'label', 'text', 'next_title' ) as $key ) {
				update_field( 'field_amour_th_' . $key, $t[ $key ], $thanks );
			}
			update_field( 'field_amour_th_photo', $this->attachment_by_blob( $t['photo'] ), $thanks );
			update_field(
				'field_amour_th_next',
				array_map(
					static fn( $n ) => array(
						'field_amour_th_step_title' => $n[0],
						'field_amour_th_step_text'  => $n[1],
					),
					$t['next']
				),
				$thanks
			);
		}

		if ( ! get_field( 'amour_404_photo', 'option' ) ) {
			update_field( 'field_amour_404_photo', $this->attachment_by_blob( $data['error_photo'] ), 'option' );
		}
		WP_CLI::log( 'About, legal pages, thank-you: pages' );
	}

	/**
	 * The booking page (board Book): its two lines, how each room and the villa shows in the search, and the season
	 * the board sells (first night 1 March, last 31 October 2027). The cancellation days, the climate fee, the prices
	 * and the engine address stay empty: they are placeholders until the engine and the price list are in.
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function booking( $ids ) {
		if ( ! function_exists( 'update_field' ) ) {
			return;
		}
		// The fields attach to pages this very run may have created (see cards()).
		if ( function_exists( 'amour_register_booking_fields' ) ) {
			amour_register_booking_fields();
		}
		$data = json_decode( (string) file_get_contents( AMOUR_CORE_DIR . '/data/pages.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
		$book = $data['book'];

		$page = $ids['book'] ?? 0;
		if ( $page && ! get_field( 'bk_lead', $page, false ) ) {
			update_field( 'field_amour_bk_lead', $book['lead'], $page );
			update_field( 'field_amour_bk_hint', $book['hint'], $page );
		}

		if ( ! get_field( 'amour_season_first', 'option', false ) ) {
			update_field( 'field_amour_season_first', $book['season'][0], 'option' );
			update_field( 'field_amour_season_last', $book['season'][1], 'option' );
		}

		foreach ( $book['units'] as $slug => $unit ) {
			$id = $ids[ $slug ] ?? 0;
			if ( ! $id || get_field( 'max_guests', $id, false ) ) {
				continue;
			}
			update_field( 'field_amour_max_guests', $unit['guests'], $id );
			update_field( 'field_amour_book_facts', $unit['facts'], $id );
			update_field( 'field_amour_book_text', $unit['text'], $id );
			update_field( 'field_amour_book_photo', $this->attachment_by_blob( $unit['photo'] ), $id );
		}
		WP_CLI::log( 'Booking: page, season, rooms and villa in the search' );
	}

	/**
	 * Places of the home map and the guide (data/home-map.json, taken from the board Main-v2-b):
	 * the hotel, the villa, six day trips with their roads, and the far label Heraklion.
	 * Adds the places that are missing; never changes a place that exists.
	 */
	private function places() {
		if ( ! function_exists( 'update_field' ) || ! post_type_exists( 'amour_place' ) ) {
			return;
		}
		if ( function_exists( 'amour_register_place_fields' ) ) {
			amour_register_place_fields();
		}
		$data  = json_decode( (string) file_get_contents( AMOUR_CORE_DIR . '/data/home-map.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
		$all   = array_merge(
			array_map( static fn( $p ) => $p + array( 'kind' => 'trip' ), $data['places'] ),
			$data['special']
		);
		$added = 0;
		foreach ( $all as $place ) {
			$existing = get_posts(
				array(
					'post_type'      => 'amour_place',
					'title'          => $place['name'],
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);
			if ( $existing ) {
				// Places from before the room map: mark the ones it shows, once.
				if ( ! empty( $place['on_rooms'] ) && '' === get_post_meta( $existing[0], 'on_rooms', true ) ) {
					update_field( 'field_amour_place_rooms', 1, $existing[0] );
				}
				if ( ! empty( $place['note'] ) && '' === (string) get_post_meta( $existing[0], 'note', true ) ) {
					update_field( 'field_amour_place_note', $place['note'], $existing[0] );
					update_field( 'field_amour_place_guide_dir', $place['guide_dir'] ?? '', $existing[0] );
					update_field( 'field_amour_place_guide_dir_phone', $place['guide_dir_phone'] ?? '', $existing[0] );
				}
				if ( ! empty( $place['on_villa'] ) && '' === get_post_meta( $existing[0], 'on_villa', true ) ) {
					update_field( 'field_amour_place_villa', 1, $existing[0] );
					update_field( 'field_amour_place_villa_label', $place['villa_label'] ?? '', $existing[0] );
				}
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'   => 'amour_place',
					'post_status' => 'publish',
					'post_title'  => $place['name'],
					'menu_order'  => (int) $place['order'],
				)
			);
			if ( function_exists( 'pll_set_post_language' ) ) {
				pll_set_post_language( $id, 'en' );
			}
			$values = array(
				'field_amour_place_kind'            => $place['kind'],
				'field_amour_place_label'           => $place['label'],
				'field_amour_place_short'           => $place['short'],
				'field_amour_place_x'               => $place['x'],
				'field_amour_place_y'               => $place['y'],
				'field_amour_place_dir'             => $place['dir'],
				'field_amour_place_dir_phone'       => $place['dir_phone'] ?? $place['dir'],
				'field_amour_place_meta'            => $place['meta'],
				'field_amour_place_text'            => $place['text'],
				'field_amour_place_note'            => $place['note'] ?? '',
				'field_amour_place_guide_dir'       => $place['guide_dir'] ?? '',
				'field_amour_place_guide_dir_phone' => $place['guide_dir_phone'] ?? '',
				'field_amour_place_route'           => $place['route'] ?? '',
				'field_amour_place_home'            => isset( $place['on_home'] ) ? (int) $place['on_home'] : 1,
				'field_amour_place_rooms'           => empty( $place['on_rooms'] ) ? 0 : 1,
				'field_amour_place_villa'           => empty( $place['on_villa'] ) ? 0 : 1,
				'field_amour_place_villa_label'     => $place['villa_label'] ?? '',
			);
			if ( ! empty( $place['photo'] ) ) {
				$values['field_amour_place_photo'] = $this->attachment_by_blob( $place['photo'] );
			}
			foreach ( $values as $key => $value ) {
				update_field( $key, $value, $id );
			}
			++$added;
		}
		WP_CLI::log( 'Places added: ' . $added );
	}

	/**
	 * Home map texts (board Main-v2-b): the opening and the four views with their cards.
	 *
	 * @param array<string, int> $ids Page ids by slug.
	 */
	private function home_map( $ids ) {
		$home = $ids['home'];
		if ( ! function_exists( 'update_field' ) || get_field( 'home_map_views', $home, false ) ) {
			return;
		}
		$guide = array(
			'title'  => 'Open the Crete guide',
			'url'    => get_permalink( $ids['crete-guide'] ),
			'target' => '',
		);
		$view  = static fn( $tab, $eyebrow, $title, $meta, $text, $text_phone, $link, $photo ) => array(
			'field_amour_home_map_view_tab'        => $tab,
			'field_amour_home_map_view_eyebrow'    => $eyebrow,
			'field_amour_home_map_view_title'      => $title,
			'field_amour_home_map_view_meta'       => $meta,
			'field_amour_home_map_view_text'       => $text,
			'field_amour_home_map_view_text_phone' => $text_phone,
			'field_amour_home_map_view_link'       => $link,
			'field_amour_home_map_view_photo'      => $photo,
		);
		update_field( 'field_amour_home_map_intro_label', 'From our door', $home );
		update_field( 'field_amour_home_map_intro_title', 'West Crete', $home );
		$photos = array();
		foreach ( array(
			array( '2484380ac652a93e6f6b883171323ca7', 'Seitan Limania' ),
			array( '6388b6418cdb641f6bd8e7b2792479ef', 'Arkadi Monastery' ),
			array( '5d62234bcd2c3732bd843737474dc29b', 'Almyrida' ),
			array( '167426f58d62fe435700966790505f2a', '' ),
			array( '86e186194bfcc48918d797e665471892', '' ),
			array( '16e92c844577adb7ca466250469281b0', '' ),
		) as $photo ) {
			$photos[] = array(
				'field_amour_home_map_intro_photo'   => $this->attachment_by_blob( $photo[0] ),
				'field_amour_home_map_intro_caption' => $photo[1],
			);
		}
		update_field( 'field_amour_home_map_intro_photos', $photos, $home );
		update_field(
			'field_amour_home_map_views',
			array(
				$view( '', 'Where we are', 'Above Almyrida bay', 'Chania 40 min · Rethymno 55 min by car', 'Nine rooms and a villa among olive groves, a 15-minute walk from the village beach.', '', $guide, 0 ),
				$view(
					'The hotel',
					'The hotel',
					'Amour Relax Hotel',
					'9 rooms · pool · our own court',
					'Stone terraces among olive groves, with the bay of Almyrida below. The beach and tavernas are 1.2 km on foot.',
					'',
					array(
						'title'  => 'See the rooms',
						'url'    => home_url( '/#rooms' ),
						'target' => '',
					),
					$this->attachment_by_blob( 'c7c14ed99aa8d9c4132d959c37050de9' )
				),
				$view(
					'Villa Eudora',
					'The villa',
					'Villa Eudora',
					'5 bedrooms · own pool · 4 min from the hotel',
					'A whole house for a family or friends, up to 11 guests, with a private pool and a long table outside.',
					'',
					array(
						'title'  => 'Discover the villa',
						'url'    => get_permalink( $ids['villa-eudora'] ),
						'target' => '',
					),
					$this->attachment_by_blob( '2aac80822b69d3836096e30d8c7de6da' )
				),
				$view( 'Day trips', 'Day trips', 'A base for west Crete', 'Drive times from the hotel', 'Point at a place on the map to see the road there.', 'Tap a place on the map to see the road there.', $guide, 0 ),
			),
			$home
		);
		WP_CLI::log( 'Home page: map' );
	}

	/**
	 * Find an attachment by one of the meta values the photo import writes.
	 *
	 * @param string $key   Meta key.
	 * @param string $value Meta value.
	 * @return int Attachment id or 0.
	 */
	private function attachment_by_meta( $key, $value ) {
		$found = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-off CLI seed.
				'meta_value'     => $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- one-off CLI seed.
			)
		);
		return $found ? (int) $found[0] : 0;
	}
}
