<?php
/**
 * login page content.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pid   = get_the_ID();
$m     = altijara_get_page_meta( $pid );
$title = get_the_title();
$urls  = function_exists( 'altijara_theme_urls' ) ? altijara_theme_urls() : array();
$sub   = $urls['subscribe'] ?? home_url( '/subscribe/' );
$reg   = $urls['register'] ?? home_url( '/register/' );
$reset = $urls['reset'] ?? home_url( '/reset/' );
$oauth_err = isset( $_GET['oauth_error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$notice    = isset( $_GET['auth_notice'] ) ? sanitize_key( wp_unslash( $_GET['auth_notice'] ) ) : ''; // phpcs:ignore
$msg       = $notice && function_exists( 'altijara_auth_notice_message' ) ? altijara_auth_notice_message( $notice ) : '';
$is_err    = $msg && ! in_array( $notice, array( 'reset_ok', 'created', 'subscribed', 'logged_out', 'reset_sent', 'verify_sent', 'verified_ok' ), true );
$prefill_email = isset( $_GET['verify_email'] ) ? sanitize_email( wp_unslash( $_GET['verify_email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$show_resend   = in_array( $notice, array( 'verify_needed', 'verify_sent', 'verify_fail' ), true );
$default_redirect = $urls['account'] ?? ( function_exists( 'altijara_account_url' ) ? altijara_account_url() : home_url( '/' ) );
$redirect_to      = $default_redirect;
if ( ! empty( $_GET['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$candidate = esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$redirect_to = wp_validate_redirect( $candidate, $default_redirect );
}
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
			<?php if ( $oauth_err ) : ?>
				<?php
				$oauth_code = isset( $_GET['oauth_error'] ) ? sanitize_key( wp_unslash( $_GET['oauth_error'] ) ) : '1'; // phpcs:ignore
				$oauth_msgs = array(
					'profile'     => __( 'تعذر قراءة بيانات حساب Facebook/Google. تأكد من صلاحية التطبيق ومنح إذن البريد إن طُلب.', 'altijara' ),
					'token'       => __( 'رفض Facebook رمز الدخول. تطابق رابط التحويل (Valid OAuth Redirect URI) مع الرابط في إعدادات القالب، وتأكد أن التطبيق Live وأن App Secret صحيح.', 'altijara' ),
					'config'      => __( 'إعدادات Facebook غير مكتملة في خيارات القالب (App ID / App Secret).', 'altijara' ),
					'code'        => __( 'لم يُرجع Facebook رمز تفويض صالحاً. أعد المحاولة.', 'altijara' ),
					'denied'      => __( 'تم إلغاء تسجيل الدخول من Facebook.', 'altijara' ),
					'user'        => __( 'تعذر إنشاء أو ربط حساب المستخدم بعد تسجيل الدخول الاجتماعي.', 'altijara' ),
					'state'       => __( 'انتهت صلاحية جلسة الدخول الاجتماعي. أعد المحاولة.', 'altijara' ),
					'unverified'  => __( 'بريد حسابك لدى المزود غير مؤكد. أكّد البريد عند Google أو Apple ثم أعد المحاولة، أو سجّل الدخول بكلمة المرور.', 'altijara' ),
				);
				$oauth_msg = $oauth_msgs[ $oauth_code ] ?? __( 'تعذر إكمال تسجيل الدخول الاجتماعي. تحقق من إعدادات Google أو Facebook أو Apple في خيارات القالب، ومن رابط التحويل (callback) في لوحة المزود.', 'altijara' );
				?>
				<div class="static-success is-visible auth-notice auth-notice--error" role="alert">
					<?php echo esc_html( $oauth_msg ); ?>
				</div>
			<?php endif; ?>
			<?php if ( $msg ) : ?>
				<div class="static-success is-visible auth-notice<?php echo $is_err ? ' auth-notice--error' : ''; ?>" role="alert">
					<?php echo esc_html( $msg ); ?>
				</div>
			<?php endif; ?>

			<form class="static-form" id="loginForm" action="<?php echo esc_url( get_permalink() ); ?>" method="post">
				<?php wp_nonce_field( 'altijara_login', 'altijara_auth_nonce' ); ?>
				<input type="hidden" name="altijara_login" value="1">
				<?php
				if ( function_exists( 'altijara_render_social_login_buttons' ) ) {
					altijara_render_social_login_buttons( 'login', $redirect_to );
				}
				?>
				<div class="static-field">
					<label for="lEmail"><?php esc_html_e( 'البريد الإلكتروني أو اسم المستخدم', 'altijara' ); ?></label>
					<input id="lEmail" name="log" type="text" required autocomplete="username" placeholder="name@example.com">
				</div>
				<div class="static-field">
					<label for="lPassword"><?php esc_html_e( 'كلمة المرور', 'altijara' ); ?></label>
					<input id="lPassword" name="pwd" type="password" required autocomplete="current-password" placeholder="••••••••">
				</div>

				<div class="auth-row">
					<label class="static-check">
						<input type="checkbox" name="rememberme" value="forever" checked>
						<span><?php esc_html_e( 'تذكرني', 'altijara' ); ?></span>
					</label>
					<a href="<?php echo esc_url( $reset ); ?>" class="static-text-link"><?php echo esc_html( $m['forgot_label'] ?? __( 'نسيت كلمة المرور؟', 'altijara' ) ); ?></a>
				</div>

				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>">

				<?php
				if ( function_exists( 'altijara_turnstile_widget_html' ) ) {
					altijara_turnstile_widget_html();
				}
				?>

				<div class="static-form-actions">
					<button type="submit" class="static-btn static-btn-primary auth-submit"><?php esc_html_e( 'دخول', 'altijara' ); ?></button>
				</div>

				<p class="static-form-note">
					<?php echo esc_html( $m['subscribe_note'] ?? __( 'ليس لديك حساب؟', 'altijara' ) ); ?>
					<?php if ( ! function_exists( 'altijara_subscriptions_enabled' ) || altijara_subscriptions_enabled() ) : ?>
						<a href="<?php echo esc_url( $sub ); ?>"><?php echo esc_html( $m['subscribe_link_label'] ?? __( 'اشترك', 'altijara' ) ); ?></a>
						·
					<?php endif; ?>
					<a href="<?php echo esc_url( $reg ); ?>"><?php esc_html_e( 'إنشاء حساب', 'altijara' ); ?></a>
				</p>
			</form>

			<?php if ( $show_resend ) : ?>
				<form class="static-form auth-verify-resend" method="post" action="<?php echo esc_url( get_permalink() ); ?>" style="margin-top:1.5rem;">
					<?php wp_nonce_field( 'altijara_verify_resend', 'altijara_auth_nonce' ); ?>
					<input type="hidden" name="altijara_verify_resend" value="1">
					<div class="static-field">
						<label for="verifyResendEmail"><?php esc_html_e( 'إعادة إرسال رابط تأكيد البريد', 'altijara' ); ?></label>
						<input id="verifyResendEmail" name="email" type="email" required autocomplete="email" value="<?php echo esc_attr( $prefill_email ); ?>" placeholder="name@example.com">
					</div>
					<?php
					if ( function_exists( 'altijara_turnstile_widget_html' ) ) {
						altijara_turnstile_widget_html();
					}
					?>
					<div class="static-form-actions">
						<button type="submit" class="static-btn auth-submit"><?php esc_html_e( 'إرسال الرابط', 'altijara' ); ?></button>
					</div>
				</form>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php

