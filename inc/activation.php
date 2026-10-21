<?php
/**
 * Theme activation — idempotent defaults, menus, report.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ALTIJARA_SETUP_FLAG' ) ) {
	define( 'ALTIJARA_SETUP_FLAG', 'altijara_theme_setup_state' );
}

/**
 * Resolve a blueprint item to a URL (or null if pending).
 *
 * @param array<string, string> $item Blueprint row.
 * @return array{url:?string,object:string,object_id:int,type:string,status:string}
 */
function altijara_resolve_blueprint_item( $item ) {
	$type = $item['type'] ?? 'custom';

	if ( 'home' === $type ) {
		return array(
			'url'       => home_url( '/' ),
			'object'    => 'custom',
			'object_id' => 0,
			'type'      => 'custom',
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
			'type'      => 'custom',
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
					'type'      => 'taxonomy',
					'status'    => 'ok',
				);
			}
		}
		return array(
			'url'       => null,
			'object'    => 'custom',
			'object_id' => 0,
			'type'      => 'custom',
			'status'    => 'pending_category:' . $key,
		);
	}

	if ( 'page_slug' === $type ) {
		$slug = sanitize_title( $item['slug'] ?? '' );
		$page = function_exists( 'altijara_get_utility_page' )
			? altijara_get_utility_page( $slug )
			: get_page_by_path( $slug );
		if ( $page instanceof WP_Post ) {
			return array(
				'url'       => get_permalink( $page ),
				'object'    => 'page',
				'object_id' => (int) $page->ID,
				'type'      => 'post_type',
				'status'    => 'ok',
			);
		}
		// Soft fallback to pretty path without creating a page.
		return array(
			'url'       => home_url( '/' . $slug . '/' ),
			'object'    => 'custom',
			'object_id' => 0,
			'type'      => 'custom',
			'status'    => 'pending_page:' . $slug,
		);
	}

	if ( 'custom' === $type ) {
		$url = isset( $item['url'] ) ? esc_url_raw( $item['url'] ) : '';
		return array(
			'url'       => $url ? $url : null,
			'object'    => 'custom',
			'object_id' => 0,
			'type'      => 'custom',
			'status'    => $url ? 'ok' : 'pending_url',
		);
	}

	return array(
		'url'       => null,
		'object'    => 'custom',
		'object_id' => 0,
		'type'      => 'custom',
		'status'    => 'skipped',
	);
}

/**
 * Build wp_update_nav_menu_item args from a resolved blueprint row.
 *
 * WP expects type=taxonomy+object=category (not type=category), and
 * type=post_type+object=page (not type=page). Wrong types often save as
 * custom links that collide with Pages instead of /category/{slug}/.
 *
 * @param array<string, mixed> $row      Blueprint row.
 * @param array<string, mixed> $resolved From altijara_resolve_blueprint_item().
 * @param int                  $position Menu position.
 * @return array<string, mixed>
 */
function altijara_nav_menu_item_args( $row, $resolved, $position = 1 ) {
	$item_type = isset( $resolved['type'] ) ? (string) $resolved['type'] : 'custom';
	$object    = isset( $resolved['object'] ) ? (string) $resolved['object'] : 'custom';
	$object_id = isset( $resolved['object_id'] ) ? (int) $resolved['object_id'] : 0;

	if ( ! in_array( $item_type, array( 'custom', 'taxonomy', 'post_type', 'post_type_archive' ), true ) ) {
		// Legacy mistaken values: "category" / "page".
		if ( 'category' === $object || 'category' === $item_type ) {
			$item_type = 'taxonomy';
			$object    = 'category';
		} elseif ( 'page' === $object || 'page' === $item_type ) {
			$item_type = 'post_type';
			$object    = 'page';
		} else {
			$item_type = 'custom';
			$object    = 'custom';
			$object_id = 0;
		}
	}

	$args = array(
		'menu-item-title'     => isset( $row['label'] ) ? (string) $row['label'] : '',
		'menu-item-status'    => 'publish',
		'menu-item-position'  => (int) $position,
		'menu-item-type'      => $item_type,
		'menu-item-object'    => $object,
		'menu-item-object-id' => $object_id,
	);

	if ( 'custom' === $item_type ) {
		$args['menu-item-url']       = isset( $resolved['url'] ) ? (string) $resolved['url'] : '';
		$args['menu-item-object']    = 'custom';
		$args['menu-item-object-id'] = 0;
	}
	// Do not set menu-item-url for taxonomy/post_type — WP derives it from the object.
	// Passing a URL can leave broken items stuck as "Custom Link" in the Menus UI.

	if ( ! empty( $row['target'] ) ) {
		$args['menu-item-target'] = $row['target'];
	}

	return $args;
}

