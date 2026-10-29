<?php
/**
 * register page content.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$urls  = function_exists( 'altijara_theme_urls' ) ? altijara_theme_urls() : array();
$login = $urls['login'] ?? home_url( '/login/' );
$sub   = $urls['subscribe'] ?? home_url( '/subscribe/' );
$notice = isset( $_GET['auth_notice'] ) ? sanitize_key( wp_unslash( $_GET['auth_notice'] ) ) : ''; // phpcs:ignore
$msg    = $notice && function_exists( 'altijara_auth_notice_message' ) ? altijara_auth_notice_message( $notice ) : '';
$is_ok  = in_array( $notice, array( 'verify_sent', 'created' ), true );
?>
<main class="container static-shell">
	<section class="static-page static-page--narrow">
		<header class="static-page-header">
			<h1 class="static-title"><?php the_title(); ?></h1>
			<p class="static-lead"><?php esc_html_e( 'أنشئ حساباً لمتابعة المحتوى وإدارة اشتراكك. ستصلك رسالة لتأكيد البريد.', 'altijara' ); ?></p>
		</header>
		<div class="static-panel static-auth-panel">
			<?php if ( $msg ) : ?>
				<div class="static-success is-visible auth-notice<?php echo $is_ok ? '' : ' auth-notice--error'; ?>" role="alert"><?php echo esc_html( $msg ); ?></div>
			<?php endif; ?>
			<form class="static-form" method="post" action="<?php echo esc_url( get_permalink() ); ?>">
				<?php wp_nonce_field( 'altijara_register', 'altijara_auth_nonce' ); ?>
				<input type="hidden" name="altijara_register" value="1">
				<?php
				if ( function_exists( 'altijara_render_social_login_buttons' ) ) {
					altijara_render_social_login_buttons( 'register' );
				}
				?>
				<div class="static-field">
					<label for="rName"><?php esc_html_e( 'الاسم', 'altijara' ); ?></label>
					<input id="rName" name="name" type="text" required autocomplete="name" placeholder="<?php esc_attr_e( 'الاسم الكامل', 'altijara' ); ?>">
				</div>
				<div class="static-field">
					<label for="rEmail"><?php esc_html_e( 'البريد الإلكتروني', 'altijara' ); ?></label>
					<input id="rEmail" name="email" type="email" required autocomplete="email" placeholder="name@example.com">
				</div>
				<div class="static-field">
					<label for="rPassword"><?php esc_html_e( 'كلمة المرور', 'altijara' ); ?></label>
					<input id="rPassword" name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="••••••••">
				</div>
				<?php
				if ( function_exists( 'altijara_turnstile_widget_html' ) ) {
					altijara_turnstile_widget_html();
				}
				?>
				<div class="static-form-actions">
					<button type="submit" class="static-btn static-btn-primary auth-submit"><?php esc_html_e( 'إنشاء الحساب', 'altijara' ); ?></button>
				</div>
				<p class="static-form-note">
					<a href="<?php echo esc_url( $login ); ?>"><?php esc_html_e( 'تسجيل الدخول', 'altijara' ); ?></a>
					<?php if ( ! function_exists( 'altijara_subscriptions_enabled' ) || altijara_subscriptions_enabled() ) : ?>
						·
						<a href="<?php echo esc_url( $sub ); ?>"><?php esc_html_e( 'اشترك', 'altijara' ); ?></a>
					<?php endif; ?>
				</p>
			</form>
		</div>
	</section>
</main>
<?php

