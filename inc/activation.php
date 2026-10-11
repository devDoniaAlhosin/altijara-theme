<?php
/**
 * Theme activation — idempotent defaults, menus, report.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALTIJARA_SETUP_FLAG', 'altijara_theme_setup_state' );

/**
 * Resolve a blueprint item to a URL (or null if pending).
 *
 * @param array<string, string> $item Blueprint row.
 * @return array{url:?string,object:string,object_id:int,status:string}
 */
function altijara_resolve_blueprint_item( $item ) {
	$type = $item['type'] ?? 'custom';

	if ( 'home' === $type ) {
		return array(
			'url'       => home_url( '/' ),
			'object'    => 'custom',
			'object_id' => 0,
			'status'    => 'ok',
		);
	}

	if ( 'issues' === $type ) {
		$url = get_post_type_archive_link( 'issue' );
		if ( ! $url ) {
			$url = home_url( '/issues/' );
		}
		return array(
			'url'       => $url,
			'object'    => 'custom',
			'object_id' => 0,
			'status'    => 'ok',
		);
	}

	if ( 'category_key' === $type ) {
		$key = sanitize_key( $item['key'] ?? '' );
		$id  = function_exists( 'altijara_resolve_category_id' ) ? altijara_resolve_category_id( $key ) : 0;
		if ( $id ) {
			$link = get_category_link( $id );
			if ( ! is_wp_error( $link ) && $link ) {
				return array(
					'url'       => $link,
					'object'    => 'category',
					'object_id' => $id,
					'status'    => 'ok',
				);
			}
		}
		return array(
			'url'       => null,
			'object'    => 'custom',
			'object_id' => 0,
			'status'    => 'pending_category:' . $key,
		);
	}

	if ( 'page_slug' === $type ) {
		$slug = sanitize_title( $item['slug'] ?? '' );
		$page = get_page_by_path( $slug );
		if ( $page ) {
			return array(
				'url'       => get_permalink( $page ),
				'object'    => 'page',
				'object_id' => (int) $page->ID,
				'status'    => 'ok',
			);
		}
		// Soft fallback to pretty path without creating a page.
		return array(
			'url'       => home_url( '/' . $slug . '/' ),
			'object'    => 'custom',
			'object_id' => 0,
			'status'    => 'pending_page:' . $slug,
		);
	}

	if ( 'custom' === $type ) {
		$url = isset( $item['url'] ) ? esc_url_raw( $item['url'] ) : '';
		return array(
			'url'       => $url ? $url : null,
			'object'    => 'custom',
			'object_id' => 0,
			'status'    => $url ? 'ok' : 'pending_url',
		);
	}

	return array(
		'url'       => null,
		'object'    => 'custom',
		'object_id' => 0,
		'status'    => 'skipped',
	);
}

/**
 * Ensure a named menu exists; populate only when empty.
 *
 * @param string                         $menu_name Menu name.
 * @param array<int, array<string, mixed>> $blueprint Items.
 * @return array{menu_id:int,created:bool,items_added:int,pending:string[],skipped:bool}
 */
