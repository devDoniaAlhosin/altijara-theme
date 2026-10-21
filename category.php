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

// Custom JNews-style module layout (saved on category term).
if ( $term_id && function_exists( 'altijara_category_has_custom_layout' ) && altijara_category_has_custom_layout( $term_id ) ) :
	$cat_name = ( $term && ! empty( $term->name ) ) ? $term->name : '';
	$pack     = altijara_get_category_layout( $term_id, true );
	?>
<main class="container category-main-wrapper" data-altijara-layout="custom">
	<header class="category-header-wrap">
		<div class="category-title-row">
			<h1 class="category-title-main"><?php echo esc_html( $cat_name ); ?></h1>
		</div>
	</header>
	<?php
	altijara_with_layout_context(
		array(
			'category' => $term_id,
			'surface'  => 'category',
		),
		static function () use ( $pack ) {
			altijara_render_layout_rows( $pack );
		}
	);
	?>
</main>
	<?php
	get_footer();
	return;
endif;

$counts = function_exists( 'altijara_get_archive_counts' )
	? altijara_get_archive_counts()
	: array(
		'sidebar_count'  => 3,
		'featured_rows'  => 2,
		'featured_count' => 8,
		'news_size'      => 10,
	);
$sidebar_n  = (int) $counts['sidebar_count'];
$featured_n = (int) $counts['featured_count'];
$news_size  = (int) $counts['news_size'];

$full_hero = $term_id && function_exists( 'altijara_category_uses_full_hero' ) && altijara_category_uses_full_hero( $term_id );
$rec_n     = $full_hero ? 0 : $sidebar_n;
$spotlight = 1 + $rec_n + $featured_n;

$paged    = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$per_page = $spotlight + $news_size;
$offset   = ( $paged - 1 ) * $per_page;
$posts    = $term_id ? altijara_get_category_posts( $term_id, $per_page, $offset ) : array();
$hero     = ! empty( $posts[0] ) ? $posts[0] : null;
if ( $full_hero ) {
	$sidebar  = array();
	$featured = array_slice( $posts, 1, $featured_n );
	$list     = array_slice( $posts, 1 + $featured_n );
} else {
	$sidebar  = array_slice( $posts, 1, $sidebar_n );
	$featured = array_slice( $posts, 1 + $sidebar_n, $featured_n );
	$list     = array_slice( $posts, $spotlight );
}
$cat_name    = ( $term && ! empty( $term->name ) ) ? $term->name : '';
$fallback    = altijara_asset( 'assets/images/issue-730-f2dfce.jpg' );
$total_posts = ( $term_id && function_exists( 'altijara_count_category_posts' ) ) ? altijara_count_category_posts( $term_id ) : count( $posts );
$news_remain = max( 0, (int) $total_posts - $spotlight );
$total_pages = $news_remain > 0 ? (int) max( 1, (int) ceil( $news_remain / $news_size ) ) : 1;

$cat_tax = $term_id
	? array(
		array(
			'taxonomy'         => 'category',
			'field'            => 'term_id',
			'terms'            => array( $term_id ),
			'include_children' => true,
			'operator'         => 'IN',
		),
	)
	: array();
$most_read = $term_id
	? altijara_query_posts(
		array(
			'posts_per_page' => 7,
			'orderby'        => 'comment_count',
			'order'          => 'DESC',
			'tax_query'      => $cat_tax,
		),
		'cat_mr_v2_' . $term_id
	)
	: array();
if ( count( $most_read ) < 4 && $term_id ) {
	$most_read = altijara_query_posts(
		array(
			'posts_per_page' => 7,
			'tax_query'      => $cat_tax,
		),
		'cat_mr_recent_v2_' . $term_id
	);
}

?>
<main class="container category-main-wrapper">

	<?php if ( $cat_name ) : ?>
		<h1 class="screen-reader-text"><?php echo esc_html( $cat_name ); ?></h1>
	<?php endif; ?>

	<?php
	$show_rec   = ! $full_hero && ! empty( $sidebar );
	$top_classes = 'cat-top-grid';
	if ( $full_hero ) {
		$top_classes .= ' is-hero-full';
	} elseif ( ! $show_rec ) {
		$top_classes .= ' is-hero-solo';
	}
	?>
	<section class="<?php echo esc_attr( $top_classes ); ?>" style="margin-top:20px;">
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

		<?php if ( $show_rec ) : ?>
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

			<?php
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
