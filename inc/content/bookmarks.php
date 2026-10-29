<?php
/**
 * Saved articles (bookmarks) — per-user reading list.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALTIJARA_SAVED_POSTS_META', 'altijara_saved_posts' );
define( 'ALTIJARA_SAVED_POSTS_MAX', 100 );

/**
 * Sanitize a list of post IDs for bookmarks.
 *
 * @param mixed $raw Raw value.
 * @return int[]
 */
function altijara_sanitize_saved_post_ids( $raw ) {
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$ids = array();
	foreach ( $raw as $id ) {
		$id = absint( $id );
		if ( $id && ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
	}
	return array_slice( $ids, 0, ALTIJARA_SAVED_POSTS_MAX );
}

/**
 * Saved post IDs for a user (newest first).
 *
 * @param int $user_id User ID (0 = current).
 * @return int[]
 */
function altijara_get_saved_post_ids( $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}
	$raw = get_user_meta( $user_id, ALTIJARA_SAVED_POSTS_META, true );
	return altijara_sanitize_saved_post_ids( $raw );
}

/**
 * Whether the user has saved a post.
 *
 * @param int $post_id Post ID.
 * @param int $user_id User ID (0 = current).
 * @return bool
 */
function altijara_user_has_saved_post( $post_id, $user_id = 0 ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return false;
	}
	return in_array( $post_id, altijara_get_saved_post_ids( $user_id ), true );
}

/**
 * Persist saved IDs for a user.
 *
 * @param int   $user_id User ID.
 * @param int[] $ids     Post IDs.
 */
function altijara_set_saved_post_ids( $user_id, array $ids ) {
	$user_id = absint( $user_id );
	if ( ! $user_id ) {
		return;
	}
	update_user_meta( $user_id, ALTIJARA_SAVED_POSTS_META, altijara_sanitize_saved_post_ids( $ids ) );
}

/**
 * Toggle save for a post. Returns new saved state.
 *
 * @param int $post_id Post ID.
 * @param int $user_id User ID (0 = current).
 * @return array{saved:bool,ids:int[]}|WP_Error
 */
function altijara_toggle_saved_post( $post_id, $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	$post_id = absint( $post_id );
	if ( ! $user_id ) {
		return new WP_Error( 'not_logged_in', __( 'يجب تسجيل الدخول لحفظ المقالات.', 'altijara' ) );
	}
	if ( ! $post_id || 'publish' !== get_post_status( $post_id ) || 'post' !== get_post_type( $post_id ) ) {
		return new WP_Error( 'invalid_post', __( 'المقال غير متاح.', 'altijara' ) );
	}

	$ids    = altijara_get_saved_post_ids( $user_id );
	$saved  = in_array( $post_id, $ids, true );
	if ( $saved ) {
		$ids = array_values( array_filter( $ids, static function ( $id ) use ( $post_id ) {
			return (int) $id !== $post_id;
		} ) );
		$saved = false;
	} else {
		array_unshift( $ids, $post_id );
		$ids   = altijara_sanitize_saved_post_ids( $ids );
		$saved = true;
	}
	altijara_set_saved_post_ids( $user_id, $ids );

	return array(
		'saved' => $saved,
		'ids'   => $ids,
	);
}

/**
 * Force-save a post (no toggle). Used after login return.
 *
 * @param int $post_id Post ID.
 * @param int $user_id User ID.
 * @return array{saved:bool,ids:int[]}|WP_Error
 */
function altijara_save_post_for_user( $post_id, $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	$post_id = absint( $post_id );
	if ( ! $user_id ) {
		return new WP_Error( 'not_logged_in', __( 'يجب تسجيل الدخول لحفظ المقالات.', 'altijara' ) );
	}
	if ( ! $post_id || 'publish' !== get_post_status( $post_id ) || 'post' !== get_post_type( $post_id ) ) {
		return new WP_Error( 'invalid_post', __( 'المقال غير متاح.', 'altijara' ) );
	}
	$ids = altijara_get_saved_post_ids( $user_id );
	if ( ! in_array( $post_id, $ids, true ) ) {
		array_unshift( $ids, $post_id );
		$ids = altijara_sanitize_saved_post_ids( $ids );
		altijara_set_saved_post_ids( $user_id, $ids );
	}
	return array(
		'saved' => true,
		'ids'   => $ids,
	);
}

