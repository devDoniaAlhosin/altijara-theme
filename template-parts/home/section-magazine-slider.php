<?php
/**
 * Hero magazine / cover slider — matches prototype markup.
 * Slides come from the issues query strategy (or posts fallback).
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config = isset( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key    = isset( $args['section_key'] ) ? $args['section_key'] : 'hero_magazine';

$slides       = $posts;
$fallback_img = altijara_asset( 'assets/images/issue-730-f2dfce.jpg' );
$first_date   = ! empty( $slides[0] ) ? altijara_format_date_ar( $slides[0]->ID ) . ' — الإصدار الرقمي' : '';
?>
<div class="magazine-cover-card" id="heroCoverSlider" data-section="<?php echo esc_attr( $key ); ?>">
	<?php if ( empty( $slides ) ) : ?>
		<div class="hero-cover-slide is-active">
			<div class="magazine-cover-img-wrap">
				<img src="<?php echo esc_url( $fallback_img ); ?>" alt="<?php esc_attr_e( 'غلاف المجلة', 'altijara' ); ?>">
			</div>
			<div class="magazine-cover-details">
				<h3 class="magazine-cover-title"><?php esc_html_e( 'أعداد مجلة التجارة', 'altijara' ); ?></h3>
				<span class="magazine-cover-date"><?php esc_html_e( 'الإصدار الرقمي', 'altijara' ); ?></span>
			</div>
		</div>
	<?php else : ?>
		<?php foreach ( $slides as $i => $slide ) : ?>
			<div class="hero-cover-slide <?php echo 0 === $i ? 'is-active' : ''; ?>">
				<div class="magazine-cover-img-wrap">
					<a href="<?php echo esc_url( get_permalink( $slide ) ); ?>">
						<img src="<?php echo esc_url( altijara_get_thumb_url( $slide->ID ) ); ?>" alt="<?php echo esc_attr( get_the_title( $slide ) ); ?>">
					</a>
				</div>
				<div class="magazine-cover-details">
					<h3 class="magazine-cover-title">
						<a href="<?php echo esc_url( get_permalink( $slide ) ); ?>"><?php echo esc_html( get_the_title( $slide ) ); ?></a>
					</h3>
					<span class="magazine-cover-date"><?php echo esc_html( altijara_format_date_ar( $slide->ID ) ); ?> — الإصدار الرقمي</span>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<div class="hero-cover-meta">
		<span class="magazine-cover-date"><?php echo esc_html( $first_date ); ?></span>
		<?php if ( count( $slides ) > 1 ) : ?>
			<div class="hero-cover-arrows">
				<button type="button" class="nav-arrow-btn hero-cover-arrow hero-cover-arrow--prev" aria-label="<?php esc_attr_e( 'العدد السابق', 'altijara' ); ?>">
					<i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
				</button>
				<button type="button" class="nav-arrow-btn hero-cover-arrow hero-cover-arrow--next" aria-label="<?php esc_attr_e( 'العدد التالي', 'altijara' ); ?>">
					<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
				</button>
			</div>
		<?php endif; ?>
	</div>
</div>
