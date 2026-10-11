<?php
/**
 * Tech cards row (cars-cards-row) — prototype markup beside Most Read.
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config  = isset( $args['config'] ) ? $args['config'] : array();
$posts   = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key     = isset( $args['section_key'] ) ? $args['section_key'] : 'tech';
$label   = ! empty( $config['label'] ) ? $config['label'] : 'تقنية';
$cat_key = ! empty( $config['category'] ) ? $config['category'] : 'tech';
?>
<div class="cars-cards-row" data-wp-cat="<?php echo esc_attr( $cat_key ); ?>" data-section="<?php echo esc_attr( $key ); ?>">
	<?php foreach ( $posts as $item ) : ?>
		<article class="news-card-v car-card">
			<div class="card-img-wrap">
				<a href="<?php echo esc_url( get_permalink( $item ) ); ?>">
					<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID ) ); ?>" alt="<?php echo esc_attr( get_the_title( $item ) ); ?>">
				</a>
			</div>
			<span class="category-tag"><?php echo esc_html( altijara_get_primary_category_name( $item->ID ) ?: $label ); ?></span>
			<h3 class="card-headline">
				<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
			</h3>
			<span class="meta-time"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
			<div class="card-bullet-sub"><?php echo esc_html( altijara_trim_at_word( $item->post_excerpt ?: $item->post_content, 90 ) ); ?></div>
		</article>
	<?php endforeach; ?>
</div>
