<?php
/**
 * Author profile — avatar, bio, verified badge, and full article archive.
 *
 * @package Altijara
 */

get_header();

$author    = get_queried_object();
$author_id = ( $author instanceof WP_User ) ? (int) $author->ID : (int) get_query_var( 'author' );
if ( ! $author instanceof WP_User && $author_id ) {
	$author = get_userdata( $author_id );
}
$name = ( $author instanceof WP_User )
	? ( $author->display_name ? $author->display_name : $author->user_login )
	: '';
$bio = ( $author instanceof WP_User ) ? trim( (string) $author->description ) : '';
$fallback = function_exists( 'altijara_asset' ) ? altijara_asset( 'assets/images/thumb-logo-fallback.png' ) : '';
$counts   = function_exists( 'altijara_get_archive_counts' )
	? altijara_get_archive_counts()
	: array( 'news_size' => 10 );
$per_page    = max( 1, (int) ( $counts['news_size'] ?? 10 ) );
$paged       = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$total       = (int) $GLOBALS['wp_query']->found_posts;
$total_pages = $total > 0 ? (int) max( 1, (int) ceil( $total / $per_page ) ) : 1;
$start       = max( 1, min( $paged, $total_pages ) );

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

$avatar = $author_id
	? get_avatar(
		$author_id,
		160,
		'',
		$name,
		array(
			'class'   => 'author-profile-avatar',
			'loading' => 'eager',
		)
	)
	: '';
$verified = $author_id && function_exists( 'altijara_author_is_verified' ) && altijara_author_is_verified( $author_id );
?>
<main class="container category-main-wrapper author-archive-wrapper" data-altijara-layout="author">
	<header class="author-profile-header">
		<div class="author-profile-identity">
			<?php if ( $avatar ) : ?>
				<div class="author-profile-avatar-wrap">
					<?php echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar is safe HTML ?>
				</div>
			<?php endif; ?>
			<div class="author-profile-meta">
				<h1 class="author-profile-name"><?php echo esc_html( $name ); ?></h1>
				<?php
				if ( $verified && function_exists( 'altijara_render_author_badge' ) ) {
					altijara_render_author_badge( $author_id );
				}
				?>
				<?php if ( $bio ) : ?>
					<p class="author-profile-bio"><?php echo esc_html( $bio ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<section class="cat-editorial-lower-grid is-sidebar-hidden">
		<div class="cat-news-col">
			<div class="cat-section-bar">
				<h2 class="cat-section-title"><?php esc_html_e( 'مقالات الكاتب', 'altijara' ); ?></h2>
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
					<p class="search-archive-empty"><?php esc_html_e( 'لا توجد مقالات لهذا الكاتب بعد.', 'altijara' ); ?></p>
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
	</section>
</main>
<?php
get_footer();
