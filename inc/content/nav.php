<?php
/**
 * Navigation locations, walkers, and menu render helpers.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all theme menu locations.
 */
function altijara_register_nav_menus() {
	register_nav_menus(
		array(
			'primary'      => __( 'Primary Menu (Header)', 'altijara' ),
			'top_bar'      => __( 'Top Bar / Drawer Links (Bottom)', 'altijara' ),
			'mobile'       => __( 'Drawer Sections (Top)', 'altijara' ),
			'footer'       => __( 'Footer Sections', 'altijara' ),
			'footer_quick' => __( 'Footer Quick Links', 'altijara' ),
			// Legacy alias used in older setup.php — keep for BC.
			'utility'      => __( 'Top Bar Links (Legacy)', 'altijara' ),
		)
	);
}
add_action( 'after_setup_theme', 'altijara_register_nav_menus', 20 );

/**
 * Direct child categories grouped by parent term ID.
 *
 * @return array<int, WP_Term[]>
 */
function altijara_category_children_map() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$map   = array();
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $map;
	}
	foreach ( $terms as $term ) {
		$parent = (int) $term->parent;
		if ( $parent < 1 ) {
			continue;
		}
		if ( ! isset( $map[ $parent ] ) ) {
			$map[ $parent ] = array();
		}
		$map[ $parent ][] = $term;
	}
	return $map;
}

/**
 * Child categories of one term.
 *
 * @param int $term_id Parent category ID.
 * @return WP_Term[]
 */
function altijara_category_direct_children( $term_id ) {
	$term_id = (int) $term_id;
	if ( $term_id < 1 ) {
		return array();
	}
	$map = altijara_category_children_map();
	return isset( $map[ $term_id ] ) ? $map[ $term_id ] : array();
}

/**
 * Register nav walkers after WP nav classes are available (avoids fatal on early load).
 */
