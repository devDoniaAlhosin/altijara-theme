<?php
/**
 * Lower sidebar widget: most-read list.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$title  = function_exists( 'altijara_section_heading' ) ? altijara_section_heading( $config, __( 'الأكثر قراءة', 'altijara' ) ) : ( isset( $config['label'] ) ? (string) $config['label'] : __( 'الأكثر قراءة', 'altijara' ) );
$explicit_cat = isset( $config['category'] ) ? trim( (string) $config['category'] ) : '';
$manual_pick  = ! empty( $config['manual_posts'] ) && is_array( $config['manual_posts'] );
// Preserve SSR when an explicit source was chosen so page JS cannot replace it with the current archive category.
$preserve_ssr = ( '' !== $explicit_cat || $manual_pick );
$cat_attr     = 0;
if ( '' !== $explicit_cat ) {
	$cat_attr = is_numeric( $explicit_cat )
		? (int) $explicit_cat
		: ( function_exists( 'altijara_resolve_category_id' ) ? (int) altijara_resolve_category_id( $explicit_cat ) : 0 );
}
?>
<div class="cat-most-read-widget">
	<?php if ( '' !== $title ) : ?>
	<div class="cat-section-bar cat-mr-bar">
		<h3 class="cat-section-title"><?php echo esc_html( $title ); ?></h3>
	</div>
	<?php endif; ?>
	<?php
	$clean = array();
	foreach ( $posts as $item ) {
		if ( $item instanceof WP_Post ) {
			$clean[] = $item;
		}
	}
	?>
	<?php if ( $clean ) : ?>
	<ol class="cat-mr-list"<?php echo $preserve_ssr ? ' data-preserve="1"' : ''; ?><?php echo $cat_attr > 0 ? ' data-category-id="' . esc_attr( (string) $cat_attr ) . '"' : ''; ?>>
		<?php foreach ( $clean as $index => $item ) : ?>
			<li class="cat-mr-item">
				<span class="cat-mr-num"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
				<div class="cat-mr-content">
					<h4 class="cat-mr-headline">
						<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
					</h4>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php else : ?>
		<?php
		$ctx           = function_exists( 'altijara_get_layout_context' ) ? altijara_get_layout_context() : array();
		$on_article    = ( isset( $ctx['surface'] ) && 'article' === $ctx['surface'] );
		$empty_message = $on_article && function_exists( 'altijara_get_article_sidebar_empty_message' )
			? altijara_get_article_sidebar_empty_message()
			: __( 'لا مقالات في هذا القسم بعد', 'altijara' );
		?>
	<article class="cat-hero-card cat-mr-empty">
		<div class="cat-hero-info">
			<h2 class="cat-hero-headline"><?php echo esc_html( $empty_message ); ?></h2>
		</div>
	</article>
	<?php endif; ?>
</div>
