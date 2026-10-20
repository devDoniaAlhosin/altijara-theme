<?php
/**
 * Category archive — posts layout (SSR) or issues layout when assigned in Archives settings.
 *
 * @package Altijara
 */
get_header();

$term    = get_queried_object();
$term_id = ( $term && isset( $term->term_id ) ) ? (int) $term->term_id : 0;
$layout  = function_exists( 'altijara_get_category_archive_layout' )
	? altijara_get_category_archive_layout( $term_id )
	: 'posts';

if ( 'issues' === $layout && function_exists( 'altijara_render_issues_archive' ) ) :
	?>
<main class="container issues-main-wrapper">
	<?php altijara_render_issues_archive(); ?>
</main>
	<?php
	get_footer();
	return;
endif;

$paged    = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$per_page = 20;
$offset   = ( $paged - 1 ) * $per_page;
$posts    = $term_id ? altijara_get_category_posts( $term_id, $per_page, $offset ) : array();
$full_hero = $term_id && function_exists( 'altijara_category_uses_full_hero' ) && altijara_category_uses_full_hero( $term_id );
$hero      = ! empty( $posts[0] ) ? $posts[0] : null;
$spotlight = $full_hero ? 9 : 12; // 1 hero (+ optional 3 rec) + 8 featured
$news_size = 10;
if ( $full_hero ) {
	$sidebar  = array();
	$featured = array_slice( $posts, 1, 8 );
	$list     = array_slice( $posts, 9 );
} else {
	$sidebar  = array_slice( $posts, 1, 3 );
	$featured = array_slice( $posts, 4, 8 );
	$list     = array_slice( $posts, 12 );
}
$cat_name    = ( $term && ! empty( $term->name ) ) ? $term->name : '';
$fallback    = altijara_asset( 'assets/images/issue-730-f2dfce.jpg' );
$total_posts = ( $term_id && function_exists( 'altijara_count_category_posts' ) ) ? altijara_count_category_posts( $term_id ) : count( $posts );
$news_remain = max( 0, (int) $total_posts - $spotlight );
$total_pages = $news_remain > 0 ? (int) max( 1, (int) ceil( $news_remain / $news_size ) ) : 1;

$most_read = $term_id
	? altijara_query_posts(
		array(
			'cat'            => $term_id,
			'posts_per_page' => 7,
			'orderby'        => 'comment_count',
			'order'          => 'DESC',
		),
		'cat_mr_' . $term_id
	)
	: array();
if ( count( $most_read ) < 4 && $term_id ) {
	$most_read = altijara_query_posts(
		array(
			'cat'            => $term_id,
			'posts_per_page' => 7,
		),
		'cat_mr_recent_' . $term_id
	);
}

