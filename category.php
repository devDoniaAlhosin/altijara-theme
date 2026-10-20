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

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$per_page = 20;
$offset   = ( $paged - 1 ) * $per_page;
$posts    = $term_id ? altijara_get_category_posts( $term_id, $per_page, $offset ) : array();
$hero     = ! empty( $posts[0] ) ? $posts[0] : null;
$sidebar  = array_slice( $posts, 1, 3 );
$featured = array_slice( $posts, 4, 8 );
$list     = array_slice( $posts, 12 );
$cat_name = ( $term && ! empty( $term->name ) ) ? $term->name : '';
$fallback = altijara_asset( 'assets/images/issue-730-f2dfce.jpg' );
?>
<main class="container category-main-wrapper">

	<?php if ( $cat_name ) : ?>
		<header class="cat-archive-header" style="margin-top:20px;margin-bottom:8px;">
			<h1 class="section-ft-heading" style="margin:0;"><?php echo esc_html( $cat_name ); ?></h1>
			<?php if ( ! empty( $term->description ) ) : ?>
				<p class="description" style="margin:8px 0 0;"><?php echo esc_html( $term->description ); ?></p>
			<?php endif; ?>
		</header>
	<?php endif; ?>

	<section class="cat-top-grid">
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
						<span class="cat-featured-tag"><?php echo esc_html( $cat_name ); ?></span>
						<h3 class="cat-featured-title">
							<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
						</h3>
						<span class="cat-featured-date"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
					</div>
				</article>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $list ) ) : ?>
		<section class="cat-list-section" style="margin:28px 0 16px;">
			<?php foreach ( $list as $item ) : ?>
				<?php if ( ! $item instanceof WP_Post ) { continue; } ?>
				<article class="cat-rec-item" style="margin-bottom:14px;">
					<img src="<?php echo esc_url( altijara_get_thumb_url( $item->ID, 'thumbnail' ) ?: $fallback ); ?>" alt="" class="cat-rec-img" loading="lazy">
					<div class="cat-rec-content">
						<h3 class="cat-rec-headline" style="font-size:1.05rem;">
							<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
						</h3>
						<span class="cat-rec-time"><?php echo esc_html( altijara_format_date_ar( $item->ID ) ); ?></span>
					</div>
				</article>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<?php if ( $paged > 1 || count( $posts ) >= $per_page ) : ?>
		<nav class="cat-pagination" style="margin:24px 0 40px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
			<?php if ( $paged > 1 ) : ?>
				<a class="static-btn static-btn-ghost" href="<?php echo esc_url( get_pagenum_link( $paged - 1 ) ); ?>"><?php esc_html_e( 'الأحدث', 'altijara' ); ?></a>
			<?php endif; ?>
			<?php if ( count( $posts ) >= $per_page ) : ?>
				<a class="static-btn static-btn-ghost" href="<?php echo esc_url( get_pagenum_link( $paged + 1 ) ); ?>"><?php esc_html_e( 'الأقدم', 'altijara' ); ?></a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>

</main>
<?php
get_footer();
