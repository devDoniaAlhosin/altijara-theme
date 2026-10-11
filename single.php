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
		$post_id   = get_the_ID();
		$related   = altijara_get_related_posts( $post_id, 7 );
		$latest    = array_slice( $related, 0, 5 );
		$read_also = array_slice( $related, 5, 2 );
		if ( count( $read_also ) < 2 ) {
			$extra     = altijara_query_posts(
				array(
					'posts_per_page' => 2,
					'post__not_in'   => array_merge( array( $post_id ), wp_list_pluck( $related, 'ID' ) ),
				),
				'read_also'
			);
			$read_also = array_slice( array_merge( $read_also, $extra ), 0, 2 );
		}
		$most_read = altijara_query_posts(
			array(
				'posts_per_page' => 7,
				'post__not_in'   => array( $post_id ),
			),
			'most_read'
		);
		$kicker = altijara_get_primary_category_name( $post_id );
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
					<div class="article-share-actions">
						<button type="button" class="btn-share-icon" onclick="copyArticleLink()" title="نسخ الرابط">
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
						</button>
					</div>
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

		<section class="bottom-related-section">
			<div class="cat-section-bar cat-mr-bar bottom-section-header">
				<h3 class="cat-section-title">اقرأ ايضاً</h3>
			</div>
			<div class="bottom-cards-list" id="bottomCardsList">
				<?php foreach ( $read_also as $item ) : ?>
					<article class="bottom-news-card">
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
		</section>
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