/**
 * Force correct nav-menu meta after save (converts legacy Custom Link → Category).
 *
 * @param int                  $item_id  Menu item post ID.
 * @param array<string, mixed> $args     Args passed to wp_update_nav_menu_item.
 */
function altijara_force_nav_menu_item_meta( $item_id, $args ) {
	$item_id = absint( $item_id );
	if ( ! $item_id || is_wp_error( $item_id ) ) {
		return;
	}
	$type = isset( $args['menu-item-type'] ) ? (string) $args['menu-item-type'] : '';
	if ( ! in_array( $type, array( 'taxonomy', 'post_type', 'custom', 'post_type_archive' ), true ) ) {
		return;
	}
	update_post_meta( $item_id, '_menu_item_type', $type );
	update_post_meta( $item_id, '_menu_item_object', isset( $args['menu-item-object'] ) ? (string) $args['menu-item-object'] : '' );
	update_post_meta( $item_id, '_menu_item_object_id', (string) absint( $args['menu-item-object-id'] ?? 0 ) );
	if ( 'custom' === $type && ! empty( $args['menu-item-url'] ) ) {
		update_post_meta( $item_id, '_menu_item_url', esc_url_raw( (string) $args['menu-item-url'] ) );
	} elseif ( 'custom' !== $type ) {
		// Clear stale custom URL so admin shows Category / Page, not Custom Link.
		delete_post_meta( $item_id, '_menu_item_url' );
	}
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
		$args    = altijara_nav_menu_item_args( $row, $resolved, $position );
		$item_id = wp_update_nav_menu_item( $report['menu_id'], 0, $args );
		if ( ! is_wp_error( $item_id ) ) {
			altijara_force_nav_menu_item_meta( (int) $item_id, $args );
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
 * Update existing Altijara menus so category items are real taxonomy links
 * (/category/{slug}/), not Pages or custom URLs that collide with pages.
 * Matches items by label from the blueprint (does not wipe custom menus).
 *
 * @return array{updated:int,added:int,pending:string[]}
 */
function altijara_sync_nav_menus_from_category_map() {
	$report = array(
		'updated' => 0,
		'added'   => 0,
		'pending' => array(),
	);

	// Prefer /category/{slug}/ over bare /{slug}/ which collides with Pages.
	$cat_base = get_option( 'category_base' );
	if ( '' === (string) $cat_base || '.' === (string) $cat_base ) {
		update_option( 'category_base', 'category' );
		flush_rewrite_rules( false );
	}

	$primary_bp = function_exists( 'altijara_default_primary_nav_blueprint' ) ? altijara_default_primary_nav_blueprint() : array();
	$footer_bp  = function_exists( 'altijara_default_footer_sections_blueprint' ) ? altijara_default_footer_sections_blueprint() : array();

	$menu_ids = array();
	$named    = array(
		'Altijara — القائمة الرئيسية' => $primary_bp,
		'Altijara — أقسام التذييل'     => $footer_bp,
	);
	foreach ( $named as $name => $blueprint ) {
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			$menu_ids[ (int) $menu->term_id ] = $blueprint;
		}
	}

	// Also sync whatever is assigned to primary / footer / mobile (renamed menus).
	$locations = get_nav_menu_locations();
	if ( is_array( $locations ) ) {
		foreach ( array( 'primary', 'mobile', 'footer' ) as $loc ) {
			if ( empty( $locations[ $loc ] ) ) {
				continue;
			}
			$mid = (int) $locations[ $loc ];
			if ( ! $mid || isset( $menu_ids[ $mid ] ) ) {
				continue;
			}
			$menu_ids[ $mid ] = ( 'footer' === $loc ) ? $footer_bp : $primary_bp;
		}
	}

	foreach ( $menu_ids as $menu_id => $blueprint ) {
		if ( ! $blueprint ) {
			continue;
		}

		$items    = wp_get_nav_menu_items( $menu_id );
		$by_title = array();
		if ( is_array( $items ) ) {
			foreach ( $items as $item ) {
				$by_title[ trim( wp_strip_all_tags( (string) $item->title ) ) ] = $item;
			}
		}

		$position = 1;
		foreach ( $blueprint as $row ) {
			$label    = isset( $row['label'] ) ? trim( (string) $row['label'] ) : '';
			$resolved = altijara_resolve_blueprint_item( $row );
			if ( ! $label || empty( $resolved['url'] ) ) {
				if ( $label ) {
					$report['pending'][] = $label . ' (' . ( $resolved['status'] ?? 'pending' ) . ')';
				}
				++$position;
				continue;
			}

			$args  = altijara_nav_menu_item_args( $row, $resolved, $position );
			$db_id = 0;
			$existing = isset( $by_title[ $label ] ) ? $by_title[ $label ] : null;
			if ( $existing ) {
				$db_id = (int) $existing->db_id;
				// Stuck Custom Link / wrong type → delete and recreate as Category.
				$wrong = ( 'taxonomy' === ( $args['menu-item-type'] ?? '' ) )
					&& ( 'taxonomy' !== (string) $existing->type || 'category' !== (string) $existing->object );
				if ( $wrong ) {
					wp_delete_post( $db_id, true );
					$db_id = 0;
				}
			}

			$item_id = wp_update_nav_menu_item( $menu_id, $db_id, $args );
			if ( ! is_wp_error( $item_id ) ) {
				altijara_force_nav_menu_item_meta( (int) $item_id, $args );
				if ( $db_id ) {
					++$report['updated'];
				} else {
					++$report['added'];
				}
			}
			++$position;
		}
	}

	return $report;
}

/**
 * Ensure utility pages exist, then rebuild top-bar menu:
 * من نحن · اتصل بنا · أعلن معنا → real Pages.
 *
 * @return array{menu_id:int,items:int,pages:array<string,mixed>,pending:string[]}
 */
function altijara_rebuild_utility_nav_menu() {
	$report = array(
		'menu_id' => 0,
		'items'   => 0,
		'pages'   => array(),
		'pending' => array(),
	);

	if ( function_exists( 'altijara_ensure_utility_pages' ) ) {
		$report['pages'] = altijara_ensure_utility_pages();
	}

	$menu_name = 'Altijara — الشريط العلوي';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$created = wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $created ) ) {
			$report['pending'][] = 'menu_create_failed';
			return $report;
		}
		$menu_id = (int) $created;
	} else {
		$menu_id = (int) $menu->term_id;
	}

	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['top_bar'] ) ) {
		$menu_id = (int) $locations['top_bar'];
	} elseif ( ! empty( $locations['utility'] ) ) {
		$menu_id = (int) $locations['utility'];
	}
	$report['menu_id'] = $menu_id;

	$existing = wp_get_nav_menu_items( $menu_id );
	if ( is_array( $existing ) ) {
		foreach ( $existing as $item ) {
			wp_delete_post( (int) $item->db_id, true );
		}
	}

	$blueprint = function_exists( 'altijara_default_utility_nav_blueprint' )
		? altijara_default_utility_nav_blueprint()
		: array();
	$position  = 1;
	foreach ( $blueprint as $row ) {
		$resolved = altijara_resolve_blueprint_item( $row );
		if ( empty( $resolved['url'] ) ) {
			$report['pending'][] = ( $row['label'] ?? '' ) . ' (' . ( $resolved['status'] ?? 'pending' ) . ')';
			continue;
		}
		$args    = altijara_nav_menu_item_args( $row, $resolved, $position );
		$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
		if ( ! is_wp_error( $item_id ) ) {
			altijara_force_nav_menu_item_meta( (int) $item_id, $args );
			++$report['items'];
			++$position;
		}
	}

	$locs = get_theme_mod( 'nav_menu_locations' );
	if ( ! is_array( $locs ) ) {
		$locs = array();
	}
	$locs['top_bar'] = $menu_id;
	$locs['utility'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locs );

	return $report;
}

