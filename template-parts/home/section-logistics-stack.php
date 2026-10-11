<?php
/**
 * Hero logistics sub-card stack
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config = isset( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key    = isset( $args['section_key'] ) ? $args['section_key'] : 'hero_logistics';
$label  = ! empty( $config['label'] ) ? $config['label'] : 'لوجستيات';
$more   = altijara_get_section_archive_url( ! empty( $config['category'] ) ? $config['category'] : 'logistics' );
$lead   = ! empty( $posts[0] ) ? $posts[0] : null;
$list   = array_slice( $posts, 1 );
?>
<div class="sub-hero-card" data-wp-cat="logistics" data-section="<?php echo esc_attr( $key ); ?>">
	<span class="pill-badge"><?php echo esc_html( $label ); ?></span>
	<?php if ( $lead ) : ?>
		<div class="card-thumb">
			<a href="<?php echo esc_url( get_permalink( $lead ) ); ?>">
				<img src="<?php echo esc_url( altijara_get_thumb_url( $lead->ID ) ); ?>" alt="<?php echo esc_attr( get_the_title( $lead ) ); ?>">
			</a>
		</div>
		<h4 class="card-title">
			<a href="<?php echo esc_url( get_permalink( $lead ) ); ?>"><?php echo esc_html( get_the_title( $lead ) ); ?></a>
		</h4>
		<p class="card-subtext">
			<span class="card-subtext-text"><?php echo esc_html( altijara_trim_at_word( $lead->post_excerpt ?: $lead->post_content, 180 ) ); ?></span>
		</p>
	<?php endif; ?>

	<div class="sub-hero-list">
		<?php foreach ( $list as $item ) : ?>
			<article class="sub-hero-item">
				<div class="sub-hero-item-thumb">
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>">
						<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID, 'medium' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $item ) ); ?>">
					</a>
				</div>
				<div class="sub-hero-item-body">
					<h5 class="sub-hero-item-title">
						<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
					</h5>
					<span class="meta-time"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<div class="sub-hero-footer">
		<?php get_template_part( 'template-parts/home/section-more', null, array( 'url' => $more ) ); ?>
	</div>
</div>
