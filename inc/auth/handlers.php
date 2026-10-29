<?php
/**
 * Branded login / register / subscribe / reset form handlers.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account URL — Woo My Account only when WooCommerce is active.
 * Without Woo there is no cart / checkout / my-account page.
 *
 * @return string
 */
function altijara_account_url() {
	if ( class_exists( 'WooCommerce', false ) && function_exists( 'wc_get_page_permalink' ) ) {
		$wc = wc_get_page_permalink( 'myaccount' );
		if ( $wc && ! is_wp_error( $wc ) ) {
			return $wc;
		}
	}
	// No Woo → no account page; send readers to subscribe / home instead.
	return altijara_utility_page_url( 'subscribe', '/subscribe/' );
}

/**
 * Register URL.
 *
 * @return string
 */
function altijara_register_url() {
	return altijara_utility_page_url( 'register', '/register/' );
}

/**
 * Lost password URL (branded).
 *
 * @return string
 */
function altijara_reset_url() {
	return altijara_utility_page_url( 'reset', '/reset/' );
}

/**
 * Flash notice helper (query arg).
 *
 * @param string $code Code.
 * @param string $base Redirect base URL.
 * @return string
 */
function altijara_auth_redirect_with( $code, $base ) {
	return add_query_arg( 'auth_notice', sanitize_key( $code ), $base );
}

/**
 * Human message for auth_notice codes.
 *
 * @param string $code Code.
 * @return string
 */
function altijara_auth_notice_message( $code ) {
	$map = array(
		'invalid'       => __( 'تعذر تسجيل الدخول. تحقق من البيانات وحاول مرة أخرى.', 'altijara' ),
		'required'      => __( 'يرجى تعبئة جميع الحقول المطلوبة.', 'altijara' ),
		'email'         => __( 'البريد الإلكتروني غير صالح.', 'altijara' ),
		'exists'        => __( 'يوجد حساب مرتبط بهذا البريد. سجّل الدخول أو أعد تعيين كلمة المرور.', 'altijara' ),
		'weak'          => __( 'كلمة المرور قصيرة جداً (٨ أحرف على الأقل).', 'altijara' ),
		'consent'       => __( 'يجب الموافقة على الشروط للمتابعة.', 'altijara' ),
		'created'       => __( 'تم إنشاء حسابك. أكّد بريدك عبر الرابط المرسل إليك قبل تسجيل الدخول.', 'altijara' ),
		'subscribed'    => __( 'تم تفعيل اشتراكك بنجاح.', 'altijara' ),
		'nonce'         => __( 'انتهت صلاحية النموذج. أعد المحاولة.', 'altijara' ),
		'rate'          => __( 'محاولات كثيرة. انتظر قليلاً ثم أعد المحاولة.', 'altijara' ),
		'reset_sent'    => __( 'تحقق من بريدك الإلكتروني لتعليمات إعادة تعيين كلمة المرور. قد تستغرق الرسالة دقيقة، وتظهر أحياناً في المجلد غير المرغوب فيه.', 'altijara' ),
		'reset_mail'    => __( 'تعذر إرسال رسالة إعادة التعيين من الخادم. ثبّت إضافة SMTP (مثل WP Mail SMTP) واضبط البريد الصادر، ثم أعد المحاولة.', 'altijara' ),
		'reset_ok'      => __( 'تم تحديث كلمة المرور. يمكنك تسجيل الدخول الآن.', 'altijara' ),
		'reset_fail'    => __( 'رابط إعادة التعيين غير صالح أو منتهٍ، أو كلمة المرور قصيرة جداً (٨ أحرف على الأقل).', 'altijara' ),
		'plan_invalid'  => __( 'الباقة المحددة غير متاحة.', 'altijara' ),
		'payments_off'  => __( 'الدفع الإلكتروني غير متاح حالياً. يمكنك الاشتراك في النشرة المجانية.', 'altijara' ),
		'checkout_fail' => __( 'تعذر بدء الدفع. تأكد من إعدادات المنتجات أو حاول لاحقاً.', 'altijara' ),
		'logged_out'    => __( 'تم تسجيل الخروج.', 'altijara' ),
		'verify_sent'   => __( 'أرسلنا رابط تأكيد إلى بريدك. تحقق من الوارد ومجلد الرسائل غير المرغوب فيها.', 'altijara' ),
		'verify_needed' => __( 'يجب تأكيد بريدك قبل تسجيل الدخول. أعد إرسال رابط التأكيد أدناه إن لم تصلك الرسالة.', 'altijara' ),
		'verified_ok'   => __( 'تم تأكيد بريدك بنجاح. أهلاً بك.', 'altijara' ),
		'verify_fail'   => __( 'رابط تأكيد البريد غير صالح أو منتهٍ. اطلب رابطاً جديداً من صفحة الدخول.', 'altijara' ),
		'captcha'       => __( 'فشل التحقق الأمني. أكّد أنك لست روبوتاً ثم أعد المحاولة.', 'altijara' ),
	);
	return $map[ sanitize_key( $code ) ] ?? '';
}

