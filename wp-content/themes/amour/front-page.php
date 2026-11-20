<?php
/**
 * Home page (boards Main-v2 and Main-v2-b; HANDOFF, section 6). One template part per block.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" tabindex="-1" class="am-home">
	<?php
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/search' );
	get_template_part( 'template-parts/home/phrase' );
	get_template_part( 'template-parts/home/estate' );
	?>
</main>
<?php
get_footer();
