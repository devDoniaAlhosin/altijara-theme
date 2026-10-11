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
			'primary'      => __( 'القائمة الرئيسية (الهيدر)', 'altijara' ),
			'top_bar'      => __( 'شريط الروابط العلوي', 'altijara' ),
			'mobile'       => __( 'قائمة الجوال', 'altijara' ),
			'footer'       => __( 'قائمة أقسام التذييل', 'altijara' ),
			'footer_quick' => __( 'روابط تهمك (التذييل)', 'altijara' ),
			// Legacy alias used in older setup.php — keep for BC.
			'utility'      => __( 'شريط الروابط العلوي (قديم)', 'altijara' ),
		)
	);
}
add_action( 'after_setup_theme', 'altijara_register_nav_menus', 20 );

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
 * Render primary header menu.
 */
function altijara_render_primary_nav() {
	$loc = altijara_resolve_menu_location( 'primary' );
	if ( ! altijara_has_nav_menu( $loc ) && 'primary' === $loc ) {
		echo '<ul class="primary-nav-list" data-wp-menu="0"></ul>';
		return;
	}
	wp_nav_menu(
		array(
			'theme_location' => $loc,
			'container'      => false,
			'menu_class'     => 'primary-nav-list',
			'menu_id'        => '',
			'fallback_cb'    => false,
			'depth'          => 2,
			'items_wrap'     => '<ul class="%2$s" data-wp-menu="1">%3$s</ul>',
		)
	);
}

/**
 * Render top bar utility menu.
 */
function altijara_render_top_bar_nav() {
	$loc = altijara_resolve_menu_location( 'top_bar' );
	if ( ! altijara_has_nav_menu( $loc ) ) {
		echo '<ul class="top-nav-links" data-wp-menu="0"></ul>';
		return;
	}
	wp_nav_menu(
		array(
			'theme_location' => $loc,
			'container'      => false,
			'menu_class'     => 'top-nav-links',
			'fallback_cb'    => false,
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
	$mid  = (int) ceil( count( $top ) / 2 );
	$cols = array( array_slice( $top, 0, $mid ), array_slice( $top, $mid ) );
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
	wp_nav_menu(
		array(
			'theme_location' => $loc,
			'container'      => false,
			'menu_class'     => 'footer-links-list',
			'fallback_cb'    => false,
			'depth'          => 1,
			'walker'         => new Altijara_Footer_Link_Walker(),
			'items_wrap'     => '<ul class="%2$s" data-wp-menu="1">%3$s</ul>',
		)
	);
	return true;
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
		<div class="sections-drawer-header">
			<h2 class="sections-drawer-title"><?php esc_html_e( 'أقسام المجلة', 'altijara' ); ?></h2>
			<button type="button" class="sections-drawer-close" id="sectionsDrawerClose" aria-label="<?php esc_attr_e( 'إغلاق القائمة', 'altijara' ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
			</button>
		</div>
		<div class="sections-drawer-body">
			<p class="sections-drawer-group-label"><?php esc_html_e( 'تصفح الأقسام', 'altijara' ); ?></p>
			<?php
			if ( altijara_has_nav_menu( $main_loc ) ) {
				wp_nav_menu(
					array(
						'theme_location' => $main_loc,
						'container'      => false,
						'menu_class'     => 'sections-drawer-list',
						'menu_id'        => 'sectionsDrawerList',
						'fallback_cb'    => false,
						'depth'          => 2,
						'items_wrap'     => '<ul id="%1$s" class="%2$s" data-wp-menu="1">%3$s</ul>',
					)
				);
			} else {
				echo '<ul class="sections-drawer-list" id="sectionsDrawerList" data-wp-menu="0"></ul>';
			}
			?>
			<p class="sections-drawer-group-label"><?php esc_html_e( 'روابط تهمك', 'altijara' ); ?></p>
			<?php
			if ( altijara_has_nav_menu( $util_loc ) ) {
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
				echo '<ul class="sections-drawer-list" id="sectionsDrawerUtility" data-wp-menu="0"></ul>';
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