/**
 * Simple IP rate limit (transient).
 *
 * @param string $action Action key.
 * @param int    $max    Max attempts.
 * @param int    $window Seconds.
 * @return bool True if allowed.
 */
function altijara_auth_rate_allow( $action, $max = 8, $window = 900 ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$key = 'altijara_rl_' . md5( $action . '|' . $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= $max ) {
		return false;
	}
	set_transient( $key, $n + 1, $window );
	return true;
}

/**
 * Handle POST login (theme form).
 */
function altijara_handle_login_post() {
	if ( empty( $_POST['altijara_login'] ) ) {
		return;
	}
	$login_url = altijara_utility_page_url( 'login', '/login/' );
	if ( ! isset( $_POST['altijara_auth_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_auth_nonce'] ) ), 'altijara_login' ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'nonce', $login_url ) );
		exit;
	}
	if ( function_exists( 'altijara_turnstile_required' ) && altijara_turnstile_required() && function_exists( 'altijara_turnstile_verify_request' ) && ! altijara_turnstile_verify_request() ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'captcha', $login_url ) );
		exit;
	}
	if ( ! altijara_auth_rate_allow( 'login' ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'rate', $login_url ) );
		exit;
	}

	$log = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
	$pwd = isset( $_POST['pwd'] ) ? (string) wp_unslash( $_POST['pwd'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( '' === $log || '' === $pwd ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'required', $login_url ) );
		exit;
	}

	$creds = array(
		'user_login'    => $log,
		'user_password' => $pwd,
		'remember'      => ! empty( $_POST['rememberme'] ),
	);
	// Allow email login.
	if ( is_email( $log ) ) {
		$user_by_email = get_user_by( 'email', $log );
		if ( $user_by_email ) {
			$creds['user_login'] = $user_by_email->user_login;
		}
	}

	$user = wp_signon( $creds, is_ssl() );
	if ( is_wp_error( $user ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'invalid', $login_url ) );
		exit;
	}

	if ( function_exists( 'altijara_user_email_is_verified' ) && ! altijara_user_email_is_verified( (int) $user->ID ) ) {
		wp_logout();
		$fail = add_query_arg(
			array(
				'auth_notice' => 'verify_needed',
				'verify_email' => rawurlencode( $user->user_email ),
			),
			$login_url
		);
		wp_safe_redirect( $fail );
		exit;
	}

	$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
	$redirect = $redirect ? wp_validate_redirect( $redirect, altijara_account_url() ) : altijara_account_url();
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'template_redirect', 'altijara_handle_login_post', 5 );

/**
 * Create WP user from subscribe/register fields.
 *
 * @param string $name  Display name.
 * @param string $email Email.
 * @param string $pass  Password.
 * @return int|WP_Error User ID.
 */
