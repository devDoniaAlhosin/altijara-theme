<?php
/**
 * Front page — SSR widget instances (Section Manager).
 * Hero grid is fixed; body sections come from ordered builder config.
 *
 * @package Altijara
 */

get_header();
?>
<main class="container" data-altijara-home-ssr="1">

	<!-- SECTION 1: HERO GRID (locked) -->
	<section class="hero-section">
		<div class="hero-grid">

			<?php altijara_render_home_section( 'hero_chamber' ); ?>

			<div class="hero-col-center">
				<?php altijara_render_home_section( 'hero_magazine' ); ?>
				<?php altijara_render_home_section( 'hero_interviews' ); ?>
			</div>

			<div class="hero-col-lead">
				<?php altijara_render_home_section( 'hero_personality' ); ?>
				<?php altijara_render_home_section( 'hero_logistics' ); ?>
			</div>

		</div>
	</section>

	<?php
	if ( function_exists( 'altijara_render_home_body_sections' ) ) {
		altijara_render_home_body_sections();
	}
	?>

</main>
<?php
get_footer();
