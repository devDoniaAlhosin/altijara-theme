<?php
/**
 * Sidebar advertising helpers.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * First Advertisement widget config from a sidebar layout pack.
 *
 * @param array<string, mixed>|null $pack Sidebar layout. Null loads the category lower sidebar pack.
 * @return array<string, mixed>
 */
function altijara_get_sidebar_ad_widget_config( $pack = null ) {
	if ( null === $pack && function_exists( 'altijara_get_category_sidebar_layout' ) ) {
		$pack = altijara_get_category_sidebar_layout( true );
	}
	if ( ! is_array( $pack ) || empty( $pack['sections'] ) || ! is_array( $pack['sections'] ) ) {
		return array();
	}
	foreach ( $pack['sections'] as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		if ( isset( $row['widget'] ) && 'cat_sidebar_ad' === $row['widget'] ) {
			return $row;
		}
	}
	return array();
}

/**
 * Resolve sidebar ad creative from a widget config, with legacy theme-option fallback.
 *
 * @param array<string, mixed>|null $config Widget section config (optional).
 * @return array{shortcode:string,image:string,url:string}|null Null when the slot should not render.
 */
function altijara_resolve_sidebar_ad_creative( $config = null ) {
	$configured = is_array( $config );
	$config     = $configured ? $config : array();
	$legacy     = altijara_get_option( 'advertising', array() );
	if ( ! is_array( $legacy ) ) {
		$legacy = array();
	}

	$shortcode = isset( $config['ad_shortcode'] ) ? trim( (string) $config['ad_shortcode'] ) : '';
	$image     = isset( $config['ad_image'] ) ? trim( (string) $config['ad_image'] ) : '';
	$url       = isset( $config['ad_url'] ) ? trim( (string) $config['ad_url'] ) : '';

	// Widget instance (saved Advertisement card) — honor its fields; do not overwrite with legacy defaults.
	$is_widget = $configured && (
		( isset( $config['widget'] ) && 'cat_sidebar_ad' === $config['widget'] )
		|| array_key_exists( 'ad_image', $config )
		|| array_key_exists( 'ad_url', $config )
		|| array_key_exists( 'ad_shortcode', $config )
	);

	if ( ! $is_widget ) {
		// Legacy Advertising option (kept for existing installs / unconfigured lower column).
		if ( '' === $shortcode && ! empty( $legacy['sidebar_shortcode'] ) ) {
			$shortcode = trim( (string) $legacy['sidebar_shortcode'] );
		}
		if ( '' === $image && ! empty( $legacy['sidebar_image'] ) ) {
			$image = (string) $legacy['sidebar_image'];
		}
		if ( '' === $url && ! empty( $legacy['sidebar_url'] ) ) {
			$url = (string) $legacy['sidebar_url'];
		}

		// When called without widget config, respect old master toggle.
		if ( array_key_exists( 'sidebar_enabled', $legacy ) && empty( $legacy['sidebar_enabled'] ) ) {
			return null;
		}
	}

	if ( ( '' === $image || '' === $url ) ) {
		$header = altijara_get_option( 'header', array() );
		if ( is_array( $header ) ) {
			if ( '' === $image && ! empty( $header['ad_image'] ) ) {
				$image = (string) $header['ad_image'];
			}
			if ( '' === $url && ! empty( $header['ad_url'] ) ) {
				$url = (string) $header['ad_url'];
			}
		}
	}

	/**
	 * Filter fallback sidebar ad image when no creative is configured.
	 *
	 * @param string               $fallback Fallback image URL.
	 * @param array<string, mixed> $config   Widget section config.
	 */
	$fallback = apply_filters(
		'altijara_sidebar_ad_fallback_image',
		'https://altijaramagazine.com/wp-content/uploads/angwp/items/31121/%D8%AE%D8%AF%D9%85%D8%A7%D8%AA%20%D9%85%D9%8F%D8%AA%D9%85%D9%8A%D8%B2%D8%A9_%20300x600%20copy.png',
		$config
	);
	if ( '' === $image && '' === $shortcode ) {
		$image = (string) $fallback;
	}

	$creative = array(
		'shortcode' => $shortcode,
		'image'     => $image,
		'url'       => $url,
	);

	/**
	 * Filter resolved sidebar ad creative (or null to hide the slot).
	 *
	 * @param array{shortcode:string,image:string,url:string}|null $creative Creative.
	 * @param array<string, mixed>                                 $config   Widget config.
	 */
	return apply_filters( 'altijara_sidebar_ad_creative', $creative, $config );
}

