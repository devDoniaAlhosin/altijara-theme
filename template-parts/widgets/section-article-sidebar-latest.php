<?php
/**
 * Article sidebar: latest posts list.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key    = isset( $args['section_key'] ) ? (string) $args['section_key'] : '';
$title  = function_exists( 'altijara_section_heading' ) ? altijara_section_heading( $config, __( 'أحدث الأخبار', 'altijara' ) ) : ( isset( $config['label'] ) ? (string) $config['label'] : __( 'أحدث الأخبار', 'altijara' ) );

$clean = array();
foreach ( $posts as $item ) {
	if ( $item instanceof WP_Post ) {
		$clean[] = $item;
	}
}
?>
<div class="sidebar-widget">
	<?php if ( '' !== $title ) : ?>
	<div class="sidebar-widget-header">
		<span class="pill-badge"><?php echo esc_html( $title ); ?></span>
	</div>
	<?php endif; ?>
	<div class="rec-items-list">
		<?php if ( $clean ) : ?>
			<?php
			foreach ( $clean as $item ) {
				$kicker = function_exists( 'altijara_section_card_category_name' )
					? altijara_section_card_category_name( $item->ID, $config, $key )
					: ( function_exists( 'altijara_get_listing_category_name' )
						? altijara_get_listing_category_name( $item->ID )
						: '' );
				get_template_part(
					'template-parts/cards/post',
					'card',
					array(
						'post'    => $item,
						'variant' => 'rec_item',
						'kicker'  => $kicker,
					)
				);
			}
			?>
		<?php else : ?>
			<p class="article-sidebar-empty">
				<?php
				echo esc_html(
					function_exists( 'altijara_get_article_sidebar_empty_message' )
						? altijara_get_article_sidebar_empty_message()
						: __( 'لا توجد مقالات في تصنيف هذا المقال حالياً', 'altijara' )
				);
				?>
			</p>
		<?php endif; ?>
	</div>
</div>