function altijara_ensure_nav_menu( $menu_name, $blueprint ) {
	$report = array(
		'menu_id'     => 0,
		'created'     => false,
		'items_added' => 0,
		'pending'     => array(),
		'skipped'     => false,
	);

	$existing = wp_get_nav_menu_object( $menu_name );
	if ( $existing ) {
		$report['menu_id'] = (int) $existing->term_id;
		$items             = wp_get_nav_menu_items( $existing->term_id );
		if ( ! empty( $items ) ) {
			$report['skipped'] = true;
			return $report;
		}
	} else {
		$created = wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $created ) ) {
			$report['pending'][] = 'menu_create_failed:' . $menu_name;
			return $report;
		}
		$report['menu_id'] = (int) $created;
		$report['created'] = true;
	}

	$position = 1;
	foreach ( $blueprint as $row ) {
		$resolved = altijara_resolve_blueprint_item( $row );
		if ( empty( $resolved['url'] ) ) {
			$report['pending'][] = ( $row['label'] ?? '' ) . ' (' . $resolved['status'] . ')';
			continue;
		}
		$args = array(
			'menu-item-title'  => $row['label'] ?? '',
			'menu-item-url'    => $resolved['url'],
			'menu-item-status' => 'publish',
			'menu-item-type'   => ( 'page' === $resolved['object'] || 'category' === $resolved['object'] ) ? $resolved['object'] : 'custom',
			'menu-item-object' => $resolved['object'],
			'menu-item-object-id' => $resolved['object_id'],
			'menu-item-position'  => $position,
		);
		if ( ! empty( $row['target'] ) ) {
			$args['menu-item-target'] = $row['target'];
		}
		// For category/page typed items WP expects object-id; for custom use url.
		if ( 'custom' === $args['menu-item-type'] ) {
			$args['menu-item-type']   = 'custom';
			$args['menu-item-object'] = 'custom';
			$args['menu-item-object-id'] = 0;
		}
		$item_id = wp_update_nav_menu_item( $report['menu_id'], 0, $args );
		if ( ! is_wp_error( $item_id ) ) {
			++$report['items_added'];
			++$position;
		}
		if ( 0 === strpos( $resolved['status'], 'pending_' ) ) {
			$report['pending'][] = ( $row['label'] ?? '' ) . ' (' . $resolved['status'] . ')';
		}
	}

	return $report;
}

/**
 * Assign menu to location only if location is empty.
 *
 * @param string $location Location.
 * @param int    $menu_id  Menu term ID.
 * @return bool Assigned now.
 */
function altijara_assign_menu_location_if_empty( $location, $menu_id ) {
	$menu_id   = absint( $menu_id );
	$locations = get_theme_mod( 'nav_menu_locations' );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	if ( ! empty( $locations[ $location ] ) ) {
		return false;
	}
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	return true;
}

/**
 * Footer sections blueprint from category keys (existing UI).
 *
 * @return array<int, array<string, string>>
 */
function altijara_default_footer_sections_blueprint() {
	$primary = altijara_default_primary_nav_blueprint();
	$out     = array();
	foreach ( $primary as $row ) {
		if ( 'category_key' === ( $row['type'] ?? '' ) ) {
			$out[] = $row;
		}
	}
	return $out;
}

/**
 * Run activation setup (idempotent).
 *
 * @return array<string, mixed> Report.
 */
