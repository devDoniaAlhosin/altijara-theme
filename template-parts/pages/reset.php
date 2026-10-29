<?php
/**
 * reset page content.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$urls  = function_exists( 'altijara_theme_urls' ) ? altijara_theme_urls() : array();
$login = $urls['login'] ?? home_url( '/login/' );
$notice  = isset( $_GET['auth_notice'] ) ? sanitize_key( wp_unslash( $_GET['auth_notice'] ) ) : ''; // phpcs:ignore
$msg     = $notice && function_exists( 'altijara_auth_notice_message' ) ? altijara_auth_notice_message( $notice ) : '';
$key     = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore
$ulogin  = isset( $_GET['login'] ) ? wp_unslash( $_GET['login'] ) : ''; // phpcs:ignore
$ulogin  = is_string( $ulogin ) ? rawurldecode( $ulogin ) : '';
$ulogin  = sanitize_user( $ulogin, true );
$reset_action = function_exists( 'altijara_reset_url' ) ? altijara_reset_url() : get_permalink();
$has_key = ( $key && $ulogin );
$is_ok   = in_array( $notice, array( 'reset_sent', 'reset_ok' ), true );
?>
<main class="container static-shell">
	<section class="static-page static-page--narrow">
		<header class="static-page-header">
			<h1 class="static-title"><?php the_title(); ?></h1>
			<p class="static-lead">
				<?php
				echo $has_key
					? esc_html__( 'أدخل كلمة مرور جديدة لحسابك.', 'altijara' )
					: esc_html__( 'أدخل بريدك لإرسال رابط إعادة التعيين.', 'altijara' );
				?>
			</p>
		</header>
		<div class="static-panel static-auth-panel">
			<?php if ( $msg ) : ?>
				<div class="static-success is-visible auth-notice<?php echo $is_ok ? '' : ' auth-notice--error'; ?>" role="alert"><?php echo esc_html( $msg ); ?></div>
			<?php endif; ?>

			<?php if ( $has_key ) : ?>
				<form class="static-form" method="post" action="<?php echo esc_url( $reset_action ); ?>">
					<?php wp_nonce_field( 'altijara_reset_set', 'altijara_auth_nonce' ); ?>
					<input type="hidden" name="altijara_reset_set" value="1">
					<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>">
					<input type="hidden" name="login" value="<?php echo esc_attr( $ulogin ); ?>">
					<div class="static-field">
						<label for="newPass"><?php esc_html_e( 'كلمة المرور الجديدة', 'altijara' ); ?></label>
						<input id="newPass" name="password" type="password" required minlength="8" autocomplete="new-password">
					</div>
					<div class="static-form-actions">
						<button type="submit" class="static-btn static-btn-primary auth-submit"><?php esc_html_e( 'حفظ كلمة المرور', 'altijara' ); ?></button>
					</div>
				</form>
			<?php else : ?>
				<form class="static-form" method="post" action="<?php echo esc_url( $reset_action ); ?>">
					<?php wp_nonce_field( 'altijara_reset', 'altijara_auth_nonce' ); ?>
					<input type="hidden" name="altijara_reset_request" value="1">
					<div class="static-field">
						<label for="userLogin"><?php esc_html_e( 'البريد أو اسم المستخدم', 'altijara' ); ?></label>
						<input id="userLogin" name="user_login" type="text" required autocomplete="username">
					</div>
					<?php
					if ( function_exists( 'altijara_turnstile_widget_html' ) ) {
						altijara_turnstile_widget_html();
					}
					?>
					<div class="static-form-actions">
						<button type="submit" class="static-btn static-btn-primary auth-submit"><?php esc_html_e( 'إرسال الرابط', 'altijara' ); ?></button>
					</div>
				</form>
			<?php endif; ?>
			<p class="static-form-note"><a href="<?php echo esc_url( $login ); ?>"><?php esc_html_e( 'العودة لتسجيل الدخول', 'altijara' ); ?></a></p>
		</div>
	</section>
</main>
<?php

