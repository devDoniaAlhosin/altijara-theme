<?php
/**
 * Hero personality / lead story
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config = isset( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key    = isset( $args['section_key'] ) ? $args['section_key'] : 'hero_personality';
$label  = ! empty( $config['label'] ) ? $config['label'] : 'شخصية العدد';
$more   = altijara_get_section_archive_url( ! empty( $config['category'] ) ? $config['category'] : 'personality' );
$lead   = ! empty( $posts[0] ) ? $posts[0] : null;
if ( ! $lead ) {
	return;
}
?>
<div class="lead-primary-story" data-section="<?php echo esc_attr( $key ); ?>">
	<div class="lead-primary-inner">
		<span class="pill-badge"><?php echo esc_html( $label ); ?></span>
		<h1 class="lead-title-giant">
			<a href="<?php echo esc_url( get_permalink( $lead ) ); ?>"><?php echo esc_html( get_the_title( $lead ) ); ?></a>
		</h1>
		<p class="lead-excerpt">
			<span class="lead-excerpt-text"><?php echo esc_html( altijara_trim_at_word( $lead->post_excerpt ?: $lead->post_content, 220 ) ); ?></span>
		</p>
		<div class="lead-story-footer">
			<?php get_template_part( 'template-parts/home/section-more', null, array( 'url' => $more ) ); ?>
		</div>
	</div>
</div>
