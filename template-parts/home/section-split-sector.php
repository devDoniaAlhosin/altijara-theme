<?php
/**
 * Single sector column (lead + sub cards) — used inside split grids
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config = isset( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key    = isset( $args['section_key'] ) ? $args['section_key'] : 'business';
$label  = function_exists( 'altijara_section_heading' ) ? altijara_section_heading( $config, '' ) : ( isset( $config['label'] ) ? (string) $config['label'] : '' );
$cat_key = ! empty( $config['category'] ) ? $config['category'] : $key;
$more   = altijara_get_section_archive_url( $cat_key );
$lead   = ! empty( $posts[0] ) ? $posts[0] : null;
$subs   = array_slice( $posts, 1 );
?>
<div class="sector-col" data-wp-cat="<?php echo esc_attr( $cat_key ); ?>" data-section="<?php echo esc_attr( $key ); ?>">
	<?php if ( '' !== $label ) : ?>
	<div class="sector-header-bar">
		<span class="pill-badge"><?php echo esc_html( $label ); ?></span>
	</div>
	<?php endif; ?>

	<?php if ( empty( $posts ) ) : ?>
		<?php
		if ( function_exists( 'altijara_render_section_empty' ) ) {
			altijara_render_section_empty( 'altijara-section-empty--body', $key );
		} else {
			echo '<div class="altijara-section-empty altijara-section-empty--body" role="status"><p>' . esc_html__( 'لا مقالات في هذا القسم بعد', 'altijara' ) . '</p></div>';
		}
		?>
	<?php else : ?>
		<?php if ( $lead ) : ?>
			<article class="sector-lead-card">
				<div class="sector-lead-img-wrap">
					<a href="<?php echo esc_url( get_permalink( $lead ) ); ?>">
						<?php
						altijara_the_thumb_img(
							$lead->ID,
							'medium_large',
							array(
								'loading'  => 'lazy',
								'decoding' => 'async',
								'sizes'    => '(max-width: 960px) 100vw, 44vw',
							)
						);
						?>
					</a>
					<?php
					if ( function_exists( 'altijara_render_video_play_badge' ) ) {
						altijara_render_video_play_badge( $lead->ID );
					}
					?>
				</div>
				<div class="sector-lead-content">
					<h4 class="sector-lead-title">
						<a href="<?php echo esc_url( get_permalink( $lead ) ); ?>"><?php echo esc_html( get_the_title( $lead ) ); ?></a>
					</h4>
					<p class="sector-lead-desc"><?php echo esc_html( altijara_trim_at_word( $lead->post_excerpt ?: $lead->post_content, 140 ) ); ?></p>
					<div class="sector-meta-line">
						<span class="sector-meta-date"><?php echo esc_html( altijara_format_date_ar( $lead->ID ) ); ?></span>
					</div>
				</div>
			</article>
		<?php endif; ?>

		<?php foreach ( $subs as $item ) : ?>
			<article class="sector-sub-card">
				<div class="sector-sub-content">
					<h5 class="sector-sub-title">
						<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
					</h5>
					<div class="sector-meta-line">
						<span class="sector-meta-author"><?php bloginfo( 'name' ); ?></span>
						<span class="sector-meta-date"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
					</div>
				</div>
				<div class="sector-sub-thumb">
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>">
						<?php
						altijara_the_thumb_img(
							$item->ID,
							'medium',
							array(
								'loading'  => 'lazy',
								'decoding' => 'async',
								'sizes'    => '104px',
							)
						);
						?>
					</a>
				</div>
			</article>
		<?php endforeach; ?>

		<div class="sector-footer-more">
			<?php
			get_template_part(
				'template-parts/home/section-more',
				null,
				array( 'url' => $more )
			);
			?>
		</div>
	<?php endif; ?>
</div>
