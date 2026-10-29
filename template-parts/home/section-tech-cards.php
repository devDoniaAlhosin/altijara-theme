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
$cat_key = ! empty( $config['category'] ) ? $config['category'] : 'tech';
?>
<div class="cars-cards-row" data-wp-cat="<?php echo esc_attr( $cat_key ); ?>" data-section="<?php echo esc_attr( $key ); ?>">
	<?php if ( empty( $posts ) ) : ?>
		<?php
		if ( function_exists( 'altijara_render_section_empty' ) ) {
			altijara_render_section_empty( 'altijara-section-empty--body', $key );
		} else {
			echo '<div class="altijara-section-empty altijara-section-empty--body" role="status"><p>' . esc_html__( 'لا مقالات في هذا القسم بعد', 'altijara' ) . '</p></div>';
		}
		?>
	<?php else : ?>
		<?php foreach ( $posts as $item ) : ?>
			<article class="news-card-v car-card">
				<div class="card-img-wrap">
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>">
						<?php
						altijara_the_thumb_img(
							$item->ID,
							'medium_large',
							array(
								'loading'  => 'lazy',
								'decoding' => 'async',
								'sizes'    => '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw',
							)
						);
						?>
					</a>
					<?php
					if ( function_exists( 'altijara_render_video_play_badge' ) ) {
						altijara_render_video_play_badge( $item->ID );
					}
					?>
				</div>
				<?php
				$card_cat = function_exists( 'altijara_section_card_category_name' )
					? altijara_section_card_category_name( $item->ID, $config, $key )
					: altijara_get_primary_category_name( $item->ID );
				?>
				<?php if ( $card_cat ) : ?>
				<span class="category-tag"><?php echo esc_html( $card_cat ); ?></span>
				<?php endif; ?>
				<h3 class="card-headline">
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
				</h3>
				<span class="meta-time"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
				<div class="card-bullet-sub"><?php echo esc_html( altijara_trim_at_word( $item->post_excerpt ?: $item->post_content, 90 ) ); ?></div>
			</article>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
