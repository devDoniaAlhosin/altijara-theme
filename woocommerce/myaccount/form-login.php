<?php
/**
 * Login / Register — Altijara branded Woo My Account forms.
 *
 * @package Altijara
 * @version 9.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$urls = function_exists( 'altijara_theme_urls' ) ? altijara_theme_urls() : array();
$theme_reg   = $urls['register'] ?? '';
$theme_reset = ! empty( $urls['reset'] ) ? $urls['reset'] : wp_lostpassword_url();

do_action( 'woocommerce_before_customer_login_form' );
?>
<div class="container static-shell altijara-wc-auth">
	<section class="static-page static-page--narrow">
		<header class="static-page-header">
			<h1 class="static-title"><?php esc_html_e( 'تسجيل الدخول', 'altijara' ); ?></h1>
			<p class="static-lead"><?php esc_html_e( 'ادخل إلى حسابك لمتابعة الاشتراك والطلبات.', 'altijara' ); ?></p>
		</header>

		<div class="u-columns col2-set static-panel static-auth-panel" id="customer_login">
			<div class="u-column1 col-1">
				<?php if ( function_exists( 'altijara_render_social_login_buttons' ) ) : ?>
					<?php altijara_render_social_login_buttons( 'login' ); ?>
				<?php endif; ?>

				<form class="woocommerce-form woocommerce-form-login login static-form" method="post" novalidate>
					<?php do_action( 'woocommerce_login_form_start' ); ?>

					<div class="static-field">
						<label for="username"><?php esc_html_e( 'البريد أو اسم المستخدم', 'altijara' ); ?>&nbsp;<span class="required">*</span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required /><?php // phpcs:ignore ?>
					</div>
					<div class="static-field">
						<label for="password"><?php esc_html_e( 'كلمة المرور', 'altijara' ); ?>&nbsp;<span class="required">*</span></label>
						<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required />
					</div>

					<?php do_action( 'woocommerce_login_form' ); ?>

					<div class="auth-row">
						<label class="static-check woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
							<span><?php esc_html_e( 'تذكرني', 'altijara' ); ?></span>
						</label>
						<a class="static-text-link" href="<?php echo esc_url( $theme_reset ); ?>"><?php esc_html_e( 'نسيت كلمة المرور؟', 'altijara' ); ?></a>
					</div>

					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<div class="static-form-actions">
						<button type="submit" class="woocommerce-button button woocommerce-form-login__submit static-btn static-btn-primary<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'دخول', 'altijara' ); ?>"><?php esc_html_e( 'دخول', 'altijara' ); ?></button>
					</div>

					<?php do_action( 'woocommerce_login_form_end' ); ?>
				</form>
			</div>

			<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
				<div class="u-column2 col-2">
					<h2 class="static-title" style="font-size:1.25rem;"><?php esc_html_e( 'إنشاء حساب', 'altijara' ); ?></h2>
					<?php if ( $theme_reg ) : ?>
						<p class="static-form-note">
							<a class="static-btn static-btn-primary" href="<?php echo esc_url( $theme_reg ); ?>"><?php esc_html_e( 'إنشاء حساب جديد', 'altijara' ); ?></a>
						</p>
					<?php else : ?>
						<form method="post" class="woocommerce-form woocommerce-form-register register static-form" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
							<?php do_action( 'woocommerce_register_form_start' ); ?>
							<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
								<div class="static-field">
									<label for="reg_username"><?php esc_html_e( 'اسم المستخدم', 'altijara' ); ?></label>
									<input type="text" name="username" id="reg_username" autocomplete="username" required />
								</div>
							<?php endif; ?>
							<div class="static-field">
								<label for="reg_email"><?php esc_html_e( 'البريد الإلكتروني', 'altijara' ); ?></label>
								<input type="email" name="email" id="reg_email" autocomplete="email" required />
							</div>
							<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
								<div class="static-field">
									<label for="reg_password"><?php esc_html_e( 'كلمة المرور', 'altijara' ); ?></label>
									<input type="password" name="password" id="reg_password" autocomplete="new-password" required />
								</div>
							<?php endif; ?>
							<?php do_action( 'woocommerce_register_form' ); ?>
							<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
							<div class="static-form-actions">
								<button type="submit" class="static-btn static-btn-primary woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="register" value="<?php esc_attr_e( 'تسجيل', 'altijara' ); ?>"><?php esc_html_e( 'تسجيل', 'altijara' ); ?></button>
							</div>
							<?php do_action( 'woocommerce_register_form_end' ); ?>
						</form>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
</div>
<?php
do_action( 'woocommerce_after_customer_login_form' );
