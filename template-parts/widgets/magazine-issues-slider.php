<?php
/**
 * Sidebar widget — من إصدارات المجلة (cover slider).
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
if ( empty( $items ) && function_exists( 'altijara_get_magazine_slider_items' ) ) {
	$items = altijara_get_magazine_slider_items( 8 );
}
if ( empty( $items ) ) {
	return;
}

$first   = $items[0];
$cover   = ! empty( $first['cover'] ) ? $first['cover'] : ( ! empty( $first['src'] ) ? $first['src'] : '' );
$href    = ! empty( $first['flipbook'] ) ? $first['flipbook'] : ( ! empty( $first['link'] ) ? $first['link'] : '#' );
$title   = ! empty( $first['title'] ) ? $first['title'] : '';
$chevron = static function ( $flip = false ) {
	$style = $flip ? ' style="transform:scaleX(-1)"' : '';
	return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"' . $style . '><path d="M15 6l-6 6 6 6"></path></svg>';
};
?>
<div class="cat-new-issue-widget" data-magazine-slider="1" data-issue-count="<?php echo esc_attr( (string) count( $items ) ); ?>">
	<script type="application/json" class="cat-new-issue-source"><?php echo wp_json_encode( array_values( $items ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
	<div class="cat-new-issue-bar">
		<h3 class="cat-section-title"><?php echo esc_html( ( isset( $args['title'] ) && '' !== (string) $args['title'] ) ? (string) $args['title'] : __( 'من إصدارات المجلة', 'altijara' ) ); ?></h3>
		<div class="cat-new-issue-arrows" <?php echo count( $items ) < 2 ? 'hidden' : ''; ?>>
			<button type="button" class="cat-new-issue-arrow cat-new-issue-arrow--prev" aria-label="<?php esc_attr_e( 'السابق', 'altijara' ); ?>">
				<?php echo $chevron( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<button type="button" class="cat-new-issue-arrow cat-new-issue-arrow--next is-accent" aria-label="<?php esc_attr_e( 'التالي', 'altijara' ); ?>">
				<?php echo $chevron( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</div>
	<div class="cat-new-issue-list">
		<?php if ( $cover ) : ?>
			<a href="<?php echo esc_url( $href ); ?>" class="cat-new-issue-cover is-ready" target="_blank" rel="noopener">
				<span class="sk sk-cover" aria-hidden="true"></span>
				<img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="eager" decoding="async">
			</a>
		<?php else : ?>
			<div class="cat-new-issue-cover" aria-hidden="true">
				<span class="sk sk-cover"></span>
			</div>
		<?php endif; ?>
	</div>
</div>
