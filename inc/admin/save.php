<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Process settings POST (notices echoed).
 */
function altijara_handle_settings_save() {
	if ( ! isset( $_POST['altijara_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_settings_nonce'] ) ), 'altijara_save_settings' ) ) {
		return;
	}

		if ( isset( $_POST['altijara_advanced'] ) && is_array( $_POST['altijara_advanced'] ) ) {
			$raw_adv  = wp_unslash( $_POST['altijara_advanced'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$prev_adv = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'advanced', array() ) : array();
			$prev_adv = is_array( $prev_adv ) ? $prev_adv : array();
			$defs_all  = function_exists( 'altijara_default_options' ) ? altijara_default_options() : array();
			$defs_adv_opts = isset( $defs_all['advanced'] ) && is_array( $defs_all['advanced'] ) ? $defs_all['advanced'] : array();
			$defs_adv = function_exists( 'altijara_empty_section_message_defaults' )
				? altijara_empty_section_message_defaults()
				: array(
					'message'  => '«{name}» — لا مقالات في هذا التصنيف بعد',
					'fallback' => 'لا توجد مقالات حالياً',
				);

			$slug_default = isset( $defs_adv_opts['admin_login_slug'] ) ? (string) $defs_adv_opts['admin_login_slug'] : 'altijaramagazine-admin-login';
			$bg_default   = isset( $defs_adv_opts['admin_login_bg'] ) ? (string) $defs_adv_opts['admin_login_bg'] : '#FFF1E5';
			$prev_slug    = isset( $prev_adv['admin_login_slug'] ) ? (string) $prev_adv['admin_login_slug'] : $slug_default;

			$login_bg = isset( $raw_adv['admin_login_bg'] ) ? trim( (string) $raw_adv['admin_login_bg'] ) : $bg_default;
			if ( ! preg_match( '/^#[0-9A-Fa-f]{6}$/', $login_bg ) ) {
				$login_bg = $bg_default;
			}

			$login_slug = isset( $raw_adv['admin_login_slug'] )
				? (string) $raw_adv['admin_login_slug']
				: $prev_slug;
			if ( function_exists( 'altijara_sanitize_admin_login_slug' ) ) {
				$login_slug = altijara_sanitize_admin_login_slug( $login_slug );
			} else {
				$login_slug = sanitize_title( $login_slug );
				if ( '' === $login_slug ) {
					$login_slug = $slug_default;
				}
			}

			$clean_adv = array_merge( $prev_adv, array(
				'cache_ttl'              => isset( $prev_adv['cache_ttl'] ) ? (int) $prev_adv['cache_ttl'] : 900,
				'empty_section_message'  => isset( $raw_adv['empty_section_message'] )
					? sanitize_text_field( (string) $raw_adv['empty_section_message'] )
					: ( isset( $prev_adv['empty_section_message'] ) ? (string) $prev_adv['empty_section_message'] : $defs_adv['message'] ),
				'empty_section_fallback' => isset( $raw_adv['empty_section_fallback'] )
					? sanitize_text_field( (string) $raw_adv['empty_section_fallback'] )
					: ( isset( $prev_adv['empty_section_fallback'] ) ? (string) $prev_adv['empty_section_fallback'] : $defs_adv['fallback'] ),
				'admin_login_slug'       => $login_slug,
				'admin_login_logo_url'   => isset( $raw_adv['admin_login_logo_url'] )
					? esc_url_raw( (string) $raw_adv['admin_login_logo_url'] )
					: ( isset( $prev_adv['admin_login_logo_url'] ) ? (string) $prev_adv['admin_login_logo_url'] : '' ),
				'admin_login_bg'         => $login_bg,
			) );
			if ( '' === trim( (string) $clean_adv['empty_section_message'] ) ) {
				$clean_adv['empty_section_message'] = $defs_adv['message'];
			}
			if ( '' === trim( (string) $clean_adv['empty_section_fallback'] ) ) {
				$clean_adv['empty_section_fallback'] = $defs_adv['fallback'];
			}
			altijara_update_options( array( 'advanced' => $clean_adv ) );

			if ( $prev_slug !== $login_slug ) {
				if ( function_exists( 'altijara_admin_login_slug' ) ) {
					altijara_admin_login_slug( true );
				}
				add_rewrite_rule(
					'^' . preg_quote( $login_slug, '/' ) . '/?$',
					'index.php?altijara_admin_login=1',
					'top'
				);
				flush_rewrite_rules( false );
				update_option( 'altijara_admin_login_rewrite_flushed', $login_slug, false );
			}

			echo '<div class="notice notice-success"><p>' . esc_html__( 'Admin login / tools settings saved.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_purge_cache'] ) ) {
			altijara_cache_purge_all();
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم تفريغ الكاش.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_reset_group'] ) ) {
			$group = sanitize_key( wp_unslash( $_POST['altijara_reset_group'] ) );
			$defaults = altijara_default_options();
			if ( isset( $defaults[ $group ] ) ) {
				altijara_update_options( array( $group => $defaults[ $group ] ) );
				echo '<div class="notice notice-success"><p>' . esc_html__( 'تمت إعادة المجموعة للافتراضي.', 'altijara' ) . '</p></div>';
			}
		}

		if ( isset( $_POST['altijara_category_map'] ) && is_array( $_POST['altijara_category_map'] ) ) {
			$map = array();
			foreach ( wp_unslash( $_POST['altijara_category_map'] ) as $k => $v ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$map[ sanitize_key( $k ) ] = sanitize_text_field( $v );
			}
			altijara_update_options( array( 'category_map' => $map ) );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ خريطة التصنيفات.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_design'] ) && is_array( $_POST['altijara_design'] ) && function_exists( 'altijara_sanitize_design_input' ) ) {
			altijara_update_options( array( 'design' => altijara_sanitize_design_input( wp_unslash( $_POST['altijara_design'] ) ) ) ); // phpcs:ignore
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ إعدادات المظهر (الخطوط والألوان).', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_header'] ) && is_array( $_POST['altijara_header'] ) ) {
			altijara_update_options( array( 'header' => altijara_sanitize_header_input( wp_unslash( $_POST['altijara_header'] ) ) ) ); // phpcs:ignore
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Header settings saved.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_footer'] ) && is_array( $_POST['altijara_footer'] ) ) {
			altijara_update_options( array( 'footer' => altijara_sanitize_footer_input( wp_unslash( $_POST['altijara_footer'] ) ) ) ); // phpcs:ignore
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Footer settings saved.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_social'] ) && is_array( $_POST['altijara_social'] ) ) {
			altijara_update_options( array( 'social' => altijara_sanitize_social_input( wp_unslash( $_POST['altijara_social'] ) ) ) ); // phpcs:ignore
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ روابط التواصل.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_auth'] ) && is_array( $_POST['altijara_auth'] ) && function_exists( 'altijara_sanitize_auth_settings' ) ) {
			altijara_update_options( array( 'auth' => altijara_sanitize_auth_settings( wp_unslash( $_POST['altijara_auth'] ) ) ) ); // phpcs:ignore
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ إعدادات تسجيل الدخول الاجتماعي.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_subscriptions'] ) && is_array( $_POST['altijara_subscriptions'] ) && function_exists( 'altijara_sanitize_subscriptions_input' ) ) {
			altijara_update_options( array( 'subscriptions' => altijara_sanitize_subscriptions_input( wp_unslash( $_POST['altijara_subscriptions'] ) ) ) ); // phpcs:ignore
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Subscription settings saved.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_emails'] ) && is_array( $_POST['altijara_emails'] ) && function_exists( 'altijara_sanitize_emails_input' ) ) {
			altijara_update_options( array( 'emails' => altijara_sanitize_emails_input( wp_unslash( $_POST['altijara_emails'] ) ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Email settings saved.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_article'] ) && is_array( $_POST['altijara_article'] ) ) {
			$a = wp_unslash( $_POST['altijara_article'] ); // phpcs:ignore
			$clean = function_exists( 'altijara_sanitize_article_settings' )
				? altijara_sanitize_article_settings( $a )
				: array(
					'show_related'  => ! empty( $a['show_related'] ),
					'show_author'   => ! empty( $a['show_author'] ),
					'related_count' => isset( $a['related_count'] ) ? max( 1, min( 12, (int) $a['related_count'] ) ) : 2,
				);
			altijara_update_options( array( 'article' => $clean ) );
			if ( ! empty( $_POST['altijara_article_sidebar_layout_submitted'] ) && function_exists( 'altijara_sanitize_layout' ) ) {
				$side_sections = isset( $_POST['altijara_article_sidebar_sections'] ) && is_array( $_POST['altijara_article_sidebar_sections'] )
					? wp_unslash( $_POST['altijara_article_sidebar_sections'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					: array();
				$side_order = isset( $_POST['altijara_article_sidebar_order'] ) && is_array( $_POST['altijara_article_sidebar_order'] )
					? wp_unslash( $_POST['altijara_article_sidebar_order'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					: array();
				$side_pack = altijara_sanitize_layout( $side_sections, $side_order, array( 'lock_hero' => false ) );
				$side_pack['visible'] = ! empty( $_POST['altijara_article_sidebar_layout_visible'] );
				altijara_update_options( array( 'article_sidebar_layout' => $side_pack ) );
			}
			if ( ! empty( $_POST['altijara_article_body_layout_submitted'] ) && function_exists( 'altijara_sanitize_layout' ) ) {
				$body_sections = isset( $_POST['altijara_article_body_sections'] ) && is_array( $_POST['altijara_article_body_sections'] )
					? wp_unslash( $_POST['altijara_article_body_sections'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					: array();
				$body_order = isset( $_POST['altijara_article_body_order'] ) && is_array( $_POST['altijara_article_body_order'] )
					? wp_unslash( $_POST['altijara_article_body_order'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					: array();
				$body_pack = altijara_sanitize_layout( $body_sections, $body_order, array( 'lock_hero' => false ) );
				$body_pack['visible'] = ! empty( $_POST['altijara_article_body_layout_visible'] );
				altijara_update_options( array( 'article_body_layout' => $body_pack ) );
			}
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ إعدادات المقال.', 'altijara' ) . '</p></div>';
		}

		if ( ! empty( $_POST['altijara_detect_category_map'] ) && function_exists( 'altijara_detect_category_map' ) ) {
			$map = altijara_detect_category_map( true );
			altijara_update_options( array( 'category_map' => $map ) );
			$notice = esc_html__( 'Category map updated from live categories.', 'altijara' );
			if ( function_exists( 'altijara_sync_nav_menus_from_category_map' ) ) {
				$sync   = altijara_sync_nav_menus_from_category_map();
				$notice .= ' ' . sprintf(
					/* translators: 1: updated count, 2: added count */
					esc_html__( 'Menus synced (%1$d updated, %2$d added).', 'altijara' ),
					(int) ( $sync['updated'] ?? 0 ),
					(int) ( $sync['added'] ?? 0 )
				);
			}
			echo '<div class="notice notice-success"><p>' . $notice . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above
		}
		if ( ! empty( $_POST['altijara_sync_nav_categories'] ) && function_exists( 'altijara_sync_nav_menus_from_category_map' ) ) {
			$sync = altijara_sync_nav_menus_from_category_map();
			echo '<div class="notice notice-success"><p>' . esc_html(
				sprintf(
					/* translators: 1: updated count, 2: added count */
					__( 'Menu category links synced (%1$d updated, %2$d added). Items now point to /category/{slug}/.', 'altijara' ),
					(int) ( $sync['updated'] ?? 0 ),
					(int) ( $sync['added'] ?? 0 )
				)
			) . '</p></div>';
			if ( ! empty( $sync['pending'] ) && is_array( $sync['pending'] ) ) {
				echo '<div class="notice notice-warning"><p>' . esc_html__( 'Some items still pending (category not mapped):', 'altijara' ) . ' ' . esc_html( implode( ', ', $sync['pending'] ) ) . '</p></div>';
			}
		}
		if ( ! empty( $_POST['altijara_rebuild_primary_nav'] ) && function_exists( 'altijara_rebuild_primary_nav_menu' ) ) {
			$built = altijara_rebuild_primary_nav_menu();
			echo '<div class="notice notice-success"><p>' . esc_html(
				sprintf(
					/* translators: %d: number of menu items created */
					__( 'Primary navbar rebuilt with %d items (all post categories).', 'altijara' ),
					(int) ( $built['items'] ?? 0 )
				)
			) . '</p></div>';
			if ( ! empty( $built['pending'] ) && is_array( $built['pending'] ) ) {
				echo '<div class="notice notice-warning"><p>' . esc_html__( 'Skipped (category not found on site):', 'altijara' ) . ' ' . esc_html( implode( ', ', $built['pending'] ) ) . '</p></div>';
			}
		}
		if ( ! empty( $_POST['altijara_rebuild_utility_nav'] ) && function_exists( 'altijara_rebuild_utility_nav_menu' ) ) {
			$built = altijara_rebuild_utility_nav_menu();
			$pages = isset( $built['pages'] ) && is_array( $built['pages'] ) ? $built['pages'] : array();
			echo '<div class="notice notice-success"><p>' . esc_html(
				sprintf(
					/* translators: 1: menu items, 2: pages created, 3: pages existed */
					__( 'Top bar rebuilt (%1$d links). Utility pages: %2$d created, %3$d already existed.', 'altijara' ),
					(int) ( $built['items'] ?? 0 ),
					(int) ( $pages['created'] ?? 0 ),
					(int) ( $pages['existed'] ?? 0 )
				)
			) . '</p></div>';
			if ( ! empty( $built['pending'] ) && is_array( $built['pending'] ) ) {
				echo '<div class="notice notice-warning"><p>' . esc_html__( 'Pending:', 'altijara' ) . ' ' . esc_html( implode( ', ', $built['pending'] ) ) . '</p></div>';
			}
		}
		if ( ! empty( $_POST['altijara_rebuild_footer_nav'] ) && function_exists( 'altijara_rebuild_footer_menus' ) ) {
			$built    = altijara_rebuild_footer_menus();
			$sec_n    = (int) ( $built['sections']['items'] ?? 0 );
			$quick_n  = (int) ( $built['quick']['items'] ?? 0 );
			echo '<div class="notice notice-success"><p>' . esc_html(
				sprintf(
					/* translators: 1: section links, 2: quick links */
					__( 'Footer menus rebuilt: %1$d section links, %2$d quick links.', 'altijara' ),
					$sec_n,
					$quick_n
				)
			) . '</p></div>';
			if ( ! empty( $built['pending'] ) && is_array( $built['pending'] ) ) {
				echo '<div class="notice notice-warning"><p>' . esc_html__( 'Pending:', 'altijara' ) . ' ' . esc_html( implode( ', ', $built['pending'] ) ) . '</p></div>';
			}
		}
		$opt_update = array();

		// Each flag updates only its own keys. archive_magazine_ids and archive_layouts are never written here.
		if ( ! empty( $_POST['altijara_archive_full_hero_submitted'] ) ) {
			$full_hero = array();
			if ( isset( $_POST['altijara_archive_full_hero'] ) && is_array( $_POST['altijara_archive_full_hero'] ) && function_exists( 'altijara_sanitize_archive_full_hero' ) ) {
				$full_hero = altijara_sanitize_archive_full_hero( wp_unslash( $_POST['altijara_archive_full_hero'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			}
			$opt_update['archive_full_hero'] = $full_hero;
		}

		if ( ! empty( $_POST['altijara_archive_sidebar_submitted'] ) ) {
			$sidebar_raw = array();
			if ( isset( $_POST['altijara_archive_sidebar'] ) && is_array( $_POST['altijara_archive_sidebar'] ) ) {
				$sidebar_raw = wp_unslash( $_POST['altijara_archive_sidebar'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			}
			$opt_update['archive_sidebar'] = function_exists( 'altijara_sanitize_archive_sidebar' )
				? altijara_sanitize_archive_sidebar( $sidebar_raw )
				: array(
					'visible' => ! empty( $sidebar_raw['visible'] ),
					'order'   => array( 'ad', 'magazine', 'most_read' ),
				);
		}

		if ( ! empty( $_POST['altijara_issues_layout_submitted'] ) ) {
			if ( ! empty( $_POST['altijara_issues_layout_reset'] ) ) {
				// null = delete stored layout so the theme preset is used again (titles, From/To, etc.).
				$opt_update['issues_archive_layout'] = null;
			} elseif ( function_exists( 'altijara_sanitize_layout' ) ) {
				// Missing sections = all modules removed; persist empty pack (do not snap back to preset).
				$sections = isset( $_POST['altijara_issues_layout_sections'] ) && is_array( $_POST['altijara_issues_layout_sections'] )
					? wp_unslash( $_POST['altijara_issues_layout_sections'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					: array();
				$order    = isset( $_POST['altijara_issues_layout_order'] ) && is_array( $_POST['altijara_issues_layout_order'] )
					? wp_unslash( $_POST['altijara_issues_layout_order'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					: array();
				$opt_update['issues_archive_layout'] = altijara_sanitize_layout( $sections, $order, array( 'lock_hero' => false ) );
			}
		}

		if ( ! empty( $_POST['altijara_archive_sidebar_layout_submitted'] ) && function_exists( 'altijara_sanitize_layout' ) ) {
			$sidebar_sections = isset( $_POST['altijara_archive_sidebar_sections'] ) && is_array( $_POST['altijara_archive_sidebar_sections'] )
				? wp_unslash( $_POST['altijara_archive_sidebar_sections'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				: array();
			$sidebar_order = isset( $_POST['altijara_archive_sidebar_order'] ) && is_array( $_POST['altijara_archive_sidebar_order'] )
				? wp_unslash( $_POST['altijara_archive_sidebar_order'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				: array();
			$sidebar_pack = altijara_sanitize_layout( $sidebar_sections, $sidebar_order, array( 'lock_hero' => false ) );
			$sidebar_pack['visible'] = ! empty( $_POST['altijara_archive_sidebar_layout_visible'] );
			$opt_update['archive_sidebar_layout'] = $sidebar_pack;
		}

		if ( ! empty( $_POST['altijara_search_sidebar_layout_submitted'] ) && function_exists( 'altijara_sanitize_layout' ) ) {
			$search_sections = isset( $_POST['altijara_search_sidebar_sections'] ) && is_array( $_POST['altijara_search_sidebar_sections'] )
				? wp_unslash( $_POST['altijara_search_sidebar_sections'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				: array();
			$search_order = isset( $_POST['altijara_search_sidebar_order'] ) && is_array( $_POST['altijara_search_sidebar_order'] )
				? wp_unslash( $_POST['altijara_search_sidebar_order'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				: array();
			$search_pack = altijara_sanitize_layout( $search_sections, $search_order, array( 'lock_hero' => false ) );
			$search_pack['visible'] = ! empty( $_POST['altijara_search_sidebar_layout_visible'] );
			$opt_update['search_sidebar_layout'] = $search_pack;
		}

		if ( ! empty( $_POST['altijara_tag_sidebar_layout_submitted'] ) && function_exists( 'altijara_sanitize_layout' ) ) {
			$tag_sections = isset( $_POST['altijara_tag_sidebar_sections'] ) && is_array( $_POST['altijara_tag_sidebar_sections'] )
				? wp_unslash( $_POST['altijara_tag_sidebar_sections'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				: array();
			$tag_order = isset( $_POST['altijara_tag_sidebar_order'] ) && is_array( $_POST['altijara_tag_sidebar_order'] )
				? wp_unslash( $_POST['altijara_tag_sidebar_order'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				: array();
			$tag_pack = altijara_sanitize_layout( $tag_sections, $tag_order, array( 'lock_hero' => false ) );
			$tag_pack['visible'] = ! empty( $_POST['altijara_tag_sidebar_layout_visible'] );
			$opt_update['tag_sidebar_layout'] = $tag_pack;
		}

		if ( ! empty( $_POST['altijara_category_archive_layout_submitted'] ) ) {
			$opt_update['archive_include_children'] = ! empty( $_POST['altijara_archive_include_children'] );
			if ( ! empty( $_POST['altijara_category_archive_layout_reset'] ) ) {
				$opt_update['category_archive_layout'] = null;
			} elseif ( function_exists( 'altijara_sanitize_layout' ) ) {
				// Missing sections = all modules removed; persist empty pack (do not snap back to preset).
				$sections = isset( $_POST['altijara_category_archive_sections'] ) && is_array( $_POST['altijara_category_archive_sections'] )
					? wp_unslash( $_POST['altijara_category_archive_sections'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					: array();
				$order    = isset( $_POST['altijara_category_archive_order'] ) && is_array( $_POST['altijara_category_archive_order'] )
					? wp_unslash( $_POST['altijara_category_archive_order'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					: array();
				$layout_pack = altijara_sanitize_layout( $sections, $order, array( 'lock_hero' => false ) );
				$cap_counts  = function_exists( 'altijara_archive_counts_from_layout_pack' )
					? altijara_archive_counts_from_layout_pack( $layout_pack )
					: ( function_exists( 'altijara_get_archive_counts' ) ? altijara_get_archive_counts() : array() );
				$opt_update['archive_counts']          = $cap_counts;
				$opt_update['category_archive_layout'] = function_exists( 'altijara_cap_archive_module_picks' )
					? altijara_cap_archive_module_picks( $layout_pack, $cap_counts )
					: $layout_pack;
			}
		}

		if ( $opt_update ) {
			altijara_update_options( $opt_update );
			if ( array_key_exists( 'issues_archive_layout', $opt_update ) && null === $opt_update['issues_archive_layout'] ) {
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Issues layout reset to theme default.', 'altijara' ) . '</p></div>';
			} else {
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Archive layouts saved.', 'altijara' ) . '</p></div>';
			}
		}
}
