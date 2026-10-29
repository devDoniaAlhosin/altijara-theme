<?php
/**
 * advertise page content.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pid    = get_the_ID();
$m      = altijara_get_page_meta( $pid );
$title  = get_the_title();
$specs  = ! empty( $m['ad_specs'] ) && is_array( $m['ad_specs'] ) ? $m['ad_specs'] : array();
$phone  = ! empty( $m['agent_phone'] ) ? $m['agent_phone'] : '';
$mobile = ! empty( $m['agent_mobile'] ) ? $m['agent_mobile'] : '';
$email  = ! empty( $m['agent_email'] ) ? $m['agent_email'] : '';

$riyal = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="riyal-icon" aria-hidden="true"><path d="m20 19.5-5.5 1.2"/><path d="M14.5 4v11.22a1 1 0 0 0 1.242.97L20 15.2"/><path d="m2.978 19.351 5.549-1.363A2 2 0 0 0 10 16V2"/><path d="M20 10 4 13.5"/></svg>';
?>
<main class="container static-shell">
	<section class="static-page static-page--wide">
		<header class="static-page-header">
			<h1 class="static-title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( ! empty( $m['lead'] ) ) : ?>
				<p class="static-lead static-lead-oneline"><?php echo esc_html( $m['lead'] ); ?></p>
			<?php endif; ?>
		</header>

		<aside class="ad-agent-card" aria-labelledby="adAgentTitle">
			<?php if ( ! empty( $m['agent_kicker'] ) ) : ?>
				<span class="static-kicker"><?php echo esc_html( $m['agent_kicker'] ); ?></span>
			<?php endif; ?>
			<h2 id="adAgentTitle"><?php echo esc_html( $m['agent_title'] ?? '' ); ?></h2>
			<?php if ( ! empty( $m['agent_intro'] ) ) : ?>
				<p class="ad-agent-intro"><?php echo esc_html( $m['agent_intro'] ); ?></p>
			<?php endif; ?>

			<div class="ad-agent-person">
				<strong><?php echo esc_html( $m['agent_name'] ?? '' ); ?></strong>
				<span><?php echo esc_html( $m['agent_role'] ?? '' ); ?></span>
			</div>

			<ul class="ad-agent-contacts">
				<?php if ( $phone ) : ?>
					<li>
						<a href="<?php echo esc_url( altijara_phone_href( $phone ) ); ?>" class="ad-agent-link">
							<span class="ad-agent-icon" aria-hidden="true">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.68 2.35a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.75.32 1.54.55 2.35.68A2 2 0 0 1 22 16.92z"/></svg>
							</span>
							<span class="ad-agent-link-text">
								<span class="label"><?php esc_html_e( 'هاتف', 'altijara' ); ?></span>
								<strong dir="ltr"><?php echo esc_html( $phone ); ?></strong>
							</span>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( $mobile ) : ?>
					<li>
						<a href="<?php echo esc_url( altijara_phone_href( $mobile ) ); ?>" class="ad-agent-link">
							<span class="ad-agent-icon" aria-hidden="true">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
							</span>
							<span class="ad-agent-link-text">
								<span class="label"><?php esc_html_e( 'جوال', 'altijara' ); ?></span>
								<strong dir="ltr"><?php echo esc_html( $mobile ); ?></strong>
							</span>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<li>
						<a href="mailto:<?php echo esc_attr( $email ); ?>" class="ad-agent-link">
							<span class="ad-agent-icon" aria-hidden="true">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
							</span>
							<span class="ad-agent-link-text">
								<span class="label"><?php esc_html_e( 'بريد', 'altijara' ); ?></span>
								<strong dir="ltr"><?php echo esc_html( $email ); ?></strong>
							</span>
						</a>
					</li>
				<?php endif; ?>
			</ul>
		</aside>

		<?php if ( $specs ) : ?>
			<div class="ad-spec-grid">
				<?php foreach ( $specs as $spec ) :
					$variant = ! empty( $spec['variant'] ) ? sanitize_key( $spec['variant'] ) : 'leader';
					$points  = ! empty( $spec['points'] ) ? preg_split( '/\r\n|\r|\n/', (string) $spec['points'] ) : array();
					?>
					<article class="ad-spec-card ad-spec-card--<?php echo esc_attr( $variant ); ?>">
						<header class="ad-spec-head">
							<h3><?php echo esc_html( $spec['title'] ?? '' ); ?></h3>
							<p><?php echo esc_html( $spec['subtitle'] ?? '' ); ?></p>
						</header>
						<div class="ad-spec-body">
							<div class="ad-spec-mock ad-spec-mock--<?php echo esc_attr( $variant ); ?>" aria-hidden="true">
								<div class="ad-spec-mock-frame<?php echo 'medium' === $variant ? ' ad-spec-mock-frame--side' : ''; ?>">
									<span class="ad-spec-mock-slot"></span>
									<span class="ad-spec-mock-lines"></span>
								</div>
							</div>
							<?php if ( ! empty( $spec['size'] ) ) : ?>
								<p class="ad-spec-size"><?php echo esc_html( $spec['size'] ); ?></p>
							<?php endif; ?>
							<?php if ( $points ) : ?>
								<ul class="ad-spec-points">
									<?php foreach ( $points as $point ) :
										$point = trim( (string) $point );
										if ( '' === $point ) {
											continue;
										}
										?>
										<li><?php echo esc_html( $point ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<div class="ad-spec-price">
								<span class="ad-spec-price-label"><?php esc_html_e( 'ابتداءً من', 'altijara' ); ?></span>
								<strong><?php echo esc_html( $spec['price'] ?? '' ); ?></strong>
								<span class="ad-spec-price-unit"><?php echo $riyal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $spec['price_note'] ?? '' ); ?></span>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $m['guidelines'] ) ) : ?>
			<hr class="static-divider">
			<div class="static-panel" style="margin-bottom:24px;">
				<h2 style="margin:0 0 10px;font-size:18px;"><?php esc_html_e( 'Sponsored content guidelines', 'altijara' ); ?></h2>
				<p style="margin:0;line-height:1.7;"><?php echo nl2br( esc_html( $m['guidelines'] ) ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $m['media_kit_url'] ) ) : ?>
			<p style="margin:0 0 20px;">
				<a class="static-btn static-btn-primary" href="<?php echo esc_url( $m['media_kit_url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Download media kit (PDF)', 'altijara' ); ?></a>
			</p>
		<?php endif; ?>

		<hr class="static-divider">

		<div class="static-split static-split--equal">
			<div class="static-panel ad-request-panel">
				<h2 class="ad-request-title"><?php echo esc_html( $m['form_title'] ?? '' ); ?></h2>
				<?php if ( ! empty( $m['form_lead'] ) ) : ?>
					<p class="ad-request-lead"><?php echo esc_html( $m['form_lead'] ); ?></p>
				<?php endif; ?>

				<?php if ( function_exists( 'altijara_page_has_form_shortcode' ) && altijara_page_has_form_shortcode( $m ) ) : ?>
					<?php altijara_render_page_form_shortcode( $m['form_shortcode'] ); ?>
				<?php else : ?>
				<form class="static-form" id="advertiseForm" action="#" method="post">
					<div class="static-form-row">
						<div class="static-field">
							<label for="aCompany"><?php esc_html_e( 'الجهة / الشركة', 'altijara' ); ?></label>
							<input id="aCompany" name="company" type="text" required autocomplete="organization">
						</div>
						<div class="static-field">
							<label for="aName"><?php esc_html_e( 'اسم المسؤول', 'altijara' ); ?></label>
							<input id="aName" name="name" type="text" required autocomplete="name">
						</div>
					</div>
					<div class="static-form-row">
						<div class="static-field">
							<label for="aEmail"><?php esc_html_e( 'البريد الإلكتروني', 'altijara' ); ?></label>
							<input id="aEmail" name="email" type="email" required autocomplete="email">
						</div>
						<div class="static-field">
							<label for="aPhone"><?php esc_html_e( 'الجوال', 'altijara' ); ?></label>
							<input id="aPhone" name="phone" type="tel" required autocomplete="tel">
						</div>
					</div>
					<div class="static-field">
						<label for="aFormat"><?php esc_html_e( 'المساحة المطلوبة', 'altijara' ); ?></label>
						<select id="aFormat" name="format" required>
							<?php foreach ( $specs as $spec ) :
								$opt_val = ! empty( $spec['variant'] ) ? sanitize_key( $spec['variant'] ) : 'leader';
								$opt_label = trim( ( $spec['title'] ?? '' ) . ( ! empty( $spec['price'] ) ? ' — من ' . $spec['price'] : '' ) );
								?>
								<option value="<?php echo esc_attr( $opt_val ); ?>"><?php echo esc_html( $opt_label ); ?></option>
							<?php endforeach; ?>
							<option value="both"><?php esc_html_e( 'المساحتان معاً', 'altijara' ); ?></option>
							<option value="print"><?php esc_html_e( 'النسخة المطبوعة', 'altijara' ); ?></option>
						</select>
					</div>
					<div class="static-field">
						<label for="aNotes"><?php esc_html_e( 'ملاحظات', 'altijara' ); ?></label>
						<textarea id="aNotes" name="notes" placeholder="<?php esc_attr_e( 'مدة الحملة، القطاع، أو أي تفاصيل إضافية...', 'altijara' ); ?>"></textarea>
					</div>
					<div class="static-form-actions">
						<button type="submit" class="static-btn static-btn-primary"><?php esc_html_e( 'إرسال الطلب', 'altijara' ); ?></button>
						<?php if ( $email ) : ?>
							<a href="mailto:<?php echo esc_attr( $email ); ?>" class="static-btn static-btn-ghost"><?php esc_html_e( 'مراسلة مباشرة', 'altijara' ); ?></a>
						<?php endif; ?>
					</div>
					<div class="static-success" id="advertiseSuccess" role="status"><?php echo esc_html( $m['success_message'] ?? '' ); ?></div>
				</form>
				<?php endif; ?>
			</div>

			<aside class="static-panel ad-why-panel">
				<div class="ad-why-top">
					<h3><?php echo esc_html( $m['why_title'] ?? '' ); ?></h3>
					<?php if ( ! empty( $m['why_text'] ) ) : ?>
						<p><?php echo esc_html( $m['why_text'] ); ?></p>
					<?php endif; ?>

					<?php if ( $specs ) : ?>
						<div class="ad-why-formats">
							<?php foreach ( $specs as $spec ) :
								$variant = ! empty( $spec['variant'] ) ? sanitize_key( $spec['variant'] ) : 'leader';
								?>
								<article class="ad-why-format">
									<div class="ad-why-mock ad-why-mock--<?php echo esc_attr( $variant ); ?>" aria-hidden="true">
										<span class="ad-why-mock-slot"></span>
									</div>
									<div class="ad-why-format-body">
										<strong><?php echo esc_html( $spec['title'] ?? '' ); ?></strong>
										<em><?php echo esc_html( $spec['subtitle'] ?? '' ); ?></em>
										<span class="ad-why-price"><?php echo esc_html( sprintf( /* translators: price */ __( 'من %s', 'altijara' ), $spec['price'] ?? '' ) ); ?> <?php echo $riyal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $spec['price_note'] ?? '' ); ?></span>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="ad-why-foot">
					<span class="ad-why-foot-label"><?php esc_html_e( 'للتكلفة حسب فترة التعاقد', 'altijara' ); ?></span>
					<?php if ( $mobile ) : ?>
						<a href="<?php echo esc_url( altijara_phone_href( $mobile ) ); ?>" dir="ltr" class="ad-why-phone"><?php echo esc_html( $mobile ); ?></a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<a href="mailto:<?php echo esc_attr( $email ); ?>" dir="ltr" class="static-text-link"><?php echo esc_html( $email ); ?></a>
					<?php endif; ?>
				</div>
			</aside>
		</div>
	</section>
</main>
<script>
document.getElementById('advertiseForm')?.addEventListener('submit', function (e) {
	e.preventDefault();
	const ok = document.getElementById('advertiseSuccess');
	if (ok) ok.classList.add('is-visible');
	this.reset();
});
</script>
<?php

