<?php
/**
 * Single article — layout from article.html, content from WP Loop (SSR)
 *
 * @package Altijara
 */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$post_id          = get_the_ID();
		$article_opts     = function_exists( 'altijara_get_article_settings' ) ? altijara_get_article_settings() : array();
		$related_initial  = isset( $article_opts['related_count'] ) ? max( 1, min( 12, (int) $article_opts['related_count'] ) ) : 2;
		$related_per_load = isset( $article_opts['related_per_load'] ) ? max( 1, min( 12, (int) $article_opts['related_per_load'] ) ) : 2;
		$show_related     = ! isset( $article_opts['show_related'] ) || ! empty( $article_opts['show_related'] );
		$related_pool     = min( 24, $related_initial + ( $related_per_load * 4 ) );

		$latest    = function_exists( 'altijara_get_article_sidebar_posts' )
			? altijara_get_article_sidebar_posts( $post_id, 'latest' )
			: altijara_get_related_posts( $post_id, 5 );
		$most_read = function_exists( 'altijara_get_article_sidebar_posts' )
			? altijara_get_article_sidebar_posts( $post_id, 'most_read' )
			: altijara_query_posts(
				array(
					'posts_per_page' => 7,
					'post__not_in'   => array( $post_id ),
				),
				'most_read'
			);

		$exclude_ids = array_merge( array( $post_id ), wp_list_pluck( $latest, 'ID' ), wp_list_pluck( $most_read, 'ID' ) );
		$read_also   = array();
		if ( $show_related ) {
			$read_also = altijara_get_related_posts( $post_id, $related_pool );
			if ( count( $read_also ) < $related_pool ) {
				$extra     = altijara_query_posts(
					array(
						'posts_per_page' => $related_pool,
						'post__not_in'   => array_merge( $exclude_ids, wp_list_pluck( $read_also, 'ID' ) ),
					),
					'read_also'
				);
				$read_also = array_slice( array_merge( $read_also, $extra ), 0, $related_pool );
			}
		}
		$related_has_more = count( $read_also ) > $related_initial;
		$kicker           = altijara_get_primary_category_name( $post_id );
		?>
<main class="article-page-wrap">
	<div class="article-container">
		<div class="article-grid">

			<article class="article-main-col" id="articleMainCol" <?php post_class(); ?>>
				<h1 class="article-title-main"><?php the_title(); ?></h1>

				<div class="article-featured-hero">
					<?php if ( has_post_thumbnail() ) : ?>
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'altijara-hero' ) ?: get_the_post_thumbnail_url( $post_id, 'full' ) ); ?>"
							alt="<?php echo esc_attr( get_the_title() ); ?>"
							class="article-featured-img">
					<?php endif; ?>
					<?php if ( has_excerpt() ) : ?>
						<div class="article-img-caption"><?php echo esc_html( get_the_excerpt() ); ?></div>
					<?php endif; ?>
				</div>

				<div class="article-meta-toolbar">
					<?php
					if ( function_exists( 'altijara_render_article_share_actions' ) ) {
						altijara_render_article_share_actions( $post_id );
					}
					?>
					<div class="article-meta-info">
						<?php if ( $kicker ) : ?><span class="article-kicker"><?php echo esc_html( $kicker ); ?></span><?php endif; ?>
						<span class="article-date"><?php echo esc_html( altijara_format_date_ar( $post_id ) ); ?></span>
					</div>
				</div>

				<div class="article-body-content" id="articleBodyContent">
					<?php the_content(); ?>
				</div>
			</article>

			<aside class="article-sidebar">
				<div class="sidebar-widget">
					<div class="sidebar-widget-header">
						<span class="pill-badge">أحدث الأخبار</span>
					</div>
					<div class="rec-items-list" id="recItemsList">
						<?php
						foreach ( $latest as $item ) {
							get_template_part( 'template-parts/cards/post', 'card', array(
								'post'    => $item,
								'variant' => 'rec_item',
							) );
						}
						?>
					</div>
				</div>

				<?php
				if ( function_exists( 'altijara_render_sidebar_ad' ) ) {
					altijara_render_sidebar_ad();
				}
				get_template_part(
					'template-parts/widgets/magazine-issues',
					'slider',
					array(
						'items' => function_exists( 'altijara_get_magazine_slider_items' )
							? altijara_get_magazine_slider_items( 8 )
							: array(),
					)
				);
				?>

				<div class="cat-most-read-widget">
					<div class="cat-section-bar cat-mr-bar">
						<h3 class="cat-section-title">الأكثر قراءة</h3>
					</div>
					<ol class="cat-mr-list" id="catMrList">
						<?php foreach ( $most_read as $idx => $item ) : ?>
							<li class="cat-mr-item">
								<span class="cat-mr-num"><?php echo esc_html( (string) ( $idx + 1 ) ); ?></span>
								<div class="cat-mr-content">
									<h4 class="cat-mr-headline">
										<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
									</h4>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>
			</aside>
		</div>

		<?php if ( $show_related && ! empty( $read_also ) ) : ?>
		<section class="bottom-related-section" data-related-initial="<?php echo esc_attr( (string) $related_initial ); ?>" data-related-per-load="<?php echo esc_attr( (string) $related_per_load ); ?>">
			<div class="cat-section-bar cat-mr-bar bottom-section-header">
				<h3 class="cat-section-title">اقرأ ايضاً</h3>
			</div>
			<div class="bottom-cards-list" id="bottomCardsList">
				<?php foreach ( $read_also as $idx => $item ) : ?>
					<article class="bottom-news-card<?php echo $idx >= $related_initial ? ' is-related-more' : ''; ?>" <?php echo $idx >= $related_initial ? 'hidden' : ''; ?>>
						<div class="bottom-card-img-wrap">
							<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID ) ); ?>" alt="<?php echo esc_attr( get_the_title( $item ) ); ?>" class="bottom-card-img" loading="lazy">
						</div>
						<div class="bottom-card-body">
							<span class="bottom-card-kicker"><?php echo esc_html( altijara_get_primary_category_name( $item->ID ) ); ?></span>
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
			<div class="bottom-section-footer" id="relatedLoadMoreWrap">
				<button type="button" class="btn-cat-load-more" id="relatedLoadMoreBtn">
					<span><?php esc_html_e( 'تحميل المزيد', 'altijara' ); ?></span>
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
				</button>
			</div>
			<?php endif; ?>
		</section>
		<?php endif; ?>
	</div>
</main>

<div id="articleToast">تم نسخ الرابط بنجاح!</div>
<script>
function copyArticleLink() {
  navigator.clipboard.writeText(window.location.href).then(function () {
    var t = document.getElementById("articleToast");
    if (!t) return;
    t.textContent = "تم نسخ رابط المقال بنجاح!";
    t.classList.add("show");
    setTimeout(function () { t.classList.remove("show"); }, 2200);
  });
}
</script>
		<?php
	endwhile;
endif;

get_footer();
