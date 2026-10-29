<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register admin menu.
 */
function altijara_register_admin_menu() {
	add_menu_page(
		__( 'Theme Options', 'altijara' ),
		__( 'Theme Options', 'altijara' ),
		'manage_options',
		'altijara-theme',
		'altijara_render_settings_page',
		'dashicons-admin-settings',
		61
	);
	add_submenu_page(
		'altijara-theme',
		__( 'Theme Options', 'altijara' ),
		__( 'Theme Options', 'altijara' ),
		'manage_options',
		'altijara-theme',
		'altijara_render_settings_page'
	);
	add_submenu_page(
		'altijara-theme',
		__( 'Category Archives', 'altijara' ),
		__( 'Category Archives', 'altijara' ),
		'manage_options',
		'altijara-category-archives',
		'altijara_render_category_archives_page'
	);
	add_submenu_page(
		'altijara-theme',
		__( 'Issues Archive', 'altijara' ),
		__( 'Issues Archive', 'altijara' ),
		'manage_options',
		'altijara-issues-archive',
		'altijara_render_issues_archive_page'
	);
	add_submenu_page(
		'altijara-theme',
		__( 'Search Template', 'altijara' ),
		__( 'Search Template', 'altijara' ),
		'manage_options',
		'altijara-search-template',
		'altijara_render_search_template_page'
	);
	add_submenu_page(
		'altijara-theme',
		__( 'Tag Archives', 'altijara' ),
		__( 'Tag Archives', 'altijara' ),
		'manage_options',
		'altijara-tag-archives',
		'altijara_render_tag_archives_page'
	);
}
add_action( 'admin_menu', 'altijara_register_admin_menu' );

/**
 * Deep-link URL into the main Theme Options shell for an archive tab.
 *
 * @param string $tab Tab id (category_archives|issues_archive|…).
 * @return string
 */
function altijara_theme_options_tab_url( $tab ) {
	$tab = sanitize_key( (string) $tab );
	return admin_url( 'admin.php?page=altijara-theme&tab=' . rawurlencode( $tab ) );
}

/**
 * Redirect archive WP submenu screens into the main shell (assets always load).
 *
 * @param string $tab Tab id.
 */
function altijara_redirect_theme_options_tab( $tab ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'altijara' ) );
	}
	wp_safe_redirect( altijara_theme_options_tab_url( $tab ) );
	exit;
}

/**
 * Category Archives submenu → Theme Options tab.
 */
function altijara_render_category_archives_page() {
	altijara_redirect_theme_options_tab( 'category_archives' );
}

/**
 * Issues Archive submenu → Theme Options tab.
 */
function altijara_render_issues_archive_page() {
	altijara_redirect_theme_options_tab( 'issues_archive' );
}

/**
 * Search Template submenu → Theme Options tab.
 */
function altijara_render_search_template_page() {
	altijara_redirect_theme_options_tab( 'search_template' );
}

/**
 * Tag Archives submenu → Theme Options tab.
 */
function altijara_render_tag_archives_page() {
	altijara_redirect_theme_options_tab( 'tag_archives' );
}

/**
 * Whether the current admin screen is Theme Options (main or archive submenu).
 *
 * Matches $_GET['page'] first so assets load even when WP's $hook slug differs.
 *
 * @param string $hook Current admin page hook.
 * @return bool
 */
function altijara_is_theme_admin_screen( $hook = '' ) {
	$pages = array(
		'altijara-theme',
		'altijara-category-archives',
		'altijara-issues-archive',
		'altijara-search-template',
		'altijara-tag-archives',
	);

	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $page && in_array( $page, $pages, true ) ) {
		return true;
	}

	$hooks = array(
		'toplevel_page_altijara-theme',
		'altijara-theme_page_altijara-category-archives',
		'altijara-theme_page_altijara-issues-archive',
		'altijara-theme_page_altijara-search-template',
		'altijara-theme_page_altijara-tag-archives',
	);

	return in_array( (string) $hook, array_merge( $hooks, $pages ), true );
}

/**
 * Enqueue admin media + settings JS.
 *
 * @param string $hook Hook.
 */
