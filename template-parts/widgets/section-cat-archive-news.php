<?php
/**
 * Category archive widget: latest news list.
 *
 * Same markup as the category template. category.php does not load this file.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config   = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$posts    = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$sid      = isset( $args['section_key'] ) ? sanitize_key( (string) $args['section_key'] ) : 'cat_archive_news';
$title    = function_exists( 'altijara_section_heading' ) ? altijara_section_heading( $config, __( 'أحدث الأخبار', 'altijara' ) ) : ( isset( $config['label'] ) ? (string) $config['label'] : __( 'أحدث الأخبار', 'altijara' ) );
$fallback = function_exists( 'altijara_asset' ) ? altijara_asset( 'assets/images/thumb-logo-fallback.png' ) : '';
$per_page = isset( $config['count'] ) ? max( 1, (int) $config['count'] ) : 10;
$offset   = isset( $config['offset'] ) ? max( 0, (int) $config['offset'] ) : 0;
$skip     = array_key_exists( 'list_offset', $config ) ? max( 0, (int) $config['list_offset'] ) : $offset;
$clean    = array();
foreach ( $posts as $item ) {
	if ( $item instanceof WP_Post ) {
		$clean[] = $item;
	}
}

$total = count( $clean );
if ( ! empty( $config['manual_posts'] ) && is_array( $config['manual_posts'] ) ) {
	$total = count( array_filter( array_map( 'absint', $config['manual_posts'] ) ) );
} elseif ( function_exists( 'altijara_resolve_section_category_id' ) && function_exists( 'altijara_count_category_posts' ) ) {
	$term_id = altijara_resolve_section_category_id( $config, $sid );
	if ( $term_id ) {
		$total = altijara_count_category_posts( $term_id );
	}
}
$remain      = max( 0, (int) $total - $skip );
$custom_source = ( ! empty( $config['manual_posts'] ) && is_array( $config['manual_posts'] ) )
	|| ( isset( $config['category'] ) && '' !== (string) $config['category'] && '0' !== (string) $config['category'] );
$total_pages = $remain > 0 ? (int) max( 1, (int) ceil( $remain / $per_page ) ) : 1;
$paged       = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$start       = max( 1, min( $paged, $total_pages ) );
$window      = range( 1, min( 7, $total_pages ) );
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
<div class="cat-news-col">
	<div class="cat-section-bar">
		<?php if ( '' !== $title ) : ?>
		<h2 class="cat-section-title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
	</div>
	<div class="cat-news-list" id="catNewsList">
		<?php if ( $clean ) : ?>
			<?php foreach ( $clean as $item ) : ?>
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
			<?php endforeach; ?>
		<?php else : ?>
			<p class="description"><?php esc_html_e( 'لا مزيد من المقالات في هذه الصفحة.', 'altijara' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $total_pages > 1 ) : ?>
	<nav class="cat-pagination-wrap" id="catPagination" aria-label="<?php esc_attr_e( 'ترقيم الصفحات', 'altijara' ); ?>" data-ajax="<?php echo $custom_source ? '0' : '1'; ?>">
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
	<?php endif; ?>
</div>
