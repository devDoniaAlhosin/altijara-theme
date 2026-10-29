<?php
/**
 * Tag archive — same list and sidebar as search, for every post with this tag.
 *
 * @package Altijara
 */

get_header();

$term     = get_queried_object();
$tag_name = ( $term instanceof WP_Term ) ? $term->name : single_tag_title( '', false );
$fallback = function_exists( 'altijara_asset' ) ? altijara_asset( 'assets/images/thumb-logo-fallback.png' ) : '';
$counts   = function_exists( 'altijara_get_archive_counts' )
	? altijara_get_archive_counts()
	: array( 'news_size' => 10 );
$per_page    = max( 1, (int) ( $counts['news_size'] ?? 10 ) );
$paged       = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$total       = (int) $GLOBALS['wp_query']->found_posts;
$total_pages = $total > 0 ? (int) max( 1, (int) ceil( $total / $per_page ) ) : 1;
$start       = max( 1, min( $paged, $total_pages ) );

$sidebar = function_exists( 'altijara_get_tag_sidebar_layout' )
	? altijara_get_tag_sidebar_layout()
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
<main class="container category-main-wrapper search-archive-wrapper tag-archive-wrapper" data-altijara-layout="tag">
	<header class="search-archive-header">
		<h1 class="search-archive-title">
			<?php
			printf(
				/* translators: %s: tag name */
				esc_html__( 'مقالات الوسم “%s”', 'altijara' ),
				esc_html( $tag_name )
			);
			?>
		</h1>
	</header>

	<section class="cat-editorial-lower-grid<?php echo $side_on ? '' : ' is-sidebar-hidden'; ?>">
		<div class="cat-news-col">
			<div class="cat-section-bar">
				<h2 class="cat-section-title"><?php echo esc_html( $tag_name ); ?></h2>
			</div>

			<p class="search-archive-meta">
				<?php
				printf(
					/* translators: %s: result count */
					esc_html( _n( '%s مقال', '%s مقال', $total, 'altijara' ) ),
					esc_html( number_format_i18n( $total ) )
				);
				?>
			</p>

			<div class="cat-news-list">
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
					<p class="search-archive-empty"><?php esc_html_e( 'لا توجد مقالات بهذا الوسم.', 'altijara' ); ?></p>
				<?php endif; ?>
			</div>

			<nav class="cat-pagination-wrap" aria-label="<?php esc_attr_e( 'ترقيم الصفحات', 'altijara' ); ?>" <?php echo $total_pages <= 1 ? 'hidden' : ''; ?> data-ajax="0">
				<?php if ( $paged > 1 ) : ?>
					<a class="cat-page-btn cat-page-prev" href="<?php echo esc_url( get_pagenum_link( $paged - 1 ) ); ?>" aria-label="<?php esc_attr_e( 'الصفحة السابقة', 'altijara' ); ?>"><?php echo altijara_chevron_svg( 'left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></a>
				<?php else : ?>
					<span class="cat-page-btn cat-page-prev" aria-disabled="true"><?php echo altijara_chevron_svg( 'left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
				<?php endif; ?>
				<div class="cat-page-numbers">
					<?php foreach ( $window as $page_item ) : ?>
						<?php if ( '…' === $page_item ) : ?>
							<?php echo '<span class="cat-page-ellipsis">…</span>'; ?>
							<?php continue; ?>
						<?php endif; ?>
						<?php $is_active = ( (int) $page_item === (int) $start ); ?>
						<?php if ( $is_active ) : ?>
							<span class="cat-page-num is-active" aria-current="page"><?php echo esc_html( (string) $page_item ); ?></span>
						<?php else : ?>
							<a class="cat-page-num" href="<?php echo esc_url( get_pagenum_link( (int) $page_item ) ); ?>"><?php echo esc_html( (string) $page_item ); ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<?php if ( $paged < $total_pages ) : ?>
					<a class="cat-page-btn cat-page-next" href="<?php echo esc_url( get_pagenum_link( $paged + 1 ) ); ?>" aria-label="<?php esc_attr_e( 'الصفحة التالية', 'altijara' ); ?>"><?php echo altijara_chevron_svg( 'right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></a>
				<?php else : ?>
					<span class="cat-page-btn cat-page-next" aria-disabled="true"><?php echo altijara_chevron_svg( 'right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?></span>
				<?php endif; ?>
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
						'surface'  => 'tag',
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
