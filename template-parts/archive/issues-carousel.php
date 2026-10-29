<?php
/**
 * Issues archive — horizontal carousel of issue cards.
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$track_id = isset( $args['id'] ) ? (string) $args['id'] : 'issuesCarouselTrack';
$sec_id   = isset( $args['sec'] ) ? (string) $args['sec'] : '';
$title    = isset( $args['title'] ) ? (string) $args['title'] : '';
$items    = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
$fallback = altijara_asset( 'assets/images/thumb-logo-fallback.png' );
if ( empty( $items ) ) {
	return;
}

?>
<section class="issues-carousel-section" <?php echo $sec_id ? 'id="' . esc_attr( $sec_id ) . '"' : ''; ?>>
	<div class="section-title-bar">
		<?php if ( '' !== $title ) : ?>
		<div class="section-title-group">
			<h2 class="section-ft-heading"><?php echo esc_html( $title ); ?></h2>
		</div>
		<?php endif; ?>
		<div class="carousel-nav-arrows">
			<button type="button" class="nav-arrow-btn prev" aria-label="<?php esc_attr_e( 'السابق', 'altijara' ); ?>" data-scroll-track="<?php echo esc_attr( $track_id ); ?>" data-scroll-step="360">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
			</button>
			<button type="button" class="nav-arrow-btn next" aria-label="<?php esc_attr_e( 'التالي', 'altijara' ); ?>" data-scroll-track="<?php echo esc_attr( $track_id ); ?>" data-scroll-step="-360">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
			</button>
		</div>
	</div>
	<div class="issues-carousel-track" id="<?php echo esc_attr( $track_id ); ?>" data-ssr="1">
		<?php foreach ( $items as $issue ) : ?>
			<?php
			if ( ! $issue instanceof WP_Post ) {
				continue;
			}
			$pid      = (int) $issue->ID;
			$number   = altijara_get_issue_meta( $pid, 'number' );
			$cover    = function_exists( 'altijara_resolve_issue_cover_url' )
				? altijara_resolve_issue_cover_url( $pid, $number ? (int) $number : 0 )
				: '';
			if ( ! $cover ) {
				$cover = altijara_get_thumb_url( $pid ) ?: $fallback;
			}
			$flipbook = altijara_get_issue_meta( $pid, 'flipbook' );
			$href     = $flipbook ? $flipbook : get_permalink( $issue );
			$badge    = $number ? sprintf( /* translators: %s number */ __( 'العدد %s', 'altijara' ), $number ) : get_the_title( $issue );
			$excerpt  = has_excerpt( $issue ) || get_the_excerpt( $issue ) ? get_the_excerpt( $issue ) : '';
			?>
			<article
				class="issue-card-ft"
				data-issue-id="<?php echo esc_attr( (string) $pid ); ?>"
				data-issue-number="<?php echo esc_attr( (string) $number ); ?>"
			>
				<a class="card-cover-wrap" href="<?php echo esc_url( $href ); ?>" target="_blank" rel="noopener">
					<img class="card-cover-img" src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $issue ) ); ?>" loading="lazy" onerror="this.src='<?php echo esc_url( $fallback ); ?>'">
					<span class="card-num-badge"><?php echo esc_html( $badge ); ?></span>
				</a>
				<div class="card-content-wrap">
					<h3 class="card-issue-title">
						<a href="<?php echo esc_url( $href ); ?>" target="_blank" rel="noopener"><?php echo esc_html( get_the_title( $issue ) ); ?></a>
					</h3>
					<?php if ( $excerpt ) : ?>
						<p class="card-issue-excerpt"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>
					<div class="card-footer-meta">
						<a href="<?php echo esc_url( $href ); ?>" class="btn-card-read-action" target="_blank" rel="noopener">
							<span><?php esc_html_e( 'اقرأ العدد', 'altijara' ); ?></span>
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
						</a>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
