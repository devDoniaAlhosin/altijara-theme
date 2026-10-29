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
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? array_values( $args['posts'] ) : array();
$key    = isset( $args['section_key'] ) ? $args['section_key'] : 'hero_interviews';
$label  = function_exists( 'altijara_section_heading' ) ? altijara_section_heading( $config, 'لقاءات' ) : ( isset( $config['label'] ) ? (string) $config['label'] : 'لقاءات' );
$more   = altijara_get_section_archive_url( ! empty( $config['category'] ) ? $config['category'] : 'interviews' );
?>
<div class="international-wire-list" data-section="<?php echo esc_attr( $key ); ?>">
	<?php if ( '' !== $label ) : ?>
	<span class="pill-badge"><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
	<?php if ( empty( $posts ) ) : ?>
		<?php
		if ( function_exists( 'altijara_render_section_empty' ) ) {
			altijara_render_section_empty( 'altijara-section-empty--hero', $key );
		} else {
			echo '<div class="altijara-section-empty altijara-section-empty--hero" role="status"><p>' . esc_html__( 'لا مقالات في هذا القسم بعد', 'altijara' ) . '</p></div>';
		}
		?>
	<?php else : ?>
		<?php foreach ( $posts as $item ) : ?>
			<div class="intl-wire-item">
				<div class="intl-wire-thumb">
					<?php
					altijara_the_thumb_img(
						$item->ID,
						'medium',
						array(
							'loading'  => 'lazy',
							'decoding' => 'async',
							'sizes'    => '120px',
						)
					);
					?>
				</div>
				<div class="intl-wire-content">
					<?php
					$card_cat = function_exists( 'altijara_section_card_category_name' )
						? altijara_section_card_category_name( $item->ID, $config, $key )
						: altijara_get_primary_category_name( $item->ID );
					?>
					<?php if ( $card_cat ) : ?>
					<span class="intl-wire-cat"><?php echo esc_html( $card_cat ); ?></span>
					<?php endif; ?>
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
	<?php endif; ?>
</div>
