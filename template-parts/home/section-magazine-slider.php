<?php
/**
 * Hero cover slider — شخصية العدد (category issue-cover-personality).
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config = isset( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$key    = isset( $args['section_key'] ) ? $args['section_key'] : 'hero_magazine';

$slides       = $posts;
$fallback_img = altijara_asset( 'assets/images/issue-730-f2dfce.jpg' );
$first_date   = ! empty( $slides[0] ) ? altijara_format_date_ar( $slides[0]->ID ) : '';

/**
 * Chevron SVG (points left). Flip with scaleX(-1) for right.
 *
 * @param bool $flip Mirror horizontally.
 * @return string
 */
$chevron = static function ( $flip = false ) {
	$style = $flip ? ' style="transform:scaleX(-1)"' : '';
	return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"' . $style . '><path d="M15 6l-6 6 6 6"></path></svg>';
};
?>
<div class="magazine-cover-card" id="heroCoverSlider" data-section="<?php echo esc_attr( $key ); ?>" data-index="0">
	<?php if ( empty( $slides ) ) : ?>
		<div class="hero-cover-slide is-active">
			<div class="magazine-cover-img-wrap">
				<img src="<?php echo esc_url( $fallback_img ); ?>" alt="<?php esc_attr_e( 'شخصية العدد', 'altijara' ); ?>">
			</div>
			<div class="magazine-cover-details">
				<h3 class="magazine-cover-title"><?php esc_html_e( 'شخصية العدد', 'altijara' ); ?></h3>
				<span class="magazine-cover-date"></span>
			</div>
		</div>
	<?php else : ?>
		<?php foreach ( $slides as $i => $slide ) : ?>
			<?php
			$is_issue   = ( 'issue' === get_post_type( $slide ) );
			$flip_href  = ( $is_issue && function_exists( 'altijara_resolve_issue_flipbook_url' ) )
				? altijara_resolve_issue_flipbook_url( $slide->ID )
				: '';
			$slide_href = $flip_href ? $flip_href : get_permalink( $slide );
			$slide_atts = $flip_href ? ' target="_blank" rel="noopener noreferrer"' : '';
			$excerpt    = altijara_trim_at_word( $slide->post_excerpt ?: $slide->post_content, 220 );
			?>
			<div
				class="hero-cover-slide <?php echo 0 === $i ? 'is-active' : ''; ?>"
				data-post-id="<?php echo esc_attr( (string) (int) $slide->ID ); ?>"
				data-title="<?php echo esc_attr( get_the_title( $slide ) ); ?>"
				data-excerpt="<?php echo esc_attr( $excerpt ); ?>"
				data-href="<?php echo esc_url( get_permalink( $slide ) ); ?>"
				data-date="<?php echo esc_attr( altijara_format_date_ar( $slide->ID ) ); ?>"
			>
				<div class="magazine-cover-img-wrap">
					<a href="<?php echo esc_url( $slide_href ); ?>"<?php echo $slide_atts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<img src="<?php echo esc_url( altijara_get_thumb_url( $slide->ID ) ?: $fallback_img ); ?>" alt="<?php echo esc_attr( get_the_title( $slide ) ); ?>">
					</a>
				</div>
				<div class="magazine-cover-details">
					<h3 class="magazine-cover-title">
						<a href="<?php echo esc_url( $slide_href ); ?>"<?php echo $slide_atts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( get_the_title( $slide ) ); ?></a>
					</h3>
					<span class="magazine-cover-date"><?php echo esc_html( altijara_format_date_ar( $slide->ID ) ); ?></span>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<div class="hero-cover-meta">
		<span class="magazine-cover-date"><?php echo esc_html( $first_date ); ?></span>
		<?php if ( count( $slides ) > 1 ) : ?>
			<div class="hero-cover-arrows">
				<button type="button" class="nav-arrow-btn hero-cover-arrow hero-cover-arrow--prev" aria-label="<?php esc_attr_e( 'السابق', 'altijara' ); ?>">
					<?php echo $chevron( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
				</button>
				<button type="button" class="nav-arrow-btn hero-cover-arrow hero-cover-arrow--next" aria-label="<?php esc_attr_e( 'التالي', 'altijara' ); ?>">
					<?php echo $chevron( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
</div>
