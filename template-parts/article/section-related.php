<?php
/**
 * Related articles under a single post.
 *
 * @package Altijara
 *
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$key    = isset( $args['section_key'] ) ? sanitize_key( (string) $args['section_key'] ) : 'article_related';
$fallback = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();

$data = function_exists( 'altijara_resolve_related_section_data' )
	? altijara_resolve_related_section_data( $key, $config, $fallback )
	: array(
		'posts'        => $fallback,
		'initial'      => 2,
		'per_load'     => 2,
		'button_label' => __( 'تحميل المزيد', 'altijara' ),
		'heading'      => __( 'اقرأ ايضاً', 'altijara' ),
	);

$read_also  = $data['posts'];
$initial    = (int) $data['initial'];
$per_load   = (int) $data['per_load'];
$button_label = (string) $data['button_label'];
$heading    = (string) $data['heading'];

if ( ! $read_also ) {
	return;
}

$related_has_more = count( $read_also ) > $initial;
$uid              = $key ? $key : 'article_related';
if ( empty( $GLOBALS['altijara_related_load_more_js'] ) ) {
	$GLOBALS['altijara_related_load_more_js'] = true;
	echo '<script>function altijaraLoadRelated(btn){var section=btn&&btn.closest?btn.closest(".bottom-related-section"):null;if(!section)return;var per=parseInt(section.getAttribute("data-related-per-load"),10);if(!per||per<1)per=2;var cards=section.querySelectorAll(".bottom-news-card.is-related-more");var n=0;for(var i=0;i<cards.length&&n<per;i++){cards[i].classList.remove("is-related-more");cards[i].hidden=false;n++;}if(!section.querySelector(".bottom-news-card.is-related-more")){var wrap=btn.closest(".bottom-section-footer");if(wrap)wrap.hidden=true;}}</script>';
}
?>
<section class="bottom-related-section" data-related-initial="<?php echo esc_attr( (string) $initial ); ?>" data-related-per-load="<?php echo esc_attr( (string) $per_load ); ?>">
	<?php if ( '' !== $heading ) : ?>
		<div class="cat-section-bar cat-mr-bar bottom-section-header">
			<h3 class="cat-section-title"><?php echo esc_html( $heading ); ?></h3>
		</div>
	<?php endif; ?>
	<div class="bottom-cards-list" id="bottomCardsList-<?php echo esc_attr( $uid ); ?>">
		<?php foreach ( $read_also as $idx => $item ) : ?>
			<?php if ( ! $item instanceof WP_Post ) { continue; } ?>
			<article class="bottom-news-card<?php echo $idx >= $initial ? ' is-related-more' : ''; ?>" <?php echo $idx >= $initial ? 'hidden' : ''; ?>>
				<div class="bottom-card-img-wrap">
					<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID ) ); ?>" alt="<?php echo esc_attr( get_the_title( $item ) ); ?>" class="bottom-card-img" loading="lazy">
					<?php if ( function_exists( 'altijara_render_video_play_badge' ) ) { altijara_render_video_play_badge( $item->ID ); } ?>
				</div>
				<div class="bottom-card-body">
					<?php
					$card_cat = function_exists( 'altijara_section_card_category_name' )
						? altijara_section_card_category_name( $item->ID, $config, $key )
						: ( function_exists( 'altijara_get_listing_category_name' )
							? altijara_get_listing_category_name( $item->ID )
							: altijara_get_primary_category_name( $item->ID ) );
					?>
					<span class="bottom-card-kicker"><?php echo esc_html( $card_cat ); ?></span>
					<h4 class="bottom-card-headline">
						<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
					</h4>
					<p class="bottom-card-excerpt"><?php echo esc_html( altijara_trim_at_word( $item->post_excerpt ?: $item->post_content, 140 ) ); ?></p>
					<span class="bottom-card-meta"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
	<?php if ( $related_has_more ) : ?>
		<div class="bottom-section-footer" id="relatedLoadMoreWrap-<?php echo esc_attr( $uid ); ?>">
			<button type="button" class="btn-cat-load-more js-related-load-more" id="relatedLoadMoreBtn-<?php echo esc_attr( $uid ); ?>" onclick="altijaraLoadRelated(this)">
				<?php echo esc_html( $button_label ); ?>
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
			</button>
		</div>
	<?php endif; ?>
</section>
