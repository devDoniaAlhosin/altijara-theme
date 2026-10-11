<?php
/**
 * Altijara Theme Settings — admin dashboard.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ALTIJARA_OPTION_KEY' ) ) {
	define( 'ALTIJARA_OPTION_KEY', 'altijara_theme_settings' );
}

/**
 * Get all settings (merged with defaults for read — does not write).
 *
 * @return array<string, mixed>
 */
function altijara_get_options() {
	$defaults = altijara_default_options();
	$saved    = get_option( ALTIJARA_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	// If a branch was corrupted to a non-array, drop it so defaults win.
	foreach ( array_keys( $defaults ) as $branch ) {
		if ( array_key_exists( $branch, $saved ) && is_array( $defaults[ $branch ] ) && ! is_array( $saved[ $branch ] ) ) {
			unset( $saved[ $branch ] );
		}
	}

	$merged = array_replace_recursive( $defaults, $saved );

	// Guarantee critical admin branches are arrays (prevents fatal on theme options).
	foreach ( array( 'general', 'design', 'header', 'footer', 'social', 'article', 'magazine', 'advertising', 'integrations', 'advanced', 'category_map', 'sections' ) as $branch ) {
		if ( ! isset( $merged[ $branch ] ) || ! is_array( $merged[ $branch ] ) ) {
			$merged[ $branch ] = isset( $defaults[ $branch ] ) && is_array( $defaults[ $branch ] ) ? $defaults[ $branch ] : array();
		}
	}
	if ( empty( $merged['social']['items'] ) || ! is_array( $merged['social']['items'] ) ) {
		$merged['social']['items'] = isset( $defaults['social']['items'] ) && is_array( $defaults['social']['items'] )
			? $defaults['social']['items']
			: array();
	}

	// category_map values must be strings (WP 6.7+ esc_attr(null) TypeError → HTTP 500).
	if ( is_array( $merged['category_map'] ) ) {
		$clean_map = array();
		foreach ( $merged['category_map'] as $k => $v ) {
			$clean_map[ sanitize_key( (string) $k ) ] = is_scalar( $v ) ? (string) $v : '';
		}
		$merged['category_map'] = $clean_map;
	}

	return $merged;
}

/**
 * @param string $key     Root key.
 * @param mixed  $default Default.
 * @return mixed
 */
function altijara_get_option( $key, $default = null ) {
	$opts = altijara_get_options();
	if ( array_key_exists( $key, $opts ) ) {
		return $opts[ $key ];
	}
	return $default;
}

/**
 * Merge-patch options (does not delete sibling keys).
 *
 * @param array<string, mixed> $patch Patch.
 */
function altijara_update_options( $patch ) {
	$saved = get_option( ALTIJARA_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$opts = array_replace_recursive( $saved, $patch );
	update_option( ALTIJARA_OPTION_KEY, $opts, false );
	if ( function_exists( 'altijara_cache_purge_all' ) ) {
		altijara_cache_purge_all();
	}
}

/**
 * Register admin menu.
 */
function altijara_register_admin_menu() {
	add_menu_page(
		__( 'إعدادات مجلة التجارة', 'altijara' ),
		__( 'Altijara Theme', 'altijara' ),
		'manage_options',
		'altijara-theme',
		'altijara_render_settings_page',
		'dashicons-admin-site-alt3',
		61
	);
}
add_action( 'admin_menu', 'altijara_register_admin_menu' );

/**
 * @return array<string, string>
 */
function altijara_settings_tabs() {
	return array(
		'general'      => __( 'إعدادات عامة', 'altijara' ),
		'design'       => __( 'المظهر (خطوط وألوان)', 'altijara' ),
		'header'       => __( 'منشئ الهيدر', 'altijara' ),
		'navigation'   => __( 'قوائم التنقّل', 'altijara' ),
		'homepage'     => __( 'أقسام الرئيسية', 'altijara' ),
		'footer'       => __( 'منشئ التذييل', 'altijara' ),
		'social'       => __( 'وسائل التواصل', 'altijara' ),
		'article'      => __( 'إعدادات المقال', 'altijara' ),
		'magazine'     => __( 'إعدادات المجلة', 'altijara' ),
		'advertising'  => __( 'الإعلانات', 'altijara' ),
		'integrations' => __( 'تكاملات ومتقدم', 'altijara' ),
	);
}

/**
 * Media field helper.
 *
 * @param string $name  Input name.
 * @param string $value URL value.
 * @param string $label Label.
 */
function altijara_admin_media_field( $name, $value, $label ) {
	?>
	<tr>
		<th><label><?php echo esc_html( $label ); ?></label></th>
		<td>
			<div class="altijara-media-field">
				<input type="url" class="regular-text altijara-media-url" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<button type="button" class="button altijara-media-upload"><?php esc_html_e( 'اختيار من المكتبة', 'altijara' ); ?></button>
				<button type="button" class="button altijara-media-clear"><?php esc_html_e( 'مسح', 'altijara' ); ?></button>
				<?php if ( $value ) : ?>
					<p><img src="<?php echo esc_url( $value ); ?>" alt="" style="max-height:48px;margin-top:8px;"></p>
				<?php endif; ?>
			</div>
		</td>
	</tr>
	<?php
}

/**
 * Sanitize header branch.
 *
 * @param array<string, mixed> $raw Raw.
 * @return array<string, mixed>
 */
function altijara_sanitize_header_input( $raw ) {
	$d = altijara_default_options()['header'];
	return array(
		'logo_url'              => isset( $raw['logo_url'] ) ? esc_url_raw( $raw['logo_url'] ) : $d['logo_url'],
		'logo_hover_url'        => isset( $raw['logo_hover_url'] ) ? esc_url_raw( $raw['logo_hover_url'] ) : $d['logo_hover_url'],
		'logo_width'            => isset( $raw['logo_width'] ) ? max( 0, (int) $raw['logo_width'] ) : 0,
		'logo_height'           => isset( $raw['logo_height'] ) ? max( 0, (int) $raw['logo_height'] ) : 0,
		'background'            => ( isset( $raw['background'] ) && is_string( $raw['background'] ) && '' !== $raw['background'] ) ? ( sanitize_hex_color( $raw['background'] ) ?: '' ) : '',
		'layout'                => isset( $raw['layout'] ) ? sanitize_key( $raw['layout'] ) : 'default',
		'sticky'                => ! empty( $raw['sticky'] ),
		'show_top_bar'          => ! empty( $raw['show_top_bar'] ),
		'top_bar_text'          => isset( $raw['top_bar_text'] ) ? sanitize_text_field( $raw['top_bar_text'] ) : '',
		'ad_image'              => isset( $raw['ad_image'] ) ? esc_url_raw( $raw['ad_image'] ) : '',
		'ad_url'                => isset( $raw['ad_url'] ) ? esc_url_raw( $raw['ad_url'] ) : '',
		'show_search'           => ! empty( $raw['show_search'] ),
		'show_login'            => ! empty( $raw['show_login'] ),
		'login_text'            => isset( $raw['login_text'] ) ? sanitize_text_field( $raw['login_text'] ) : $d['login_text'],
		'login_url'             => isset( $raw['login_url'] ) ? esc_url_raw( $raw['login_url'] ) : '',
		'show_subscribe'        => ! empty( $raw['show_subscribe'] ),
		'subscribe_text'        => isset( $raw['subscribe_text'] ) ? sanitize_text_field( $raw['subscribe_text'] ) : $d['subscribe_text'],
		'subscribe_url'         => isset( $raw['subscribe_url'] ) ? esc_url_raw( $raw['subscribe_url'] ) : '',
		'show_social'           => ! empty( $raw['show_social'] ),
		'show_date'             => ! empty( $raw['show_date'] ),
		'mobile_show_search'    => ! empty( $raw['mobile_show_search'] ),
		'mobile_show_subscribe' => ! empty( $raw['mobile_show_subscribe'] ),
	);
}

/**
 * @param array<string, mixed> $raw Raw.
 * @return array<string, mixed>
 */
function altijara_sanitize_footer_input( $raw ) {
	$d = altijara_default_options()['footer'];
	return array(
		'logo_url'         => isset( $raw['logo_url'] ) ? esc_url_raw( $raw['logo_url'] ) : $d['logo_url'],
		'description'      => isset( $raw['description'] ) ? sanitize_textarea_field( $raw['description'] ) : $d['description'],
		'sections_heading' => isset( $raw['sections_heading'] ) ? sanitize_text_field( $raw['sections_heading'] ) : $d['sections_heading'],
		'quick_heading'    => isset( $raw['quick_heading'] ) ? sanitize_text_field( $raw['quick_heading'] ) : $d['quick_heading'],
		'social_heading'   => isset( $raw['social_heading'] ) ? sanitize_text_field( $raw['social_heading'] ) : $d['social_heading'],
		'show_brand'       => ! empty( $raw['show_brand'] ),
		'show_sections'    => ! empty( $raw['show_sections'] ),
		'show_quick'       => ! empty( $raw['show_quick'] ),
		'show_social'      => ! empty( $raw['show_social'] ),
		'contact_address'  => isset( $raw['contact_address'] ) ? sanitize_textarea_field( $raw['contact_address'] ) : '',
		'contact_email'    => isset( $raw['contact_email'] ) ? sanitize_email( $raw['contact_email'] ) : '',
		'contact_phone'    => isset( $raw['contact_phone'] ) ? sanitize_text_field( $raw['contact_phone'] ) : '',
		'copyright'        => isset( $raw['copyright'] ) ? sanitize_text_field( $raw['copyright'] ) : $d['copyright'],
		'background'       => ( isset( $raw['background'] ) && is_string( $raw['background'] ) && '' !== $raw['background'] ) ? ( sanitize_hex_color( $raw['background'] ) ?: '' ) : '',
		'text_color'       => ( isset( $raw['text_color'] ) && is_string( $raw['text_color'] ) && '' !== $raw['text_color'] ) ? ( sanitize_hex_color( $raw['text_color'] ) ?: '' ) : '',
		'layout'           => isset( $raw['layout'] ) ? sanitize_key( $raw['layout'] ) : 'default',
	);
}

/**
 * @param array<string, mixed> $raw Raw.
 * @return array{items: array<int, array<string, mixed>>}
 */
function altijara_sanitize_social_input( $raw ) {
	$items_in = isset( $raw['items'] ) && is_array( $raw['items'] ) ? $raw['items'] : array();
	$items    = array();
	$order    = 10;
	foreach ( $items_in as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$url = isset( $row['url'] ) ? esc_url_raw( $row['url'] ) : '';
		$items[] = array(
			'platform' => isset( $row['platform'] ) ? sanitize_key( $row['platform'] ) : 'custom',
			'label'    => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '',
			'url'      => $url,
			'enabled'  => ! empty( $row['enabled'] ),
			'order'    => isset( $row['order'] ) ? (int) $row['order'] : $order,
		);
		$order += 10;
	}
	return array( 'items' => $items );
}

/**
 * Enqueue admin media + settings JS.
 *
 * @param string $hook Hook.
 */
function altijara_admin_enqueue_settings( $hook ) {
	if ( 'toplevel_page_altijara-theme' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'altijara-admin-settings',
		altijara_asset( 'assets/js/admin/theme-settings.js' ),
		array( 'jquery' ),
		ALTIJARA_THEME_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'altijara_admin_enqueue_settings' );

/**
 * Render settings page (catch fatals so admin shows the real error instead of blank 500).
 */
function altijara_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	try {
		altijara_render_settings_page_content();
	} catch ( Throwable $e ) {
		echo '<div class="wrap" dir="ltr">';
		echo '<h1>Altijara Theme Options — error</h1>';
		echo '<div class="notice notice-error"><p><strong>' . esc_html( $e->getMessage() ) . '</strong></p>';
		echo '<p><code>' . esc_html( $e->getFile() . ':' . $e->getLine() ) . '</code></p>';
		echo '<pre style="max-width:960px;overflow:auto;background:#fff;padding:12px;border:1px solid #ccd0d4;">' . esc_html( $e->getTraceAsString() ) . '</pre>';
		echo '<p>Re-upload the full <code>altijara-theme</code> folder, then purge LiteSpeed cache. Enable <code>WP_DEBUG_LOG</code> if this persists.</p>';
		echo '</div></div>';
	}
}

/**
 * Settings page body.
 */
function altijara_render_settings_page_content() {
	$tabs   = altijara_settings_tabs();
	$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $tabs[ $active ] ) ) {
		$active = 'general';
	}

	if ( isset( $_POST['altijara_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_settings_nonce'] ) ), 'altijara_save_settings' ) ) {
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
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ إعدادات الهيدر.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_footer'] ) && is_array( $_POST['altijara_footer'] ) ) {
			altijara_update_options( array( 'footer' => altijara_sanitize_footer_input( wp_unslash( $_POST['altijara_footer'] ) ) ) ); // phpcs:ignore
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ إعدادات التذييل.', 'altijara' ) . '</p></div>';
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
						'sidebar_enabled' => ! empty( $ad['sidebar_enabled'] ),
					),
				)
			);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ إعدادات الإعلانات.', 'altijara' ) . '</p></div>';
		}

		if ( isset( $_POST['altijara_sections'] ) && is_array( $_POST['altijara_sections'] ) ) {
			$sections = altijara_sanitize_sections_input( wp_unslash( $_POST['altijara_sections'] ) ); // phpcs:ignore
			altijara_update_options( array( 'sections' => $sections ) );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تم حفظ أقسام الرئيسية.', 'altijara' ) . '</p></div>';
		}
		if ( isset( $_POST['altijara_reset_sections'] ) ) {
			altijara_update_options( array( 'sections' => array() ) );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'تمت إعادة أقسام الرئيسية للقيم الافتراضية.', 'altijara' ) . '</p></div>';
		}
	}

	$opts    = altijara_get_options();
	$defs    = altijara_default_options();
	$header  = is_array( $opts['header'] ?? null ) ? $opts['header'] : ( $defs['header'] ?? array() );
	$footer  = is_array( $opts['footer'] ?? null ) ? $opts['footer'] : ( $defs['footer'] ?? array() );
	$general = is_array( $opts['general'] ?? null ) ? $opts['general'] : ( $defs['general'] ?? array() );
	$article = is_array( $opts['article'] ?? null ) ? $opts['article'] : ( $defs['article'] ?? array() );
	$magazine = is_array( $opts['magazine'] ?? null ) ? $opts['magazine'] : ( $defs['magazine'] ?? array() );
	$advertising = is_array( $opts['advertising'] ?? null ) ? $opts['advertising'] : ( $defs['advertising'] ?? array() );
	$social_items = array();
	if ( is_array( $opts['social'] ?? null ) && ! empty( $opts['social']['items'] ) && is_array( $opts['social']['items'] ) ) {
		foreach ( $opts['social']['items'] as $row ) {
			if ( is_array( $row ) ) {
				$social_items[] = $row;
			}
		}
	}
	if ( empty( $social_items ) ) {
		$social_items = $defs['social']['items'] ?? array();
	}
	$social = $social_items;
	$map    = is_array( $opts['category_map'] ?? null ) ? $opts['category_map'] : ( $defs['category_map'] ?? array() );
	?>
	<div class="wrap" dir="rtl">
		<h1><?php esc_html_e( 'Altijara Theme — إعدادات القالب', 'altijara' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'تطوير القالب بواسطة', 'altijara' ); ?>
			<a href="https://o2nations.com/" target="_blank" rel="noopener">O2Nations</a>
		</p>
		<nav class="nav-tab-wrapper" style="margin-top:12px;">
			<?php foreach ( $tabs as $id => $label ) : ?>
				<a class="nav-tab <?php echo $active === $id ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=altijara-theme&tab=' . $id ) ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<form method="post" style="margin-top:20px;max-width:960px;">
			<?php wp_nonce_field( 'altijara_save_settings', 'altijara_settings_nonce' ); ?>

			<?php if ( 'general' === $active ) : ?>
				<table class="form-table">
					<tr>
						<th><label for="site_tagline_ar"><?php esc_html_e( 'الشعار النصي', 'altijara' ); ?></label></th>
						<td><input class="regular-text" type="text" id="site_tagline_ar" name="altijara_general[site_tagline_ar]" value="<?php echo esc_attr( $general['site_tagline_ar'] ?? '' ); ?>"></td>
					</tr>
				</table>
				<h2><?php esc_html_e( 'خريطة التصنيفات', 'altijara' ); ?></h2>
				<table class="form-table">
					<?php foreach ( $map as $key => $value ) : ?>
						<tr>
							<th><label><?php echo esc_html( (string) $key ); ?></label></th>
							<td>
								<input class="regular-text" type="text" name="altijara_category_map[<?php echo esc_attr( (string) $key ); ?>]" value="<?php echo esc_attr( is_scalar( $value ) ? (string) $value : '' ); ?>">
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button( __( 'حفظ', 'altijara' ) ); ?>

			<?php elseif ( 'design' === $active ) : ?>
				<?php
				if ( ! function_exists( 'altijara_default_design' ) || ! function_exists( 'altijara_font_main_presets' ) ) {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'ملف المظهر (design.php) غير محمّل. أعد رفع مجلد القالب بالكامل.', 'altijara' ) . '</p></div>';
				} else {
				$design = is_array( $opts['design'] ?? null ) ? array_merge( altijara_default_design(), $opts['design'] ) : altijara_default_design();
				$main_presets  = altijara_font_main_presets();
				$serif_presets = altijara_font_serif_presets();
				?>
				<p><?php esc_html_e( 'الخطوط الافتراضية هي نفسها المستخدمة في التصميم: IBM Plex Sans Arabic و Playfair Display — محمّلة محلياً من مجلد القالب (بدون Google Fonts).', 'altijara' ); ?></p>
				<h2><?php esc_html_e( 'الخطوط', 'altijara' ); ?></h2>
				<table class="form-table">
					<tr>
						<th><label for="font_main"><?php esc_html_e( 'الخط الأساسي (النصوص)', 'altijara' ); ?></label></th>
						<td>
							<select name="altijara_design[font_main]" id="font_main">
								<?php foreach ( $main_presets as $key => $preset ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $design['font_main'], $key ); ?>><?php echo esc_html( $preset['label'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'المسار: assets/fonts/ibm-plex-sans-arabic/', 'altijara' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="font_main_custom"><?php esc_html_e( 'خط أساسي مخصص (CSS)', 'altijara' ); ?></label></th>
						<td>
							<input class="large-text" type="text" id="font_main_custom" name="altijara_design[font_main_custom]" value="<?php echo esc_attr( $design['font_main_custom'] ); ?>" placeholder="'IBM Plex Sans Arabic', Tahoma, sans-serif">
							<p class="description"><?php esc_html_e( 'يُستخدم فقط عند اختيار «مخصص».', 'altijara' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="font_serif"><?php esc_html_e( 'خط العناوين المزخرف', 'altijara' ); ?></label></th>
						<td>
							<select name="altijara_design[font_serif]" id="font_serif">
								<?php foreach ( $serif_presets as $key => $preset ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $design['font_serif'], $key ); ?>><?php echo esc_html( $preset['label'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'المسار: assets/fonts/playfair-display/', 'altijara' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="font_serif_custom"><?php esc_html_e( 'خط مزخرف مخصص (CSS)', 'altijara' ); ?></label></th>
						<td>
							<input class="large-text" type="text" id="font_serif_custom" name="altijara_design[font_serif_custom]" value="<?php echo esc_attr( $design['font_serif_custom'] ); ?>" placeholder="'Playfair Display', Georgia, serif">
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'الألوان', 'altijara' ); ?></h2>
				<table class="form-table">
					<?php
					$color_fields = array(
						'bg_salmon'       => __( 'خلفية الصفحة', 'altijara' ),
						'bg_salmon_light' => __( 'خلفية فاتحة ثانوية', 'altijara' ),
						'bg_card'         => __( 'خلفية البطاقات', 'altijara' ),
						'ink_black'       => __( 'لون النص الأساسي', 'altijara' ),
						'ink_secondary'   => __( 'لون النص الثانوي', 'altijara' ),
						'ink_muted'       => __( 'لون النص الخافت', 'altijara' ),
						'accent_brand'    => __( 'لون العلامة / التمييز', 'altijara' ),
						'accent_hover'    => __( 'لون التمييز عند المرور', 'altijara' ),
						'accent_amber'    => __( 'لون تنبيهي (amber)', 'altijara' ),
						'footer_bg'       => __( 'خلفية التذييل (اختياري)', 'altijara' ),
						'footer_text'     => __( 'نص التذييل (اختياري)', 'altijara' ),
					);
					foreach ( $color_fields as $ckey => $clabel ) :
						$val = $design[ $ckey ] ?? '';
						?>
						<tr>
							<th><label for="design-<?php echo esc_attr( $ckey ); ?>"><?php echo esc_html( $clabel ); ?></label></th>
							<td>
								<input type="text" class="altijara-color-text" id="design-<?php echo esc_attr( $ckey ); ?>" name="altijara_design[<?php echo esc_attr( $ckey ); ?>]" value="<?php echo esc_attr( $val ); ?>" placeholder="#1897B8" style="width:110px">
								<input type="color" class="altijara-color-picker" value="<?php echo esc_attr( $val && preg_match( '/^#[0-9A-Fa-f]{6}$/', $val ) ? $val : '#1897B8' ); ?>" data-target="design-<?php echo esc_attr( $ckey ); ?>" style="vertical-align:middle;margin-inline-start:8px;">
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button( __( 'حفظ المظهر', 'altijara' ) ); ?>
				<button type="submit" name="altijara_reset_group" value="design" class="button" onclick="return confirm('إعادة الخطوط والألوان للافتراضي؟');"><?php esc_html_e( 'إعادة للافتراضي', 'altijara' ); ?></button>
				<script>
				(function(){
					document.querySelectorAll('.altijara-color-picker').forEach(function(picker){
						picker.addEventListener('input', function(){
							var t = document.getElementById(picker.getAttribute('data-target'));
							if (t) t.value = picker.value;
						});
					});
					document.querySelectorAll('.altijara-color-text').forEach(function(input){
						input.addEventListener('change', function(){
							var picker = document.querySelector('.altijara-color-picker[data-target="'+input.id+'"]');
							if (picker && /^#[0-9A-Fa-f]{6}$/.test(input.value)) picker.value = input.value;
						});
					});
				})();
				</script>
				<?php } ?>

			<?php elseif ( 'header' === $active ) : ?>
				<table class="form-table">
					<?php
					altijara_admin_media_field( 'altijara_header[logo_url]', $header['logo_url'] ?? '', __( 'شعار الموقع', 'altijara' ) );
					altijara_admin_media_field( 'altijara_header[logo_hover_url]', $header['logo_hover_url'] ?? '', __( 'شعار بديل (hover)', 'altijara' ) );
					?>
					<tr>
						<th><?php esc_html_e( 'أبعاد الشعار (اختياري)', 'altijara' ); ?></th>
						<td>
							<label><?php esc_html_e( 'عرض', 'altijara' ); ?> <input type="number" min="0" name="altijara_header[logo_width]" value="<?php echo esc_attr( (string) (int) ( $header['logo_width'] ?? 0 ) ); ?>" style="width:80px"></label>
							<label style="margin-inline-start:12px;"><?php esc_html_e( 'ارتفاع', 'altijara' ); ?> <input type="number" min="0" name="altijara_header[logo_height]" value="<?php echo esc_attr( (string) (int) ( $header['logo_height'] ?? 0 ) ); ?>" style="width:80px"></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'خلفية الهيدر', 'altijara' ); ?></th>
						<td><input type="text" name="altijara_header[background]" value="<?php echo esc_attr( $header['background'] ?? '' ); ?>" placeholder="#ffffff" class="regular-text"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'نمط التخطيط', 'altijara' ); ?></th>
						<td>
							<select name="altijara_header[layout]">
								<option value="default" <?php selected( $header['layout'] ?? '', 'default' ); ?>><?php esc_html_e( 'الافتراضي', 'altijara' ); ?></option>
							</select>
						</td>
					</tr>
					<tr><th><?php esc_html_e( 'خيارات الظهور', 'altijara' ); ?></th>
						<td>
							<label><input type="checkbox" name="altijara_header[sticky]" value="1" <?php checked( ! empty( $header['sticky'] ) ); ?>> <?php esc_html_e( 'هيدر لاصق', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_header[show_top_bar]" value="1" <?php checked( ! empty( $header['show_top_bar'] ) ); ?>> <?php esc_html_e( 'الشريط العلوي', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_header[show_date]" value="1" <?php checked( ! empty( $header['show_date'] ) ); ?>> <?php esc_html_e( 'عرض التاريخ', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_header[show_social]" value="1" <?php checked( ! empty( $header['show_social'] ) ); ?>> <?php esc_html_e( 'أيقونات التواصل', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_header[show_search]" value="1" <?php checked( ! empty( $header['show_search'] ) ); ?>> <?php esc_html_e( 'زر البحث', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_header[show_login]" value="1" <?php checked( ! empty( $header['show_login'] ) ); ?>> <?php esc_html_e( 'زر تسجيل الدخول', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_header[show_subscribe]" value="1" <?php checked( ! empty( $header['show_subscribe'] ) ); ?>> <?php esc_html_e( 'زر الاشتراك', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_header[mobile_show_search]" value="1" <?php checked( ! empty( $header['mobile_show_search'] ) ); ?>> <?php esc_html_e( 'بحث على الجوال', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_header[mobile_show_subscribe]" value="1" <?php checked( ! empty( $header['mobile_show_subscribe'] ) ); ?>> <?php esc_html_e( 'اشتراك على الجوال', 'altijara' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'نص الشريط العلوي', 'altijara' ); ?></th>
						<td><input class="regular-text" type="text" name="altijara_header[top_bar_text]" value="<?php echo esc_attr( $header['top_bar_text'] ?? '' ); ?>"></td>
					</tr>
					<?php altijara_admin_media_field( 'altijara_header[ad_image]', $header['ad_image'] ?? '', __( 'إعلان الهيدر', 'altijara' ) ); ?>
					<tr>
						<th><?php esc_html_e( 'رابط الإعلان', 'altijara' ); ?></th>
						<td><input class="regular-text" type="url" name="altijara_header[ad_url]" value="<?php echo esc_attr( $header['ad_url'] ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'تسجيل الدخول', 'altijara' ); ?></th>
						<td>
							<input class="regular-text" type="text" name="altijara_header[login_text]" value="<?php echo esc_attr( $header['login_text'] ?? '' ); ?>" placeholder="تسجيل الدخول">
							<input class="regular-text" type="url" name="altijara_header[login_url]" value="<?php echo esc_attr( $header['login_url'] ?? '' ); ?>" placeholder="<?php echo esc_attr( home_url( '/login/' ) ); ?>">
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'الاشتراك', 'altijara' ); ?></th>
						<td>
							<input class="regular-text" type="text" name="altijara_header[subscribe_text]" value="<?php echo esc_attr( $header['subscribe_text'] ?? '' ); ?>">
							<input class="regular-text" type="url" name="altijara_header[subscribe_url]" value="<?php echo esc_attr( $header['subscribe_url'] ?? '' ); ?>" placeholder="<?php echo esc_attr( home_url( '/subscribe/' ) ); ?>">
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'حفظ الهيدر', 'altijara' ) ); ?>
				<button type="submit" name="altijara_reset_group" value="header" class="button" onclick="return confirm('إعادة إعدادات الهيدر للافتراضي؟');"><?php esc_html_e( 'إعادة للافتراضي', 'altijara' ); ?></button>

			<?php elseif ( 'navigation' === $active ) : ?>
				<p><?php esc_html_e( 'تُدار القوائم عبر محرر قوائم ووردبريس الأصلي. عند تفعيل القالب تُنشأ قوائم افتراضية من واجهة التصميم الحالية.', 'altijara' ); ?></p>
				<?php
				$locs = get_registered_nav_menus();
				$assigned = get_nav_menu_locations();
				?>
				<table class="widefat striped" style="max-width:720px;">
					<thead><tr><th><?php esc_html_e( 'الموقع', 'altijara' ); ?></th><th><?php esc_html_e( 'القائمة المعيّنة', 'altijara' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $locs as $loc => $label ) : ?>
						<tr>
							<td><?php echo esc_html( $label ); ?> <code><?php echo esc_html( $loc ); ?></code></td>
							<td>
								<?php
								if ( ! empty( $assigned[ $loc ] ) ) {
									$m = wp_get_nav_menu_object( $assigned[ $loc ] );
									echo $m ? esc_html( $m->name ) : '—';
								} else {
									echo '—';
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p style="margin-top:16px;">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'فتح محرر القوائم', 'altijara' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php?action=locations' ) ); ?>"><?php esc_html_e( 'إدارة مواقع القوائم', 'altijara' ); ?></a>
				</p>

			<?php elseif ( 'homepage' === $active ) : ?>
				<p><strong><?php esc_html_e( 'إدارة أقسام الرئيسية — نظام الودجات', 'altijara' ); ?></strong></p>
				<div class="altijara-sections-list">
					<?php foreach ( altijara_get_sections() as $id => $section ) : ?>
						<?php altijara_render_section_admin_card( $id, $section ); ?>
					<?php endforeach; ?>
				</div>
				<div class="altijara-section-actions" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
					<?php submit_button( __( 'حفظ أقسام الرئيسية', 'altijara' ), 'primary', 'submit', false ); ?>
					<button type="submit" name="altijara_purge_cache" class="button button-secondary" value="1"><?php esc_html_e( 'تفريغ كاش الأقسام', 'altijara' ); ?></button>
					<button type="submit" name="altijara_reset_sections" class="button" value="1" onclick="return confirm('إعادة كل الأقسام للافتراضي؟');"><?php esc_html_e( 'إعادة للافتراضي', 'altijara' ); ?></button>
				</div>

			<?php elseif ( 'footer' === $active ) : ?>
				<table class="form-table">
					<?php altijara_admin_media_field( 'altijara_footer[logo_url]', $footer['logo_url'] ?? '', __( 'شعار التذييل', 'altijara' ) ); ?>
					<tr>
						<th><?php esc_html_e( 'الوصف', 'altijara' ); ?></th>
						<td><textarea class="large-text" rows="3" name="altijara_footer[description]"><?php echo esc_textarea( $footer['description'] ?? '' ); ?></textarea></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'عناوين الأعمدة', 'altijara' ); ?></th>
						<td>
							<input class="regular-text" name="altijara_footer[sections_heading]" value="<?php echo esc_attr( $footer['sections_heading'] ?? '' ); ?>" placeholder="الأقسام">
							<input class="regular-text" name="altijara_footer[quick_heading]" value="<?php echo esc_attr( $footer['quick_heading'] ?? '' ); ?>" placeholder="روابط تهمك">
							<input class="regular-text" name="altijara_footer[social_heading]" value="<?php echo esc_attr( $footer['social_heading'] ?? '' ); ?>" placeholder="تابعونا على">
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'الظهور', 'altijara' ); ?></th>
						<td>
							<label><input type="checkbox" name="altijara_footer[show_brand]" value="1" <?php checked( ! empty( $footer['show_brand'] ) ); ?>> <?php esc_html_e( 'عمود العلامة', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_footer[show_sections]" value="1" <?php checked( ! empty( $footer['show_sections'] ) ); ?>> <?php esc_html_e( 'عمود الأقسام', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_footer[show_quick]" value="1" <?php checked( ! empty( $footer['show_quick'] ) ); ?>> <?php esc_html_e( 'روابط تهمك', 'altijara' ); ?></label><br>
							<label><input type="checkbox" name="altijara_footer[show_social]" value="1" <?php checked( ! empty( $footer['show_social'] ) ); ?>> <?php esc_html_e( 'التواصل', 'altijara' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'معلومات الاتصال', 'altijara' ); ?></th>
						<td>
							<textarea class="large-text" rows="2" name="altijara_footer[contact_address]" placeholder="<?php esc_attr_e( 'العنوان', 'altijara' ); ?>"><?php echo esc_textarea( $footer['contact_address'] ?? '' ); ?></textarea><br>
							<input class="regular-text" type="email" name="altijara_footer[contact_email]" value="<?php echo esc_attr( $footer['contact_email'] ?? '' ); ?>" placeholder="email@example.com">
							<input class="regular-text" type="text" name="altijara_footer[contact_phone]" value="<?php echo esc_attr( $footer['contact_phone'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'الهاتف', 'altijara' ); ?>">
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'حقوق النشر', 'altijara' ); ?></th>
						<td>
							<input class="large-text" type="text" name="altijara_footer[copyright]" value="<?php echo esc_attr( $footer['copyright'] ?? '' ); ?>">
							<p class="description"><?php esc_html_e( 'استخدم {year} لإدراج السنة الحالية.', 'altijara' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'ألوان', 'altijara' ); ?></th>
						<td>
							<label><?php esc_html_e( 'خلفية', 'altijara' ); ?> <input type="text" name="altijara_footer[background]" value="<?php echo esc_attr( $footer['background'] ?? '' ); ?>" placeholder="#1a1a1a"></label>
							<label style="margin-inline-start:12px;"><?php esc_html_e( 'نص', 'altijara' ); ?> <input type="text" name="altijara_footer[text_color]" value="<?php echo esc_attr( $footer['text_color'] ?? '' ); ?>" placeholder="#ffffff"></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'نمط التخطيط', 'altijara' ); ?></th>
						<td>
							<select name="altijara_footer[layout]">
								<option value="default" <?php selected( $footer['layout'] ?? '', 'default' ); ?>><?php esc_html_e( 'الافتراضي', 'altijara' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<p class="description"><?php esc_html_e( 'روابط أعمدة التذييل تُدار من قوائم ووردبريس (أقسام التذييل / روابط تهمك).', 'altijara' ); ?></p>
				<?php submit_button( __( 'حفظ التذييل', 'altijara' ) ); ?>
				<button type="submit" name="altijara_reset_group" value="footer" class="button" onclick="return confirm('إعادة التذييل للافتراضي؟');"><?php esc_html_e( 'إعادة للافتراضي', 'altijara' ); ?></button>

			<?php elseif ( 'social' === $active ) : ?>
				<p><?php esc_html_e( 'روابط موحّدة للهيدر والتذييل. عطّل المنصة لإخفائها.', 'altijara' ); ?></p>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'تفعيل', 'altijara' ); ?></th>
							<th><?php esc_html_e( 'المنصة', 'altijara' ); ?></th>
							<th><?php esc_html_e( 'التسمية', 'altijara' ); ?></th>
							<th><?php esc_html_e( 'الرابط', 'altijara' ); ?></th>
							<th><?php esc_html_e( 'الترتيب', 'altijara' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$platforms = altijara_social_platform_choices();
						foreach ( $social as $i => $item ) :
							?>
							<tr>
								<td><input type="checkbox" name="altijara_social[items][<?php echo (int) $i; ?>][enabled]" value="1" <?php checked( ! empty( $item['enabled'] ) ); ?>></td>
								<td>
									<select name="altijara_social[items][<?php echo (int) $i; ?>][platform]">
										<?php foreach ( $platforms as $pk => $pl ) : ?>
											<option value="<?php echo esc_attr( $pk ); ?>" <?php selected( $item['platform'] ?? '', $pk ); ?>><?php echo esc_html( $pl ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td><input type="text" name="altijara_social[items][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $item['label'] ?? '' ); ?>" class="regular-text"></td>
								<td><input type="url" name="altijara_social[items][<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( $item['url'] ?? '' ); ?>" class="regular-text"></td>
								<td><input type="number" name="altijara_social[items][<?php echo (int) $i; ?>][order]" value="<?php echo esc_attr( (string) (int) ( $item['order'] ?? 10 ) ); ?>" style="width:70px"></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button( __( 'حفظ روابط التواصل', 'altijara' ) ); ?>
				<button type="submit" name="altijara_reset_group" value="social" class="button" onclick="return confirm('إعادة روابط التواصل للافتراضي؟');"><?php esc_html_e( 'إعادة للافتراضي', 'altijara' ); ?></button>

			<?php elseif ( 'article' === $active ) : ?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'مقالات ذات صلة', 'altijara' ); ?></th>
						<td>
							<label><input type="checkbox" name="altijara_article[show_related]" value="1" <?php checked( ! empty( $article['show_related'] ) ); ?>> <?php esc_html_e( 'إظهار', 'altijara' ); ?></label>
							<label style="margin-inline-start:12px;"><?php esc_html_e( 'العدد', 'altijara' ); ?>
								<input type="number" min="1" max="12" name="altijara_article[related_count]" value="<?php echo esc_attr( (string) (int) ( $article['related_count'] ?? 2 ) ); ?>" style="width:70px">
							</label>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'حفظ', 'altijara' ) ); ?>

			<?php elseif ( 'magazine' === $active ) : ?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'أساس FlipHTML5', 'altijara' ); ?></th>
						<td><input class="regular-text" type="url" name="altijara_magazine[flip_base]" value="<?php echo esc_attr( $magazine['flip_base'] ?? '' ); ?>"></td>
					</tr>
				</table>
				<?php submit_button( __( 'حفظ', 'altijara' ) ); ?>

			<?php elseif ( 'advertising' === $active ) : ?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'إعلان جانبي', 'altijara' ); ?></th>
						<td><label><input type="checkbox" name="altijara_advertising[sidebar_enabled]" value="1" <?php checked( ! empty( $advertising['sidebar_enabled'] ) ); ?>> <?php esc_html_e( 'مفعّل', 'altijara' ); ?></label></td>
					</tr>
				</table>
				<p class="description"><?php esc_html_e( 'إعلان الهيدر يُضبط من تبويب منشئ الهيدر.', 'altijara' ); ?></p>
				<?php submit_button( __( 'حفظ', 'altijara' ) ); ?>

			<?php else : ?>
				<p>
					<button type="submit" name="altijara_purge_cache" class="button" value="1"><?php esc_html_e( 'تفريغ كل كاش القالب', 'altijara' ); ?></button>
					<button type="submit" name="altijara_rerun_setup" class="button button-secondary" value="1" onclick="return confirm('تشغيل إعداد التفعيل مجدداً؟ لن يكرر القوائم الموجودة.');">
						<?php esc_html_e( 'إعادة تشغيل إعداد التفعيل (آمن)', 'altijara' ); ?>
					</button>
				</p>
				<?php
				$setup_flag = defined( 'ALTIJARA_SETUP_FLAG' ) ? ALTIJARA_SETUP_FLAG : 'altijara_theme_setup_state';
				$state      = get_option( $setup_flag, array() );
				if ( ! empty( $state['last_report'] ) && is_array( $state['last_report'] ) ) :
					?>
					<h3><?php esc_html_e( 'آخر تقرير إعداد', 'altijara' ); ?></h3>
					<pre style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-width:800px;overflow:auto;direction:ltr;text-align:left;"><?php echo esc_html( wp_json_encode( $state['last_report'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); ?></pre>
				<?php endif; ?>
			<?php endif; ?>
		</form>
	</div>
	<?php
}
