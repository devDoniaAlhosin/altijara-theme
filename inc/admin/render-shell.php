<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One settings-nav button.
 *
 * @param string               $id     Tab id.
 * @param array<string, mixed> $tab    Tab config.
 * @param string               $active Active tab id.
 */
function altijara_render_settings_nav_button( $id, $tab, $active ) {
	$label = is_array( $tab ) ? ( $tab['label'] ?? $id ) : (string) $tab;
	$hint  = is_array( $tab ) ? ( $tab['hint'] ?? '' ) : '';
	$icon  = is_array( $tab ) && ! empty( $tab['icon'] ) ? $tab['icon'] : 'dashicons-admin-generic';
	$on    = ( $active === $id );
	?>
	<button type="button"
		class="altijara-nav-item <?php echo $on ? 'is-active' : ''; ?>"
		data-tab="<?php echo esc_attr( $id ); ?>"
		aria-controls="altijara-panel-<?php echo esc_attr( $id ); ?>"
		aria-selected="<?php echo $on ? 'true' : 'false'; ?>"
		title="<?php echo esc_attr( $hint ); ?>">
		<span class="dashicons <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
		<span class="altijara-nav-item__label"><?php echo esc_html( $label ); ?></span>
	</button>
	<?php
}

/**
 * Render settings page (catch fatals so admin shows the real error instead of blank 500).
 */