/**
 * Wipe and rebuild footer section + quick-link menus from blueprints.
 *
 * @return array{sections:array<string,mixed>,quick:array<string,mixed>,pending:string[]}
 */
function altijara_rebuild_footer_menus() {
	$report = array(
		'sections' => array( 'menu_id' => 0, 'items' => 0 ),
		'quick'    => array( 'menu_id' => 0, 'items' => 0 ),
		'pending'  => array(),
	);

	if ( function_exists( 'altijara_detect_category_map' ) ) {
		$map = altijara_detect_category_map( true );
		altijara_update_options( array( 'category_map' => $map ) );
	}

	$specs = array(
		'sections' => array(
			'name'      => 'Altijara — أقسام التذييل',
			'location'  => 'footer',
			'blueprint' => function_exists( 'altijara_default_footer_sections_blueprint' ) ? altijara_default_footer_sections_blueprint() : array(),
		),
		'quick'    => array(
			'name'      => 'Altijara — روابط تهمك',
			'location'  => 'footer_quick',
			'blueprint' => function_exists( 'altijara_default_footer_quick_blueprint' ) ? altijara_default_footer_quick_blueprint() : array(),
		),
	);

	foreach ( $specs as $key => $spec ) {
		$menu = wp_get_nav_menu_object( $spec['name'] );
		if ( ! $menu ) {
			$created = wp_create_nav_menu( $spec['name'] );
			if ( is_wp_error( $created ) ) {
				$report['pending'][] = $spec['name'] . ':create_failed';
				continue;
			}
			$menu_id = (int) $created;
		} else {
			$menu_id = (int) $menu->term_id;
		}

		$locations = get_nav_menu_locations();
		if ( ! empty( $locations[ $spec['location'] ] ) ) {
			$menu_id = (int) $locations[ $spec['location'] ];
		}

		$existing = wp_get_nav_menu_items( $menu_id );
		if ( is_array( $existing ) ) {
			foreach ( $existing as $item ) {
				wp_delete_post( (int) $item->db_id, true );
			}
		}

		$position = 1;
		$items    = 0;
		foreach ( (array) $spec['blueprint'] as $row ) {
			$resolved = altijara_resolve_blueprint_item( $row );
			if ( empty( $resolved['url'] ) ) {
				$report['pending'][] = ( $row['label'] ?? '' ) . ' (' . ( $resolved['status'] ?? 'pending' ) . ')';
				continue;
			}
			$args    = altijara_nav_menu_item_args( $row, $resolved, $position );
			$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
			if ( ! is_wp_error( $item_id ) ) {
				altijara_force_nav_menu_item_meta( (int) $item_id, $args );
				++$items;
				++$position;
			}
		}

		$locs = get_theme_mod( 'nav_menu_locations' );
		if ( ! is_array( $locs ) ) {
			$locs = array();
		}
		$locs[ $spec['location'] ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locs );

		$report[ $key ] = array(
			'menu_id' => $menu_id,
			'items'   => $items,
		);
	}

	return $report;
}

