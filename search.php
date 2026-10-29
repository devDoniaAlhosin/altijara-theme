<?php
/**
 * Search results — category-style news list + sidebar, with live search.
 *
 * @package Altijara
 */

get_header();

$query      = get_search_query();
$fallback   = function_exists( 'altijara_asset' ) ? altijara_asset( 'assets/images/thumb-logo-fallback.png' ) : '';
$counts     = function_exists( 'altijara_get_archive_counts' )
	? altijara_get_archive_counts()
	: array( 'news_size' => 10 );
$per_page   = max( 1, (int) ( $counts['news_size'] ?? 10 ) );
$paged      = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$total      = (int) $GLOBALS['wp_query']->found_posts;
$total_pages = $total > 0 ? (int) max( 1, (int) ceil( $total / $per_page ) ) : 1;
$start      = max( 1, min( $paged, $total_pages ) );

$sidebar = function_exists( 'altijara_get_search_sidebar_layout' )
	? altijara_get_search_sidebar_layout()
	: array(
		'visible'  => false,
		'sections' => array(),
		'order'    => array(),
	);
$side_on = function_exists( 'altijara_category_sidebar_has_widgets' )
	? altijara_category_sidebar_has_widgets( $sidebar )
	: ( ! empty( $sidebar['visible'] ) && ! empty( $sidebar['sections'] ) && ! empty( $sidebar['order'] ) );

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
?>
<main class="container category-main-wrapper search-archive-wrapper" data-altijara-layout="search">
	<header class="search-archive-header">
		<h1 class="search-archive-title" id="searchArchiveTitle">
			<?php
			if ( '' !== $query ) {
				printf(
					/* translators: %s: search query */
					esc_html__( 'نتائج البحث لـ “%s”', 'altijara' ),
					esc_html( $query )
				);
			} else {
				esc_html_e( 'البحث في الأرشيف', 'altijara' );
			}
			?>
		</h1>
	</header>

	<section class="cat-editorial-lower-grid<?php echo $side_on ? '' : ' is-sidebar-hidden'; ?>">
		<div class="cat-news-col">
			<div class="cat-section-bar">
				<h2 class="cat-section-title"><?php esc_html_e( 'نتائج البحث', 'altijara' ); ?></h2>
			</div>

			<form class="search-archive-form" id="searchArchiveForm" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="searchArchiveInput"><?php esc_html_e( 'ابحث في المقالات', 'altijara' ); ?></label>
				<input
					type="search"
					id="searchArchiveInput"
					class="search-archive-input"
					name="s"
					value="<?php echo esc_attr( $query ); ?>"
					placeholder="<?php esc_attr_e( 'ابحث بالعنوان، أو جزء منه، أو رقم المقال أو العدد…', 'altijara' ); ?>"
					autocomplete="off"
					enterkeyhint="search"
				>
				<button type="submit" class="search-archive-submit" aria-label="<?php esc_attr_e( 'بحث', 'altijara' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
				</button>
			</form>

			<p class="search-archive-meta" id="searchArchiveMeta" <?php echo $query ? '' : 'hidden'; ?>>
				<?php
				if ( $query ) {
					printf(
						/* translators: 1: result count, 2: query */
						esc_html( _n( '%1$s نتيجة لـ “%2$s”', '%1$s نتيجة لـ “%2$s”', $total, 'altijara' ) ),
						esc_html( number_format_i18n( $total ) ),
						esc_html( $query )
					);
				}
				?>
			</p>

			<div class="cat-news-list" id="catNewsList" aria-live="polite">
				<?php if ( have_posts() ) : ?>
					<?php
					while ( have_posts() ) :
						the_post();
						$item = get_post();
						if ( ! $item instanceof WP_Post ) {
							continue;
						}
						?>
						<article class="cat-news-item">
							<div class="cat-news-img-wrap">
								<a href="<?php echo esc_url( get_permalink( $item ) ); ?>">
									<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID, 'medium' ) ?: $fallback ); ?>" alt="<?php echo esc_attr( html_entity_decode( get_the_title( $item ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ) ); ?>" loading="lazy">
								</a>
							</div>
							<div class="cat-news-content">
								<h3 class="cat-news-headline">
									<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( html_entity_decode( get_the_title( $item ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ) ); ?></a>
								</h3>
								<p class="cat-news-excerpt"><?php echo esc_html( altijara_trim_at_word( get_the_excerpt( $item ) ?: $item->post_content, 140 ) ); ?></p>
								<div class="cat-news-meta-row">
									<div class="cat-news-tags-group">
										<span class="cat-news-time"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
									</div>
									<?php
									if ( function_exists( 'altijara_render_save_button' ) ) {
										altijara_render_save_button( $item->ID );
									}
									?>
								</div>
							</div>
						</article>
					<?php endwhile; ?>
				<?php else : ?>
					<p class="search-archive-empty" id="searchArchiveEmpty"><?php esc_html_e( 'لا توجد نتائج مطابقة. جرّب كلمات أخرى.', 'altijara' ); ?></p>
				<?php endif; ?>
			</div>

			<nav class="cat-pagination-wrap" id="catPagination" aria-label="<?php esc_attr_e( 'ترقيم الصفحات', 'altijara' ); ?>" <?php echo $total_pages <= 1 ? 'hidden' : ''; ?> data-ajax="1">
				<button type="button" class="cat-page-btn cat-page-prev" id="catPagePrev" aria-label="<?php esc_attr_e( 'الصفحة السابقة', 'altijara' ); ?>" <?php disabled( $paged <= 1 ); ?>><?php echo altijara_chevron_svg( 'left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></button>
				<div class="cat-page-numbers" id="catPageNumbers">
					<?php foreach ( $window as $page_item ) : ?>
						<?php if ( '…' === $page_item ) : ?>
							<?php echo '<span class="cat-page-ellipsis">…</span>'; ?>
							<?php continue; ?>
						<?php endif; ?>
						<?php $is_active = ( (int) $page_item === (int) $start ); ?>
						<button type="button" class="cat-page-num<?php echo $is_active ? ' is-active' : ''; ?>" data-page="<?php echo esc_attr( (string) $page_item ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'صفحة %d', 'altijara' ), (int) $page_item ) ); ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?>><?php echo esc_html( (string) $page_item ); ?></button>
					<?php endforeach; ?>
				</div>
				<button type="button" class="cat-page-btn cat-page-next" id="catPageNext" aria-label="<?php esc_attr_e( 'الصفحة التالية', 'altijara' ); ?>" <?php disabled( $paged >= $total_pages ); ?>><?php echo altijara_chevron_svg( 'right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></button>
			</nav>
		</div>

		<?php
		if ( $side_on && function_exists( 'altijara_render_category_sidebar_column' ) ) {
			$render_side = static function () use ( $sidebar ) {
				altijara_render_category_sidebar_column( $sidebar );
			};
			if ( function_exists( 'altijara_with_layout_context' ) ) {
				altijara_with_layout_context(
					array(
						'category' => 0,
						'surface'  => 'search',
					),
					$render_side
				);
			} else {
				$render_side();
			}
		}
		?>
	</section>
</main>
<?php
get_footer();
