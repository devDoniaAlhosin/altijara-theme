<?php
/**
 * Template Name: Home 2
 * Controllable homepage — sections edited via page metabox.
 * Note: WordPress still loads front-page.php for the site front; that file
 * detects this template on the static front page and uses page meta.
 *
 * @package Altijara
 */

get_header();

if ( function_exists( 'altijara_render_home2_markup' ) ) {
	altijara_render_home2_markup();
} else {
	// Fallback identical to classic front markup.
	?>
	<main class="container" data-altijara-home-ssr="1" data-altijara-home="2">
		<?php
		if ( function_exists( 'altijara_render_home_hero_grid' ) ) {
			altijara_render_home_hero_grid();
		}
		if ( function_exists( 'altijara_render_home_body_sections' ) ) {
			altijara_render_home_body_sections();
		}
		?>
	</main>
	<?php
}

get_footer();
