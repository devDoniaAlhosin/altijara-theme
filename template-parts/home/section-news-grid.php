<?php
/**
 * 4-column news grid (followups / automotive / tech)
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config  = isset( $args['config'] ) ? $args['config'] : array();
$posts   = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key     = isset( $args['section_key'] ) ? $args['section_key'] : 'followups';
$label   = function_exists( 'altijara_section_heading' ) ? altijara_section_heading( $config, '' ) : ( isset( $config['label'] ) ? (string) $config['label'] : '' );
$cat_key = ! empty( $config['category'] ) ? $config['category'] : $key;
$more    = altijara_get_section_archive_url( $cat_key );
$is_issues = ! empty( $config['source'] ) && 'issues' === $config['source'];
$grid_class = 'news-grid-4col' . ( $is_issues ? ' news-grid-4col--issues' : '' );
$fallback   = function_exists( 'altijara_asset' ) ? altijara_asset( 'assets/images/thumb-logo-fallback.png' ) : '';
?>
<?php if ( '' !== $label ) : ?>
<div class="section-bar-wrapper" data-section-bar="<?php echo esc_attr( $key ); ?>">
	<div class="section-bar-badge-right">
		<span class="pill-badge"><?php echo esc_html( $label ); ?></span>
	</div>
</div>
<?php endif; ?>

<section class="<?php echo esc_attr( $grid_class ); ?>" data-wp-cat="<?php echo esc_attr( $cat_key ); ?>" data-section="<?php echo esc_attr( $key ); ?>">
	<?php if ( empty( $posts ) ) : ?>
		<?php
		if ( function_exists( 'altijara_render_section_empty' ) ) {
			altijara_render_section_empty( 'altijara-section-empty--body', $key );
		} else {
			echo '<div class="altijara-section-empty altijara-section-empty--body" role="status"><p>' . esc_html__( 'لا مقالات في هذا القسم بعد', 'altijara' ) . '</p></div>';
		}
		?>
	<?php else : ?>
		<?php foreach ( $posts as $item ) : ?>
			<?php
			$pid       = (int) $item->ID;
			$permalink = get_permalink( $item );
			$is_issue  = $is_issues || ( $item instanceof WP_Post && 'issue' === $item->post_type );
			$href      = $permalink;
			$cover     = '';
			$issue_num = '';
			$badge     = '';

			if ( $is_issue && function_exists( 'altijara_get_issue_meta' ) ) {
				$issue_num = altijara_get_issue_meta( $pid, 'number' );
				$flipbook  = altijara_get_issue_meta( $pid, 'flipbook' );
				if ( $flipbook ) {
					$href = $flipbook;
				}
				$cover = function_exists( 'altijara_resolve_issue_cover_url' )
					? altijara_resolve_issue_cover_url( $pid, $issue_num ? (int) $issue_num : 0 )
					: '';
				if ( ! $cover && function_exists( 'altijara_get_thumb_url' ) ) {
					$cover = altijara_get_thumb_url( $pid );
				}
				if ( ! $cover ) {
					$cover = $fallback;
				}
				$badge = $issue_num
					? sprintf(
						/* translators: %s: magazine issue number */
						__( 'العدد %s', 'altijara' ),
						$issue_num
					)
					: get_the_title( $item );
			}
			?>
			<article class="news-card-v<?php echo $is_issue ? ' news-card-v--issue' : ''; ?>">
				<?php if ( $is_issue ) : ?>
					<a class="card-cover-wrap" href="<?php echo esc_url( $href ); ?>"<?php echo ( $href !== $permalink ) ? ' target="_blank" rel="noopener"' : ''; ?>>
						<img class="card-cover-img" src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $item ) ); ?>" loading="lazy" decoding="async"<?php echo $fallback ? ' onerror="this.src=\'' . esc_url( $fallback ) . '\'"' : ''; ?>>
						<?php if ( $badge ) : ?>
							<span class="card-num-badge"><?php echo esc_html( $badge ); ?></span>
						<?php endif; ?>
					</a>
				<?php else : ?>
					<div class="card-img-wrap">
						<a href="<?php echo esc_url( $permalink ); ?>">
							<?php
							altijara_the_thumb_img(
								$pid,
								'medium_large',
								array(
									'loading'  => 'lazy',
									'decoding' => 'async',
									'sizes'    => '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 25vw',
								)
							);
							?>
						</a>
						<?php
						if ( function_exists( 'altijara_render_video_play_badge' ) ) {
							altijara_render_video_play_badge( $pid );
						}
						?>
					</div>
				<?php endif; ?>
				<?php
				$card_cat = '';
				if ( $is_issue && function_exists( 'altijara_get_issue_meta' ) ) {
					if ( $issue_num ) {
						$card_cat = sprintf(
							/* translators: %s: magazine issue number */
							__( 'العدد %s', 'altijara' ),
							$issue_num
						);
					} else {
						$issue_tag = altijara_get_issue_meta( $pid, 'tag' );
						if ( $issue_tag ) {
							$card_cat = $issue_tag;
						}
					}
				}
				if ( '' === $card_cat ) {
					$card_cat = function_exists( 'altijara_section_card_category_name' )
						? altijara_section_card_category_name( $pid, $config, $key )
						: altijara_get_primary_category_name( $pid );
				}
				?>
				<?php if ( $card_cat && ! $is_issue ) : ?>
				<span class="category-tag"><?php echo esc_html( $card_cat ); ?></span>
				<?php endif; ?>
				<h3 class="card-headline">
					<a href="<?php echo esc_url( $href ); ?>"<?php echo ( $is_issue && $href !== $permalink ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( get_the_title( $item ) ); ?></a>
				</h3>
				<span class="meta-time"><?php echo esc_html( altijara_format_date_ar( $pid ) ); ?></span>
				<div class="card-bullet-sub"><?php echo esc_html( altijara_trim_at_word( $item->post_excerpt ?: $item->post_content, 90 ) ); ?></div>
			</article>
		<?php endforeach; ?>
	<?php endif; ?>
</section>
<?php if ( ! empty( $posts ) && ! $is_issues ) : ?>
<div class="section-footer-more">
	<?php
	get_template_part(
		'template-parts/home/section-more',
		null,
		array( 'url' => $more )
	);
	?>
</div>
<?php endif; ?>
