<?php
/**
 * Subscription tiers — user meta only; Woo product map when payments available.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** User meta keys. */
define( 'ALTIJARA_META_PLAN', 'altijara_plan' );
define( 'ALTIJARA_META_PLAN_START', 'altijara_plan_start' );
define( 'ALTIJARA_META_PLAN_EXPIRY', 'altijara_plan_expiry' );

/** Legacy WP role slugs (removed; kept for one-time cleanup). */
define(
	'ALTIJARA_LEGACY_PLAN_ROLES',
	array( 'altijara_newsletter', 'altijara_digital', 'altijara_full' )
);

/**
 * Canonical tier definitions.
 *
 * @return array<string, array<string, mixed>>
 */
function altijara_subscription_tiers() {
	$tiers = array(
		'newsletter' => array(
			'label'       => __( 'النشرة الأسبوعية', 'altijara' ),
			'description' => __( 'مجاناً — ملخص أسبوعي لأبرز الأخبار', 'altijara' ),
			'paid'        => false,
			'access'      => array( 'free', 'members' ),
			'duration'    => 0, // No expiry.
		),
		'digital'    => array(
			'label'       => __( 'النسخة الرقمية', 'altijara' ),
			'description' => __( 'تصفح أعداد المجلة والتقارير الخاصة عبر الموقع', 'altijara' ),
			'paid'        => true,
			'access'      => array( 'free', 'members', 'subscribers' ),
			'duration'    => YEAR_IN_SECONDS,
		),
		'full'       => array(
			'label'       => __( 'النسخة المطبوعة', 'altijara' ),
			'description' => __( 'تصلك النسخة الورقية من المجلة عند صدورها', 'altijara' ),
			'paid'        => true,
			'access'      => array( 'free', 'members', 'subscribers' ),
			'duration'    => YEAR_IN_SECONDS,
		),
	);

	/**
	 * Filter subscription tiers.
	 *
	 * @param array<string, array<string, mixed>> $tiers Tiers.
	 */
	return apply_filters( 'altijara_subscription_tiers', $tiers );
}

/**
 * One-time: strip legacy plan roles from users and remove role definitions.
 * Preserves altijara_plan user meta.
 */
function altijara_migrate_plans_meta_only() {
	if ( get_option( 'altijara_plans_meta_only_v1' ) ) {
		return;
	}

	$roles = ALTIJARA_LEGACY_PLAN_ROLES;
	do {
		$q = new WP_User_Query(
			array(
				'role__in' => $roles,
				'fields'   => 'ID',
				'number'   => 200,
			)
		);
		$ids = (array) $q->get_results();
		foreach ( $ids as $uid ) {
			$user = get_userdata( (int) $uid );
			if ( ! $user ) {
				continue;
			}
			foreach ( $roles as $role ) {
				if ( in_array( $role, (array) $user->roles, true ) ) {
					$user->remove_role( $role );
				}
			}
		}
	} while ( $ids );

	foreach ( $roles as $role ) {
		if ( get_role( $role ) ) {
			remove_role( $role );
		}
	}

	update_option( 'altijara_plans_meta_only_v1', 1, true );
}
add_action( 'admin_init', 'altijara_migrate_plans_meta_only' );
add_action( 'init', 'altijara_migrate_plans_meta_only', 20 );

/**
 * Subscription settings from Theme Options (product map + grace).
 *
 * @return array<string, mixed>
 */
