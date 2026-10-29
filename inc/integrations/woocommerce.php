<?php
/**
 * WooCommerce soft integration — payment gateways only; safe when WC inactive.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether WooCommerce is active and usable for checkout.
 *
 * @return bool
 */
function altijara_payments_available() {
	if ( ! class_exists( 'WooCommerce', false ) ) {
		return false;
	}
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return false;
	}
	$checkout_id = (int) wc_get_page_id( 'checkout' );
	if ( $checkout_id <= 0 || 'publish' !== get_post_status( $checkout_id ) ) {
		return false;
	}
	return (bool) apply_filters( 'altijara_payments_available', true );
}

/**
 * Bootstrap WC theme support only when plugin is present.
 */
function altijara_wc_bootstrap() {
	if ( ! class_exists( 'WooCommerce', false ) ) {
		return;
	}

	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Magazine is not a shop — hide catalog from menus / disable default shop redirects noise.
	add_filter( 'woocommerce_enqueue_styles', 'altijara_wc_dequeue_default_styles' );
	add_action( 'wp_enqueue_scripts', 'altijara_wc_enqueue_theme_skin', 40 );

	add_filter( 'woocommerce_account_menu_items', 'altijara_wc_account_menu_items' );
	add_action( 'woocommerce_account_dashboard', 'altijara_wc_account_plan_summary', 5 );

	add_action( 'woocommerce_order_status_processing', 'altijara_wc_activate_plan_from_order' );
	add_action( 'woocommerce_order_status_completed', 'altijara_wc_activate_plan_from_order' );
	add_action( 'woocommerce_order_status_cancelled', 'altijara_wc_maybe_revoke_plan_from_order' );
	add_action( 'woocommerce_order_status_refunded', 'altijara_wc_maybe_revoke_plan_from_order' );
	add_action( 'woocommerce_order_status_failed', 'altijara_wc_maybe_revoke_plan_from_order' );

	// Soften storefront: redirect shop to subscribe.
	add_action( 'template_redirect', 'altijara_wc_redirect_shop_to_subscribe' );
	add_action( 'template_redirect', 'altijara_wc_redirect_orders_when_subs_off', 20 );

	add_filter( 'body_class', 'altijara_wc_body_class' );

	add_filter( 'the_title', 'altijara_wc_arabic_page_title', 10, 2 );
	add_filter( 'document_title_parts', 'altijara_wc_document_title_parts', 25 );

	// Persist tier key from cart → order line item.
	add_filter( 'woocommerce_get_item_data', 'altijara_wc_display_cart_tier', 10, 2 );
	add_action( 'woocommerce_checkout_create_order_line_item', 'altijara_wc_save_tier_on_order_item', 10, 4 );

	// Branded emails: theme overrides in woocommerce/emails/{email-header,email-footer,email-styles}.php
	// (RTL is set via dir="rtl" on the HTML shell when is_rtl().)
}
add_action( 'after_setup_theme', 'altijara_wc_bootstrap', 40 );

/**
 * Arabic front-end title for Cart / Checkout / My Account.
 *
 * @return string Empty when not on a WC commerce page.
 */
function altijara_wc_commerce_title() {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return __( 'السلة', 'altijara' );
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
			return '';
		}
		return __( 'إتمام الطلب', 'altijara' );
	}
	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		return __( 'حسابي', 'altijara' );
	}
	return '';
}

/**
 * Replace WP page titles on Woo surfaces (main loop only).
 *
 * @param string $title   Title.
 * @param int    $post_id Post ID.
 * @return string
 */
function altijara_wc_arabic_page_title( $title, $post_id = 0 ) {
	if ( is_admin() ) {
		return $title;
	}
	$post_id = (int) $post_id;
	if ( $post_id < 1 || ! function_exists( 'wc_get_page_id' ) ) {
		return $title;
	}
	$pages = array(
		(int) wc_get_page_id( 'cart' ),
		(int) wc_get_page_id( 'checkout' ),
		(int) wc_get_page_id( 'myaccount' ),
	);
	if ( ! in_array( $post_id, $pages, true ) ) {
		return $title;
	}
	$arabic = altijara_wc_commerce_title();
	return $arabic !== '' ? $arabic : $title;
}

