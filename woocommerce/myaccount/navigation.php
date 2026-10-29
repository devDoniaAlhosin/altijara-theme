<?php
/**
 * My Account navigation — Altijara styled.
 *
 * @package Altijara
 * @version 9.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );
?>
<nav class="woocommerce-MyAccount-navigation altijara-wc-nav" aria-label="<?php esc_attr_e( 'حسابي', 'altijara' ); ?>">
	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<?php
do_action( 'woocommerce_after_account_navigation' );
