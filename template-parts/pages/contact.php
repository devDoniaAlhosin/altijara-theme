<?php
/**
 * contact page content.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pid   = get_the_ID();
$m     = altijara_get_page_meta( $pid );
$title = get_the_title();
$email = ! empty( $m['agent_email'] ) ? $m['agent_email'] : ( $m['chip_email'] ?? '' );
$phone = ! empty( $m['agent_phone'] ) ? $m['agent_phone'] : '';
$mobile = ! empty( $m['agent_mobile'] ) ? $m['agent_mobile'] : '';
?>
<main class="container static-shell">
	<section class="static-page static-page--wide">
		<header class="static-page-header">
			<h1 class="static-title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( ! empty( $m['lead'] ) ) : ?>
				<p class="static-lead"><?php echo esc_html( $m['lead'] ); ?></p>
			<?php endif; ?>
		</header>

		<div class="static-contact-chips">
			<?php if ( ! empty( $m['chip_email'] ) ) : ?>
				<a class="static-chip" href="mailto:<?php echo esc_attr( $m['chip_email'] ); ?>"><strong><?php echo esc_html( $m['chip_email'] ); ?></strong></a>
			<?php endif; ?>
			<?php if ( ! empty( $m['chip_name'] ) ) : ?>
				<a class="static-chip" href="<?php echo esc_url( home_url( '/advertise/' ) ); ?>"><strong><?php echo esc_html( $m['chip_name'] ); ?></strong></a>
			<?php endif; ?>
			<?php if ( ! empty( $m['chip_org'] ) ) : ?>
				<span class="static-chip"><strong><?php echo esc_html( $m['chip_org'] ); ?></strong></span>
			<?php endif; ?>
		</div>

		<div class="static-split static-contact-layout">
			<?php if ( function_exists( 'altijara_page_has_form_shortcode' ) && altijara_page_has_form_shortcode( $m ) ) : ?>
				<div id="altijara-qf" class="static-form static-panel static-form--shortcode contact-shortcode-panel">
					<h2><?php echo esc_html( $m['form_title'] ?? '' ); ?></h2>
					<?php altijara_render_page_form_shortcode( $m['form_shortcode'], false ); ?>
				</div>
			<?php else : ?>
			<form class="static-form static-panel" id="contactForm" action="#" method="post">
				<h2 style="margin:0 0 14px;font-size:18px;font-weight:800;"><?php echo esc_html( $m['form_title'] ?? '' ); ?></h2>
				<div class="static-form-row">
					<div class="static-field">
						<label for="cName"><?php esc_html_e( 'الاسم', 'altijara' ); ?></label>
						<input id="cName" name="name" type="text" required autocomplete="name" placeholder="<?php esc_attr_e( 'الاسم الكامل', 'altijara' ); ?>">
					</div>
					<div class="static-field">
						<label for="cEmail"><?php esc_html_e( 'البريد الإلكتروني', 'altijara' ); ?></label>
						<input id="cEmail" name="email" type="email" required autocomplete="email" placeholder="name@example.com">
					</div>
				</div>
				<div class="static-field">
					<label for="cSubject"><?php esc_html_e( 'الموضوع', 'altijara' ); ?></label>
					<select id="cSubject" name="subject" required>
						<option value="editorial"><?php esc_html_e( 'استفسار تحريري', 'altijara' ); ?></option>
						<option value="advertise"><?php esc_html_e( 'إعلان / نشر أخبار', 'altijara' ); ?></option>
						<option value="subscribe"><?php esc_html_e( 'اشتراك', 'altijara' ); ?></option>
						<option value="other"><?php esc_html_e( 'أخرى', 'altijara' ); ?></option>
					</select>
				</div>
				<div class="static-field">
					<label for="cMessage"><?php esc_html_e( 'الرسالة', 'altijara' ); ?></label>
					<textarea id="cMessage" name="message" required placeholder="<?php esc_attr_e( 'اكتب رسالتك هنا...', 'altijara' ); ?>"></textarea>
				</div>
				<div class="static-form-actions">
					<button type="submit" class="static-btn static-btn-primary"><?php esc_html_e( 'إرسال الرسالة', 'altijara' ); ?></button>
					<a href="<?php echo esc_url( home_url( '/advertise/' ) ); ?>" class="static-btn static-btn-ghost"><?php esc_html_e( 'صفحة أعلن معنا', 'altijara' ); ?></a>
				</div>
				<div class="static-success" id="contactSuccess" role="status"><?php echo esc_html( $m['success_message'] ?? '' ); ?></div>
			</form>
			<?php endif; ?>

			<aside class="ad-agent-card ad-agent-card--sidebar" aria-labelledby="contactAdAgentTitle">
				<?php if ( ! empty( $m['agent_kicker'] ) ) : ?>
					<span class="static-kicker"><?php echo esc_html( $m['agent_kicker'] ); ?></span>
				<?php endif; ?>
				<h2 id="contactAdAgentTitle"><?php echo esc_html( $m['agent_title'] ?? '' ); ?></h2>
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
		</div>
	</section>
</main>
<script>
document.getElementById('contactForm')?.addEventListener('submit', function (e) {
	e.preventDefault();
	document.getElementById('contactSuccess')?.classList.add('is-visible');
	this.reset();
});
</script>
<?php