function altijara_admin_enqueue_settings( $hook ) {
	if ( ! altijara_is_theme_admin_screen( $hook ) ) {
		return;
	}
	wp_enqueue_media();
	if ( function_exists( 'altijara_enqueue_section_manager_assets' ) ) {
		altijara_enqueue_section_manager_assets();
	} else {
		wp_enqueue_style( 'dashicons' );
	}
	$settings_js = ALTIJARA_THEME_DIR . '/assets/js/admin/theme-settings.js';
	wp_enqueue_script(
		'altijara-admin-settings',
		altijara_asset( 'assets/js/admin/theme-settings.js' ),
		array( 'jquery' ),
		file_exists( $settings_js ) ? (string) filemtime( $settings_js ) : ALTIJARA_THEME_VERSION,
		true
	);

	// All Theme Options panels stay in the DOM (SPA tabs) — keep emails preview ready.
	altijara_admin_enqueue_emails_builder();
}
add_action( 'admin_enqueue_scripts', 'altijara_admin_enqueue_settings' );

/**
 * Emails builder CSS/JS (live preview).
 */
function altijara_admin_enqueue_emails_builder() {
	$css_path = ALTIJARA_THEME_DIR . '/assets/css/admin-emails.css';
	$js_path  = ALTIJARA_THEME_DIR . '/assets/js/admin/emails-builder.js';
	wp_enqueue_style(
		'altijara-admin-emails',
		altijara_asset( 'assets/css/admin-emails.css' ),
		array( 'dashicons' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : ALTIJARA_THEME_VERSION
	);
	wp_enqueue_script(
		'altijara-admin-emails',
		altijara_asset( 'assets/js/admin/emails-builder.js' ),
		array( 'jquery' ),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : ALTIJARA_THEME_VERSION,
		true
	);
	wp_localize_script(
		'altijara-admin-emails',
		'AltijaraMailBuilder',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'altijara_mail_preview' ),
			'i18n'    => array(
				'ready'    => __( 'Ready', 'altijara' ),
				'updating' => __( 'Updating…', 'altijara' ),
				'error'    => __( 'Preview failed', 'altijara' ),
			),
		)
	);
}

/**
 * Archives menu item opens Category Archives. The two archive screens sit under it.
 *
 * @param string $submenu_file Current submenu slug.
 * @return string
 */
function altijara_archive_submenu_file( $submenu_file ) {
	global $plugin_page;
	$archive_pages = array( 'altijara-category-archives', 'altijara-issues-archive', 'altijara-search-template', 'altijara-tag-archives' );
	if ( in_array( $plugin_page, $archive_pages, true ) ) {
		return $plugin_page;
	}
	if ( 'altijara-theme' === $plugin_page ) {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$map = array(
			'category_archives' => 'altijara-category-archives',
			'issues_archive'    => 'altijara-issues-archive',
			'search_template'   => 'altijara-search-template',
			'tag_archives'      => 'altijara-tag-archives',
		);
		if ( isset( $map[ $tab ] ) ) {
			return $map[ $tab ];
		}
	}
	return $submenu_file;
}
add_filter( 'submenu_file', 'altijara_archive_submenu_file' );

/**
 * Place Archives above its archive screens in the Altijara admin menu.
 */