function altijara_create_reader_user( $name, $email, $pass ) {
	$email = sanitize_email( $email );
	$name  = sanitize_text_field( $name );
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'email', 'email' );
	}
	if ( email_exists( $email ) || username_exists( $email ) ) {
		return new WP_Error( 'exists', 'exists' );
	}
	if ( strlen( $pass ) < 8 ) {
		return new WP_Error( 'weak', 'weak' );
	}
	$login = sanitize_user( current( explode( '@', $email ) ), true );
	if ( ! $login || username_exists( $login ) ) {
		$login = sanitize_user( 'u' . wp_generate_password( 8, false ), true );
	}
	$user_id = wp_insert_user(
		array(
			'user_login'   => $login,
			'user_email'   => $email,
			'user_pass'    => $pass,
			'display_name' => $name ? $name : $login,
			// Match social login + WooCommerce: storefront readers are customers.
			'role'         => class_exists( 'WooCommerce', false ) ? 'customer' : 'subscriber',
		)
	);
	return $user_id;
}

/**
 * Handle subscribe form POST.
 */
function altijara_handle_subscribe_post() {
	if ( empty( $_POST['altijara_subscribe'] ) ) {
		return;
	}
	$sub_url = altijara_utility_page_url( 'subscribe', '/subscribe/' );
	if ( ! isset( $_POST['altijara_auth_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_auth_nonce'] ) ), 'altijara_subscribe' ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'nonce', $sub_url ) );
		exit;
	}
	if ( function_exists( 'altijara_turnstile_required' ) && altijara_turnstile_required() && function_exists( 'altijara_turnstile_verify_request' ) && ! altijara_turnstile_verify_request() ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'captcha', $sub_url ) );
		exit;
	}
	if ( ! altijara_auth_rate_allow( 'subscribe', 6 ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'rate', $sub_url ) );
		exit;
	}
	if ( empty( $_POST['consent'] ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'consent', $sub_url ) );
		exit;
	}

	$plan = isset( $_POST['plan'] ) ? sanitize_key( wp_unslash( $_POST['plan'] ) ) : 'newsletter';
	$tiers = function_exists( 'altijara_subscription_tiers' ) ? altijara_subscription_tiers() : array();
	if ( empty( $tiers[ $plan ] ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'plan_invalid', $sub_url ) );
		exit;
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$pass  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore
		$created = altijara_create_reader_user( $name, $email, $pass );
		if ( is_wp_error( $created ) ) {
			wp_safe_redirect( altijara_auth_redirect_with( $created->get_error_code(), $sub_url ) );
			exit;
		}
		$user_id = (int) $created;
		// Require inbox confirmation before session / checkout.
		if ( function_exists( 'altijara_start_email_verification' ) ) {
			altijara_start_email_verification( $user_id, $sub_url );
		}
		$login_url = altijara_utility_page_url( 'login', '/login/' );
		wp_safe_redirect(
			add_query_arg(
				array(
					'auth_notice'  => 'verify_sent',
					'verify_email' => rawurlencode( $email ),
				),
				$login_url
			)
		);
		exit;
	}

	$is_paid = ! empty( $tiers[ $plan ]['paid'] );

	if ( ! $is_paid ) {
		if ( function_exists( 'altijara_activate_user_plan' ) ) {
			altijara_activate_user_plan( $user_id, $plan );
		}
		$after = class_exists( 'WooCommerce', false ) ? altijara_account_url() : home_url( '/' );
		wp_safe_redirect( altijara_auth_redirect_with( 'subscribed', $after ) );
		exit;
	}

	// Paid path — requires Woo + product map.
	if ( ! function_exists( 'altijara_payments_available' ) || ! altijara_payments_available() || ! altijara_paid_plans_available() ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'payments_off', $sub_url ) );
		exit;
	}

	$product_id = altijara_tier_product_id( $plan );
	if ( ! $product_id || ! function_exists( 'altijara_wc_start_checkout_for_tier' ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'checkout_fail', $sub_url ) );
		exit;
	}

	$result = altijara_wc_start_checkout_for_tier( $user_id, $plan, $product_id );
	if ( is_wp_error( $result ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'checkout_fail', $sub_url ) );
		exit;
	}
	wp_safe_redirect( $result );
	exit;
}
add_action( 'template_redirect', 'altijara_handle_subscribe_post', 5 );

