<?php
/**
 * Site footer.
 *
 * @package Amour
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="site-footer">
	<?php
	wp_nav_menu(
		array(
			'theme_location'  => 'legal',
			'container'       => 'nav',
			'container_class' => 'legal-nav',
			'fallback_cb'     => false,
		)
	);
	?>
</footer>
<?php wp_footer(); ?>
</body>
</html>
