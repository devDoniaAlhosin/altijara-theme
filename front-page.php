<?php
/**
 * Front page — uses Home 2 page meta when the static front page
 * has template Home 2; otherwise theme-options sections.
 *
 * @package Altijara
 */

get_header();

if ( function_exists( 'altijara_front_uses_home2' ) && altijara_front_uses_home2() && function_exists( 'altijara_render_home2_markup' ) ) {
	altijara_render_home2_markup();
} else {
	?>
<main class="container" data-altijara-home-ssr="1">

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
