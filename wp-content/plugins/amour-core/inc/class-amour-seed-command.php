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

		// Photos first: the menu items point at them.
		if ( ! empty( $assoc_args['media'] ) ) {
			$this->media( rtrim( $assoc_args['media'], '/' ) );
		}

		$this->menus( $ids );
		$this->hotel_settings();
		$this->home_content( $ids['home'] );
		$this->cards( $ids );

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

		foreach ( self::OLD_MENUS as $old ) {
			$old_menu = wp_get_nav_menu_object( $old );
			if ( $old_menu ) {
				wp_delete_nav_menu( $old_menu->term_id );
			}
		}

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
				} elseif ( str_contains( $target, '#' ) ) {
					// A section of another page, e.g. villa-eudora#the-house (the section ids come with the page).
					list( $slug, $anchor )  = explode( '#', $target, 2 );
					$data['menu-item-type'] = 'custom';
					$data['menu-item-url']  = get_permalink( $ids[ $slug ] ) . '#' . $anchor;
				} else {
					$data['menu-item-type']      = 'post_type';
					$data['menu-item-object']    = 'page';
					$data['menu-item-object-id'] = $ids[ $target ];
				}
				$item_id = wp_update_nav_menu_item( $menu_id, 0, $data );

				// Photo in the arch and the line under it (Menu-list board).
				if ( isset( $item[3] ) && function_exists( 'update_field' ) && ! is_wp_error( $item_id ) ) {
					$photo = $this->attachment_by_blob( $item[2] );
					if ( $photo ) {
						update_field( 'field_amour_menu_photo', $photo, $item_id );
					}
					update_field( 'field_amour_menu_line', $item[3], $item_id );
				}
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