/**
 * Login URL that returns to $return_url and optionally saves a post.
 *
 * @param string $return_url Absolute URL to return to.
 * @param int    $save_post  Optional post ID to save after login.
 * @return string
 */
function altijara_bookmarks_login_url( $return_url = '', $save_post = 0 ) {
	$login = function_exists( 'altijara_utility_page_url' )
		? altijara_utility_page_url( 'login', '/login/' )
		: home_url( '/login/' );

	$return_url = $return_url ? esc_url_raw( $return_url ) : '';
	if ( ! $return_url ) {
		$return_url = home_url( '/' );
	}
	$save_post = absint( $save_post );
	if ( $save_post ) {
		$return_url = add_query_arg( 'save_post', $save_post, $return_url );
	}

	return add_query_arg( 'redirect_to', $return_url, $login );
}

/**
 * Public URL for the saved-articles list.
 *
 * @return string
 */
function altijara_saved_articles_url() {
	if ( class_exists( 'WooCommerce', false ) && function_exists( 'wc_get_account_endpoint_url' ) ) {
		return wc_get_account_endpoint_url( 'saved' );
	}
	if ( function_exists( 'altijara_utility_page_url' ) ) {
		return altijara_utility_page_url( 'saved', '/saved/' );
	}
	return home_url( '/saved/' );
}

/**
 * Whether archive and article save buttons are shown.
 *
 * @return bool
 */
function altijara_save_buttons_enabled() {
	if ( ! function_exists( 'altijara_get_article_settings' ) ) {
		return true;
	}
	$settings = altijara_get_article_settings();
	return ! isset( $settings['show_save'] ) || ! empty( $settings['show_save'] );
}

function altijara_bookmarks_js_config() {
	$logged_in = is_user_logged_in();
	return array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'altijara_bookmarks' ),
		'loggedIn'  => $logged_in,
		'showSave'  => function_exists( 'altijara_save_buttons_enabled' ) ? altijara_save_buttons_enabled() : true,
		'savedIds'  => $logged_in ? altijara_get_saved_post_ids() : array(),
		'loginUrl'  => function_exists( 'altijara_utility_page_url' )
			? altijara_utility_page_url( 'login', '/login/' )
			: home_url( '/login/' ),
		'savedUrl'  => altijara_saved_articles_url(),
		'i18n'      => array(
			'save'         => __( 'حفظ المقال', 'altijara' ),
			'saveLabel'    => __( 'حفظ', 'altijara' ),
			'savedLabel'   => __( 'تم الحفظ', 'altijara' ),
			'savedSuccess'   => __( 'تم حفظ المقال في محفوظاتك.', 'altijara' ),
			'removedSuccess' => __( 'تم إزالة المقال من محفوظاتك.', 'altijara' ),
			'viewSaved'      => __( 'عرض المحفوظات', 'altijara' ),
			'needLogin'    => __( 'سجّل الدخول لحفظ المقالات في حسابك.', 'altijara' ),
			'loginCta'     => __( 'تسجيل الدخول', 'altijara' ),
			'error'        => __( 'تعذر حفظ المقال. حاول مرة أخرى.', 'altijara' ),
			'emptySaved'   => __( 'لا توجد مقالات محفوظة بعد. استخدم زر «حفظ» في قوائم الأخبار.', 'altijara' ),
		),
	);
}

/**
 * AJAX: toggle save (logged-in) or signal needLogin (guest).
 */