/**
 * Match browser tab titles to Arabic WC page labels.
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function altijara_wc_document_title_parts( $parts ) {
	if ( ! is_array( $parts ) ) {
		return $parts;
	}
	$arabic = altijara_wc_commerce_title();
	if ( $arabic !== '' ) {
		$parts['title'] = $arabic;
	}
	return $parts;
}

/**
 * Show tier in cart (optional).
 *
 * @param array<int, array<string, string>> $item_data Item data.
 * @param array<string, mixed>              $cart_item Cart item.
 * @return array<int, array<string, string>>
 */
function altijara_wc_display_cart_tier( $item_data, $cart_item ) {
	if ( ! empty( $cart_item['altijara_tier'] ) ) {
		$item_data[] = array(
			'key'   => __( 'الباقة', 'altijara' ),
			'value' => sanitize_key( $cart_item['altijara_tier'] ),
		);
	}
	return $item_data;
}

/**
 * Copy cart tier meta onto order line item.
 *
 * @param WC_Order_Item_Product $item          Item.
 * @param string                $cart_item_key Key.
 * @param array<string, mixed>  $values        Cart values.
 * @param WC_Order              $order         Order.
 */
function altijara_wc_save_tier_on_order_item( $item, $cart_item_key, $values, $order ) {
	if ( ! empty( $values['altijara_tier'] ) ) {
		$item->add_meta_data( 'altijara_tier', sanitize_key( $values['altijara_tier'] ), true );
	}
}

/**
 * Dequeue heavy Woo gallery CSS on non-product pages; keep checkout/account minimal.
 *
 * @param array<string, string> $styles Styles.
 * @return array<string, string>
 */
function altijara_wc_dequeue_default_styles( $styles ) {
	if ( is_cart() || is_checkout() || is_account_page() ) {
		return $styles;
	}
	// Outside commerce surfaces, drop Woo styles entirely.
	return array();
}

/**
 * Theme skin for account / checkout / login.
 */
function altijara_wc_enqueue_theme_skin() {
	if ( ! altijara_payments_available() ) {
		return;
	}
	if ( ! ( is_cart() || is_checkout() || is_account_page() ) ) {
		return;
	}
	$path = get_template_directory() . '/assets/css/woocommerce-account.css';
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : ALTIJARA_THEME_VERSION;
	wp_enqueue_style(
		'altijara-woocommerce',
		altijara_asset( 'assets/css/woocommerce-account.css' ),
		array( 'altijara-main' ),
		$ver
	);
}

/**
 * Slim My Account menu.
 *
 * @param array<string, string> $items Items.
 * @return array<string, string>
 */
function altijara_wc_account_menu_items( $items ) {
	$subs_on = ! function_exists( 'altijara_subscriptions_enabled' ) || altijara_subscriptions_enabled();
	$keep    = $subs_on
		? array( 'dashboard', 'saved', 'orders', 'edit-account', 'customer-logout' )
		: array( 'dashboard', 'saved', 'edit-account', 'customer-logout' );
	$out     = array();
	foreach ( $keep as $key ) {
		if ( isset( $items[ $key ] ) ) {
			$out[ $key ] = $items[ $key ];
		} elseif ( 'saved' === $key ) {
			$out['saved'] = __( 'محفوظاتي', 'altijara' );
		}
	}
	// Arabic labels.
	if ( isset( $out['dashboard'] ) ) {
		$out['dashboard'] = __( 'لوحة الحساب', 'altijara' );
	}
	if ( isset( $out['saved'] ) ) {
		$out['saved'] = __( 'محفوظاتي', 'altijara' );
	}
	if ( isset( $out['orders'] ) ) {
		$out['orders'] = __( 'المدفوعات والطلبات', 'altijara' );
	}
	if ( isset( $out['edit-account'] ) ) {
		$out['edit-account'] = __( 'الملف الشخصي', 'altijara' );
	}
	if ( isset( $out['customer-logout'] ) ) {
		$out['customer-logout'] = __( 'تسجيل الخروج', 'altijara' );
	}
	return $out;
}

/**
 * Plan summary on Woo dashboard.
 */