/**
 * Handle register form (standalone).
 */
function altijara_handle_register_post() {
	if ( empty( $_POST['altijara_register'] ) ) {
		return;
	}
	$reg_url = altijara_register_url();
	if ( ! isset( $_POST['altijara_auth_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_auth_nonce'] ) ), 'altijara_register' ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'nonce', $reg_url ) );
		exit;
	}
	if ( function_exists( 'altijara_turnstile_required' ) && altijara_turnstile_required() && function_exists( 'altijara_turnstile_verify_request' ) && ! altijara_turnstile_verify_request() ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'captcha', $reg_url ) );
		exit;
	}
	if ( ! altijara_auth_rate_allow( 'register', 6 ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'rate', $reg_url ) );
		exit;
	}
	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$pass  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore
	$created = altijara_create_reader_user( $name, $email, $pass );
	if ( is_wp_error( $created ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( $created->get_error_code(), $reg_url ) );
		exit;
	}
	if ( function_exists( 'altijara_start_email_verification' ) ) {
		altijara_start_email_verification( (int) $created );
	}
	$login_url = altijara_utility_page_url( 'login', '/login/' );
	wp_safe_redirect(
		add_query_arg(
			array(
				'auth_notice'  => 'verify_sent',
				'verify_email' => rawurlencode( $email ),
			),
			$login_url
		)
	);
	exit;
}
add_action( 'template_redirect', 'altijara_handle_register_post', 5 );

/**
 * Branded subject for core retrieve_password().
 *
 * @param string  $title      Default title.
 * @param string  $user_login Login.
 * @param WP_User $user_data  User.
 * @return string
 */
function altijara_retrieve_password_title( $title, $user_login = '', $user_data = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$emails = function_exists( 'altijara_get_emails_settings' ) ? altijara_get_emails_settings() : array();
	if ( ! empty( $emails['reset_subject'] ) ) {
		return (string) $emails['reset_subject'];
	}
	return __( 'إعادة تعيين كلمة المرور — مجلة التجارة', 'altijara' );
}

/**
 * Branded reset link for core retrieve_password() (not wp-login.php).
 *
 * @param string  $message    Default message.
 * @param string  $key        Reset key.
 * @param string  $user_login Login.
 * @param WP_User $user_data  User.
 * @return string
 */
function altijara_retrieve_password_message( $message, $key, $user_login, $user_data ) {
	$reset_url = altijara_reset_url();
	$link      = add_query_arg(
		array(
			'key'   => $key,
			'login' => rawurlencode( $user_login ),
		),
		$reset_url
	);
	$emails = function_exists( 'altijara_get_emails_settings' ) ? altijara_get_emails_settings() : array();
	$name   = ( $user_data instanceof WP_User && $user_data->display_name ) ? $user_data->display_name : $user_login;
	$title  = ! empty( $emails['reset_heading'] ) ? (string) $emails['reset_heading'] : __( 'إعادة تعيين كلمة المرور', 'altijara' );
	$intro  = ! empty( $emails['reset_intro'] ) ? (string) $emails['reset_intro'] : __( 'طلبت إعادة تعيين كلمة المرور. اضغط الزر التالي (الرابط صالح لفترة محدودة):', 'altijara' );
	$cta    = ! empty( $emails['reset_cta'] ) ? (string) $emails['reset_cta'] : __( 'تعيين كلمة مرور جديدة', 'altijara' );
	$body   = '<p>' . sprintf(
		/* translators: %s: display name */
		esc_html__( 'مرحباً %s،', 'altijara' ),
		esc_html( $name )
	) . '</p>';
	$body .= '<p>' . esc_html( $intro ) . '</p>';
	if ( function_exists( 'altijara_mail_cta_html' ) ) {
		$body .= altijara_mail_cta_html( $link, $cta );
	} else {
		$body .= '<p><a href="' . esc_url( $link ) . '">' . esc_html( $link ) . '</a></p>';
	}
	$body .= '<p style="margin-top:24px;font-size:13px;color:#6e7787;">' . esc_html__( 'إن لم تطلب ذلك فتجاهل هذه الرسالة.', 'altijara' ) . '</p>';

	if ( function_exists( 'altijara_mail_wrap_html' ) ) {
		return altijara_mail_wrap_html( $title, $body );
	}
	return wp_strip_all_tags( $body ) . "\n\n" . $link;
}