function altijara_ajax_toggle_save() {
	check_ajax_referer( 'altijara_bookmarks', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$force   = ! empty( $_POST['force_save'] );

	if ( ! is_user_logged_in() ) {
		$return = isset( $_POST['return_url'] ) ? esc_url_raw( wp_unslash( $_POST['return_url'] ) ) : '';
		if ( ! $return ) {
			$return = wp_get_referer() ? wp_get_referer() : home_url( '/' );
		}
		$return = wp_validate_redirect( $return, home_url( '/' ) );
		wp_send_json_success(
			array(
				'needLogin' => true,
				'loginUrl'  => altijara_bookmarks_login_url( $return, $post_id ),
				'message'   => __( 'سجّل الدخول لحفظ المقالات في حسابك.', 'altijara' ),
			)
		);
	}

	$result = $force
		? altijara_save_post_for_user( $post_id )
		: altijara_toggle_saved_post( $post_id );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error(
			array(
				'message' => $result->get_error_message(),
			),
			400
		);
	}

	wp_send_json_success(
		array(
			'saved' => ! empty( $result['saved'] ),
			'ids'   => isset( $result['ids'] ) ? array_map( 'absint', $result['ids'] ) : array(),
		)
	);
}
add_action( 'wp_ajax_altijara_toggle_save', 'altijara_ajax_toggle_save' );
add_action( 'wp_ajax_nopriv_altijara_toggle_save', 'altijara_ajax_toggle_save' );

/**
 * Markup attributes + classes for a save button (SSR hydrate).
 *
 * @param int $post_id Post ID.
 * @return array{class:string,label:string,title:string}
 */
function altijara_save_button_state( $post_id ) {
	$post_id = absint( $post_id );
	$saved   = $post_id && is_user_logged_in() && altijara_user_has_saved_post( $post_id );
	return array(
		'class' => 'btn-cat-save' . ( $saved ? ' saved' : '' ),
		'label' => $saved ? __( 'تم الحفظ', 'altijara' ) : __( 'حفظ', 'altijara' ),
		'title' => $saved ? __( 'إزالة من المحفوظات', 'altijara' ) : __( 'حفظ المقال', 'altijara' ),
	);
}

/**
 * Print a save button for a post.
 *
 * @param int                  $post_id Post ID.
 * @param array<string, mixed> $args    force=true keeps the button on the saved-articles page.
 */
function altijara_render_save_button( $post_id, $args = array() ) {
	if ( ! is_array( $args ) ) {
		$args = array();
	}
	if ( empty( $args['force'] ) && function_exists( 'altijara_save_buttons_enabled' ) && ! altijara_save_buttons_enabled() ) {
		return;
	}
	$post_id = absint( $post_id );
	$state   = altijara_save_button_state( $post_id );
	$style   = ( false !== strpos( $state['class'], 'saved' ) ) ? ' style="color:var(--color-brand, #1897B8)"' : '';
	?>
	<button type="button" class="<?php echo esc_attr( $state['class'] ); ?>" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>" onclick="typeof toggleSaveArticle==='function'&&toggleSaveArticle(this)" title="<?php echo esc_attr( $state['title'] ); ?>"<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
		<span><?php echo esc_html( $state['label'] ); ?></span>
	</button>
	<?php
}

/**
 * Render the saved-articles list (My Account endpoint + standalone page).
 */