function altijara_register_nav_walkers() {
	if ( ! class_exists( 'Walker_Nav_Menu', false ) ) {
		return;
	}

	if ( ! class_exists( 'Altijara_Footer_Link_Walker', false ) ) {
		/**
		 * Footer quick-links walker (span + caret).
		 */
		class Altijara_Footer_Link_Walker extends Walker_Nav_Menu {
			/**
			 * @param string   $output Output.
			 * @param WP_Post  $item   Item.
			 * @param int      $depth  Depth.
			 * @param stdClass $args   Args.
			 * @param int      $id     ID.
			 */
			public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
				$classes = empty( $item->classes ) ? array() : (array) $item->classes;
				$class   = $classes ? ' class="' . esc_attr( implode( ' ', array_map( 'sanitize_html_class', $classes ) ) ) . '"' : '';
				$url     = ! empty( $item->url ) ? $item->url : '';
				$target  = ! empty( $item->target ) ? $item->target : '';
				$rel     = '_blank' === $target ? 'noopener' : '';
				$title   = apply_filters( 'the_title', $item->title, $item->ID );

				$output .= '<li' . $class . '>';
				$output .= '<a href="' . esc_url( $url ) . '"';
				if ( $target ) {
					$output .= ' target="' . esc_attr( $target ) . '"';
				}
				if ( $rel ) {
					$output .= ' rel="' . esc_attr( $rel ) . '"';
				}
				$output .= '><span>' . esc_html( $title ) . '</span><span class="footer-caret">‹</span></a>';
			}

			/**
			 * @param string   $output Output.
			 * @param WP_Post  $item   Item.
			 * @param int      $depth  Depth.
			 * @param stdClass $args   Args.
			 */
			public function end_el( &$output, $item, $depth = 0, $args = null ) {
				$output .= '</li>';
			}
		}
	}

	if ( ! class_exists( 'Altijara_Drawer_Nav_Walker', false ) ) {
		/**
		 * Sections drawer walker — expandable parents with caret toggle.
		 */
		class Altijara_Drawer_Nav_Walker extends Walker_Nav_Menu {
			/**
			 * Parent item ID awaiting its sub-menu id attribute.
			 *
			 * @var int
			 */
			protected $pending_submenu_parent = 0;

			/**
			 * Category term children to print when the menu item itself has no submenu.
			 *
			 * @var array<int, WP_Term[]>
			 */
			protected $injected_children = array();

			/**
			 * @param string   $output Output.
			 * @param int      $depth  Depth.
			 * @param stdClass $args   Args.
			 */
			public function start_lvl( &$output, $depth = 0, $args = null ) {
				$indent = str_repeat( "\t", $depth );
				$id     = $this->pending_submenu_parent ? 'drawer-sub-' . (int) $this->pending_submenu_parent : '';
				$this->pending_submenu_parent = 0;
				$id_attr = $id ? ' id="' . esc_attr( $id ) . '"' : '';
				$output .= "\n{$indent}<ul class=\"sub-menu\"{$id_attr}>\n";
			}

			/**
			 * @param string   $output Output.
			 * @param WP_Post  $item   Item.
			 * @param int      $depth  Depth.
			 * @param stdClass $args   Args.
			 * @param int      $id     ID.
			 */
			public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
				$classes = empty( $item->classes ) ? array() : (array) $item->classes;
				$classes[] = 'menu-item-' . (int) $item->ID;

				$has_children = in_array( 'menu-item-has-children', $classes, true )
					|| ( is_object( $args ) && ! empty( $args->has_children ) );

				// Flat category links still expand when the term has child categories.
				$term_children = array();
				if ( ! $has_children && 0 === (int) $depth && isset( $item->object ) && 'category' === $item->object ) {
					$term_children = altijara_category_direct_children( (int) $item->object_id );
				}
				if ( $term_children ) {
					$classes[] = 'menu-item-has-children';
					$this->injected_children[ (int) $item->ID ] = $term_children;
				}

				$class_names = implode( ' ', array_map( 'sanitize_html_class', array_filter( $classes ) ) );
				$url         = ! empty( $item->url ) ? $item->url : '';
				$target      = ! empty( $item->target ) ? $item->target : '';
				$rel         = '_blank' === $target ? 'noopener' : '';
				$title       = apply_filters( 'the_title', $item->title, $item->ID );
				$submenu_id  = 'drawer-sub-' . (int) $item->ID;

				$output .= '<li class="' . esc_attr( $class_names ) . '">';

				$link_attrs = ' href="' . esc_url( $url ) . '"';
				if ( $target ) {
					$link_attrs .= ' target="' . esc_attr( $target ) . '"';
				}
				if ( $rel ) {
					$link_attrs .= ' rel="' . esc_attr( $rel ) . '"';
				}
				if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true ) ) {
					$link_attrs .= ' class="is-active"';
				}

				$expandable = ( $has_children || $term_children ) && 0 === (int) $depth;
				if ( $expandable ) {
					if ( $has_children ) {
						$this->pending_submenu_parent = (int) $item->ID;
					}
					$output .= '<div class="drawer-item-row">';
					$output .= '<a' . $link_attrs . '><span>' . esc_html( $title ) . '</span></a>';
					$output .= '<button type="button" class="drawer-submenu-toggle" aria-expanded="false" aria-controls="' . esc_attr( $submenu_id ) . '" aria-label="' . esc_attr( sprintf( /* translators: %s: menu item title */ __( 'Show submenu for %s', 'altijara' ), $title ) ) . '">';
					$output .= '<span class="drawer-caret" aria-hidden="true">‹</span>';
					$output .= '</button>';
					$output .= '</div>';
				} else {
					$output .= '<a' . $link_attrs . '><span>' . esc_html( $title ) . '</span></a>';
				}
			}

			/**
			 * @param string   $output Output.
			 * @param WP_Post  $item   Item.
			 * @param int      $depth  Depth.
			 * @param stdClass $args   Args.
			 */
			public function end_el( &$output, $item, $depth = 0, $args = null ) {
				$item_id = (int) $item->ID;
				if ( ! empty( $this->injected_children[ $item_id ] ) ) {
					$submenu_id = 'drawer-sub-' . $item_id;
					$output    .= '<ul class="sub-menu" id="' . esc_attr( $submenu_id ) . '">';
					foreach ( $this->injected_children[ $item_id ] as $child ) {
						if ( ! $child instanceof WP_Term ) {
							continue;
						}
						$link = get_term_link( $child );
						if ( is_wp_error( $link ) ) {
							continue;
						}
						$output .= '<li class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-' . (int) $child->term_id . '">';
						$output .= '<a href="' . esc_url( $link ) . '"><span>' . esc_html( $child->name ) . '</span></a>';
						$output .= "</li>\n";
					}
					$output .= "</ul>\n";
					unset( $this->injected_children[ $item_id ] );
				}
				$output .= "</li>\n";
			}
		}
	}
}
add_action( 'init', 'altijara_register_nav_walkers', 1 );

/**
 * Whether a location has an assigned menu with items.
 *
 * @param string $location Location slug.
 */
