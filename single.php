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
		$post_id = get_the_ID();
		?>
<main class="article-page-wrap">
	<div class="article-container">
		<div class="article-grid">

			<article class="article-main-col" id="articleMainCol" <?php post_class(); ?>>
				<h1 class="article-title-main"><?php the_title(); ?></h1>

				<div class="article-featured-hero">
					<?php
					if ( function_exists( 'altijara_render_featured_media' ) ) {
						altijara_render_featured_media( $post_id );
					} elseif ( has_post_thumbnail() ) {
						?>
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'altijara-hero' ) ?: get_the_post_thumbnail_url( $post_id, 'full' ) ); ?>"
							alt="<?php echo esc_attr( get_the_title() ); ?>"
							class="article-featured-img">
						<?php
					}
					?>
					<?php if ( has_excerpt() ) : ?>
						<div class="article-img-caption"><?php echo esc_html( get_the_excerpt() ); ?></div>
					<?php endif; ?>
				</div>

				<div class="article-meta-toolbar">
					<?php
					if ( function_exists( 'altijara_render_article_author_box' ) ) {
						altijara_render_article_author_box( $post_id );
					}
					if ( function_exists( 'altijara_render_article_share_actions' ) ) {
						altijara_render_article_share_actions( $post_id );
					}
					?>
				</div>

				<div class="article-body-content" id="articleBodyContent">
					<?php the_content(); ?>
				</div>

				<?php
				if ( function_exists( 'altijara_render_article_tags' ) ) {
					altijara_render_article_tags( $post_id );
				}
				?>
			</article>

			<?php
			if ( function_exists( 'altijara_render_article_sidebar' ) ) {
				altijara_render_article_sidebar( $post_id );
			}
			?>
		</div>

		<?php
		if ( function_exists( 'altijara_render_article_modules' ) ) {
			altijara_render_article_modules( $post_id );
		}
		?>
	</div>
</main>

<div id="articleToast">تم نسخ الرابط بنجاح!</div>
<script>
function copyArticleLink(message) {
  navigator.clipboard.writeText(window.location.href).then(function () {
    var t = document.getElementById("articleToast");
    if (!t) return;
    t.textContent = message || "تم نسخ رابط المقال بنجاح!";
    t.classList.add("show");
    setTimeout(function () { t.classList.remove("show"); }, 2200);
  });
}
document.addEventListener("click", function (e) {
  var btn = e.target.closest("[data-share-native]");
  if (!btn) return;
  var url = btn.getAttribute("data-share-url") || window.location.href;
  var title = btn.getAttribute("data-share-title") || document.title;
  if (navigator.share) {
    navigator.share({ title: title, text: title, url: url }).catch(function () {});
    return;
  }
  copyArticleLink("المشاركة من الجهاز غير متاحة، تم نسخ الرابط");
});
</script>
		<?php
	endwhile;
endif;

get_footer();