function altijara_render_saved_articles_list() {
	if ( ! is_user_logged_in() ) {
		$login = function_exists( 'altijara_bookmarks_login_url' )
			? altijara_bookmarks_login_url( altijara_saved_articles_url() )
			: home_url( '/login/' );
		echo '<p class="altijara-saved-empty">' . esc_html__( 'سجّل الدخول لعرض المقالات المحفوظة.', 'altijara' ) . '</p>';
		echo '<p><a class="static-btn static-btn-primary" href="' . esc_url( $login ) . '">' . esc_html__( 'تسجيل الدخول', 'altijara' ) . '</a></p>';
		return;
	}

	$ids = altijara_get_saved_post_ids();
	if ( ! $ids ) {
		echo '<p class="altijara-saved-empty">' . esc_html__( 'لا توجد مقالات محفوظة بعد. استخدم زر «حفظ» في قوائم الأخبار.', 'altijara' ) . '</p>';
		return;
	}

	$fallback = function_exists( 'altijara_asset' ) ? altijara_asset( 'assets/images/thumb-logo-fallback.png' ) : '';
	echo '<div class="altijara-saved-grid" id="altijaraSavedGrid">';
	foreach ( $ids as $pid ) {
		$item = get_post( $pid );
		if ( ! $item instanceof WP_Post || 'publish' !== $item->post_status ) {
			continue;
		}
		$article_title = $item->post_title;
		$thumb = function_exists( 'altijara_get_thumb_url' ) ? ( altijara_get_thumb_url( $item->ID, 'medium' ) ?: $fallback ) : $fallback;
		$excerpt = function_exists( 'altijara_trim_at_word' )
			? altijara_trim_at_word( get_the_excerpt( $item ) ?: $item->post_content, 110 )
			: wp_trim_words( get_the_excerpt( $item ), 18 );
		$kicker = function_exists( 'altijara_get_listing_category_name' )
			? altijara_get_listing_category_name( $item->ID )
			: ( function_exists( 'altijara_get_primary_category_name' ) ? altijara_get_primary_category_name( $item->ID ) : '' );
		?>
		<article class="altijara-saved-card altijara-saved-item" data-post-id="<?php echo esc_attr( (string) $pid ); ?>">
			<a class="altijara-saved-card__media" href="<?php echo esc_url( get_permalink( $item ) ); ?>">
				<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $article_title ); ?>" loading="lazy">
			</a>
			<div class="altijara-saved-card__body">
				<?php if ( $kicker ) : ?>
					<span class="altijara-saved-card__kicker"><?php echo esc_html( $kicker ); ?></span>
				<?php endif; ?>
				<h3 class="altijara-saved-card__title">
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( $article_title ); ?></a>
				</h3>
				<p class="altijara-saved-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<div class="altijara-saved-card__meta">
					<span class="altijara-saved-card__date"><?php echo esc_html( function_exists( 'altijara_format_date_ar' ) ? altijara_format_date_ar( $item->ID ) : get_the_date( '', $item ) ); ?></span>
					<?php altijara_render_save_button( $item->ID, array( 'force' => true ) ); ?>
				</div>
			</div>
		</article>
		<?php
	}
	echo '</div>';
}

/**
 * Register WooCommerce My Account endpoint for saved articles.
 */
function altijara_bookmarks_register_wc_endpoint() {
	if ( ! class_exists( 'WooCommerce', false ) ) {
		return;
	}
	add_rewrite_endpoint( 'saved', EP_ROOT | EP_PAGES );
	add_filter( 'woocommerce_account_menu_items', 'altijara_bookmarks_wc_menu_item', 20 );
	add_action( 'woocommerce_account_saved_endpoint', 'altijara_render_saved_articles_list' );
}
add_action( 'init', 'altijara_bookmarks_register_wc_endpoint' );

/**
 * Insert «محفوظاتي» into Woo account menu.
 *
 * @param array<string, string> $items Menu items.
 * @return array<string, string>
 */
function altijara_bookmarks_wc_menu_item( $items ) {
	if ( isset( $items['saved'] ) ) {
		$items['saved'] = __( 'محفوظاتي', 'altijara' );
		return $items;
	}
	$out = array();
	foreach ( $items as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'dashboard' === $key ) {
			$out['saved'] = __( 'محفوظاتي', 'altijara' );
		}
	}
	if ( ! isset( $out['saved'] ) ) {
		$out['saved'] = __( 'محفوظاتي', 'altijara' );
	}
	return $out;
}

/**
 * Flush rewrite rules once after adding the saved endpoint.
 */
function altijara_bookmarks_maybe_flush_rewrites() {
	if ( get_option( 'altijara_saved_endpoint_flushed' ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'altijara_saved_endpoint_flushed', 1, false );
}
add_action( 'init', 'altijara_bookmarks_maybe_flush_rewrites', 99 );
