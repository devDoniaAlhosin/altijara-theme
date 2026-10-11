<?php
/**
 * Hero interviews wire list
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config = isset( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key    = isset( $args['section_key'] ) ? $args['section_key'] : 'hero_interviews';
$label  = ! empty( $config['label'] ) ? $config['label'] : 'لقاءات';
$more   = altijara_get_section_archive_url( ! empty( $config['category'] ) ? $config['category'] : 'interviews' );
?>
<div class="international-wire-list" data-section="<?php echo esc_attr( $key ); ?>">
	<span class="pill-badge"><?php echo esc_html( $label ); ?></span>
	<?php foreach ( $posts as $item ) : ?>
		<div class="intl-wire-item">
			<div class="intl-wire-thumb">
				<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID, 'medium' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $item ) ); ?>">
			</div>
			<div class="intl-wire-content">
				<span class="intl-wire-cat"><?php echo esc_html( altijara_get_primary_category_name( $item->ID ) ?: $label ); ?></span>
				<h4 class="intl-wire-title">
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
				</h4>
				<p class="intl-wire-desc"><?php echo esc_html( altijara_trim_at_word( $item->post_excerpt ?: $item->post_content, 120 ) ); ?></p>
				<span class="intl-wire-time"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="wire-list-footer">
		<?php get_template_part( 'template-parts/home/section-more', null, array( 'url' => $more ) ); ?>
	</div>
</div>