/**
 * Wipe and rebuild the primary navbar from the full category blueprint
 * (Home + all section categories + Issues) as real Category taxonomy links.
 *
 * @return array{menu_id:int,items:int,pending:string[]}
 */
function altijara_rebuild_primary_nav_menu() {
	$report = array(
		'menu_id' => 0,
		'items'   => 0,
		'pending' => array(),
	);

	if ( function_exists( 'altijara_detect_category_map' ) ) {
		$map = altijara_detect_category_map( true );
		altijara_update_options( array( 'category_map' => $map ) );
	}

	$cat_base = get_option( 'category_base' );
	if ( '' === (string) $cat_base || '.' === (string) $cat_base ) {
		update_option( 'category_base', 'category' );
		flush_rewrite_rules( false );
	}

	$menu_name = 'Altijara — القائمة الرئيسية';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$created = wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $created ) ) {
			$report['pending'][] = 'menu_create_failed';
			return $report;
		}
		$menu_id = (int) $created;
	} else {
		$menu_id = (int) $menu->term_id;
	}
	$report['menu_id'] = $menu_id;

	// Prefer menu already assigned to primary (may be renamed).
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) ) {
		$menu_id           = (int) $locations['primary'];
		$report['menu_id'] = $menu_id;
	}

	$existing = wp_get_nav_menu_items( $menu_id );
	if ( is_array( $existing ) ) {
		foreach ( $existing as $item ) {
			wp_delete_post( (int) $item->db_id, true );
		}
	}

	$blueprint = function_exists( 'altijara_default_primary_nav_blueprint' )
		? altijara_default_primary_nav_blueprint()
		: array();
	$position  = 1;
	foreach ( $blueprint as $row ) {
		$resolved = altijara_resolve_blueprint_item( $row );
		if ( empty( $resolved['url'] ) ) {
			$report['pending'][] = ( $row['label'] ?? '' ) . ' (' . ( $resolved['status'] ?? 'pending' ) . ')';
			continue;
		}
		$args    = altijara_nav_menu_item_args( $row, $resolved, $position );
		$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
		if ( ! is_wp_error( $item_id ) ) {
			altijara_force_nav_menu_item_meta( (int) $item_id, $args );
			++$report['items'];
			++$position;
		}
	}

	// Force-assign to primary + mobile.
	$locs = get_theme_mod( 'nav_menu_locations' );
	if ( ! is_array( $locs ) ) {
		$locs = array();
	}
	$locs['primary'] = $menu_id;
	$locs['mobile']  = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locs );

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
 * Whether a page already uses the expected Altijara template.
 *
 * @param WP_Post $page     Page.
 * @param string  $template Template file e.g. page-about.php.
 * @return bool
 */