function altijara_has_nav_menu( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		// Fall back utility → top_bar.
		if ( 'top_bar' === $location && ! empty( $locations['utility'] ) ) {
			return true;
		}
		return false;
	}
	$menu = wp_get_nav_menu_object( $locations[ $location ] );
	if ( ! $menu ) {
		return false;
	}
	$items = wp_get_nav_menu_items( $menu->term_id );
	return ! empty( $items );
}

/**
 * Resolve effective location (utility alias).
 *
 * @param string $location Preferred location.
 * @return string
 */
function altijara_resolve_menu_location( $location ) {
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations[ $location ] ) ) {
		return $location;
	}
	if ( 'top_bar' === $location && ! empty( $locations['utility'] ) ) {
		return 'utility';
	}
	if ( 'mobile' === $location && ! empty( $locations['primary'] ) ) {
		return 'primary';
	}
	return $location;
}

/**
 * Render primary nav from theme blueprint (Home + all post categories + Issues).
 * Used when WP menu is empty or incomplete.
 */
function altijara_render_primary_nav_from_blueprint() {
	if ( ! function_exists( 'altijara_default_primary_nav_blueprint' ) || ! function_exists( 'altijara_resolve_blueprint_item' ) ) {
		echo '<ul class="primary-nav-list" data-wp-menu="0"></ul>';
		return;
	}

	$current_cat = is_category() ? (int) get_queried_object_id() : 0;
	$is_home     = is_front_page() || is_home();
	$is_issues   = function_exists( 'altijara_is_issues_archive_view' ) && altijara_is_issues_archive_view();
	if ( ! $is_issues && ( is_post_type_archive( 'issue' ) || is_singular( 'issue' ) ) ) {
		$is_issues = true;
	}

	echo '<ul class="primary-nav-list" data-wp-menu="1">';
	foreach ( altijara_default_primary_nav_blueprint() as $row ) {
		$resolved = altijara_resolve_blueprint_item( $row );
		if ( empty( $resolved['url'] ) ) {
			continue;
		}
		$label   = isset( $row['label'] ) ? (string) $row['label'] : '';
		$type    = isset( $row['type'] ) ? (string) $row['type'] : '';
		$classes = array( 'menu-item' );
		if ( 'home' === $type && $is_home ) {
			$classes[] = 'current-menu-item';
			$classes[] = 'active';
		} elseif ( 'issues' === $type && $is_issues ) {
			$classes[] = 'current-menu-item';
			$classes[] = 'active';
		} elseif ( 'category_key' === $type && $current_cat && (int) ( $resolved['object_id'] ?? 0 ) === $current_cat ) {
			$classes[] = 'current-menu-item';
			$classes[] = 'active';
		}
		if ( 'taxonomy' === ( $resolved['type'] ?? '' ) ) {
			$classes[] = 'menu-item-object-category';
			$classes[] = 'menu-item-type-taxonomy';
		}
		printf(
			'<li class="%1$s"><a href="%2$s">%3$s</a></li>',
			esc_attr( implode( ' ', $classes ) ),
			esc_url( $resolved['url'] ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Render primary header menu.
 */
function altijara_render_primary_nav() {
	$loc       = altijara_resolve_menu_location( 'primary' );
	$locations = get_nav_menu_locations();
	$menu_id   = ( ! empty( $locations[ $loc ] ) ) ? (int) $locations[ $loc ] : 0;
	$items     = $menu_id ? wp_get_nav_menu_items( $menu_id ) : array();
	$count     = is_array( $items ) ? count( $items ) : 0;

	// Respect Appearance → Menus whenever the assigned location has items.
	if ( $count < 1 ) {
		altijara_render_primary_nav_from_blueprint();
		return;
	}

	wp_nav_menu(
		array(
			'theme_location' => $loc,
			'container'      => false,
			'menu_class'     => 'primary-nav-list',
			'menu_id'        => '',
			'fallback_cb'    => 'altijara_render_primary_nav_from_blueprint',
			'depth'          => 2,
			'items_wrap'     => '<ul class="%2$s" data-wp-menu="1">%3$s</ul>',
		)
	);
}

/**
 * Render top bar from blueprint: من نحن · اتصل بنا · أعلن معنا.
 */
function altijara_render_top_bar_nav_from_blueprint() {
	if ( ! function_exists( 'altijara_default_utility_nav_blueprint' ) || ! function_exists( 'altijara_resolve_blueprint_item' ) ) {
		echo '<ul class="top-nav-links" data-wp-menu="0"></ul>';
		return;
	}

	echo '<ul class="top-nav-links" data-wp-menu="1">';
	foreach ( altijara_default_utility_nav_blueprint() as $row ) {
		$resolved = altijara_resolve_blueprint_item( $row );
		if ( empty( $resolved['url'] ) ) {
			continue;
		}
		$slug    = isset( $row['slug'] ) ? (string) $row['slug'] : '';
		$label   = isset( $row['label'] ) ? (string) $row['label'] : '';
		$classes = array( 'menu-item' );
		$current = false;
		if ( $slug && is_page( $slug ) ) {
			$current = true;
		} elseif ( ! empty( $resolved['object_id'] ) && is_page( (int) $resolved['object_id'] ) ) {
			$current = true;
		}
		if ( $current ) {
			$classes[] = 'current-menu-item';
			$classes[] = 'is-current';
		}
		printf(
			'<li class="%1$s"><a href="%2$s"%3$s>%4$s</a></li>',
			esc_attr( implode( ' ', $classes ) ),
			esc_url( $resolved['url'] ),
			$current ? ' class="is-current"' : '',
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Render top bar utility menu.
 */
function altijara_render_top_bar_nav() {
	$loc       = altijara_resolve_menu_location( 'top_bar' );
	$locations = get_nav_menu_locations();
	$menu_id   = ( ! empty( $locations[ $loc ] ) ) ? (int) $locations[ $loc ] : 0;
	$items     = $menu_id ? wp_get_nav_menu_items( $menu_id ) : array();
	$count     = is_array( $items ) ? count( $items ) : 0;

	// Respect Appearance → Menus whenever the assigned location has items.
	if ( $count < 1 ) {
		altijara_render_top_bar_nav_from_blueprint();
		return;
	}

	wp_nav_menu(
		array(
			'theme_location' => $loc,
			'container'      => false,
			'menu_class'     => 'top-nav-links',
			'fallback_cb'    => 'altijara_render_top_bar_nav_from_blueprint',
			'depth'          => 1,
			'items_wrap'     => '<ul class="%2$s" data-wp-menu="1">%3$s</ul>',
		)
	);
}

/**
 * Render footer sections menu as two columns (prototype layout).
 */
function altijara_render_footer_sections_menu() {
	$loc = 'footer';
	if ( ! altijara_has_nav_menu( $loc ) ) {
		echo '<div class="footer-sections-subcols" id="footerSections" data-wp-menu="0"></div>';
		return;
	}
	$locations = get_nav_menu_locations();
	$menu_id   = (int) $locations[ $loc ];
	$items     = wp_get_nav_menu_items( $menu_id );
	if ( function_exists( 'altijara_filter_subscribe_menu_items' ) ) {
		$items = altijara_filter_subscribe_menu_items( $items );
	}
	if ( empty( $items ) ) {
		echo '<div class="footer-sections-subcols" id="footerSections" data-wp-menu="0"></div>';
		return;
	}
	$top = array_values(
		array_filter(
			$items,
			static function ( $item ) {
				return empty( $item->menu_item_parent );
			}
		)
	);
	// Seven rows per column. The heading fills the first row, so column one holds six links.
	$per   = 7;
	$first = array_slice( $top, 0, $per - 1 );
	$rest  = array_slice( $top, $per - 1 );
	$cols  = array( $first );
	if ( ! empty( $rest ) ) {
		$cols = array_merge( $cols, array_chunk( $rest, $per ) );
	}
	$footer = altijara_get_option( 'footer', array() );
	$heading = ! empty( $footer['sections_heading'] ) ? $footer['sections_heading'] : __( 'الأقسام', 'altijara' );

	echo '<div class="footer-sections-subcols" id="footerSections" data-wp-menu="1">';
	foreach ( $cols as $i => $col ) {
		if ( empty( $col ) ) {
			continue;
		}
		if ( 0 === $i ) {
			echo '<div class="footer-sections-col">';
			echo '<h4 class="footer-heading">' . esc_html( $heading ) . '</h4>';
		}
		echo '<ul class="footer-links-list">';
		foreach ( $col as $item ) {
			printf(
				'<li><a href="%1$s"><span>%2$s</span><span class="footer-caret">‹</span></a></li>',
				esc_url( $item->url ),
				esc_html( $item->title )
			);
		}
		echo '</ul>';
		if ( 0 === $i ) {
			echo '</div>';
		}
	}
	echo '</div>';
}

/**
 * Render footer quick links.
 */
function altijara_render_footer_quick_menu() {
	$loc = 'footer_quick';
	if ( ! altijara_has_nav_menu( $loc ) ) {
		return false;
	}
	$args = array(
		'theme_location' => $loc,
		'container'      => false,
		'menu_class'     => 'footer-links-list',
		'fallback_cb'    => false,
		'depth'          => 1,
		'items_wrap'     => '<ul class="%2$s" data-wp-menu="1">%3$s</ul>',
	);
	if ( class_exists( 'Altijara_Footer_Link_Walker', false ) ) {
		$args['walker'] = new Altijara_Footer_Link_Walker();
	}
	wp_nav_menu( $args );
	return true;
}

/**
 * Render drawer section links from the primary category blueprint.
 */
function altijara_render_drawer_sections_from_blueprint() {
	if ( ! function_exists( 'altijara_default_primary_nav_blueprint' ) || ! function_exists( 'altijara_resolve_blueprint_item' ) ) {
		echo '<ul class="sections-drawer-list" id="sectionsDrawerList" data-wp-menu="0"></ul>';
		return;
	}
	$current_cat = is_category() ? (int) get_queried_object_id() : 0;
	$is_home     = is_front_page() || is_home();
	$is_issues   = function_exists( 'altijara_is_issues_archive_view' ) && altijara_is_issues_archive_view();
	echo '<ul class="sections-drawer-list" id="sectionsDrawerList" data-wp-menu="1">';
	foreach ( altijara_default_primary_nav_blueprint() as $row ) {
		$resolved = altijara_resolve_blueprint_item( $row );
		if ( empty( $resolved['url'] ) ) {
			continue;
		}
		$label   = isset( $row['label'] ) ? (string) $row['label'] : '';
		$type    = isset( $row['type'] ) ? (string) $row['type'] : '';
		$active  = '';
		if ( ( 'home' === $type && $is_home ) || ( 'issues' === $type && $is_issues ) || ( 'category_key' === $type && $current_cat && (int) ( $resolved['object_id'] ?? 0 ) === $current_cat ) ) {
			$active = ' is-active';
		}
		$children = array();
		if ( 'category_key' === $type && ! empty( $resolved['object_id'] ) ) {
			$children = altijara_category_direct_children( (int) $resolved['object_id'] );
		}
		if ( $children ) {
			$sub_id = 'drawer-sub-cat-' . (int) $resolved['object_id'];
			echo '<li class="menu-item menu-item-has-children">';
			echo '<div class="drawer-item-row">';
			printf(
				'<a href="%1$s" class="%2$s"><span>%3$s</span></a>',
				esc_url( $resolved['url'] ),
				esc_attr( trim( $active ) ),
				esc_html( $label )
			);
			printf(
				'<button type="button" class="drawer-submenu-toggle" aria-expanded="false" aria-controls="%1$s" aria-label="%2$s"><span class="drawer-caret" aria-hidden="true">‹</span></button>',
				esc_attr( $sub_id ),
				esc_attr( sprintf( /* translators: %s: category name */ __( 'Show submenu for %s', 'altijara' ), $label ) )
			);
			echo '</div>';
			echo '<ul class="sub-menu" id="' . esc_attr( $sub_id ) . '">';
			foreach ( $children as $child ) {
				if ( ! $child instanceof WP_Term ) {
					continue;
				}
				$child_link = get_term_link( $child );
				if ( is_wp_error( $child_link ) ) {
					continue;
				}
				printf(
					'<li class="menu-item menu-item-type-taxonomy menu-item-object-category"><a href="%1$s"><span>%2$s</span></a></li>',
					esc_url( $child_link ),
					esc_html( $child->name )
				);
			}
			echo '</ul></li>';
		} else {
			printf(
				'<li><a href="%1$s" class="%2$s"><span>%3$s</span></a></li>',
				esc_url( $resolved['url'] ),
				esc_attr( trim( $active ) ),
				esc_html( $label )
			);
		}
	}
	echo '</ul>';
}

/**
 * Render drawer utility links from the top-bar blueprint.
 */
function altijara_render_drawer_utility_from_blueprint() {
	if ( ! function_exists( 'altijara_default_utility_nav_blueprint' ) || ! function_exists( 'altijara_resolve_blueprint_item' ) ) {
		echo '<ul class="sections-drawer-list" id="sectionsDrawerUtility" data-wp-menu="0"></ul>';
		return;
	}
	echo '<ul class="sections-drawer-list" id="sectionsDrawerUtility" data-wp-menu="1">';
	foreach ( altijara_default_utility_nav_blueprint() as $row ) {
		$resolved = altijara_resolve_blueprint_item( $row );
		if ( empty( $resolved['url'] ) ) {
			continue;
		}
		$label = isset( $row['label'] ) ? (string) $row['label'] : '';
		printf(
			'<li><a href="%1$s"><span>%2$s</span><span class="drawer-caret" aria-hidden="true">‹</span></a></li>',
			esc_url( $resolved['url'] ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Whether a menu location has at least one item (else drawer uses blueprint).
 *
 * @param string $location Location.
 * @param int    $min      Minimum items (default 1 — honor Appearance → Menus as-is).
 * @return bool
 */
function altijara_drawer_menu_is_usable( $location, $min = 1 ) {
	$locations = get_nav_menu_locations();
	$menu_id   = ( ! empty( $locations[ $location ] ) ) ? (int) $locations[ $location ] : 0;
	if ( ! $menu_id ) {
		return false;
	}
	$items = wp_get_nav_menu_items( $menu_id );
	$count = is_array( $items ) ? count( $items ) : 0;
	return $count >= max( 1, (int) $min );
}

/**
 * Render mobile drawer lists from WP menus.
 */
function altijara_render_mobile_drawer_markup() {
	$main_loc = altijara_resolve_menu_location( 'mobile' );
	$util_loc = altijara_resolve_menu_location( 'top_bar' );
	?>
	<div class="sections-drawer-overlay" id="sectionsDrawerOverlay" aria-hidden="true"></div>
	<aside class="sections-drawer" id="sectionsDrawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'أقسام المجلة', 'altijara' ); ?>" data-wp-menu="1">
		<?php
		$drawer_logo = function_exists( 'altijara_theme_logo_url' ) ? altijara_theme_logo_url( 'header' ) : '';
		?>
		<div class="sections-drawer-header">
			<div class="sections-drawer-brand">
				<?php if ( $drawer_logo ) : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="sections-drawer-logo-link">
						<img src="<?php echo esc_url( $drawer_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="sections-drawer-logo">
					</a>
				<?php endif; ?>
				<h2 class="sections-drawer-title"><?php esc_html_e( 'أقسام المجلة', 'altijara' ); ?></h2>
			</div>
			<button type="button" class="sections-drawer-close" id="sectionsDrawerClose" aria-label="<?php esc_attr_e( 'إغلاق القائمة', 'altijara' ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
			</button>
		</div>
		<div class="sections-drawer-body">
			<p class="sections-drawer-group-label"><?php esc_html_e( 'تصفح الأقسام', 'altijara' ); ?></p>
			<?php
			if ( altijara_drawer_menu_is_usable( $main_loc ) ) {
				$main_args = array(
					'theme_location' => $main_loc,
					'container'      => false,
					'menu_class'     => 'sections-drawer-list',
					'menu_id'        => 'sectionsDrawerList',
					'fallback_cb'    => false,
					'depth'          => 2,
					'items_wrap'     => '<ul id="%1$s" class="%2$s" data-wp-menu="1">%3$s</ul>',
				);
				if ( class_exists( 'Altijara_Drawer_Nav_Walker', false ) ) {
					$main_args['walker'] = new Altijara_Drawer_Nav_Walker();
				}
				wp_nav_menu( $main_args );
			} else {
				altijara_render_drawer_sections_from_blueprint();
			}
			?>
			<p class="sections-drawer-group-label"><?php esc_html_e( 'روابط تهمك', 'altijara' ); ?></p>
			<?php
			if ( altijara_drawer_menu_is_usable( $util_loc ) ) {
				wp_nav_menu(
					array(
						'theme_location' => $util_loc,
						'container'      => false,
						'menu_class'     => 'sections-drawer-list',
						'menu_id'        => 'sectionsDrawerUtility',
						'fallback_cb'    => false,
						'depth'          => 1,
						'items_wrap'     => '<ul id="%1$s" class="%2$s" data-wp-menu="1">%3$s</ul>',
					)
				);
			} else {
				altijara_render_drawer_utility_from_blueprint();
			}
			?>
		</div>
	</aside>
	<?php
}

/**
 * Add current-menu-item → active class for primary nav CSS.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function altijara_nav_menu_css_class( $classes ) {
	if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true ) ) {
		$classes[] = 'active';
		$classes[] = 'is-current';
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'altijara_nav_menu_css_class' );
