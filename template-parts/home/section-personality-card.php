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
$label  = function_exists( 'altijara_section_heading' ) ? altijara_section_heading( $config, 'شخصية العدد' ) : ( isset( $config['label'] ) ? (string) $config['label'] : 'شخصية العدد' );
$source = ( isset( $config['source'] ) && 'issues' === $config['source'] ) ? 'issues' : 'posts';
$count  = isset( $config['count'] ) ? max( 1, (int) $config['count'] ) : 1;
$posts  = array_slice( array_values( $posts ), 0, $count );
if ( 'issues' === $source ) {
	$more = get_post_type_archive_link( 'issue' );
	$more = $more ? $more : home_url( '/' );
} else {
	$more = altijara_get_section_archive_url( ! empty( $config['category'] ) ? $config['category'] : 'personality' );
}
$lead = ! empty( $posts[0] ) ? $posts[0] : null;
$rest = array_slice( $posts, 1 );
?>
<div class="lead-primary-story" data-section="<?php echo esc_attr( $key ); ?>" data-count="<?php echo esc_attr( (string) $count ); ?>">
	<div class="lead-primary-inner">
		<?php if ( '' !== $label ) : ?>
			<span class="pill-badge"><?php echo esc_html( $label ); ?></span>
		<?php endif; ?>
		<?php if ( ! $lead ) : ?>
			<?php
			if ( function_exists( 'altijara_render_section_empty' ) ) {
				altijara_render_section_empty( 'altijara-section-empty--hero', $key );
			} else {
				echo '<div class="altijara-section-empty altijara-section-empty--hero" role="status"><p>' . esc_html__( 'لا مقالات في هذا القسم بعد', 'altijara' ) . '</p></div>';
			}
			?>
		<?php else : ?>
			<h2 class="lead-title-giant">
				<a href="<?php echo esc_url( get_permalink( $lead ) ); ?>"><?php echo esc_html( get_the_title( $lead ) ); ?></a>
			</h2>
			<p class="lead-excerpt">
				<span class="lead-excerpt-text"><?php echo esc_html( altijara_trim_at_word( $lead->post_excerpt ?: $lead->post_content, 280 ) ); ?></span>
			</p>
			<?php if ( $rest ) : ?>
				<ul class="lead-extra-list">
					<?php foreach ( $rest as $extra ) : ?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $extra ) ); ?>"><?php echo esc_html( get_the_title( $extra ) ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<div class="lead-story-footer">
				<?php get_template_part( 'template-parts/home/section-more', null, array( 'url' => $more ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