function altijara_page_uses_theme_template( $page, $template ) {
	if ( ! $page instanceof WP_Post ) {
		return false;
	}
	$current = (string) get_post_meta( $page->ID, '_wp_page_template', true );
	return $current === $template;
}

/**
 * Next free page slug: about, about-2, about-3…
 *
 * @param string $base Base slug.
 * @return string
 */
function altijara_next_free_page_slug( $base ) {
	$base = sanitize_title( $base );
	if ( ! get_page_by_path( $base ) ) {
		return $base;
	}
	$n = 2;
	while ( $n < 50 ) {
		$candidate = $base . '-' . $n;
		if ( ! get_page_by_path( $candidate ) ) {
			return $candidate;
		}
		++$n;
	}
	return $base . '-' . wp_generate_password( 4, false, false );
}

/**
 * Seed page meta defaults when empty.
 *
 * @param int    $page_id  Page ID.
 * @param string $template Template filename.
 */
function altijara_seed_utility_page_meta( $page_id, $template ) {
	$page_id = absint( $page_id );
	if ( ! $page_id || ! defined( 'ALTIJARA_PAGE_META_KEY' ) ) {
		return;
	}
	$existing = get_post_meta( $page_id, ALTIJARA_PAGE_META_KEY, true );
	if ( is_array( $existing ) && ! empty( $existing ) ) {
		return;
	}
	$schema = '';
	if ( function_exists( 'altijara_get_page_schema_key' ) ) {
		$schema = altijara_get_page_schema_key( $page_id );
	}
	if ( ! $schema && $template && function_exists( 'altijara_page_template_map' ) ) {
		$map    = altijara_page_template_map();
		$schema = isset( $map[ $template ] ) ? $map[ $template ] : '';
	}
	if ( ! $schema && preg_match( '/page-([a-z0-9-]+)\.php$/', $template, $m ) ) {
		$schema = $m[1];
	}
	if ( ! $schema || ! function_exists( 'altijara_page_meta_defaults' ) ) {
		return;
	}
	$defaults = altijara_page_meta_defaults( $schema );
	if ( $defaults ) {
		update_post_meta( $page_id, ALTIJARA_PAGE_META_KEY, $defaults );
	}
}

/**
 * Get theme-owned utility page by logical slug (about → about or about-2).
 *
 * @param string $logical_slug Blueprint slug.
 * @return WP_Post|null
 */
