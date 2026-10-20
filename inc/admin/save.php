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

		if ( isset( $_POST['altijara_general'] ) && is_array( $_POST['altijara_general'] ) ) {
			$g = wp_unslash( $_POST['altijara_general'] ); // phpcs:ignore
			altijara_update_options(
				array(
					'general' => array(
						'site_tagline_ar' => sanitize_text_field( $g['site_tagline_ar'] ?? '' ),
					),
				)
			);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ الإعدادات العامة.', 'altijara' ) . '</p></div>';
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

		if ( isset( $_POST['altijara_article'] ) && is_array( $_POST['altijara_article'] ) ) {
			$a = wp_unslash( $_POST['altijara_article'] ); // phpcs:ignore
			altijara_update_options(
				array(
					'article' => array(
						'show_related'  => ! empty( $a['show_related'] ),
						'related_count' => isset( $a['related_count'] ) ? max( 1, min( 12, (int) $a['related_count'] ) ) : 2,
					),
				)
			);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ إعدادات المقال.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_magazine'] ) && is_array( $_POST['altijara_magazine'] ) ) {
			$m = wp_unslash( $_POST['altijara_magazine'] ); // phpcs:ignore
			altijara_update_options(
				array(
					'magazine' => array(
						'flip_base' => esc_url_raw( $m['flip_base'] ?? '' ),
					),
				)
			);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ إعدادات المجلة.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_advertising'] ) && is_array( $_POST['altijara_advertising'] ) ) {
			$ad = wp_unslash( $_POST['altijara_advertising'] ); // phpcs:ignore
			altijara_update_options(
				array(
					'advertising' => array(
						'sidebar_enabled'   => ! empty( $ad['sidebar_enabled'] ),
						'sidebar_shortcode' => isset( $ad['sidebar_shortcode'] ) ? sanitize_text_field( $ad['sidebar_shortcode'] ) : '',
						'sidebar_image'     => isset( $ad['sidebar_image'] ) ? esc_url_raw( $ad['sidebar_image'] ) : '',
						'sidebar_url'       => isset( $ad['sidebar_url'] ) ? esc_url_raw( $ad['sidebar_url'] ) : '',
					),
				)
			);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ إعدادات الإعلانات.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_sections'] ) && is_array( $_POST['altijara_sections'] ) ) {
			$order_raw = isset( $_POST['altijara_sections_order'] ) ? wp_unslash( $_POST['altijara_sections_order'] ) : array(); // phpcs:ignore
			if ( ! is_array( $order_raw ) ) {
				$order_raw = array();
			}
			$pack = altijara_sanitize_sections_input( wp_unslash( $_POST['altijara_sections'] ), $order_raw ); // phpcs:ignore
			$opts = get_option( ALTIJARA_OPTION_KEY, array() );
			if ( ! is_array( $opts ) ) {
				$opts = array();
			}
			$opts['sections']       = $pack['sections'];
			$opts['sections_order'] = $pack['order'];
			update_option( ALTIJARA_OPTION_KEY, $opts, false );
			if ( function_exists( 'altijara_cache_purge_all' ) ) {
				altijara_cache_purge_all();
			}
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Homepage sections saved.', 'altijara' ) . '</p></div>';
		}
		if ( isset( $_POST['altijara_reset_sections'] ) ) {
			$opts = get_option( ALTIJARA_OPTION_KEY, array() );
			if ( ! is_array( $opts ) ) {
				$opts = array();
			}
			$opts['sections']       = array();
			$opts['sections_order'] = array();
			update_option( ALTIJARA_OPTION_KEY, $opts, false );
			if ( function_exists( 'altijara_cache_purge_all' ) ) {
				altijara_cache_purge_all();
			}
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Homepage sections reset to defaults.', 'altijara' ) . '</p></div>';
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
		if ( isset( $_POST['altijara_archive_layouts'] ) && is_array( $_POST['altijara_archive_layouts'] ) && function_exists( 'altijara_sanitize_archive_layouts' ) ) {
			$layouts = altijara_sanitize_archive_layouts( wp_unslash( $_POST['altijara_archive_layouts'] ) ); // phpcs:ignore
			$full_hero = array();
			if ( isset( $_POST['altijara_archive_full_hero'] ) && is_array( $_POST['altijara_archive_full_hero'] ) && function_exists( 'altijara_sanitize_archive_full_hero' ) ) {
				$full_hero = altijara_sanitize_archive_full_hero( wp_unslash( $_POST['altijara_archive_full_hero'] ) ); // phpcs:ignore
			} elseif ( function_exists( 'altijara_sanitize_archive_full_hero' ) ) {
				// Form submitted without any checkbox checked → clear list.
				$full_hero = array();
			}
			altijara_update_options(
				array(
					'archive_layouts'   => $layouts,
					'archive_full_hero' => $full_hero,
				)
			);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Archive layouts saved.', 'altijara' ) . '</p></div>';
		}
}