function altijara_wc_account_plan_summary() {
	if ( function_exists( 'altijara_subscriptions_enabled' ) && ! altijara_subscriptions_enabled() ) {
		return;
	}
	if ( ! function_exists( 'altijara_get_user_plan' ) ) {
		return;
	}
	$user_id = get_current_user_id();
	$plan    = altijara_get_user_plan( $user_id );
	$tiers   = altijara_subscription_tiers();
	$active  = (bool) ( $plan && isset( $tiers[ $plan ] ) );
	$label   = $active ? $tiers[ $plan ]['label'] : __( 'لا توجد باقة نشطة', 'altijara' );
	$expiry  = (int) get_user_meta( $user_id, ALTIJARA_META_PLAN_EXPIRY, true );
	$sub     = function_exists( 'altijara_theme_urls' ) ? ( altijara_theme_urls()['subscribe'] ?? '' ) : '';

	echo '<div class="altijara-account-plan' . ( $active ? ' is-active-plan' : ' is-empty-plan' ) . '">';
	echo '<div class="altijara-account-plan__head">';
	echo '<h3 class="altijara-account-plan__title">' . esc_html__( 'باقة الاشتراك', 'altijara' ) . '</h3>';
	echo '<span class="altijara-account-plan__badge">' . esc_html( $active ? __( 'نشطة', 'altijara' ) : __( 'غير مشترَك', 'altijara' ) ) . '</span>';
	echo '</div>';
	echo '<p class="altijara-account-plan__label">' . esc_html( $label ) . '</p>';
	if ( $expiry > 0 ) {
		echo '<p class="altijara-account-plan__expiry">' . esc_html(
			sprintf(
				/* translators: %s: date */
				__( 'تنتهي في: %s', 'altijara' ),
				function_exists( 'altijara_format_date_ar' ) ? altijara_format_date_ar( $expiry ) : gmdate( 'Y-m-d', $expiry )
			)
		) . '</p>';
	}
	if ( $sub ) {
		echo '<div class="altijara-account-plan__actions">';
		echo '<a class="altijara-account-btn" href="' . esc_url( $sub ) . '">' . esc_html__( 'تغيير الباقة / اشترك', 'altijara' ) . '</a>';
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Body class when on WC surfaces.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function altijara_wc_body_class( $classes ) {
	if ( function_exists( 'is_account_page' ) && ( is_account_page() || is_checkout() || is_cart() ) ) {
		$classes[] = 'altijara-wc-surface';
		$classes[] = 'static-shell-wc';
	}
	return $classes;
}

/**
 * Redirect shop archive to subscribe (no public catalog).
 */
function altijara_wc_redirect_shop_to_subscribe() {
	if ( ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}
	$url = function_exists( 'altijara_theme_urls' )
		? ( altijara_theme_urls()['subscribe'] ?? home_url( '/subscribe/' ) )
		: home_url( '/subscribe/' );
	wp_safe_redirect( $url );
	exit;
}

/**
 * When subscriptions are off, send /my-account/orders/ back to the account dashboard.
 */
function altijara_wc_redirect_orders_when_subs_off() {
	if ( ! function_exists( 'altijara_subscriptions_enabled' ) || altijara_subscriptions_enabled() ) {
		return;
	}
	if ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'orders' ) ) {
		return;
	}
	$account = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
	wp_safe_redirect( $account );
	exit;
}

/**
 * Empty cart, add tier product, return checkout URL.
 *
 * @param int    $user_id    User ID.
 * @param string $tier_key   Tier key.
 * @param int    $product_id Product ID.
 * @return string|WP_Error Checkout URL.
 */
function altijara_wc_start_checkout_for_tier( $user_id, $tier_key, $product_id ) {
	if ( ! altijara_payments_available() || ! function_exists( 'WC' ) ) {
		return new WP_Error( 'payments_off', 'payments_off' );
	}
	$product = wc_get_product( $product_id );
	if ( ! $product || ! $product->is_purchasable() ) {
		return new WP_Error( 'product', 'checkout_fail' );
	}

	if ( null === WC()->cart ) {
		wc_load_cart();
	}
	WC()->cart->empty_cart();
	$added = WC()->cart->add_to_cart( $product_id, 1, 0, array(), array( 'altijara_tier' => sanitize_key( $tier_key ) ) );
	if ( ! $added ) {
		return new WP_Error( 'cart', 'checkout_fail' );
	}

	// Remember intended tier on user for order hook fallback.
	update_user_meta( (int) $user_id, '_altijara_pending_tier', sanitize_key( $tier_key ) );

	$checkout = wc_get_checkout_url();
	return $checkout ? $checkout : new WP_Error( 'checkout', 'checkout_fail' );
}

