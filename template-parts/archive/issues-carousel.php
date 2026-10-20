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
$fallback = altijara_asset( 'assets/images/issue-730-f2dfce.jpg' );
if ( empty( $items ) ) {
	return;
}
?>
<section class="issues-carousel-section" <?php echo $sec_id ? 'id="' . esc_attr( $sec_id ) . '"' : ''; ?>>
	<div class="section-title-bar">
		<div class="section-title-group" style="display: flex; align-items: center; gap: 14px;">
			<h2 class="section-ft-heading"><?php echo esc_html( $title ); ?></h2>
		</div>
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
			$cover    = altijara_get_thumb_url( $pid ) ?: $fallback;
			$number   = altijara_get_issue_meta( $pid, 'number' );
			$flipbook = altijara_get_issue_meta( $pid, 'flipbook' );
			$pdf      = altijara_get_issue_meta( $pid, 'pdf' );
			$href     = $flipbook ? $flipbook : get_permalink( $issue );
			$badge    = $number ? sprintf( /* translators: %s number */ __( 'العدد %s', 'altijara' ), $number ) : get_the_title( $issue );
			?>
			<article class="issue-card" data-issue-id="<?php echo esc_attr( (string) $pid ); ?>" data-issue-number="<?php echo esc_attr( (string) $number ); ?>">
				<div class="card-cover-wrap" data-open-issue="<?php echo esc_attr( (string) $pid ); ?>">
					<img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $issue ) ); ?>" loading="lazy" onerror="this.src='<?php echo esc_url( $fallback ); ?>'">
					<span class="issue-card-badge"><?php echo esc_html( $badge ); ?></span>
				</div>
				<div class="card-body">
					<h3 class="issue-card-title">
						<a href="<?php echo esc_url( get_permalink( $issue ) ); ?>"><?php echo esc_html( get_the_title( $issue ) ); ?></a>
					</h3>
					<span class="issue-card-date"><?php echo esc_html( altijara_format_date_ar( $pid ) ); ?></span>
					<div class="issue-card-actions">
						<a class="issue-card-link" href="<?php echo esc_url( $href ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'تصفح', 'altijara' ); ?></a>
						<?php if ( $pdf ) : ?>
							<a class="issue-card-link js-pdf-download" href="<?php echo esc_url( $pdf ); ?>" download><?php esc_html_e( 'PDF', 'altijara' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
