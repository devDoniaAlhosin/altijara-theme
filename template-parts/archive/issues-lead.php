<?php
/**
 * Issues archive — lead / featured issue.
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$issue    = isset( $args['issue'] ) ? $args['issue'] : null;
$fallback = altijara_asset( 'assets/images/issue-730-f2dfce.jpg' );
?>
<section class="issues-lead-hero">
	<div class="issues-lead-grid">
		<?php if ( $issue instanceof WP_Post ) : ?>
			<?php
			$pid      = (int) $issue->ID;
			$number   = altijara_get_issue_meta( $pid, 'number' );
			$cover    = function_exists( 'altijara_resolve_issue_cover_url' )
				? altijara_resolve_issue_cover_url( $pid, $number ? (int) $number : 0 )
				: '';
			if ( ! $cover ) {
				$cover = altijara_get_thumb_url( $pid ) ?: $fallback;
			}
			$flipbook = altijara_get_issue_meta( $pid, 'flipbook' );
			$pdf      = altijara_get_issue_meta( $pid, 'pdf' );
			$pages    = altijara_get_issue_meta( $pid, 'pages' );
			$files    = altijara_get_issue_meta( $pid, 'files' );
			$talks    = altijara_get_issue_meta( $pid, 'talks' );
			$tag      = altijara_get_issue_meta( $pid, 'tag' );
			$read_url = $flipbook ? $flipbook : get_permalink( $issue );
			$tag_line = $number
				? sprintf( /* translators: %s issue number */ __( 'العدد %s — الأحدث', 'altijara' ), $number )
				: __( 'الأحدث', 'altijara' );
			$date_txt = function_exists( 'altijara_format_date_ar' ) ? altijara_format_date_ar( $pid ) : get_the_date( '', $issue );
			?>
			<div class="lead-media-wrap">
				<div class="lead-cover-frame">
					<img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $issue ) ); ?>" class="lead-cover-img" onerror="this.src='<?php echo esc_url( $fallback ); ?>'">
					<span class="lead-issue-tag"><?php echo esc_html( $tag_line ); ?></span>
					<span class="lead-play-badge" aria-hidden="true">
						<span class="play-icon-svg">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
						</span>
						<span class="lead-media-pill"><?php esc_html_e( 'نسخة رقمية', 'altijara' ); ?></span>
					</span>
				</div>
			</div>
			<div class="lead-info-wrap">
				<div class="lead-meta-row">
					<span class="lead-type-badge"><?php echo esc_html( $tag ? $tag : __( 'مجلة غرفة جدة', 'altijara' ) ); ?></span>
					<?php if ( $date_txt ) : ?>
						<span class="lead-date-txt"><?php echo esc_html( $date_txt ); ?></span>
					<?php endif; ?>
				</div>
				<h2 class="lead-headline">
					<a href="<?php echo esc_url( get_permalink( $issue ) ); ?>"><?php echo esc_html( get_the_title( $issue ) ); ?></a>
				</h2>
				<?php if ( has_excerpt( $issue ) || get_the_excerpt( $issue ) ) : ?>
					<p class="lead-synopsis"><?php echo esc_html( get_the_excerpt( $issue ) ); ?></p>
				<?php endif; ?>
				<?php if ( $pages || $files || $talks ) : ?>
					<div class="lead-stats-row">
						<?php if ( $pages ) : ?>
							<div class="lead-stat-chip"><strong><?php echo esc_html( $pages ); ?></strong><span><?php esc_html_e( 'صفحة', 'altijara' ); ?></span></div>
						<?php endif; ?>
						<?php if ( $files ) : ?>
							<div class="lead-stat-chip"><strong><?php echo esc_html( $files ); ?></strong><span><?php esc_html_e( 'ملفات', 'altijara' ); ?></span></div>
						<?php endif; ?>
						<?php if ( $talks ) : ?>
							<div class="lead-stat-chip"><strong><?php echo esc_html( $talks ); ?></strong><span><?php esc_html_e( 'حوارات', 'altijara' ); ?></span></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="lead-actions-row">
					<a href="<?php echo esc_url( $read_url ); ?>" class="btn-lead-primary" target="_blank" rel="noopener">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
						<span><?php esc_html_e( 'تصفح النسخة الرقمية', 'altijara' ); ?></span>
					</a>
					<?php if ( $pdf ) : ?>
						<a href="<?php echo esc_url( $pdf ); ?>" class="btn-lead-secondary js-pdf-download" data-pdf-url="<?php echo esc_url( $pdf ); ?>" download>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
							<span><?php esc_html_e( 'تحميل ملف PDF', 'altijara' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		<?php else : ?>
			<div class="lead-info-wrap">
				<h2 class="lead-headline"><?php esc_html_e( 'أعداد مجلة التجارة', 'altijara' ); ?></h2>
				<p class="lead-synopsis"><?php esc_html_e( 'لم يتم نشر أعداد بعد. أعد تشغيل إعداد القالب أو أضف أعداداً من لوحة التحكم (أعداد المجلة).', 'altijara' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