/**
 * Resolve tier key from order line items / user meta.
 *
 * @param WC_Order $order Order.
 * @return string
 */
function altijara_wc_tier_from_order( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return '';
	}
	$settings = function_exists( 'altijara_get_subscription_settings' ) ? altijara_get_subscription_settings() : array();
	$digital  = (int) ( $settings['product_digital'] ?? 0 );
	$full     = (int) ( $settings['product_full'] ?? 0 );

	foreach ( $order->get_items() as $item ) {
		$pid = (int) $item->get_product_id();
		$cart_tier = $item->get_meta( 'altijara_tier' );
		if ( $cart_tier ) {
			return sanitize_key( (string) $cart_tier );
		}
		if ( $full && $pid === $full ) {
			return 'full';
		}
		if ( $digital && $pid === $digital ) {
			return 'digital';
		}
	}

	$user_id = $order->get_user_id();
	if ( $user_id ) {
		$pending = sanitize_key( (string) get_user_meta( $user_id, '_altijara_pending_tier', true ) );
		if ( $pending ) {
			return $pending;
		}
	}
	return '';
}

/**
 * Activate plan when order is paid.
 *
 * @param int $order_id Order ID.
 */
function altijara_wc_activate_plan_from_order( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	if ( $order->get_meta( '_altijara_plan_activated' ) ) {
		return; // Idempotent.
	}
	$user_id = $order->get_user_id();
	if ( ! $user_id ) {
		return;
	}
	$tier = altijara_wc_tier_from_order( $order );
	if ( ! $tier || ! function_exists( 'altijara_activate_user_plan' ) ) {
		return;
	}
	if ( altijara_activate_user_plan( $user_id, $tier ) ) {
		$order->update_meta_data( '_altijara_plan_activated', $tier );
		$order->save();
		delete_user_meta( $user_id, '_altijara_pending_tier' );
	}
}

/**
 * Revoke plan on cancel/refund/fail (only if this order activated it).
 *
 * @param int $order_id Order ID.
 */
function altijara_wc_maybe_revoke_plan_from_order( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$activated = $order->get_meta( '_altijara_plan_activated' );
	if ( ! $activated ) {
		return;
	}
	$user_id = $order->get_user_id();
	if ( ! $user_id || ! function_exists( 'altijara_clear_user_plan' ) ) {
		return;
	}
	$current = function_exists( 'altijara_get_user_plan' ) ? altijara_get_user_plan( $user_id ) : '';
	// Clear only if still on the same tier this order granted.
	if ( $current === $activated || ! $current ) {
		altijara_clear_user_plan( $user_id );
	}
	$order->delete_meta_data( '_altijara_plan_activated' );
	$order->save();
}

/**
 * Suggest LiteSpeed / cache exclusions (filterable list).
 *
 * @return string[] Path fragments.
 */
function altijara_wc_cache_exclude_paths() {
	$paths = array( '/checkout/', '/cart/', '/my-account/', '/order-received/', '/login/', '/register/', '/reset/', '/subscribe/' );
	return apply_filters( 'altijara_wc_cache_exclude_paths', $paths );
}

/**
 * Ask page caches not to store private auth/commerce surfaces.
 */
function altijara_nocache_private_surfaces() {
	if ( is_admin() ) {
		return;
	}
	$slug = '';
	if ( is_page() ) {
		$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
	}
	$private_slugs = array( 'login', 'register', 'reset', 'subscribe', 'checkout', 'cart', 'my-account' );
	$is_wc         = function_exists( 'is_checkout' ) && ( is_checkout() || is_cart() || ( function_exists( 'is_account_page' ) && is_account_page() ) );
	if ( $is_wc || in_array( $slug, $private_slugs, true ) ) {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
	}
}
add_action( 'template_redirect', 'altijara_nocache_private_surfaces', 1 );
