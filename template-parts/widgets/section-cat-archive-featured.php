<?php
/**
 * Category archive widget: featured card row.
 *
 * Same markup as the category template. category.php does not load this file.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$clean = array();
foreach ( $posts as $item ) {
	if ( $item instanceof WP_Post ) {
		$clean[] = $item;
	}
}
if ( ! $clean ) {
	return;
}
$fallback = function_exists( 'altijara_asset' ) ? altijara_asset( 'assets/images/thumb-logo-fallback.png' ) : '';
?>
<section class="cat-featured-row-4col">
	<?php foreach ( $clean as $item ) : ?>
		<article class="cat-featured-card">
			<a href="<?php echo esc_url( get_permalink( $item ) ); ?>" class="cat-featured-img-link">
				<div class="cat-featured-img-wrap">
					<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID, 'medium' ) ?: $fallback ); ?>" alt="<?php echo esc_attr( get_the_title( $item ) ); ?>" loading="lazy">
					<?php if ( function_exists( 'altijara_render_video_play_badge' ) ) { altijara_render_video_play_badge( $item->ID ); } ?>
				</div>
			</a>
			<div class="cat-featured-body">
				<?php
				$config       = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
				$key          = isset( $args['section_key'] ) ? (string) $args['section_key'] : '';
				$featured_tag = '';
				if ( function_exists( 'altijara_section_card_category_name' ) ) {
					$featured_tag = altijara_section_card_category_name( $item->ID, $config, $key );
				} elseif ( function_exists( 'altijara_get_listing_category_name' ) ) {
					$featured_tag = altijara_get_listing_category_name( $item->ID );
				} elseif ( function_exists( 'altijara_get_primary_category_name' ) ) {
					$featured_tag = altijara_get_primary_category_name( $item->ID );
				}
				?>
				<?php if ( $featured_tag ) : ?>
					<span class="cat-featured-tag"><?php echo esc_html( $featured_tag ); ?></span>
				<?php endif; ?>
				<h3 class="cat-featured-title">
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
				</h3>
				<span class="cat-featured-date"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
			</div>
		</article>
	<?php endforeach; ?>
</section>