/**
 * Handle lost-password request via WordPress core mail path (SMTP plugins apply).
 */
function altijara_handle_reset_request_post() {
	if ( empty( $_POST['altijara_reset_request'] ) ) {
		return;
	}
	$reset_url = altijara_reset_url();
	if ( ! isset( $_POST['altijara_auth_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_auth_nonce'] ) ), 'altijara_reset' ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'nonce', $reset_url ) );
		exit;
	}
	if ( function_exists( 'altijara_turnstile_required' ) && altijara_turnstile_required() && function_exists( 'altijara_turnstile_verify_request' ) && ! altijara_turnstile_verify_request() ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'captcha', $reset_url ) );
		exit;
	}
	if ( ! altijara_auth_rate_allow( 'reset', 5 ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'rate', $reset_url ) );
		exit;
	}
	$user_login = isset( $_POST['user_login'] ) ? trim( wp_unslash( $_POST['user_login'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$user_login = sanitize_text_field( $user_login );

	if ( '' === $user_login ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'required', $reset_url ) );
		exit;
	}

	// Unknown account → same success copy (no enumeration). Known account → core mail.
	$user = is_email( $user_login ) ? get_user_by( 'email', $user_login ) : get_user_by( 'login', $user_login );
	if ( ! $user instanceof WP_User ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'reset_sent', $reset_url ) );
		exit;
	}

	add_filter( 'retrieve_password_title', 'altijara_retrieve_password_title', 10, 3 );
	add_filter( 'retrieve_password_message', 'altijara_retrieve_password_message', 10, 4 );
	if ( function_exists( 'altijara_mail_content_type_html' ) ) {
		add_filter( 'wp_mail_content_type', 'altijara_mail_content_type_html' );
	}
	if ( function_exists( 'altijara_mail_filter_from_name' ) ) {
		add_filter( 'wp_mail_from_name', 'altijara_mail_filter_from_name' );
	}
	// Core expects the login name (not email) once the user is resolved.
	$result = retrieve_password( $user->user_login );
	remove_filter( 'retrieve_password_title', 'altijara_retrieve_password_title', 10 );
	remove_filter( 'retrieve_password_message', 'altijara_retrieve_password_message', 10 );
	if ( function_exists( 'altijara_mail_filter_from_name' ) ) {
		remove_filter( 'wp_mail_from_name', 'altijara_mail_filter_from_name' );
	}
	if ( function_exists( 'altijara_mail_content_type_html' ) ) {
		remove_filter( 'wp_mail_content_type', 'altijara_mail_content_type_html' );
	}

	if ( is_wp_error( $result ) ) {
		$code = $result->get_error_code();
		if ( in_array( $code, array( 'retrieve_password_email_failure', 'wp_mail_failed' ), true ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Altijara: password reset mail failed — ' . $result->get_error_message() );
			}
			wp_safe_redirect( altijara_auth_redirect_with( 'reset_mail', $reset_url ) );
			exit;
		}
		// Still avoid enumerating obscure lookup errors.
		wp_safe_redirect( altijara_auth_redirect_with( 'reset_sent', $reset_url ) );
		exit;
	}

	wp_safe_redirect( altijara_auth_redirect_with( 'reset_sent', $reset_url ) );
	exit;
}
add_action( 'template_redirect', 'altijara_handle_reset_request_post', 5 );

