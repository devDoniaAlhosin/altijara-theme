<?php
/**
 * Category archive — per-term custom layout, else site-wide Category Archives modules.
 *
 * @package Altijara
 */
get_header();

$term    = get_queried_object();
$term_id = ( $term && isset( $term->term_id ) ) ? (int) $term->term_id : 0;

// Custom module layout (saved on category term).
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
			if ( function_exists( 'altijara_home_pack_apply_stream_offsets' ) ) {
				$pack = altijara_home_pack_apply_stream_offsets( $pack );
			}
			altijara_render_layout_rows( $pack );
		}
	);
	?>
</main>
	<?php
	get_footer();
	return;
endif;

// Site-wide Category Archives pack (or theme preset when never configured).
if ( function_exists( 'altijara_render_category_archive_page' ) && altijara_render_category_archive_page( $term_id ) ) {
	return;
}

$cat_name = ( $term && ! empty( $term->name ) ) ? $term->name : '';
?>
<main class="container category-main-wrapper" data-altijara-layout="empty">
	<?php if ( $cat_name ) : ?>
		<h1 class="category-title-main"><?php echo esc_html( $cat_name ); ?></h1>
	<?php endif; ?>
	<p class="description"><?php esc_html_e( 'لا مقالات في هذا القسم بعد', 'altijara' ); ?></p>
</main>
<?php
get_footer();
