<?php
/**
 * Fallback template. Page templates by design come in the next steps (HANDOFF, section 17).
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" tabindex="-1">
	<?php if ( is_404() ) : ?>
		<h1><?php esc_html_e( 'This path ends here', 'amour' ); ?></h1>
	<?php else : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
			</article>
			<?php
		endwhile;
		?>
	<?php endif; ?>
</main>
<?php
get_footer();
