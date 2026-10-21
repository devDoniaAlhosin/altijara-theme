<?php
/**
 * Homepage sections — homepage builder UI (shared layout builder).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Settings → Homepage tab builder.
 */
function altijara_render_homepage_builder() {
	$sections = altijara_get_sections();
	$order    = altijara_get_body_section_ids();
	$home2_id = function_exists( 'altijara_get_static_front_page_id' ) ? altijara_get_static_front_page_id() : 0;
	$uses_h2  = $home2_id && 'home-2.php' === (string) get_page_template_slug( $home2_id );
	?>
	<div class="altijara-home-builder-wrap">
		<?php if ( $uses_h2 ) : ?>
			<div class="notice notice-info inline" style="margin:0 0 16px;">
				<p>
					<?php esc_html_e( 'The site front page uses the Home 2 template. Prefer editing the layout on that page’s Altijara metabox (full widget builder).', 'altijara' ); ?>
					<a href="<?php echo esc_url( get_edit_post_link( $home2_id ) ); ?>"><?php esc_html_e( 'Edit Home 2 page', 'altijara' ); ?></a>
				</p>
			</div>
		<?php endif; ?>
		<?php
		if ( function_exists( 'altijara_render_layout_builder' ) ) {
			altijara_render_layout_builder(
				array(
					'builder_id'   => 'theme_home',
					'field_prefix' => 'altijara_sections',
					'order_field'  => 'altijara_sections_order',
					'sections'     => $sections,
					'order'        => $order,
					'surface'      => 'home',
					'lock_hero'    => true,
				)
			);
		}
		?>
	</div>
	<?php
}