$issue_posts = function_exists( 'altijara_get_issue_posts' ) ? altijara_get_issue_posts( 6, 0 ) : array();
$lead_issue  = ! empty( $issue_posts[0] ) ? $issue_posts[0] : null;
?>
<main class="container category-main-wrapper">

	<?php if ( $cat_name ) : ?>
		<h1 class="screen-reader-text"><?php echo esc_html( $cat_name ); ?></h1>
	<?php endif; ?>

	<section class="cat-top-grid<?php echo $full_hero ? ' is-hero-full' : ''; ?>" style="margin-top:20px;">
		<?php if ( $hero instanceof WP_Post ) : ?>
			<article class="cat-hero-card" id="catHeroCard">
				<div class="cat-hero-info">
					<h2 class="cat-hero-headline">
						<a href="<?php echo esc_url( get_permalink( $hero ) ); ?>"><?php echo esc_html( get_the_title( $hero ) ); ?></a>
					</h2>
					<?php if ( get_the_excerpt( $hero ) ) : ?>
						<p class="cat-hero-synopsis"><?php echo esc_html( get_the_excerpt( $hero ) ); ?></p>
					<?php endif; ?>
					<div class="cat-hero-meta">
						<span class="lead-time"><?php echo esc_html( altijara_format_date_ar( $hero->ID ) ); ?></span>
					</div>
				</div>
				<div class="cat-hero-img-wrap">
					<a href="<?php echo esc_url( get_permalink( $hero ) ); ?>">
						<img src="<?php echo esc_url( altijara_get_thumb_url( $hero->ID ) ?: $fallback ); ?>" alt="<?php echo esc_attr( get_the_title( $hero ) ); ?>" loading="eager">
					</a>
				</div>
			</article>
		<?php else : ?>
			<article class="cat-hero-card">
				<div class="cat-hero-info">
					<h2 class="cat-hero-headline"><?php esc_html_e( 'لا مقالات في هذا القسم بعد', 'altijara' ); ?></h2>
				</div>
			</article>
		<?php endif; ?>

		<?php if ( ! $full_hero ) : ?>
			<aside class="cat-recommended-col">
				<div class="cat-rec-list" id="recList">
					<?php foreach ( $sidebar as $item ) : ?>
						<?php if ( ! $item instanceof WP_Post ) { continue; } ?>
						<div class="cat-rec-item">
							<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID, 'medium' ) ?: $fallback ); ?>" alt="" class="cat-rec-img" loading="lazy">
							<div class="cat-rec-content">
								<h4 class="cat-rec-headline">
									<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
								</h4>
								<span class="cat-rec-time"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</aside>
		<?php endif; ?>
	</section>

	<?php if ( ! empty( $featured ) ) : ?>
		<section class="cat-featured-row-4col">
			<?php foreach ( $featured as $item ) : ?>
				<?php if ( ! $item instanceof WP_Post ) { continue; } ?>
				<article class="cat-featured-card">
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>" class="cat-featured-img-link">
						<div class="cat-featured-img-wrap">
							<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID, 'medium' ) ?: $fallback ); ?>" alt="<?php echo esc_attr( get_the_title( $item ) ); ?>" loading="lazy">
						</div>
					</a>
					<div class="cat-featured-body">
						<h3 class="cat-featured-title">
							<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
						</h3>
						<span class="cat-featured-date"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
					</div>
				</article>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<section class="cat-editorial-lower-grid">
		<div class="cat-news-col">
			<div class="cat-section-bar">
				<h2 class="cat-section-title"><?php esc_html_e( 'أحدث الأخبار', 'altijara' ); ?></h2>
			</div>
			<div class="cat-news-list" id="catNewsList">
				<?php if ( ! empty( $list ) ) : ?>
					<?php foreach ( $list as $item ) : ?>
						<?php if ( ! $item instanceof WP_Post ) { continue; } ?>
						<article class="cat-news-item">
							<div class="cat-news-img-wrap">
								<a href="<?php echo esc_url( get_permalink( $item ) ); ?>">
									<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID, 'medium' ) ?: $fallback ); ?>" alt="<?php echo esc_attr( get_the_title( $item ) ); ?>" loading="lazy">
								</a>
							</div>
							<div class="cat-news-content">
								<h3 class="cat-news-headline">
									<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
								</h3>
								<p class="cat-news-excerpt"><?php echo esc_html( altijara_trim_at_word( get_the_excerpt( $item ) ?: $item->post_content, 140 ) ); ?></p>
								<div class="cat-news-meta-row">
									<div class="cat-news-tags-group">
										<span class="cat-news-time"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
									</div>
									<button type="button" class="btn-cat-save" onclick="typeof toggleSaveArticle==='function'&&toggleSaveArticle(this)" title="<?php esc_attr_e( 'حفظ المقال', 'altijara' ); ?>">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
										<span><?php esc_html_e( 'حفظ', 'altijara' ); ?></span>
									</button>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				<?php elseif ( $hero instanceof WP_Post ) : ?>
					<p class="description"><?php esc_html_e( 'لا مزيد من المقالات في هذه الصفحة.', 'altijara' ); ?></p>
				<?php endif; ?>
			</div>

			<nav class="cat-pagination-wrap" id="catPagination" aria-label="<?php esc_attr_e( 'ترقيم الصفحات', 'altijara' ); ?>" <?php echo $total_pages <= 1 ? 'hidden' : ''; ?> data-ajax="1">
				<button type="button" class="cat-page-btn cat-page-prev" id="catPagePrev" aria-label="<?php esc_attr_e( 'الصفحة السابقة', 'altijara' ); ?>" <?php disabled( $paged <= 1 ); ?>><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
				<div class="cat-page-numbers" id="catPageNumbers">
					<?php
					$start = max( 1, min( $paged, $total_pages ) );
					$window = range( 1, min( 7, $total_pages ) );
					if ( $total_pages > 7 ) {
						$lo     = max( 2, $start - 1 );
						$hi     = min( $total_pages - 1, $start + 1 );
						$window = array( 1 );
						if ( $lo > 2 ) {
							$window[] = '…';
						}
						for ( $i = $lo; $i <= $hi; $i++ ) {
							$window[] = $i;
						}
						if ( $hi < $total_pages - 1 ) {
							$window[] = '…';
						}
						$window[] = $total_pages;
					}
					foreach ( $window as $item ) :
						if ( '…' === $item ) :
							echo '<span class="cat-page-ellipsis">…</span>';
							continue;
						endif;
						$is_active = ( (int) $item === (int) $start );
						?>
						<button type="button" class="cat-page-num<?php echo $is_active ? ' is-active' : ''; ?>" data-page="<?php echo esc_attr( (string) $item ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'صفحة %d', 'altijara' ), (int) $item ) ); ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?>><?php echo esc_html( (string) $item ); ?></button>
					<?php endforeach; ?>
				</div>
				<button type="button" class="cat-page-btn cat-page-next" id="catPageNext" aria-label="<?php esc_attr_e( 'الصفحة التالية', 'altijara' ); ?>" <?php disabled( $paged >= $total_pages ); ?>><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
			</nav>
		</div>

		<aside class="cat-opinion-col">
			<?php altijara_render_sidebar_ad(); ?>

			<div class="cat-new-issue-widget">
				<div class="cat-new-issue-bar">
					<h3 class="cat-section-title"><?php esc_html_e( 'من إصدارات المجلة', 'altijara' ); ?></h3>
					<div class="cat-new-issue-arrows" <?php echo count( $issue_posts ) < 2 ? 'hidden' : ''; ?>>
						<button type="button" class="cat-new-issue-arrow cat-new-issue-arrow--prev" aria-label="<?php esc_attr_e( 'السابق', 'altijara' ); ?>"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
						<button type="button" class="cat-new-issue-arrow cat-new-issue-arrow--next is-accent" aria-label="<?php esc_attr_e( 'التالي', 'altijara' ); ?>"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
					</div>
				</div>
				<div class="cat-new-issue-list" id="catNewIssueList">
					<?php if ( $lead_issue instanceof WP_Post ) : ?>
						<?php
						$payload = function_exists( 'altijara_issue_to_js_payload' ) ? altijara_issue_to_js_payload( $lead_issue ) : array();
						$cover   = ! empty( $payload['cover'] ) ? $payload['cover'] : ( altijara_get_thumb_url( $lead_issue->ID ) ?: $fallback );
						$href    = ! empty( $payload['flipbook'] ) ? $payload['flipbook'] : get_permalink( $lead_issue );
						$title   = ! empty( $payload['title'] ) ? $payload['title'] : get_the_title( $lead_issue );
						?>
						<a href="<?php echo esc_url( $href ); ?>" class="cat-new-issue-cover" target="_blank" rel="noopener">
							<span class="sk sk-cover" aria-hidden="true"></span>
							<img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="eager" decoding="async" onload="this.parentElement.classList.add('is-ready')">
						</a>
					<?php else : ?>
						<div class="cat-new-issue-cover" aria-hidden="true">
							<span class="sk sk-cover"></span>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="cat-most-read-widget">
				<div class="cat-section-bar cat-mr-bar">
					<h3 class="cat-section-title"><?php esc_html_e( 'الأكثر قراءة', 'altijara' ); ?></h3>
				</div>
				<ol class="cat-mr-list" id="catMrList">
					<?php foreach ( $most_read as $idx => $item ) : ?>
						<?php if ( ! $item instanceof WP_Post ) { continue; } ?>
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
	</section>

</main>
<?php
get_footer();
