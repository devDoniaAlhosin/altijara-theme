<?php
/**
 * Category archive widget: hero and recommended list.
 *
 * Same markup as the former inline category template; loaded via the archive pack.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config  = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$posts   = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$full    = isset( $config['variant'] ) && 'full' === $config['variant'];
$clean   = array();
foreach ( $posts as $item ) {
	if ( $item instanceof WP_Post ) {
		$clean[] = $item;
	}
}
$hero    = ! empty( $clean[0] ) ? $clean[0] : null;
$sidebar = $full ? array() : array_slice( $clean, 1 );
$show_rec = ! $full && ! empty( $sidebar );
$fallback = function_exists( 'altijara_asset' ) ? altijara_asset( 'assets/images/thumb-logo-fallback.png' ) : '';
$classes  = 'cat-top-grid';
if ( $full ) {
	$classes .= ' is-hero-full';
} elseif ( ! $show_rec ) {
	$classes .= ' is-hero-solo';
}
?>
<section class="<?php echo esc_attr( $classes ); ?>" style="margin-top:20px;">
	<?php if ( $hero instanceof WP_Post ) : ?>
		<article class="cat-hero-card" id="catHeroCard">
			<div class="cat-hero-info">
				<h2 class="cat-hero-headline">
					<a href="<?php echo esc_url( get_permalink( $hero ) ); ?>"><?php echo esc_html( get_the_title( $hero ) ); ?></a>
				</h2>
				<?php if ( get_the_excerpt( $hero ) ) : ?>
					<p class="cat-hero-synopsis"><?php echo esc_html( get_the_excerpt( $hero ) ); ?></p>
				<?php endif; ?>
				<div class="cat-hero-meta">
					<span class="lead-time"><?php echo esc_html( altijara_format_date_ar( $hero->ID ) ); ?></span>
				</div>
			</div>
			<div class="cat-hero-img-wrap">
				<a href="<?php echo esc_url( get_permalink( $hero ) ); ?>">
					<img src="<?php echo esc_url( altijara_get_thumb_url( $hero->ID ) ?: $fallback ); ?>" alt="<?php echo esc_attr( get_the_title( $hero ) ); ?>" loading="eager">
					<?php if ( function_exists( 'altijara_render_video_play_badge' ) ) { altijara_render_video_play_badge( $hero->ID ); } ?>
				</a>
			</div>
		</article>
	<?php else : ?>
		<article class="cat-hero-card">
			<div class="cat-hero-info">
				<h2 class="cat-hero-headline"><?php esc_html_e( 'لا مقالات في هذا القسم بعد', 'altijara' ); ?></h2>
			</div>
		</article>
	<?php endif; ?>

	<?php if ( $show_rec ) : ?>
		<aside class="cat-recommended-col">
			<div class="cat-rec-list" id="recList">
				<?php foreach ( $sidebar as $item ) : ?>
					<div class="cat-rec-item">
						<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID, 'medium' ) ?: $fallback ); ?>" alt="" class="cat-rec-img" loading="lazy">
						<div class="cat-rec-content">
							<h4 class="cat-rec-headline">
								<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
							</h4>
							<span class="cat-rec-time"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</aside>
	<?php endif; ?>
</section>