function altijara_run_theme_setup() {
	$report = array(
		'options' => 'skipped',
		'menus'   => array(),
		'pending' => array(),
		'flush'   => false,
	);

	// 1) Fill missing theme options only.
	$saved = get_option( ALTIJARA_OPTION_KEY, null );
	if ( ! is_array( $saved ) ) {
		update_option( ALTIJARA_OPTION_KEY, altijara_default_options(), false );
		$report['options'] = 'created';
	} else {
		$filled = altijara_fill_missing_recursive( altijara_default_options(), $saved );
		// Never wipe homepage sections if user has none saved — leave empty to use code defaults.
		if ( array_key_exists( 'sections', $saved ) ) {
			$filled['sections'] = $saved['sections'];
		}
		update_option( ALTIJARA_OPTION_KEY, $filled, false );
		$report['options'] = 'merged_missing';
	}

	// 2) Menus.
	$menus = array(
		array(
			'name'      => 'Altijara — القائمة الرئيسية',
			'location'  => 'primary',
			'blueprint' => altijara_default_primary_nav_blueprint(),
			'also'      => array( 'mobile' ),
		),
		array(
			'name'      => 'Altijara — الشريط العلوي',
			'location'  => 'top_bar',
			'blueprint' => altijara_default_utility_nav_blueprint(),
			'also'      => array( 'utility' ),
		),
		array(
			'name'      => 'Altijara — أقسام التذييل',
			'location'  => 'footer',
			'blueprint' => altijara_default_footer_sections_blueprint(),
			'also'      => array(),
		),
		array(
			'name'      => 'Altijara — روابط تهمك',
			'location'  => 'footer_quick',
			'blueprint' => altijara_default_footer_quick_blueprint(),
			'also'      => array(),
		),
	);

	foreach ( $menus as $spec ) {
		$result = altijara_ensure_nav_menu( $spec['name'], $spec['blueprint'] );
		$assigned = false;
		if ( $result['menu_id'] ) {
			$assigned = altijara_assign_menu_location_if_empty( $spec['location'], $result['menu_id'] );
			foreach ( $spec['also'] as $extra_loc ) {
				altijara_assign_menu_location_if_empty( $extra_loc, $result['menu_id'] );
			}
		}
		$report['menus'][ $spec['location'] ] = array(
			'name'        => $spec['name'],
			'created'     => $result['created'],
			'items_added' => $result['items_added'],
			'skipped'     => $result['skipped'],
			'assigned'    => $assigned,
			'pending'     => $result['pending'],
		);
		$report['pending'] = array_merge( $report['pending'], $result['pending'] );
	}

	// 3) Flush rewrites once per setup version (issue CPT archive).
	$state = get_option( ALTIJARA_SETUP_FLAG, array() );
	if ( ! is_array( $state ) ) {
		$state = array();
	}
	$ver = defined( 'ALTIJARA_THEME_VERSION' ) ? ALTIJARA_THEME_VERSION : '1';
	if ( empty( $state['rewrite_flushed'] ) || ( $state['version'] ?? '' ) !== $ver ) {
		flush_rewrite_rules( false );
		$report['flush'] = true;
		$state['rewrite_flushed'] = true;
	}
	$state['version']     = $ver;
	$state['last_run']    = time();
	$state['last_report'] = $report;
	update_option( ALTIJARA_SETUP_FLAG, $state, false );

	set_transient( 'altijara_setup_admin_notice', $report, DAY_IN_SECONDS );

	return $report;
}
add_action( 'after_switch_theme', 'altijara_run_theme_setup' );

/**
 * Admin notice after activation.
 */
function altijara_setup_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$report = get_transient( 'altijara_setup_admin_notice' );
	if ( ! $report || ! is_array( $report ) ) {
		return;
	}
	delete_transient( 'altijara_setup_admin_notice' );

	$created = 0;
	$skipped = 0;
	foreach ( $report['menus'] as $row ) {
		if ( ! empty( $row['created'] ) || ! empty( $row['items_added'] ) ) {
			++$created;
		}
		if ( ! empty( $row['skipped'] ) ) {
			++$skipped;
		}
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong><?php esc_html_e( 'Altijara Theme — اكتمل الإعداد الأولي', 'altijara' ); ?></strong></p>
		<p>
			<?php
			printf(
				/* translators: 1: options status, 2: menus created/updated, 3: skipped */
				esc_html__( 'الإعدادات: %1$s · قوائم محدّثة: %2$d · قوائم موجودة (تم التخطي): %3$d', 'altijara' ),
				esc_html( (string) $report['options'] ),
				(int) $created,
				(int) $skipped
			);
			?>
		</p>
		<?php if ( ! empty( $report['pending'] ) ) : ?>
			<p><?php esc_html_e( 'عناصر تحتاج مراجعة يدوية (تصنيف/صفحة غير موجودة):', 'altijara' ); ?></p>
			<ul style="list-style:disc;margin-inline-start:1.5em;">
				<?php foreach ( array_slice( $report['pending'], 0, 20 ) as $p ) : ?>
					<li><?php echo esc_html( $p ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=altijara-theme' ) ); ?>"><?php esc_html_e( 'فتح إعدادات القالب', 'altijara' ); ?></a>
			|
			<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'تحرير القوائم', 'altijara' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'altijara_setup_admin_notice' );

/**
 * Manual re-run from Integrations tab (still idempotent).
 */
function altijara_admin_rerun_setup() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( empty( $_POST['altijara_rerun_setup'] ) ) {
		return;
	}
	if ( ! isset( $_POST['altijara_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_settings_nonce'] ) ), 'altijara_save_settings' ) ) {
		return;
	}
	altijara_run_theme_setup();
}
add_action( 'admin_init', 'altijara_admin_rerun_setup' );