function altijara_get_subscription_settings() {
	$defaults = array(
		'enabled'          => false,
		'product_digital'  => 0,
		'product_full'     => 0,
		'grace_days'       => 3,
		'paid_coming_soon' => false,
	);
	$saved = altijara_get_option( 'subscriptions', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return array_merge( $defaults, $saved );
}

/**
 * Whether magazine subscriptions are enabled on the front end.
 *
 * When off: hide My Account plan/Orders, header subscribe button, footer/menu
 * subscribe links, and login/register subscribe CTAs. Admin mapping stays available.
 *
 * @return bool
 */
function altijara_subscriptions_enabled() {
	$settings = altijara_get_subscription_settings();
	$on       = ! empty( $settings['enabled'] );
	/**
	 * Filter whether subscriptions UI is active on the front.
	 *
	 * @param bool $on Enabled.
	 */
	return (bool) apply_filters( 'altijara_subscriptions_enabled', $on );
}

/**
 * Canonical subscribe page URL for chrome / nav matching.
 *
 * @return string
 */
function altijara_subscribe_page_url() {
	if ( function_exists( 'altijara_theme_urls' ) ) {
		$urls = altijara_theme_urls();
		if ( ! empty( $urls['subscribe'] ) ) {
			return (string) $urls['subscribe'];
		}
	}
	if ( function_exists( 'altijara_utility_page_url' ) ) {
		return (string) altijara_utility_page_url( 'subscribe', '/subscribe/' );
	}
	return home_url( '/subscribe/' );
}

/**
 * Whether a URL points at the theme subscribe page.
 *
 * @param string $url Candidate URL.
 * @return bool
 */
function altijara_is_subscribe_destination( $url ) {
	$url = (string) $url;
	if ( '' === $url || '#' === $url ) {
		return false;
	}
	$target = wp_parse_url( altijara_subscribe_page_url() );
	$cand   = wp_parse_url( $url );
	if ( empty( $target['path'] ) || empty( $cand['path'] ) ) {
		return false;
	}
	$t_path = untrailingslashit( (string) $target['path'] );
	$c_path = untrailingslashit( (string) $cand['path'] );
	if ( $t_path !== $c_path ) {
		return false;
	}
	$t_host = isset( $target['host'] ) ? strtolower( (string) $target['host'] ) : '';
	$c_host = isset( $cand['host'] ) ? strtolower( (string) $cand['host'] ) : '';
	if ( $t_host && $c_host && $t_host !== $c_host ) {
		return false;
	}
	return true;
}

/**
 * Whether a nav menu item is the subscribe CTA.
 *
 * @param object $item Menu item.
 * @return bool
 */
function altijara_nav_item_is_subscribe( $item ) {
	if ( ! is_object( $item ) ) {
		return false;
	}
	if ( ! empty( $item->object ) && 'page' === $item->object && ! empty( $item->object_id ) ) {
		$page = get_post( (int) $item->object_id );
		if ( $page instanceof WP_Post && 'page' === $page->post_type ) {
			if ( 'subscribe' === $page->post_name ) {
				return true;
			}
			$tpl = (string) get_page_template_slug( $page );
			if ( 'page-subscribe.php' === $tpl ) {
				return true;
			}
		}
	}
	if ( ! empty( $item->url ) && altijara_is_subscribe_destination( (string) $item->url ) ) {
		return true;
	}
	return false;
}

/**
 * Drop subscribe links from menus when subscriptions are disabled.
 *
 * @param array<int, object> $items Menu items.
 * @return array<int, object>
 */
function altijara_filter_subscribe_menu_items( $items ) {
	if ( ! is_array( $items ) || empty( $items ) ) {
		return $items;
	}
	if ( altijara_subscriptions_enabled() ) {
		return $items;
	}
	$kept = array();
	foreach ( $items as $item ) {
		if ( altijara_nav_item_is_subscribe( $item ) ) {
			continue;
		}
		$kept[] = $item;
	}
	return $kept;
}

/**
 * Filter wp_nav_menu objects (footer quick links, top bar, etc.).
 *
 * @param array<int, object> $items Menu items.
 * @param object|array       $args  Menu args.
 * @return array<int, object>
 */
function altijara_wp_nav_menu_hide_subscribe( $items, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	return altijara_filter_subscribe_menu_items( $items );
}
add_filter( 'wp_nav_menu_objects', 'altijara_wp_nav_menu_hide_subscribe', 20, 2 );

/**
 * Sanitize subscriptions admin input.
 *
 * @param array<string, mixed> $raw Raw POST.
 * @return array<string, mixed>
 */
function altijara_sanitize_subscriptions_input( $raw ) {
	return array(
		'enabled'          => ! empty( $raw['enabled'] ),
		'product_digital'  => isset( $raw['product_digital'] ) ? absint( $raw['product_digital'] ) : 0,
		'product_full'     => isset( $raw['product_full'] ) ? absint( $raw['product_full'] ) : 0,
		'grace_days'       => isset( $raw['grace_days'] ) ? max( 0, min( 30, (int) $raw['grace_days'] ) ) : 3,
		'paid_coming_soon' => ! empty( $raw['paid_coming_soon'] ),
	);
}

/**
 * Woo product ID mapped to a paid tier (0 if none).
 *
 * @param string $tier_key Tier key.
 * @return int
 */
function altijara_tier_product_id( $tier_key ) {
	$tier_key = sanitize_key( $tier_key );
	$settings = altijara_get_subscription_settings();
	$map      = array(
		'digital' => (int) ( $settings['product_digital'] ?? 0 ),
		'full'    => (int) ( $settings['product_full'] ?? 0 ),
	);
	return $map[ $tier_key ] ?? 0;
}

/**
 * Whether paid checkout UI should show.
 *
 * @return bool
 */
function altijara_paid_plans_available() {
	if ( function_exists( 'altijara_subscriptions_enabled' ) && ! altijara_subscriptions_enabled() ) {
		return false;
	}
	if ( ! function_exists( 'altijara_payments_available' ) || ! altijara_payments_available() ) {
		return false;
	}
	$settings = altijara_get_subscription_settings();
	if ( ! empty( $settings['paid_coming_soon'] ) ) {
		return false;
	}
	return (bool) ( altijara_tier_product_id( 'digital' ) || altijara_tier_product_id( 'full' ) );
}

/**
 * Current plan key for a user (empty if none / expired).
 *
 * @param int $user_id User ID.
 * @return string
 */
function altijara_get_user_plan( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return '';
	}
	$plan = sanitize_key( (string) get_user_meta( $user_id, ALTIJARA_META_PLAN, true ) );
	if ( ! $plan || ! isset( altijara_subscription_tiers()[ $plan ] ) ) {
		return '';
	}
	if ( altijara_user_plan_expired( $user_id ) ) {
		return '';
	}
	return $plan;
}

