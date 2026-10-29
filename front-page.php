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
	$classic = function_exists( 'altijara_get_classic_home_layout_pack' )
		? altijara_get_classic_home_layout_pack()
		: array( 'sections' => array(), 'order' => array() );
	$classic_sections = isset( $classic['sections'] ) && is_array( $classic['sections'] ) ? $classic['sections'] : array();
	?>
<main class="container" data-altijara-home-ssr="1">

	<?php
	$render_classic = static function () {
		if ( function_exists( 'altijara_render_home_hero_grid' ) ) {
			altijara_render_home_hero_grid();
		}
		if ( function_exists( 'altijara_render_home_body_sections' ) ) {
			altijara_render_home_body_sections();
		}
	};
	if ( $classic_sections && function_exists( 'altijara_with_layout_sections' ) ) {
		altijara_with_layout_sections( $classic_sections, $render_classic );
	} else {
		$render_classic();
	}
	?>

</main>
	<?php
}

get_footer();
