<?php
/**
 * Template Name: تسجيل الدخول
 * Login page — layout fixed; copy from page meta.
 *
 * @package Altijara
 */
get_header();

$pid   = get_the_ID();
$m     = altijara_get_page_meta( $pid );
$title = get_the_title();
?>
<main class="container static-shell">
	<section class="static-page static-page--narrow">
		<header class="static-page-header">
			<h1 class="static-title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( ! empty( $m['lead'] ) ) : ?>
				<p class="static-lead"><?php echo esc_html( $m['lead'] ); ?></p>
			<?php endif; ?>
		</header>

		<div class="static-panel static-auth-panel">
			<form class="static-form" id="loginForm" action="#" method="post">
				<div class="auth-social">
					<button type="button" class="auth-social-btn" data-social="Google">
						<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.4h6.5c-.3 1.5-1.1 2.8-2.4 3.6v3h3.9c2.3-2.1 3.5-5.2 3.5-8.7z"/><path fill="#34A853" d="M12 24c3.2 0 5.9-1.1 7.9-2.9l-3.9-3c-1.1.7-2.4 1.2-4 1.2-3.1 0-5.7-2.1-6.6-4.9H1.4v3.1C3.4 21.3 7.4 24 12 24z"/><path fill="#FBBC05" d="M5.4 14.4c-.2-.7-.4-1.4-.4-2.4s.1-1.7.4-2.4V6.5H1.4C.5 8.3 0 10.1 0 12s.5 3.7 1.4 5.5l4-3.1z"/><path fill="#EA4335" d="M12 4.8c1.7 0 3.3.6 4.5 1.8l3.4-3.4C17.9 1.1 15.2 0 12 0 7.4 0 3.4 2.7 1.4 6.5l4 3.1C6.3 6.8 8.9 4.8 12 4.8z"/></svg>
						<span><?php esc_html_e( 'الدخول عبر Google', 'altijara' ); ?></span>
					</button>
					<button type="button" class="auth-social-btn" data-social="Facebook">
						<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="#1877F2" d="M24 12.1C24 5.4 18.6 0 12 0S0 5.4 0 12.1c0 6 4.4 11 10.1 11.9v-8.4H7.1v-3.5h3V9.4c0-3 1.8-4.7 4.5-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9v2.3h3.3l-.5 3.5h-2.8V24C19.6 23.1 24 18.1 24 12.1z"/></svg>
						<span><?php esc_html_e( 'الدخول عبر Facebook', 'altijara' ); ?></span>
					</button>
				</div>
				<div class="auth-divider"><span><?php esc_html_e( 'أو', 'altijara' ); ?></span></div>
				<div class="static-field">
					<label for="lEmail"><?php esc_html_e( 'البريد الإلكتروني', 'altijara' ); ?></label>
					<input id="lEmail" name="email" type="email" required autocomplete="email" placeholder="name@example.com">
				</div>
				<div class="static-field">
					<label for="lPassword"><?php esc_html_e( 'كلمة المرور', 'altijara' ); ?></label>
					<input id="lPassword" name="password" type="password" required autocomplete="current-password" placeholder="••••••••">
				</div>

				<div class="auth-row">
					<label class="static-check">
						<input type="checkbox" name="remember" checked>
						<span><?php esc_html_e( 'تذكرني', 'altijara' ); ?></span>
					</label>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="static-text-link"><?php echo esc_html( $m['forgot_label'] ?? __( 'نسيت كلمة المرور؟', 'altijara' ) ); ?></a>
				</div>

				<div class="static-form-actions">
					<button type="submit" class="static-btn static-btn-primary auth-submit"><?php esc_html_e( 'دخول', 'altijara' ); ?></button>
				</div>

				<div class="static-success" id="loginSuccess" role="status" data-default="<?php echo esc_attr( $m['success_message'] ?? '' ); ?>"><?php echo esc_html( $m['success_message'] ?? '' ); ?></div>
				<p class="static-form-note"><?php echo esc_html( $m['subscribe_note'] ?? '' ); ?> <a href="<?php echo esc_url( home_url( '/subscribe/' ) ); ?>"><?php echo esc_html( $m['subscribe_link_label'] ?? __( 'اشترك', 'altijara' ) ); ?></a></p>
			</form>
		</div>
	</section>
</main>
<script>
document.getElementById('loginForm')?.addEventListener('submit', function (e) {
	e.preventDefault();
	const note = document.getElementById('loginSuccess');
	if (note) note.textContent = note.getAttribute('data-default') || '';
	note?.classList.add('is-visible');
});
document.querySelectorAll('.auth-social-btn').forEach(function (btn) {
	btn.addEventListener('click', function () {
		const note = document.getElementById('loginSuccess');
		if (!note) return;
		note.textContent = 'تم الدخول عبر ' + btn.getAttribute('data-social') + ' (وضع تجريبي).';
		note.classList.add('is-visible');
	});
});
</script>
<?php
get_footer();
