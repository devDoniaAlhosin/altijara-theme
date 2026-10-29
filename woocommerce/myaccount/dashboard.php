<?php
/**
 * My Account dashboard — Altijara plan summary + Woo intro.
 *
 * @package Altijara
 * @version 9.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed = array( 'a' => array( 'href' => true ) );
?>
<div class="altijara-wc-dashboard static-panel">
	<p>
		<?php
		printf(
			/* translators: 1: user display name 2: logout url */
			wp_kses( __( 'مرحباً %1$s (لست أنت؟ <a href="%2$s">تسجيل الخروج</a>)', 'altijara' ), $allowed ),
			'<strong>' . esc_html( wp_get_current_user()->display_name ) . '</strong>',
			esc_url( wc_logout_url() )
		);
		?>
	</p>
	<p>
		<?php
		$subs_on = ! function_exists( 'altijara_subscriptions_enabled' ) || altijara_subscriptions_enabled();
		if ( $subs_on ) {
			printf(
				/* translators: 1: orders URL 2: edit account URL */
				wp_kses( __( 'من لوحة حسابك يمكنك عرض <a href="%1$s">طلباتك ومدفوعاتك</a> و<a href="%2$s">تعديل بيانات الحساب</a>.', 'altijara' ), $allowed ),
				esc_url( wc_get_endpoint_url( 'orders' ) ),
				esc_url( wc_get_endpoint_url( 'edit-account' ) )
			);
		} else {
			printf(
				/* translators: 1: saved articles URL 2: edit account URL */
				wp_kses( __( 'من لوحة حسابك يمكنك عرض <a href="%1$s">محفوظاتي</a> و<a href="%2$s">تعديل بيانات الحساب</a>.', 'altijara' ), $allowed ),
				esc_url( wc_get_endpoint_url( 'saved' ) ),
				esc_url( wc_get_endpoint_url( 'edit-account' ) )
			);
		}
		?>
	</p>
	<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );
	?>
</div>
<?php
/**
 * Deprecated hooks kept for plugins.
 */
do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
