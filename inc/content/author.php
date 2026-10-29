<?php
/**
 * Author profile helpers — article author box, verified badge, user fields.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default badge label for verified economic analysts.
 *
 * @return string
 */
function altijara_author_default_badge_label() {
	return __( 'محلل اقتصادي معتمد', 'altijara' );
}

/**
 * Whether the author shows the verified economic analyst badge.
 *
 * Missing meta defaults to on for users who can publish posts.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function altijara_author_is_verified( $user_id ) {
	$user_id = absint( $user_id );
	if ( ! $user_id ) {
		return false;
	}
	$raw = get_user_meta( $user_id, 'altijara_verified_analyst', true );
	if ( '' === $raw || null === $raw ) {
		$user = get_userdata( $user_id );
		return $user instanceof WP_User && user_can( $user, 'publish_posts' );
	}
	return (bool) (int) $raw;
}

/**
 * Badge / role label under the author name.
 *
 * @param int $user_id User ID.
 * @return string
 */
function altijara_author_badge_label( $user_id ) {
	$user_id = absint( $user_id );
	$custom  = $user_id ? (string) get_user_meta( $user_id, 'altijara_author_title', true ) : '';
	$custom  = trim( $custom );
	if ( '' !== $custom ) {
		return $custom;
	}
	return altijara_author_default_badge_label();
}

/**
 * Whether the article author box should render.
 *
 * @return bool
 */
function altijara_show_article_author_box() {
	if ( ! function_exists( 'altijara_get_article_settings' ) ) {
		return true;
	}
	$settings = altijara_get_article_settings();
	return ! isset( $settings['show_author'] ) || ! empty( $settings['show_author'] );
}

/**
 * Markup for the verified analyst badge.
 *
 * @param int  $user_id User ID.
 * @param bool $echo    Echo or return.
 * @return string
 */
function altijara_render_author_badge( $user_id, $echo = true ) {
	$user_id = absint( $user_id );
	if ( ! $user_id || ! altijara_author_is_verified( $user_id ) ) {
		return '';
	}
	$label = altijara_author_badge_label( $user_id );
	$html  = sprintf(
		'<span class="author-verified-badge" title="%1$s"><span class="author-verified-badge__icon" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1.2 14.2-3.5-3.5 1.4-1.4 2.1 2.1 4.5-4.5 1.4 1.4-5.9 5.9z"/></svg></span><span class="author-verified-badge__text">%2$s</span></span>',
		esc_attr( $label ),
		esc_html( $label )
	);
	if ( $echo ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above
	}
	return $html;
}

/**
 * Author box on single articles (avatar, name → profile, badge, date).
 *
 * @param int $post_id Post ID.
 */
function altijara_render_article_author_box( $post_id = 0 ) {
	if ( ! altijara_show_article_author_box() ) {
		return;
	}
	$post_id = $post_id ? absint( $post_id ) : (int) get_the_ID();
	if ( ! $post_id ) {
		return;
	}
	$author_id = (int) get_post_field( 'post_author', $post_id );
	if ( ! $author_id ) {
		return;
	}
	$user = get_userdata( $author_id );
	if ( ! $user instanceof WP_User ) {
		return;
	}

	$name    = $user->display_name ? $user->display_name : $user->user_login;
	$url     = get_author_posts_url( $author_id );
	$date    = function_exists( 'altijara_format_date_ar' ) ? altijara_format_date_ar( $post_id ) : get_the_date( '', $post_id );
	$avatar  = get_avatar(
		$author_id,
		88,
		'',
		$name,
		array(
			'class'   => 'author-avatar',
			'loading' => 'lazy',
		)
	);
	?>
	<a class="author-block" href="<?php echo esc_url( $url ); ?>" rel="author">
		<span class="author-avatar-badge">
			<?php
			if ( $avatar ) {
				echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar is safe HTML
			} else {
				echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 ) );
			}
			?>
		</span>
		<span class="author-details">
			<span class="author-name-row">
				<span class="author-name"><?php echo esc_html( $name ); ?></span>
				<?php altijara_render_author_badge( $author_id ); ?>
			</span>
			<?php if ( $date ) : ?>
				<span class="article-date-read"><?php echo esc_html( $date ); ?></span>
			<?php endif; ?>
		</span>
	</a>
	<?php
}

/**
 * Author archives use the same page size as the news list.
 *
 * @param WP_Query $query Query.
 */
function altijara_pre_get_posts_author( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_author() ) {
		return;
	}
	$counts   = function_exists( 'altijara_get_archive_counts' )
		? altijara_get_archive_counts()
		: array( 'news_size' => 10 );
	$per_page = max( 1, (int) ( $counts['news_size'] ?? 10 ) );
	$query->set( 'posts_per_page', $per_page );
	$query->set( 'post_type', 'post' );
}
add_action( 'pre_get_posts', 'altijara_pre_get_posts_author' );

/**
 * Extra fields on the user profile screen.
 *
 * @param WP_User $user User.
 */
function altijara_author_profile_fields( $user ) {
	if ( ! $user instanceof WP_User ) {
		return;
	}
	$verified = altijara_author_is_verified( (int) $user->ID );
	$title    = (string) get_user_meta( (int) $user->ID, 'altijara_author_title', true );
	?>
	<h2><?php esc_html_e( 'Altijara author profile', 'altijara' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="altijara_verified_analyst"><?php esc_html_e( 'Verified analyst', 'altijara' ); ?></label></th>
			<td>
				<label>
					<input type="checkbox" name="altijara_verified_analyst" id="altijara_verified_analyst" value="1" <?php checked( $verified ); ?>>
					<?php esc_html_e( 'Show “verified economic analyst” badge on articles and the author page', 'altijara' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th><label for="altijara_author_title"><?php esc_html_e( 'Badge / role label', 'altijara' ); ?></label></th>
			<td>
				<input type="text" class="regular-text" name="altijara_author_title" id="altijara_author_title" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php echo esc_attr( altijara_author_default_badge_label() ); ?>">
				<p class="description"><?php esc_html_e( 'Optional. Leave empty to use the default verified label.', 'altijara' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'altijara_author_profile_fields' );
add_action( 'edit_user_profile', 'altijara_author_profile_fields' );

/**
 * Save author profile fields.
 *
 * @param int $user_id User ID.
 */
function altijara_author_profile_save( $user_id ) {
	$user_id = absint( $user_id );
	if ( ! $user_id || ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	update_user_meta( $user_id, 'altijara_verified_analyst', ! empty( $_POST['altijara_verified_analyst'] ) ? '1' : '0' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$title = isset( $_POST['altijara_author_title'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['altijara_author_title'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	update_user_meta( $user_id, 'altijara_author_title', $title );
}
add_action( 'personal_options_update', 'altijara_author_profile_save' );
add_action( 'edit_user_profile_update', 'altijara_author_profile_save' );