function altijara_get_utility_page( $logical_slug ) {
	$logical_slug = sanitize_title( $logical_slug );
	$ids          = altijara_get_option( 'utility_page_ids', array() );
	if ( is_array( $ids ) && ! empty( $ids[ $logical_slug ] ) ) {
		$post = get_post( (int) $ids[ $logical_slug ] );
		if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
			return $post;
		}
	}
	$page = get_page_by_path( $logical_slug );
	return ( $page instanceof WP_Post ) ? $page : null;
}

/**
 * Ensure utility pages exist with matching templates (idempotent).
 * If about/contact already exist without Altijara templates, create about-2 / contact-2.
 *
 * @return array{created:int,existed:int,template_set:int,errors:string[],details:array<string,string>,ids:array<string,int>}
 */
function altijara_ensure_utility_pages() {
	$report = array(
		'created'      => 0,
		'existed'      => 0,
		'template_set' => 0,
		'errors'       => array(),
		'details'      => array(),
		'ids'          => array(),
	);

	if ( ! function_exists( 'altijara_default_utility_pages_blueprint' ) ) {
		return $report;
	}

	$ids = altijara_get_option( 'utility_page_ids', array() );
	if ( ! is_array( $ids ) ) {
		$ids = array();
	}

	foreach ( altijara_default_utility_pages_blueprint() as $row ) {
		$logical  = sanitize_title( $row['slug'] ?? '' );
		$title    = isset( $row['title'] ) ? (string) $row['title'] : $logical;
		$template = isset( $row['template'] ) ? sanitize_file_name( $row['template'] ) : '';
		if ( ! $logical || ! $template ) {
			continue;
		}

		// Prefer previously resolved theme page ID.
		$page = null;
		if ( ! empty( $ids[ $logical ] ) ) {
			$maybe = get_post( (int) $ids[ $logical ] );
			if ( $maybe instanceof WP_Post && 'trash' !== $maybe->post_status ) {
				$page = $maybe;
			}
		}

		if ( ! $page ) {
			$by_path = get_page_by_path( $logical );
			if ( $by_path instanceof WP_Post && altijara_page_uses_theme_template( $by_path, $template ) ) {
				$page = $by_path;
			} elseif ( $by_path instanceof WP_Post ) {
				$current = (string) get_post_meta( $by_path->ID, '_wp_page_template', true );
				// Reclaim default/empty template pages for the theme.
				if ( '' === $current || 'default' === $current ) {
					update_post_meta( $by_path->ID, '_wp_page_template', $template );
					$page = $by_path;
					++$report['template_set'];
					$report['details'][ $logical ] = 'existed_template_set';
				} else {
					// Conflict: create about-2 / contact-2…
					$new_slug = altijara_next_free_page_slug( $logical );
					$page_id  = wp_insert_post(
						array(
							'post_title'   => $title,
							'post_name'    => $new_slug,
							'post_status'  => 'publish',
							'post_type'    => 'page',
							'post_content' => '',
						),
						true
					);
					if ( is_wp_error( $page_id ) ) {
						$report['errors'][]         = $logical . ': ' . $page_id->get_error_message();
						$report['details'][ $logical ] = 'error';
						continue;
					}
					update_post_meta( (int) $page_id, '_wp_page_template', $template );
					altijara_seed_utility_page_meta( (int) $page_id, $template );
					$page = get_post( (int) $page_id );
					++$report['created'];
					++$report['template_set'];
					$report['details'][ $logical ] = 'created_conflict:' . $new_slug;
				}
			}
		}

		if ( ! $page ) {
			$page_id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_name'    => $logical,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => '',
				),
				true
			);
			if ( is_wp_error( $page_id ) ) {
				$report['errors'][]            = $logical . ': ' . $page_id->get_error_message();
				$report['details'][ $logical ] = 'error';
				continue;
			}
			update_post_meta( (int) $page_id, '_wp_page_template', $template );
			altijara_seed_utility_page_meta( (int) $page_id, $template );
			$page = get_post( (int) $page_id );
			++$report['created'];
			++$report['template_set'];
			$report['details'][ $logical ] = 'created';
		} else {
			if ( empty( $report['details'][ $logical ] ) ) {
				++$report['existed'];
				$report['details'][ $logical ] = 'existed';
			}
			$current = (string) get_post_meta( $page->ID, '_wp_page_template', true );
			if ( $current !== $template ) {
				update_post_meta( $page->ID, '_wp_page_template', $template );
				++$report['template_set'];
			}
			altijara_seed_utility_page_meta( (int) $page->ID, $template );
		}

		if ( $page instanceof WP_Post ) {
			$ids[ $logical ]           = (int) $page->ID;
			$report['ids'][ $logical ] = (int) $page->ID;
		}
	}

	altijara_update_options( array( 'utility_page_ids' => $ids ) );

	return $report;
}