/**
 * Render category/article sidebar ad slot.
 * Priority: shortcode → dedicated image → header billboard image → empty placeholder.
 *
 * @param array<string, mixed>|null $config Optional widget section config.
 */
function altijara_render_sidebar_ad( $config = null ) {
	$ad = altijara_resolve_sidebar_ad_creative( $config );
	if ( null === $ad ) {
		return;
	}

	$shortcode = $ad['shortcode'];
	$image     = $ad['image'];
	$url       = $ad['url'];
	?>
	<div class="cat-sidebar-ad-widget" data-ad-slot="sidebar">
		<div class="cat-sidebar-ad-label">
			<span><?php esc_html_e( 'مساحة إعلانية', 'altijara' ); ?></span>
			<span class="cat-sidebar-ad-badge"><?php esc_html_e( 'إعلان', 'altijara' ); ?></span>
		</div>
		<?php if ( $shortcode ) : ?>
			<div class="cat-sidebar-ad-shortcode">
				<?php echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ad plugin shortcode HTML ?>
			</div>
		<?php elseif ( $image ) : ?>
			<?php if ( $url ) : ?>
				<a class="cat-sidebar-ad-link" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener sponsored">
					<img class="cat-sidebar-ad-img" src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy">
				</a>
			<?php else : ?>
				<span class="cat-sidebar-ad-link">
					<img class="cat-sidebar-ad-img" src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy">
				</span>
			<?php endif; ?>
		<?php else : ?>
			<div class="cat-sidebar-ad-placeholder" aria-hidden="true">
				<span><?php esc_html_e( 'مساحة إعلانية', 'altijara' ); ?></span>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Whether the header billboard shortcode is currently expanding.
 *
 * @return bool
 */
function altijara_is_header_billboard_render() {
	return ! empty( $GLOBALS['altijara_header_billboard_render'] );
}

/**
 * Force Advanced Ads to allow display while the header billboard expands.
 *
 * Explicit Theme Options placement should show on every front surface
 * (including the issue CPT archive at /issues/), even when the ad’s
 * display conditions would otherwise blank archives.
 *
 * @param bool $can Whether the ad may display.
 * @return bool
 */
function altijara_force_header_ad_can_display( $can ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( altijara_is_header_billboard_render() ) {
		return true;
	}
	return (bool) $can;
}

/**
 * Fallback post ID for AR Ad Manager AJAX (plugin returns no banners when post_id is empty).
 *
 * @return int
 */
function altijara_ar_ad_manager_fallback_post_id() {
	$front = (int) get_option( 'page_on_front' );
	if ( $front > 0 ) {
		return $front;
	}
	$page = (int) get_option( 'page_for_posts' );
	if ( $page > 0 ) {
		return $page;
	}
	return 1;
}

/**
 * Whether a URL path is the issues CPT archive.
 *
 * @param string $url Absolute or relative URL.
 * @return bool
 */
function altijara_url_is_issues_archive( $url ) {
	$path = wp_parse_url( (string) $url, PHP_URL_PATH );
	if ( ! is_string( $path ) || '' === $path ) {
		return false;
	}
	$path = untrailingslashit( $path );
	if ( '' === $path ) {
		return false;
	}
	// CPT has_archive => 'issues' (also tolerate bare /issue).
	return (bool) preg_match( '#/(issues|issue)$#', $path );
}

/**
 * Whether the current HTTP referer is the issues archive.
 *
 * @return bool
 */
function altijara_http_referer_is_issues_archive() {
	$ref = wp_get_referer();
	if ( ! $ref && ! empty( $_SERVER['HTTP_REFERER'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ref = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}
	return $ref ? altijara_url_is_issues_archive( $ref ) : false;
}

/**
 * Clear AR Ad Manager per-post / per-category banner gates (meta short-circuit).
 *
 * @param mixed  $value     Current filter value.
 * @param int    $object_id Object ID.
 * @param string $meta_key  Meta key.
 * @param bool   $single    Single flag.
 * @return mixed
 */
function altijara_ar_ad_manager_relax_banner_targeting_meta( $value, $object_id, $meta_key, $single ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( empty( $GLOBALS['altijara_ar_ad_relax_targeting'] ) ) {
		return $value;
	}
	$keys = array(
		'ar_ad_manager_extra_banner_posts',
		'ar_ad_manager_extra_banner_categories',
	);
	if ( ! in_array( (string) $meta_key, $keys, true ) ) {
		return $value;
	}
	return $single ? '' : array( '' );
}

/**
 * Before AR Ad Manager zone AJAX: ensure post_id and relax targeting on /issues/.
 */
function altijara_ar_ad_manager_ajax_bootstrap() {
	$pid = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $pid < 1 ) {
		$_GET['post_id'] = (string) altijara_ar_ad_manager_fallback_post_id(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	if ( altijara_http_referer_is_issues_archive() ) {
		$GLOBALS['altijara_ar_ad_relax_targeting'] = true;
		add_filter( 'get_post_metadata', 'altijara_ar_ad_manager_relax_banner_targeting_meta', 10, 4 );
	}
}
add_action( 'wp_ajax_ar_ad_manager_zone_data', 'altijara_ar_ad_manager_ajax_bootstrap', 0 );
add_action( 'wp_ajax_nopriv_ar_ad_manager_zone_data', 'altijara_ar_ad_manager_ajax_bootstrap', 0 );

/**
 * Fix AR Ad Manager localized post_id on archives (get_the_ID() is often 0).
 */
function altijara_ar_ad_manager_fix_localized_post_id() {
	if ( is_admin() ) {
		return;
	}

	$handle = 'ar-ad-manager-main-public-js';
	if ( ! wp_script_is( $handle, 'enqueued' ) && ! wp_script_is( $handle, 'registered' ) ) {
		return;
	}

	$current = (int) get_the_ID();
	$needs   = ( $current < 1 );
	$issues  = ( function_exists( 'altijara_is_issues_archive_view' ) && altijara_is_issues_archive_view() )
		|| is_post_type_archive( 'issue' );

	if ( ! $needs && ! $issues ) {
		return;
	}

	$fallback = (int) altijara_ar_ad_manager_fallback_post_id();
	$js       = sprintf(
		'(function(){if(!window.ar_wp_main_variables){return;}var p=parseInt(ar_wp_main_variables.post_id,10)||0;if(!p){ar_wp_main_variables.post_id=%1$d;}%2$s})();',
		$fallback,
		$issues ? 'ar_wp_main_variables.post_id=' . $fallback . ';' : ''
	);
	wp_add_inline_script( $handle, $js, 'after' );
}
add_action( 'wp_enqueue_scripts', 'altijara_ar_ad_manager_fix_localized_post_id', 100 );

/**
 * Whether this request is the magazine issues archive (/issues/).
 *
 * @return bool
 */
function altijara_is_issues_archive_context() {
	return ( function_exists( 'altijara_is_issues_archive_view' ) && altijara_is_issues_archive_view() )
		|| is_post_type_archive( 'issue' );
}

/**
 * On /issues/ only: strip Adning `_ning_hidden` so the header billboard shows.
 * Do not touch other pages — Adning uses that class for its rotator.
 *
 * @param string $html Shortcode HTML.
 * @return string
 */
function altijara_unhide_adning_header_markup( $html ) {
	$html = (string) $html;
	if ( '' === $html || false === strpos( $html, '_ning_hidden' ) ) {
		return $html;
	}
	if ( ! altijara_is_issues_archive_context() ) {
		return $html;
	}
	return (string) preg_replace( '/\s*_ning_hidden\b/', '', $html );
}

/**
 * Snapshot + spoof main query as the front page (Adning returns zone HTML there).
 *
 * Used only when /issues/ archive yields empty Adning output. Do NOT spoof
 * singular issue — that emptied the zone in earlier tests.
 *
 * @return array<string, mixed>|null
 */
function altijara_header_ad_begin_front_context() {
	global $wp_query, $post;

	$snap = array(
		'post'                 => isset( $post ) ? $post : null,
		'is_singular'          => isset( $wp_query->is_singular ) ? $wp_query->is_singular : null,
		'is_single'            => isset( $wp_query->is_single ) ? $wp_query->is_single : null,
		'is_page'              => isset( $wp_query->is_page ) ? $wp_query->is_page : null,
		'is_archive'           => isset( $wp_query->is_archive ) ? $wp_query->is_archive : null,
		'is_post_type_archive' => isset( $wp_query->is_post_type_archive ) ? $wp_query->is_post_type_archive : null,
		'is_home'              => isset( $wp_query->is_home ) ? $wp_query->is_home : null,
		'is_front_page'        => isset( $wp_query->is_front_page ) ? $wp_query->is_front_page : null,
		'queried_object'       => isset( $wp_query->queried_object ) ? $wp_query->queried_object : null,
		'queried_object_id'    => isset( $wp_query->queried_object_id ) ? $wp_query->queried_object_id : null,
	);

	$front_id = (int) get_option( 'page_on_front' );
	$front    = $front_id > 0 ? get_post( $front_id ) : null;
	if ( $front instanceof WP_Post ) {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- temporary Adning expand context.
		$post                        = $front;
		$wp_query->queried_object    = $front;
		$wp_query->queried_object_id = (int) $front->ID;
		$wp_query->is_page           = true;
		$wp_query->is_singular       = true;
		$wp_query->is_single         = false;
	} else {
		$wp_query->is_page     = false;
		$wp_query->is_singular = false;
		$wp_query->is_single   = false;
	}

	$wp_query->is_archive           = false;
	$wp_query->is_post_type_archive = false;
	$wp_query->is_home              = true;
	$wp_query->is_front_page        = true;

	return $snap;
}

/**
 * Restore main query after front-page spoof.
 *
 * @param array<string, mixed>|null $snap Snapshot.
 */
function altijara_header_ad_end_front_context( $snap ) {
	if ( ! is_array( $snap ) ) {
		return;
	}
	global $wp_query, $post;

	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restore after temporary spoof.
	$post = $snap['post'];
	foreach ( array( 'is_singular', 'is_single', 'is_page', 'is_archive', 'is_post_type_archive', 'is_home', 'is_front_page' ) as $flag ) {
		if ( array_key_exists( $flag, $snap ) && null !== $snap[ $flag ] ) {
			$wp_query->{$flag} = $snap[ $flag ];
		}
	}
	$wp_query->queried_object    = $snap['queried_object'];
	$wp_query->queried_object_id = $snap['queried_object_id'];
}

/**
 * Expand the Theme Options header billboard shortcode.
 *
 * On /issues/, Adning often returns empty zone HTML (archive ≠ “For issue”).
 * Retry under a temporary front-page query context, then strip `_ning_hidden`.
 *
 * @param string $shortcode Shortcode string.
 * @return string Expanded HTML (may be empty).
 */
function altijara_do_header_ad_shortcode( $shortcode ) {
	$shortcode = trim( (string) $shortcode );
	if ( '' === $shortcode ) {
		return '';
	}

	$GLOBALS['altijara_header_billboard_render'] = true;
	add_filter( 'advanced-ads-can-display', 'altijara_force_header_ad_can_display', 999 );
	add_filter( 'advanced-ads-can-display-ads', 'altijara_force_header_ad_can_display', 999 );

	$front_snap = null;

	try {
		$out = do_shortcode( $shortcode );
		$bad = ( ! is_string( $out ) || '' === trim( $out ) || trim( $out ) === $shortcode );

		// Real CPT archive: Adning blanks the zone. Home context still works.
		if ( $bad && altijara_is_issues_archive_context() ) {
			$front_snap = altijara_header_ad_begin_front_context();
			$out        = do_shortcode( $shortcode );
			altijara_header_ad_end_front_context( $front_snap );
			$front_snap = null;
		}

		if ( ! is_string( $out ) || '' === trim( $out ) || trim( $out ) === $shortcode ) {
			return '';
		}
		return altijara_unhide_adning_header_markup( $out );
	} finally {
		altijara_header_ad_end_front_context( $front_snap );
		remove_filter( 'advanced-ads-can-display', 'altijara_force_header_ad_can_display', 999 );
		remove_filter( 'advanced-ads-can-display-ads', 'altijara_force_header_ad_can_display', 999 );
		unset( $GLOBALS['altijara_header_billboard_render'] );
	}
}

/**
 * Admin-only Adning / header-ad debug dump.
 *
 * Visit any front URL as an admin with ?altijara_ad_debug=1
 * Example: https://example.com/issues/?altijara_ad_debug=1
 */
function altijara_debug_header_ad_dump() {
	if ( is_admin() || empty( $_GET['altijara_ad_debug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$header = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'header', array() ) : array();
	$sc     = is_array( $header ) && ! empty( $header['ad_shortcode'] ) ? trim( (string) $header['ad_shortcode'] ) : '';

	global $shortcode_tags;
	$registered = is_array( $shortcode_tags ) ? array_keys( $shortcode_tags ) : array();
	$adning_on  = in_array( 'adning', $registered, true );
	$angwp      = defined( 'ADNI_VERSION' )
		|| class_exists( 'ADNI_Main', false )
		|| class_exists( 'ADNI_Filters', false )
		|| class_exists( 'ADNI_Shortcodes', false );

	$raw_out = $sc ? do_shortcode( $sc ) : '';
	$via_hdr = ( $sc && function_exists( 'altijara_do_header_ad_shortcode' ) )
		? altijara_do_header_ad_shortcode( $sc )
		: $raw_out;

	$info = array(
		'url'                      => home_url( add_query_arg( array() ) ),
		'is_front_page'            => is_front_page(),
		'is_home'                  => is_home(),
		'is_singular'              => is_singular(),
		'is_singular_issue'        => is_singular( 'issue' ),
		'is_post_type_archive'     => is_post_type_archive(),
		'is_post_type_archive_issue' => is_post_type_archive( 'issue' ),
		'altijara_issues_archive'  => function_exists( 'altijara_is_issues_archive_view' ) ? altijara_is_issues_archive_view() : null,
		'queried_object'           => is_object( get_queried_object() ) ? wp_json_encode( get_queried_object(), JSON_PARTIAL_OUTPUT_ON_ERROR ) : get_queried_object(),
		'get_post_type'            => get_post_type(),
		'get_the_ID'               => get_the_ID(),
		'body_class'               => get_body_class(),
		'header_ad_shortcode'      => $sc,
		'adning_shortcode_registered' => $adning_on,
		'adning_related_shortcodes'   => array_values(
			array_filter(
				$registered,
				static function ( $tag ) {
					return false !== stripos( (string) $tag, 'adning' ) || false !== stripos( (string) $tag, 'angwp' );
				}
			)
		),
		'angwp_plugin_likely_active'  => (bool) $angwp,
		'do_shortcode_len'            => is_string( $raw_out ) ? strlen( $raw_out ) : 0,
		'do_shortcode_empty'          => ( ! is_string( $raw_out ) || '' === trim( $raw_out ) ),
		'do_shortcode_unchanged'      => ( is_string( $raw_out ) && trim( $raw_out ) === $sc ),
		'do_shortcode_preview'        => is_string( $raw_out ) ? substr( $raw_out, 0, 800 ) : gettype( $raw_out ),
		'header_helper_len'             => is_string( $via_hdr ) ? strlen( $via_hdr ) : 0,
		'header_helper_empty'           => ( ! is_string( $via_hdr ) || '' === trim( $via_hdr ) ),
		'header_helper_has_ning_hidden' => ( is_string( $via_hdr ) && false !== strpos( $via_hdr, '_ning_hidden' ) ),
		'header_helper_preview'         => is_string( $via_hdr ) ? substr( $via_hdr, 0, 800 ) : gettype( $via_hdr ),
		'has_adning_show_hide_filter'   => has_filter( 'adning_show_hide' ),
	);

	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo "Altijara header ad / Adning debug\n";
	echo "=================================\n\n";
	foreach ( $info as $key => $val ) {
		if ( is_bool( $val ) ) {
			$val = $val ? 'true' : 'false';
		} elseif ( is_array( $val ) || is_object( $val ) ) {
			$val = wp_json_encode( $val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		} elseif ( null === $val ) {
			$val = 'null';
		}
		echo $key . ":\n" . $val . "\n\n";
	}
	exit;
}
add_action( 'template_redirect', 'altijara_debug_header_ad_dump', 0 );
