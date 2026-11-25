<?php
/**
 * The Rooms page (board Rooms; HANDOFF, section 2). The opening with the date search, the three kinds of room
 * (photo and text in turn left and right), In every room, the villa for families and groups, Good to know.
 * Texts: the Rooms page in the admin; the rooms: each room page, tab "On the Rooms page".
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
$amour_f      = static fn( $name ) => amour_page_field( $name );
$amour_rooms  = amour_rooms_list();
$amour_every  = array_filter( wp_list_pluck( (array) $amour_f( 'rooms_every' ), 'text' ) );
$amour_family = array_filter( (array) $amour_f( 'family_facts' ), static fn( $row ) => ! empty( $row['label'] ) );
$amour_know   = array_filter( (array) $amour_f( 'know' ), static fn( $row ) => ! empty( $row['label'] ) );
$amour_paw    = 'M14 34a6 8 0 1 0 0-16 6 8 0 0 0 0 16zM26 22.5a6 8.5 0 1 0 0-17 6 8.5 0 0 0 0 17zM40 22.5a6 8.5 0 1 0 0-17 6 8.5 0 0 0 0 17zM51 34a6 8 0 1 0 0-16 6 8 0 0 0 0 16zM32 30c-9 0-17 11-17 19 0 6 5 9 10 8 3-.5 4.5-2 7-2s4 1.5 7 2c5 1 10-2 10-8 0-8-8-19-17-19z';
?>
<main id="main" tabindex="-1" class="am-rooms-page">

	<section class="am-rl-open" id="book" aria-labelledby="am-rl-title">
		<div class="am-rl-in">
			<nav class="am-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'amour' ); ?>">
				<a href="<?php echo esc_url( amour_home_url() ); ?>"><?php esc_html_e( 'Home', 'amour' ); ?></a> · <span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
			</nav>
			<div class="am-rl-open__grid">
				<h1 id="am-rl-title" class="am-rl-open__title"><?php echo esc_html( $amour_f( 'rooms_title' ) ? $amour_f( 'rooms_title' ) : get_the_title() ); ?></h1>
				<?php if ( $amour_f( 'rooms_text' ) ) : ?>
					<p class="am-rl-open__text"><?php echo esc_html( $amour_f( 'rooms_text' ) ); ?></p>
				<?php endif; ?>
			</div>
			<?php get_template_part( 'template-parts/date-search' ); ?>
		</div>
	</section>

	<section class="am-rl-list" aria-label="<?php esc_attr_e( 'The rooms', 'amour' ); ?>">
		<div class="am-rl-in am-rl-list__in">
			<?php foreach ( $amour_rooms as $amour_i => $amour_room ) : ?>
				<article class="am-rl-room<?php echo $amour_i % 2 ? ' is-flip' : ''; ?>" aria-labelledby="am-rl-room-<?php echo (int) $amour_i; ?>">
					<a class="am-rl-room__pic" href="<?php echo esc_url( $amour_room['url'] ); ?>" tabindex="-1" aria-hidden="true">
						<?php
						echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
							$amour_room['photo'],
							'large',
							false,
							array(
								'sizes'    => '(max-width: 760px) 92vw, 620px',
								'loading'  => $amour_i ? 'lazy' : 'eager',
								'decoding' => 'async',
							)
						);
						?>
					</a>
					<div class="am-rl-room__text">
						<?php /* translators: %s: number of the room kind, 01, 02… */ ?>
						<p class="am-eyebrow"><?php echo esc_html( sprintf( __( 'Room %s', 'amour' ), str_pad( (string) ( $amour_i + 1 ), 2, '0', STR_PAD_LEFT ) ) ); ?></p>
						<h2 id="am-rl-room-<?php echo (int) $amour_i; ?>" class="am-rl-room__name"><?php echo esc_html( $amour_room['name'] ); ?></h2>
						<?php if ( $amour_room['text'] ) : ?>
							<p><?php echo esc_html( $amour_room['text'] ); ?></p>
						<?php endif; ?>
						<?php if ( $amour_room['facts'] ) : ?>
							<dl class="am-rl-room__facts">
								<?php foreach ( $amour_room['facts'] as $amour_fact ) : ?>
									<dt><?php echo esc_html( $amour_fact['label'] ); ?></dt>
									<dd><?php echo esc_html( $amour_fact['value'] ); ?></dd>
								<?php endforeach; ?>
							</dl>
						<?php endif; ?>
						<div class="am-rl-room__foot">
							<?php if ( $amour_room['price'] ) : ?>
								<?php /* translators: %s: price in euros. */ ?>
								<p class="am-rl-room__price"><?php echo esc_html( sprintf( __( 'from €%s a night', 'amour' ), $amour_room['price'] ) ); ?></p>
							<?php endif; ?>
							<div class="am-rl-room__actions">
								<a class="am-btn am-btn--ghost" href="<?php echo esc_url( $amour_room['url'] ); ?>">
									<?php esc_html_e( 'View room', 'amour' ); ?><span class="sr-only">: <?php echo esc_html( $amour_room['name'] ); ?></span>
								</a>
								<a class="am-btn" href="<?php echo esc_url( amour_page_url( 'book' ) ); ?>" data-amour-cal="arr">
									<?php esc_html_e( 'Check dates', 'amour' ); ?><span class="sr-only">: <?php echo esc_html( $amour_room['name'] ); ?></span>
								</a>
							</div>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<?php if ( $amour_every ) : ?>
		<section class="am-rl-every" aria-labelledby="am-rl-every">
			<div class="am-rl-in am-rl-every__in">
				<h2 id="am-rl-every" class="am-rl-every__title"><?php echo esc_html( $amour_f( 'rooms_every_title' ) ); ?></h2>
				<ul class="am-rl-every__list">
					<?php foreach ( $amour_every as $amour_line ) : ?>
						<li><?php echo esc_html( $amour_line ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $amour_f( 'family_title' ) ) : ?>
		<section class="am-rl-family" data-theme="eudora" data-tone="dark" aria-labelledby="am-rl-family">
			<?php
			if ( $amour_f( 'family_bg' ) ) {
				echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
					(int) $amour_f( 'family_bg' ),
					'full',
					false,
					array(
						'class'    => 'am-rl-family__bg',
						'alt'      => '',
						'sizes'    => '100vw',
						'loading'  => 'lazy',
						'decoding' => 'async',
					)
				);
			}
			?>
			<div class="am-rl-in am-rl-family__in">
				<div class="am-rl-family__text">
					<?php echo amour_logo( 'eudora-horizontal', __( 'Villa Eudora', 'amour' ), 'am-rl-family__logo', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
					<h2 id="am-rl-family" class="am-rl-family__title"><?php echo esc_html( $amour_f( 'family_title' ) ); ?></h2>
					<?php if ( $amour_f( 'family_text' ) ) : ?>
						<p class="am-rl-family__line"><?php echo esc_html( $amour_f( 'family_text' ) ); ?></p>
					<?php endif; ?>
					<?php if ( $amour_family ) : ?>
						<dl class="am-rl-family__facts">
							<?php foreach ( $amour_family as $amour_fact ) : ?>
								<div>
									<dt><?php echo esc_html( $amour_fact['label'] ); ?></dt>
									<dd><?php echo esc_html( $amour_fact['value'] ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
					<a class="am-btn am-btn--on-photo" href="<?php echo esc_url( amour_page_url( 'villa-eudora' ) ); ?>"><?php esc_html_e( 'Discover the villa', 'amour' ); ?></a>
				</div>
				<?php if ( $amour_f( 'family_photo' ) ) : ?>
					<figure class="am-rl-family__fig">
						<span class="am-rl-family__arch">
							<?php
							echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function escapes.
								(int) $amour_f( 'family_photo' ),
								'large',
								false,
								array(
									'sizes'    => '(max-width: 760px) 92vw, 400px',
									'loading'  => 'lazy',
									'decoding' => 'async',
								)
							);
							?>
						</span>
						<?php if ( $amour_f( 'family_caption' ) ) : ?>
							<figcaption><?php echo esc_html( $amour_f( 'family_caption' ) ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $amour_know ) : ?>
		<section class="am-rl-know" aria-labelledby="am-rl-know">
			<div class="am-rl-in">
				<div class="am-rl-know__head">
					<h2 id="am-rl-know" class="am-rl-know__title"><?php echo esc_html( $amour_f( 'know_title' ) ); ?></h2>
					<a href="<?php echo esc_url( amour_page_url( 'questions' ) ); ?>"><?php esc_html_e( 'All questions and house rules', 'amour' ); ?></a>
				</div>
				<div class="am-rl-know__grid">
					<?php foreach ( $amour_know as $amour_note ) : ?>
						<div class="am-rl-note">
							<p class="am-rl-note__label"><?php echo esc_html( $amour_note['label'] ); ?></p>
							<?php if ( '' !== trim( (string) $amour_note['figure'] ) ) : ?>
								<p class="am-rl-note__fig"><?php echo esc_html( $amour_note['figure'] ); ?></p>
							<?php else : ?>
								<span class="am-rl-note__fig" aria-hidden="true"><svg viewBox="0 0 64 64" width="62" height="62" focusable="false"><path d="<?php echo esc_attr( $amour_paw ); ?>"/></svg></span>
							<?php endif; ?>
							<p class="am-rl-note__text"><?php echo esc_html( $amour_note['text'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