function altijara_render_settings_page( $forced_tab = '' ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	try {
		altijara_render_settings_page_content( $forced_tab );
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
 *
 * @param string $forced_tab Tab to open when the request does not name one.
 */
function altijara_render_settings_page_content( $forced_tab = '' ) {
	$tabs   = altijara_settings_tabs();
	$active = 'design';
	if ( isset( $_POST['altijara_active_tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$active = sanitize_key( wp_unslash( $_POST['altijara_active_tab'] ) );
	} elseif ( isset( $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active = sanitize_key( wp_unslash( $_GET['tab'] ) );
	} elseif ( is_string( $forced_tab ) && isset( $tabs[ $forced_tab ] ) ) {
		$active = $forced_tab;
	}
	if ( isset( $tabs[ $active ]['children'][0] ) ) {
		$active = sanitize_key( $tabs[ $active ]['children'][0] );
	}
	if ( ! isset( $tabs[ $active ] ) || ! empty( $tabs[ $active ]['children'] ) ) {
		$active = 'design';
	}
	$brand_logo = altijara_admin_brand_logo_url();

	altijara_handle_settings_save();

	$opts    = altijara_get_options();
	$defs    = altijara_default_options();
	$header  = is_array( $opts['header'] ?? null ) ? $opts['header'] : ( $defs['header'] ?? array() );
	$footer  = is_array( $opts['footer'] ?? null ) ? $opts['footer'] : ( $defs['footer'] ?? array() );
	$article = is_array( $opts['article'] ?? null ) ? $opts['article'] : ( $defs['article'] ?? array() );
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
	$map       = is_array( $opts['category_map'] ?? null ) ? $opts['category_map'] : ( $defs['category_map'] ?? array() );
	$base_url = admin_url( 'admin.php?page=altijara-theme' );
	$panel_ctx = array(
		'opts'        => $opts,
		'defs'        => $defs,
		'header'      => $header,
		'footer'      => $footer,
		'article'     => $article,
		'social'      => $social,
		'map'         => $map,
	);
	?>
	<div class="wrap altijara-admin-wrap" id="altijara-theme-dashboard" data-active-tab="<?php echo esc_attr( $active ); ?>" data-base-url="<?php echo esc_url( $base_url ); ?>">
		<header class="altijara-admin-hero">
			<div class="altijara-admin-hero__brand">
				<?php if ( $brand_logo ) : ?>
					<img class="altijara-admin-logo" src="<?php echo esc_url( $brand_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php else : ?>
					<span class="altijara-admin-logo-fallback"><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
				<div class="altijara-admin-hero__text">
					<h1><?php esc_html_e( 'Theme Options', 'altijara' ); ?></h1>
					<p><?php esc_html_e( 'Design, chrome, archives, and tools for the Altijara magazine theme.', 'altijara' ); ?></p>
				</div>
			</div>
			<div class="altijara-admin-hero__meta">
				<span class="altijara-admin-badge">v<?php echo esc_html( ALTIJARA_THEME_VERSION ); ?></span>
				<a class="altijara-admin-credit-link" href="https://o2nations.com/" target="_blank" rel="noopener">O2Nations</a>
			</div>
		</header>

		<div class="altijara-admin-shell">
			<aside class="altijara-admin-sidebar" aria-label="<?php esc_attr_e( 'Theme settings sections', 'altijara' ); ?>">
				<nav class="altijara-admin-nav">
					<?php foreach ( $tabs as $id => $tab ) :
						if ( ! empty( $tab['parent'] ) ) {
							continue;
						}
						if ( ! empty( $tab['children'] ) && is_array( $tab['children'] ) ) :
							$child_ids = array_values( array_filter( $tab['children'], static function ( $cid ) use ( $tabs ) {
								return isset( $tabs[ $cid ] );
							} ) );
							if ( ! $child_ids ) {
								continue;
							}
							$first   = $child_ids[0];
							$in_group = in_array( $active, $child_ids, true );
							$label   = is_array( $tab ) ? ( $tab['label'] ?? $id ) : (string) $tab;
							$hint    = is_array( $tab ) ? ( $tab['hint'] ?? '' ) : '';
							$icon    = is_array( $tab ) && ! empty( $tab['icon'] ) ? $tab['icon'] : 'dashicons-admin-generic';
							?>
							<div class="altijara-nav-group<?php echo $in_group ? ' is-current is-open' : ''; ?>" data-children="<?php echo esc_attr( implode( ',', $child_ids ) ); ?>">
								<button type="button"
									class="altijara-nav-item"
									data-tab="<?php echo esc_attr( $first ); ?>"
									data-nav-parent="1"
									title="<?php echo esc_attr( $hint ); ?>">
									<span class="dashicons <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
									<span class="altijara-nav-item__label"><?php echo esc_html( $label ); ?></span>
									<span class="dashicons dashicons-arrow-down-alt2 altijara-nav-caret" aria-hidden="true"></span>
								</button>
								<div class="altijara-nav-sub">
									<?php foreach ( $child_ids as $cid ) :
										altijara_render_settings_nav_button( $cid, $tabs[ $cid ], $active );
									endforeach; ?>
								</div>
							</div>
							<?php
							continue;
						endif;
						altijara_render_settings_nav_button( $id, $tab, $active );
					endforeach; ?>
				</nav>
			</aside>

			<div class="altijara-admin-main">
				<?php foreach ( $tabs as $id => $tab ) :
					if ( ! empty( $tab['children'] ) ) {
						continue;
					}
					$label = is_array( $tab ) ? ( $tab['label'] ?? $id ) : (string) $tab;
					$hint  = is_array( $tab ) ? ( $tab['hint'] ?? '' ) : '';
					$icon  = is_array( $tab ) && ! empty( $tab['icon'] ) ? $tab['icon'] : 'dashicons-admin-generic';
					$is_on = ( $active === $id );
					$parent_label = '';
					if ( ! empty( $tab['parent'] ) && isset( $tabs[ $tab['parent'] ]['label'] ) ) {
						$parent_label = (string) $tabs[ $tab['parent'] ]['label'];
					}
					?>
					<section
						id="altijara-panel-<?php echo esc_attr( $id ); ?>"
						class="altijara-panel <?php echo $is_on ? 'is-active' : ''; ?>"
						data-tab="<?php echo esc_attr( $id ); ?>"
						role="tabpanel"
						<?php echo $is_on ? '' : 'hidden'; ?>>
						<div class="altijara-panel__intro">
							<span class="dashicons <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
							<div>
								<?php if ( $parent_label ) : ?>
									<span class="altijara-panel__crumb"><?php echo esc_html( $parent_label ); ?></span>
								<?php endif; ?>
								<strong class="altijara-panel__title"><?php echo esc_html( $label ); ?></strong>
								<?php if ( $hint ) : ?>
									<p><?php echo esc_html( $hint ); ?></p>
								<?php endif; ?>
							</div>
						</div>
						<form method="post" class="altijara-admin-form" data-tab-form="<?php echo esc_attr( $id ); ?>">
							<?php wp_nonce_field( 'altijara_save_settings', 'altijara_settings_nonce' ); ?>
							<input type="hidden" name="altijara_active_tab" value="<?php echo esc_attr( $id ); ?>">
			<?php altijara_render_settings_panel( $id, $panel_ctx ); ?>
						</form>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}
