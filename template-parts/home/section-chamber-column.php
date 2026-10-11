<?php
/**
 * Hero chamber column (prototype classes preserved)
 *
 * @package Altijara
 * @var string   $args['section_key']
 * @var array    $args['config']
 * @var WP_Post[] $args['posts']
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config = isset( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key    = isset( $args['section_key'] ) ? $args['section_key'] : 'hero_chamber';
$label  = ! empty( $config['label'] ) ? $config['label'] : 'أخبار الغرفة';
$more   = altijara_get_section_archive_url( ! empty( $config['category'] ) ? $config['category'] : 'chamber' );
$lead   = ! empty( $posts[0] ) ? $posts[0] : null;
$list   = array_slice( $posts, 1 );
?>
<div class="hero-col-opinion" data-section="<?php echo esc_attr( $key ); ?>">
	<div class="chamber-box">
		<span class="pill-badge"><?php echo esc_html( $label ); ?></span>

		<?php if ( $lead ) : ?>
			<article class="chamber-lead">
				<a href="<?php echo esc_url( get_permalink( $lead ) ); ?>" class="chamber-lead-img-wrap">
					<img src="<?php echo esc_url( altijara_get_thumb_url( $lead->ID ) ); ?>" alt="<?php echo esc_attr( get_the_title( $lead ) ); ?>">
				</a>
				<h3 class="chamber-lead-title">
					<a href="<?php echo esc_url( get_permalink( $lead ) ); ?>"><?php echo esc_html( get_the_title( $lead ) ); ?></a>
				</h3>
				<p class="chamber-lead-excerpt"><?php echo esc_html( altijara_trim_at_word( $lead->post_excerpt ?: $lead->post_content, 160 ) ); ?></p>
			</article>
		<?php endif; ?>

		<ul class="chamber-opinion-list">
			<?php foreach ( $list as $item ) : ?>
				<?php
				get_template_part(
					'template-parts/cards/post',
					'card',
					array(
						'post'    => $item,
						'variant' => 'chamber_item',
					)
				);
				?>
			<?php endforeach; ?>
		</ul>

		<div class="chamber-box-footer">
			<?php get_template_part( 'template-parts/home/section-more', null, array( 'url' => $more ) ); ?>
		</div>
	</div>
</div>