function altijara_nest_archives_menu() {
	global $submenu;
	if ( empty( $submenu['altijara-theme'] ) || ! is_array( $submenu['altijara-theme'] ) ) {
		return;
	}
	$cat    = null;
	$issues = null;
	$search = null;
	$tag    = null;
	$rest   = array();
	foreach ( $submenu['altijara-theme'] as $item ) {
		$slug = isset( $item[2] ) ? (string) $item[2] : '';
		if ( 'altijara-category-archives' === $slug ) {
			$cat = $item;
			continue;
		}
		if ( 'altijara-issues-archive' === $slug ) {
			$issues = $item;
			continue;
		}
		if ( 'altijara-search-template' === $slug ) {
			$search = $item;
			continue;
		}
		if ( 'altijara-tag-archives' === $slug ) {
			$tag = $item;
			continue;
		}
		$rest[] = $item;
	}
	if ( ! $cat || ! $issues ) {
		return;
	}
	$archives = array(
		__( 'Archives', 'altijara' ),
		'manage_options',
		'admin.php?page=altijara-theme&tab=category_archives',
		__( 'Category Archives', 'altijara' ),
		'altijara-archives-item',
	);
	$ordered   = array();
	$ordered[] = array_shift( $rest );
	$ordered[] = $archives;
	$ordered[] = $cat;
	$ordered[] = $issues;
	if ( $search ) {
		$ordered[] = $search;
	}
	if ( $tag ) {
		$ordered[] = $tag;
	}
	foreach ( $rest as $item ) {
		$ordered[] = $item;
	}
	$submenu['altijara-theme'] = $ordered;
}
add_action( 'admin_menu', 'altijara_nest_archives_menu', 20 );

/**
 * Flyout under Archives in the WordPress admin menu.
 */
function altijara_archive_menu_flyout() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<style>
		#adminmenu li.altijara-archives-item > a .altijara-menu-caret { float: right; opacity: .7; }
		#adminmenu .altijara-archives-flyout { display: none; margin: 0; padding: 0 0 4px; list-style: none; }
		#adminmenu li.altijara-archives-item:hover > .altijara-archives-flyout,
		#adminmenu li.altijara-archives-item:focus-within > .altijara-archives-flyout,
		#adminmenu li.altijara-archives-item.is-open > .altijara-archives-flyout { display: block; }
		#adminmenu .altijara-archives-flyout a { padding-left: 28px !important; font-size: 12px; }
	</style>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var root = document.querySelector('#toplevel_page_altijara-theme .wp-submenu');
			if (!root) return;
			var archivesLi = root.querySelector('li.altijara-archives-item');
			if (!archivesLi) return;
			var cat = null;
			var issues = null;
			var search = null;
			var tag = null;
			root.querySelectorAll('li > a').forEach(function (link) {
				if (link.parentElement === archivesLi) return;
				var href = link.getAttribute('href') || '';
				if (href.indexOf('page=altijara-category-archives') !== -1 || href.indexOf('tab=category_archives') !== -1) cat = link.parentElement;
				if (href.indexOf('page=altijara-issues-archive') !== -1 || href.indexOf('tab=issues_archive') !== -1) issues = link.parentElement;
				if (href.indexOf('page=altijara-search-template') !== -1 || href.indexOf('tab=search_template') !== -1) search = link.parentElement;
				if (href.indexOf('page=altijara-tag-archives') !== -1 || href.indexOf('tab=tag_archives') !== -1) tag = link.parentElement;
			});
			if (!cat || !issues) return;
			var fly = document.createElement('ul');
			fly.className = 'altijara-archives-flyout';
			fly.appendChild(cat);
			fly.appendChild(issues);
			if (search) fly.appendChild(search);
			if (tag) fly.appendChild(tag);
			archivesLi.appendChild(fly);
			var page = '';
			var tab = '';
			try {
				var params = new URLSearchParams(window.location.search);
				page = params.get('page') || '';
				tab = params.get('tab') || '';
			} catch (err) {}
			var archiveTabs = { category_archives: 1, issues_archive: 1, search_template: 1, tag_archives: 1 };
			if (page === 'altijara-category-archives' || page === 'altijara-issues-archive' || page === 'altijara-search-template' || page === 'altijara-tag-archives' || (page === 'altijara-theme' && archiveTabs[tab])) {
				archivesLi.classList.add('is-open');
			}
			fly.querySelectorAll('a').forEach(function (link) {
				var href = link.getAttribute('href') || '';
				var matchTab = tab && href.indexOf('tab=' + tab) !== -1;
				var matchPage = page && page !== 'altijara-theme' && href.indexOf('page=' + page) !== -1;
				if (matchTab || matchPage) {
					link.parentElement.classList.add('current');
					link.setAttribute('aria-current', 'page');
				}
			});
		});
	</script>
	<?php
}
add_action( 'admin_footer', 'altijara_archive_menu_flyout' );
