<?php
/**
 * Front page — SSR widget instances (Section Manager).
 * Preserves prototype CSS class structure; no redesign.
 *
 * @package Altijara
 */

get_header();
?>
<main class="container" data-altijara-home-ssr="1">

	<!-- SECTION 1: HERO GRID -->
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

	<!-- SECTION 2: متابعات -->
	<?php altijara_render_home_section( 'followups' ); ?>

	<!-- SECTION 3: مال وأعمال + الطاقة -->
	<?php if ( altijara_section_is_visible( 'business' ) || altijara_section_is_visible( 'energy' ) ) : ?>
		<section class="home-split-sectors-section">
			<div class="home-split-grid">
				<?php altijara_render_home_section( 'business' ); ?>
				<?php altijara_render_home_section( 'energy' ); ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- SECTION 4: سيارات -->
	<?php altijara_render_home_section( 'automotive' ); ?>

	<!-- SECTION 5: تقنية + الأكثر قراءة (prototype cars-section) -->
	<?php
	$tech_cfg     = altijara_get_section_config( 'tech' );
	$tech_variant = ! empty( $tech_cfg['variant'] ) ? $tech_cfg['variant'] : 'cars_row';
	$show_tech    = altijara_section_is_visible( 'tech' );
	$show_most    = altijara_section_is_visible( 'most_read' );
	?>
	<?php if ( $show_tech || $show_most ) : ?>
		<?php if ( 'cars_row' === $tech_variant || $show_most ) : ?>
			<?php if ( $show_tech ) : ?>
				<div class="section-bar-wrapper" data-section-bar="tech">
					<div class="section-bar-badge-right">
						<span class="pill-badge"><?php echo esc_html( $tech_cfg['label'] ); ?></span>
					</div>
				</div>
			<?php endif; ?>
			<section class="cars-section">
				<div class="cars-grid">
					<?php altijara_render_home_section( 'most_read' ); ?>
					<?php altijara_render_home_section( 'tech' ); ?>
				</div>
			</section>
			<?php if ( $show_tech ) : ?>
				<div class="section-footer-more">
					<?php
					get_template_part(
						'template-parts/home/section-more',
						null,
						array(
							'url' => altijara_get_section_archive_url( ! empty( $tech_cfg['category'] ) ? $tech_cfg['category'] : 'tech' ),
						)
					);
					?>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<?php altijara_render_home_section( 'tech' ); ?>
			<?php altijara_render_home_section( 'most_read' ); ?>
		<?php endif; ?>
	<?php endif; ?>

	<!-- SECTION 6: صحة + سياحة -->
	<?php if ( altijara_section_is_visible( 'health' ) || altijara_section_is_visible( 'tourism' ) ) : ?>
		<section class="home-split-sectors-section">
			<div class="home-split-grid">
				<?php altijara_render_home_section( 'health' ); ?>
				<?php altijara_render_home_section( 'tourism' ); ?>
			</div>
		</section>
	<?php endif; ?>

</main>
<?php
get_footer();
