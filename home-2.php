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
	// Fallback identical to classic front markup (with stream offsets).
	$classic = function_exists( 'altijara_get_classic_home_layout_pack' )
		? altijara_get_classic_home_layout_pack()
		: array( 'sections' => array(), 'order' => array() );
	$classic_sections = isset( $classic['sections'] ) && is_array( $classic['sections'] ) ? $classic['sections'] : array();
	?>
	<main class="container" data-altijara-home-ssr="1" data-altijara-home="2">
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