/**
 * Point WordPress / WooCommerce “lost password” links to the branded page.
 *
 * @param string $url Lost-password URL.
 * @return string
 */
function altijara_filter_lostpassword_url( $url ) {
	$branded = altijara_reset_url();
	return $branded ? $branded : $url;
}
add_filter( 'lostpassword_url', 'altijara_filter_lostpassword_url', 20 );

/**
 * Do not cache auth forms (stale nonces break login / reset / social start).
 */
function altijara_nocache_auth_pages() {
	if ( is_admin() || ! is_page() ) {
		return;
	}
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	if ( ! in_array( $slug, array( 'login', 'register', 'reset', 'subscribe' ), true ) ) {
		return;
	}
	nocache_headers();
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
}
add_action( 'template_redirect', 'altijara_nocache_auth_pages', 0 );

/**
 * Handle set-new-password.
 */
function altijara_handle_reset_set_post() {
	if ( empty( $_POST['altijara_reset_set'] ) ) {
		return;
	}
	$reset_url = altijara_reset_url();
	if ( ! isset( $_POST['altijara_auth_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_auth_nonce'] ) ), 'altijara_reset_set' ) ) {
		wp_safe_redirect( altijara_auth_redirect_with( 'nonce', $reset_url ) );
		exit;
	}
	$login = isset( $_POST['login'] ) ? sanitize_user( wp_unslash( $_POST['login'] ), true ) : '';
	$key   = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$pass  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore
	// Tolerate a once-extra-encoded login from older reset emails.
	if ( $login && false !== strpos( $login, '%' ) ) {
		$decoded = rawurldecode( $login );
		if ( $decoded && $decoded !== $login ) {
			$login = sanitize_user( $decoded, true );
		}
	}
	$user = ( $key && $login ) ? check_password_reset_key( $key, $login ) : new WP_Error( 'invalid', 'invalid' );
	if ( is_wp_error( $user ) || strlen( $pass ) < 8 ) {
		$fail = altijara_auth_redirect_with( 'reset_fail', $reset_url );
		if ( $key && $login ) {
			$fail = add_query_arg(
				array(
					'key'   => $key,
					'login' => $login,
				),
				$fail
			);
		}
		wp_safe_redirect( $fail );
		exit;
	}
	reset_password( $user, $pass );
	$login_url = altijara_utility_page_url( 'login', '/login/' );
	wp_safe_redirect( altijara_auth_redirect_with( 'reset_ok', $login_url ) );
	exit;
}
add_action( 'template_redirect', 'altijara_handle_reset_set_post', 5 );

/**
 * Logout via ?altijara_logout=1&_wpnonce=
 */
