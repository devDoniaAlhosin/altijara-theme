<?php
/**
 * Template Name: من نحن
 * About page — layout fixed; copy from page meta.
 *
 * @package Altijara
 */
get_header();

$pid   = get_the_ID();
$m     = altijara_get_page_meta( $pid );
$title = get_the_title();
$stats = ! empty( $m['stats'] ) && is_array( $m['stats'] ) ? $m['stats'] : array();
$offers = ! empty( $m['offers'] ) && is_array( $m['offers'] ) ? $m['offers'] : array();
$dist   = ! empty( $m['dist_items'] ) && is_array( $m['dist_items'] ) ? $m['dist_items'] : array();
$phone  = ! empty( $m['hq_phone'] ) ? $m['hq_phone'] : '';
?>
<main class="container static-shell">
	<section class="static-page static-page--wide">
		<header class="static-page-header">
			<h1 class="static-title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( ! empty( $m['lead'] ) ) : ?>
				<p class="static-lead about-lead-oneline"><?php echo esc_html( $m['lead'] ); ?></p>
			<?php endif; ?>
		</header>

		<aside class="ad-agent-card about-highlight" aria-labelledby="aboutHighlightTitle">
			<?php if ( ! empty( $m['highlight_kicker'] ) ) : ?>
				<span class="static-kicker"><?php echo esc_html( $m['highlight_kicker'] ); ?></span>
			<?php endif; ?>
			<h2 id="aboutHighlightTitle"><?php echo esc_html( $m['highlight_title'] ?? '' ); ?></h2>
			<?php if ( ! empty( $m['highlight_intro'] ) ) : ?>
				<p class="ad-agent-intro"><?php echo esc_html( $m['highlight_intro'] ); ?></p>
			<?php endif; ?>

			<?php if ( $stats ) : ?>
				<ul class="ad-agent-contacts">
					<?php foreach ( $stats as $stat ) : ?>
						<li>
							<div class="ad-agent-link">
								<span class="ad-agent-icon" aria-hidden="true">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
								</span>
								<span class="ad-agent-link-text">
									<strong><?php echo esc_html( $stat['value'] ?? '' ); ?></strong>
									<span class="label"><?php echo esc_html( $stat['label'] ?? '' ); ?></span>
								</span>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</aside>

		<?php if ( ! empty( $m['offers_title'] ) ) : ?>
			<h2 class="about-section-title"><?php echo esc_html( $m['offers_title'] ); ?></h2>
		<?php endif; ?>
		<?php if ( $offers ) : ?>
			<ul class="about-offer-list">
				<?php foreach ( $offers as $i => $offer ) : ?>
					<li>
						<span class="about-offer-num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<div>
							<strong><?php echo esc_html( $offer['title'] ?? '' ); ?></strong>
							<p><?php echo esc_html( $offer['text'] ?? '' ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( ! empty( $m['mission'] ) ) : ?>
			<p class="about-mission"><?php echo esc_html( $m['mission'] ); ?></p>
		<?php endif; ?>

		<div class="static-split about-bottom">
			<div class="about-distribution">
				<?php if ( ! empty( $m['dist_intro'] ) ) : ?>
					<p><?php echo esc_html( $m['dist_intro'] ); ?></p>
				<?php endif; ?>
				<?php if ( $dist ) : ?>
					<ul class="about-dist-list">
						<?php foreach ( $dist as $item ) : ?>
							<li><?php echo esc_html( $item['text'] ?? '' ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<aside class="about-hq-card">
				<?php if ( ! empty( $m['hq_kicker'] ) ) : ?>
					<span class="static-kicker"><?php echo esc_html( $m['hq_kicker'] ); ?></span>
				<?php endif; ?>
				<h3><?php echo esc_html( $m['hq_title'] ?? '' ); ?></h3>
				<?php if ( ! empty( $m['hq_address'] ) ) : ?>
					<p class="about-hq-address"><?php echo esc_html( $m['hq_address'] ); ?></p>
				<?php endif; ?>
				<ul class="about-hq-meta">
					<?php if ( $phone ) : ?>
						<li>
							<span><?php echo esc_html( $m['hq_phone_label'] ?? '' ); ?></span>
							<a href="<?php echo esc_url( altijara_phone_href( $phone ) ); ?>" dir="ltr"><?php echo esc_html( $phone ); ?></a>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $m['hq_po_label'] ) || ! empty( $m['hq_po_value'] ) ) : ?>
						<li>
							<span><?php echo esc_html( $m['hq_po_label'] ?? '' ); ?></span>
							<strong><?php echo esc_html( $m['hq_po_value'] ?? '' ); ?></strong>
						</li>
					<?php endif; ?>
				</ul>
				<div class="static-form-actions about-hq-actions">
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="static-btn static-btn-primary"><?php echo esc_html( $m['hq_contact_label'] ?? __( 'Contact', 'altijara' ) ); ?></a>
					<a href="<?php echo esc_url( home_url( '/advertise/' ) ); ?>" class="static-btn static-btn-ghost"><?php echo esc_html( $m['hq_advertise_label'] ?? __( 'Advertise', 'altijara' ) ); ?></a>
				</div>
			</aside>
		</div>
	</section>
</main>
<?php
get_footer();
