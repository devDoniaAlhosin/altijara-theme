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
$label  = function_exists( 'altijara_section_heading' ) ? altijara_section_heading( $config, 'أخبار الغرفة' ) : ( isset( $config['label'] ) ? (string) $config['label'] : 'أخبار الغرفة' );
$more   = altijara_get_section_archive_url( ! empty( $config['category'] ) ? $config['category'] : 'chamber' );
$lead   = ! empty( $posts[0] ) ? $posts[0] : null;
$list   = array_slice( $posts, 1 );
?>
<div class="hero-col-opinion" data-section="<?php echo esc_attr( $key ); ?>">
	<div class="chamber-box">
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
			<?php if ( $lead ) : ?>
				<article class="chamber-lead">
					<a href="<?php echo esc_url( get_permalink( $lead ) ); ?>" class="chamber-lead-img-wrap">
						<?php
						altijara_the_thumb_img(
							$lead->ID,
							'medium_large',
							array(
								'loading'  => 'eager',
								'decoding' => 'async',
								'sizes'    => '(max-width: 768px) 100vw, 330px',
							)
						);
						?>
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
					$kicker = function_exists( 'altijara_section_card_category_name' )
						? altijara_section_card_category_name( $item->ID, $config, $key )
						: '';
					get_template_part(
						'template-parts/cards/post',
						'card',
						array(
							'post'    => $item,
							'variant' => 'chamber_item',
							'kicker'  => $kicker,
						)
					);
					?>
				<?php endforeach; ?>
			</ul>

			<div class="chamber-box-footer">
				<?php get_template_part( 'template-parts/home/section-more', null, array( 'url' => $more ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