/**
 * Run activation setup (idempotent).
 *
 * @return array<string, mixed> Report.
 */
function altijara_run_theme_setup() {
	$report = array(
		'options' => 'skipped',
		'pages'   => array(),
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

	// 2) Utility pages (before menus so page_slug items resolve to real pages).
	$report['pages'] = altijara_ensure_utility_pages();
	if ( ! empty( $report['pages']['errors'] ) && is_array( $report['pages']['errors'] ) ) {
		$report['pending'] = array_merge( $report['pending'], $report['pages']['errors'] );
	}

	// 2a) Home 2 page as static front page (metabox-controlled homepage).
	if ( function_exists( 'altijara_ensure_home2_front_page' ) ) {
		$report['home2'] = altijara_ensure_home2_front_page();
	}

	// 2b) Seed category map from live terms when present.
	if ( function_exists( 'altijara_detect_category_map' ) ) {
		$map = altijara_detect_category_map( true );
		altijara_update_options( array( 'category_map' => $map ) );
		$report['category_map'] = 'detected';
	}

	// 2c) Seed magazine issues CPT + featured images from media / issues_data.json.
	// after_switch_theme may run before init — register CPT first.
	if ( function_exists( 'altijara_register_issue_cpt' ) ) {
		altijara_register_issue_cpt();
	}
	if ( function_exists( 'altijara_ingest_issues_from_json' ) ) {
		$report['issues'] = altijara_ingest_issues_from_json( true );
	}

	// 3) Force-rebuild theme-owned menus (upload/reactivate must refresh stale Custom Links).
	$report['menus']['primary']      = altijara_rebuild_primary_nav_menu();
	$report['menus']['top_bar']      = altijara_rebuild_utility_nav_menu();
	$report['menus']['footer']       = altijara_rebuild_footer_menus();
	$report['pending']               = array_merge(
		$report['pending'],
		(array) ( $report['menus']['primary']['pending'] ?? array() ),
		(array) ( $report['menus']['top_bar']['pending'] ?? array() ),
		(array) ( $report['menus']['footer']['pending'] ?? array() )
	);

	// Refresh any remaining category taxonomy links.
	$report['menu_sync'] = altijara_sync_nav_menus_from_category_map();

	// 4) Flush rewrites once per setup version (issue CPT archive).
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
 * Run setup when theme zip is uploaded over an already-active theme
 * (after_switch_theme does not fire). Compares ALTIJARA_THEME_VERSION.
 */
function altijara_maybe_run_versioned_setup() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$ver   = defined( 'ALTIJARA_THEME_VERSION' ) ? ALTIJARA_THEME_VERSION : '1';
	$state = get_option( ALTIJARA_SETUP_FLAG, array() );
	if ( ! is_array( $state ) ) {
		$state = array();
	}
	if ( ( $state['version'] ?? '' ) === $ver && ! empty( $state['last_run'] ) ) {
		return;
	}
	// Avoid recursion / double run within same request.
	if ( get_transient( 'altijara_setup_running' ) ) {
		return;
	}
	set_transient( 'altijara_setup_running', 1, 60 );
	altijara_run_theme_setup();
	delete_transient( 'altijara_setup_running' );
}
add_action( 'admin_init', 'altijara_maybe_run_versioned_setup', 5 );

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
	$menus   = isset( $report['menus'] ) && is_array( $report['menus'] ) ? $report['menus'] : array();
	foreach ( $menus as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		if ( ! empty( $row['created'] ) || ! empty( $row['items_added'] ) ) {
			++$created;
		}
		if ( ! empty( $row['skipped'] ) ) {
			++$skipped;
		}
	}
	$pages          = isset( $report['pages'] ) && is_array( $report['pages'] ) ? $report['pages'] : array();
	$pages_created  = isset( $pages['created'] ) ? (int) $pages['created'] : 0;
	$pages_existed  = isset( $pages['existed'] ) ? (int) $pages['existed'] : 0;
	$options_status = isset( $report['options'] ) && is_scalar( $report['options'] ) ? (string) $report['options'] : '—';
	$pending        = isset( $report['pending'] ) && is_array( $report['pending'] ) ? $report['pending'] : array();
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong><?php esc_html_e( 'Altijara Theme — اكتمل الإعداد الأولي', 'altijara' ); ?></strong></p>
		<p>
			<?php
			printf(
				/* translators: 1: options status, 2: pages created, 3: pages existed, 4: menus updated, 5: menus skipped */
				esc_html__( 'الإعدادات: %1$s · صفحات جديدة: %2$d · صفحات موجودة: %3$d · قوائم محدّثة: %4$d · قوائم موجودة (تم التخطي): %5$d', 'altijara' ),
				esc_html( $options_status ),
				$pages_created,
				$pages_existed,
				(int) $created,
				(int) $skipped
			);
			?>
		</p>
		<?php if ( ! empty( $pending ) ) : ?>
			<p><?php esc_html_e( 'عناصر تحتاج مراجعة يدوية (تصنيف/صفحة غير موجودة):', 'altijara' ); ?></p>
			<ul style="list-style:disc;margin-inline-start:1.5em;">
				<?php foreach ( array_slice( $pending, 0, 20 ) as $p ) : ?>
					<li><?php echo esc_html( is_scalar( $p ) ? (string) $p : wp_json_encode( $p ) ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<p>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'عرض الصفحات', 'altijara' ); ?></a>
			|
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

/**
 * Manual issues ingest from Tools tab.
 */
function altijara_admin_ingest_issues() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( empty( $_POST['altijara_ingest_issues'] ) ) {
		return;
	}
	if ( ! isset( $_POST['altijara_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_settings_nonce'] ) ), 'altijara_save_settings' ) ) {
		return;
	}
	if ( function_exists( 'altijara_register_issue_cpt' ) ) {
		altijara_register_issue_cpt();
	}
	if ( ! function_exists( 'altijara_ingest_issues_from_json' ) ) {
		return;
	}
	$report = altijara_ingest_issues_from_json( true );
	set_transient(
		'altijara_issues_ingest_notice',
		$report,
		HOUR_IN_SECONDS
	);
}
add_action( 'admin_init', 'altijara_admin_ingest_issues' );

/**
 * Admin notice after manual issues ingest.
 */
function altijara_issues_ingest_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$report = get_transient( 'altijara_issues_ingest_notice' );
	if ( ! $report || ! is_array( $report ) ) {
		return;
	}
	delete_transient( 'altijara_issues_ingest_notice' );
	?>
	<div class="notice notice-success is-dismissible">
		<p>
			<?php
			printf(
				/* translators: 1: created, 2: updated, 3: thumbs attached */
				esc_html__( 'Issues synced — created: %1$d, updated: %2$d, featured images attached: %3$d.', 'altijara' ),
				(int) ( $report['created'] ?? 0 ),
				(int) ( $report['updated'] ?? 0 ),
				(int) ( $report['thumbs'] ?? 0 )
			);
			?>
		</p>
		<?php if ( ! empty( $report['errors'] ) && is_array( $report['errors'] ) ) : ?>
			<ul style="list-style:disc;margin-inline-start:1.5em;">
				<?php foreach ( array_slice( $report['errors'], 0, 10 ) as $err ) : ?>
					<li><?php echo esc_html( is_scalar( $err ) ? (string) $err : wp_json_encode( $err ) ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'admin_notices', 'altijara_issues_ingest_admin_notice' );