/**
 * Whether plan is past expiry (+ grace).
 *
 * @param int $user_id User ID.
 * @return bool
 */
function altijara_user_plan_expired( $user_id ) {
	$expiry = (int) get_user_meta( $user_id, ALTIJARA_META_PLAN_EXPIRY, true );
	if ( $expiry <= 0 ) {
		return false;
	}
	$grace = (int) ( altijara_get_subscription_settings()['grace_days'] ?? 3 );
	$limit = $expiry + ( $grace * DAY_IN_SECONDS );
	return time() > $limit;
}

/**
 * Activate a tier for a user (user meta only).
 *
 * @param int    $user_id  User ID.
 * @param string $tier_key Tier key.
 * @return bool
 */
function altijara_activate_user_plan( $user_id, $tier_key ) {
	$user_id  = (int) $user_id;
	$tier_key = sanitize_key( $tier_key );
	$tiers    = altijara_subscription_tiers();
	if ( ! $user_id || empty( $tiers[ $tier_key ] ) ) {
		return false;
	}
	$tier = $tiers[ $tier_key ];
	if ( ! get_userdata( $user_id ) ) {
		return false;
	}

	$now = time();
	update_user_meta( $user_id, ALTIJARA_META_PLAN, $tier_key );
	update_user_meta( $user_id, ALTIJARA_META_PLAN_START, $now );

	$duration = (int) ( $tier['duration'] ?? 0 );
	if ( $duration > 0 ) {
		update_user_meta( $user_id, ALTIJARA_META_PLAN_EXPIRY, $now + $duration );
	} else {
		delete_user_meta( $user_id, ALTIJARA_META_PLAN_EXPIRY );
	}

	/**
	 * Fires after a plan is activated.
	 *
	 * @param int    $user_id  User.
	 * @param string $tier_key Tier.
	 */
	do_action( 'altijara_user_plan_activated', $user_id, $tier_key );

	return true;
}

/**
 * Downgrade / clear plan (e.g. refund). Meta only.
 *
 * @param int $user_id User ID.
 */
function altijara_clear_user_plan( $user_id ) {
	$user_id = (int) $user_id;
	if ( ! $user_id || ! get_userdata( $user_id ) ) {
		return;
	}
	delete_user_meta( $user_id, ALTIJARA_META_PLAN );
	delete_user_meta( $user_id, ALTIJARA_META_PLAN_START );
	delete_user_meta( $user_id, ALTIJARA_META_PLAN_EXPIRY );
}

/**
 * Whether user may access an issue access_level.
 *
 * @param string $access_level free|members|subscribers.
 * @param int    $user_id      User ID (0 = current).
 * @return bool
 */
function altijara_user_can_access_level( $access_level, $user_id = 0 ) {
	$access_level = sanitize_key( $access_level );
	if ( ! $access_level || 'free' === $access_level ) {
		return true;
	}
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}
	if ( user_can( $user_id, 'edit_posts' ) ) {
		return true;
	}
	// Members = any logged-in reader.
	if ( 'members' === $access_level ) {
		return true;
	}
	$plan = altijara_get_user_plan( $user_id );
	if ( ! $plan ) {
		return false;
	}
	$tiers = altijara_subscription_tiers();
	$allow = $tiers[ $plan ]['access'] ?? array( 'free' );
	return in_array( $access_level, $allow, true );
}

/**
 * Daily cron: expire plans past grace.
 */
function altijara_cron_expire_plans() {
	$q = new WP_User_Query(
		array(
			'meta_key'     => ALTIJARA_META_PLAN_EXPIRY,
			'meta_compare' => '>',
			'meta_value'   => 0,
			'fields'       => 'ID',
			'number'       => 200,
		)
	);
	foreach ( (array) $q->get_results() as $uid ) {
		if ( altijara_user_plan_expired( (int) $uid ) ) {
			altijara_clear_user_plan( (int) $uid );
		}
	}
}
add_action( 'altijara_daily_expire_plans', 'altijara_cron_expire_plans' );

/**
 * Schedule expiry cron.
 */
function altijara_schedule_plan_expiry_cron() {
	if ( ! wp_next_scheduled( 'altijara_daily_expire_plans' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'altijara_daily_expire_plans' );
	}
}
add_action( 'init', 'altijara_schedule_plan_expiry_cron' );