function altijara_handle_logout() {
	if ( empty( $_GET['altijara_logout'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore
	if ( ! wp_verify_nonce( $nonce, 'altijara_logout' ) ) {
		return;
	}
	wp_logout();
	wp_safe_redirect( altijara_auth_redirect_with( 'logged_out', altijara_utility_page_url( 'login', '/login/' ) ) );
	exit;
}
add_action( 'template_redirect', 'altijara_handle_logout', 4 );

/**
 * Theme login URL. Sign-out and the old account login both land here.
 *
 * @return string
 */
function altijara_login_page_url() {
	return function_exists( 'altijara_utility_page_url' )
		? altijara_utility_page_url( 'login', '/login/' )
		: home_url( '/login/' );
}

/**
 * Front-end / Woo logout → member /login/. Admin logout → custom admin portal.
 *
 * @param string $redirect Requested redirect.
 * @return string
 */
function altijara_logout_redirect_to_login( $redirect ) {
	$slug = function_exists( 'altijara_admin_login_slug' ) ? altijara_admin_login_slug() : 'altijaramagazine-admin-login';

	if ( is_string( $redirect ) && false !== strpos( $redirect, $slug ) && function_exists( 'altijara_admin_login_url' ) ) {
		return altijara_admin_login_url();
	}

	$referer = wp_get_referer();
	if ( is_string( $referer ) && false !== strpos( $referer, '/wp-admin' ) && function_exists( 'altijara_admin_login_url' ) ) {
		return altijara_admin_login_url();
	}

	if ( ! empty( $_REQUEST['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$requested = esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( false !== strpos( $requested, $slug ) && function_exists( 'altijara_admin_login_url' ) ) {
			return altijara_admin_login_url();
		}
	}

	return altijara_login_page_url();
}
add_filter( 'woocommerce_logout_default_redirect_url', 'altijara_logout_redirect_to_login', 100000 );
add_filter( 'logout_redirect', 'altijara_logout_redirect_to_login', 100000 );

/**
 * Logged-out visits to My Account use the theme login page, not the WooCommerce form.
 */
function altijara_redirect_guest_account_to_login() {
	if ( is_admin() || wp_doing_ajax() || is_user_logged_in() ) {
		return;
	}
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}
	$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$uri    = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$target = ( is_ssl() ? 'https://' : 'http://' ) . $host . $uri;
	$target = wp_validate_redirect( $target, home_url( '/' ) );
	wp_safe_redirect( add_query_arg( 'redirect_to', $target, altijara_login_page_url() ) );
	exit;
}
add_action( 'template_redirect', 'altijara_redirect_guest_account_to_login', 8 );

/**
 * Redirect logged-in users away from login/register toward account.
 */
function altijara_redirect_logged_in_from_auth_pages() {
	if ( ! is_user_logged_in() || is_admin() ) {
		return;
	}
	if ( ! is_page() ) {
		return;
	}
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	if ( in_array( $slug, array( 'login', 'register' ), true ) ) {
		wp_safe_redirect( altijara_account_url() );
		exit;
	}
}
add_action( 'template_redirect', 'altijara_redirect_logged_in_from_auth_pages', 20 );

/**
 * Ensure login / register / reset / subscribe pages exist (not cart / checkout / my-account).
 * Also trash any theme-created "account" page left over from earlier setups.
 */
function altijara_maybe_ensure_auth_pages() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( get_option( 'altijara_auth_pages_v2' ) ) {
		return;
	}
	if ( function_exists( 'altijara_ensure_utility_pages' ) ) {
		altijara_ensure_utility_pages();
	}
	if ( function_exists( 'altijara_retire_theme_account_page' ) ) {
		altijara_retire_theme_account_page();
	}
	update_option( 'altijara_auth_pages_v2', 1, false );
}
add_action( 'admin_init', 'altijara_maybe_ensure_auth_pages', 30 );

/**
 * Remove theme-owned /account/ page — My Account belongs to WooCommerce only.
 */
function altijara_retire_theme_account_page() {
	$ids = altijara_get_option( 'utility_page_ids', array() );
	if ( ! is_array( $ids ) ) {
		$ids = array();
	}

	$candidates = array();
	if ( ! empty( $ids['account'] ) ) {
		$candidates[] = (int) $ids['account'];
	}
	$by_path = get_page_by_path( 'account' );
	if ( $by_path instanceof WP_Post ) {
		$candidates[] = (int) $by_path->ID;
	}

	$candidates = array_unique( array_filter( $candidates ) );
	foreach ( $candidates as $page_id ) {
		$tpl = (string) get_post_meta( $page_id, '_wp_page_template', true );
		// Only retire theme account template — never touch Woo page templates.
		if ( 'page-account.php' === $tpl || '' === $tpl || 'default' === $tpl ) {
			// If this page is Woo's myaccount, leave it.
			if ( class_exists( 'WooCommerce', false ) && function_exists( 'wc_get_page_id' ) ) {
				$woo_id = (int) wc_get_page_id( 'myaccount' );
				if ( $woo_id && $woo_id === $page_id ) {
					continue;
				}
			}
			if ( 'page-account.php' === $tpl ) {
				wp_trash_post( $page_id );
			}
		}
	}

	if ( isset( $ids['account'] ) ) {
		unset( $ids['account'] );
		altijara_update_options( array( 'utility_page_ids' => $ids ) );
	}
}
