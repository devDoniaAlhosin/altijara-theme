<?php
/**
 * Shared PHP helpers
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme asset URL.
 *
 * @param string $path Path relative to theme root or assets/.
 */
function altijara_asset( $path ) {
	return trailingslashit( ALTIJARA_THEME_URI ) . ltrim( $path, '/' );
}

/**
 * Named theme URLs for templates + JS.
 *
 * @return array<string, string>
 */
function altijara_theme_urls() {
	return array(
		'home'      => home_url( '/' ),
		'about'     => home_url( '/about/' ),
		'contact'   => home_url( '/contact/' ),
		'advertise' => home_url( '/advertise/' ),
		'subscribe' => home_url( '/subscribe/' ),
		'login'     => home_url( '/login/' ),
		'issues'    => get_post_type_archive_link( 'issue' ) ?: home_url( '/issues/' ),
		'rest'      => esc_url_raw( rest_url( 'wp/v2' ) ),
		'theme'     => ALTIJARA_THEME_URI,
		'site'      => home_url( '/' ),
	);
}

/**
 * Arabic Gregorian short date: "16 سبتمبر 2026".
 *
 * @param int|string|null $date Post ID, timestamp, or date string.
 */
function altijara_format_date_ar( $date = null ) {
	$months = array(
		1  => 'يناير',
		2  => 'فبراير',
		3  => 'مارس',
		4  => 'أبريل',
		5  => 'مايو',
		6  => 'يونيو',
		7  => 'يوليو',
		8  => 'أغسطس',
		9  => 'سبتمبر',
		10 => 'أكتوبر',
		11 => 'نوفمبر',
		12 => 'ديسمبر',
	);

	if ( null === $date ) {
		$ts = current_time( 'timestamp' );
	} elseif ( is_numeric( $date ) && (int) $date > 200000000 ) {
		$ts = (int) $date;
	} elseif ( is_numeric( $date ) ) {
		$ts = (int) get_post_time( 'U', true, (int) $date );
	} else {
		$ts = strtotime( (string) $date );
	}

	if ( ! $ts ) {
		return '';
	}

	$day   = (int) wp_date( 'j', $ts );
	$month = (int) wp_date( 'n', $ts );
	$year  = (int) wp_date( 'Y', $ts );

	return sprintf( '%d %s %d', $day, $months[ $month ] ?? '', $year );
}

/**
 * Archive / category URL for a section key (slug-mapped, not hardcoded IDs).
 *
 * @param string $key Section key.
 */
function altijara_get_section_archive_url( $key ) {
	$cat_id = altijara_resolve_category_id( $key );
	if ( $cat_id ) {
		$link = get_category_link( $cat_id );
		if ( $link && ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return home_url( '/' );
}

/**
 * Trim text at word boundary.
 *
 * @param string $text    Source text.
 * @param int    $max_len Max length.
 */
function altijara_trim_at_word( $text, $max_len = 160 ) {
	$text = wp_strip_all_tags( (string) $text );
	if ( mb_strlen( $text ) <= $max_len ) {
		return $text;
	}
	$cut        = mb_substr( $text, 0, $max_len );
	$last_space = mb_strrpos( $cut, ' ' );
	if ( false !== $last_space && $last_space > 40 ) {
		$cut = mb_substr( $cut, 0, $last_space );
	}
	return rtrim( $cut ) . '...';
}

/**
 * Featured image URL with fallback.
 *
 * @param int         $post_id Post ID.
 * @param string|int[] $size    Image size.
 */
function altijara_get_thumb_url( $post_id, $size = 'large' ) {
	$url = get_the_post_thumbnail_url( $post_id, $size );
	if ( $url ) {
		return $url;
	}
	return altijara_asset( 'assets/images/issue-730-f2dfce.jpg' );
}

/**
 * Primary category label for a post.
 *
 * @param int $post_id Post ID.
 */
function altijara_get_primary_category_name( $post_id ) {
	$terms = get_the_category( $post_id );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	return $terms[0]->name;
}

/**
 * Render category/article sidebar ad slot.
 * Priority: shortcode → dedicated image → header billboard image → empty placeholder.
 */
function altijara_render_sidebar_ad() {
	$ad = altijara_get_option( 'advertising', array() );
	if ( ! is_array( $ad ) || empty( $ad['sidebar_enabled'] ) ) {
		return;
	}

	$shortcode = isset( $ad['sidebar_shortcode'] ) ? trim( (string) $ad['sidebar_shortcode'] ) : '';
	$image     = isset( $ad['sidebar_image'] ) ? (string) $ad['sidebar_image'] : '';
	$url       = isset( $ad['sidebar_url'] ) ? (string) $ad['sidebar_url'] : '';
	$fallback  = 'https://altijaramagazine.com/wp-content/uploads/angwp/items/31121/%D8%AE%D8%AF%D9%85%D8%A7%D8%AA%20%D9%85%D9%8F%D8%AA%D9%85%D9%8A%D8%B2%D8%A9_%20300x600%20copy.png';

	if ( ! $image || ! $url ) {
		$header = altijara_get_option( 'header', array() );
		if ( is_array( $header ) ) {
			if ( ! $image && ! empty( $header['ad_image'] ) ) {
				$image = (string) $header['ad_image'];
			}
			if ( ! $url && ! empty( $header['ad_url'] ) ) {
				$url = (string) $header['ad_url'];
			}
		}
	}
	if ( ! $image ) {
		$image = $fallback;
	}
	?>
	<div class="cat-sidebar-ad-widget" data-ad-slot="sidebar">
		<div class="cat-sidebar-ad-label">
			<span><?php esc_html_e( 'مساحة إعلانية', 'altijara' ); ?></span>
			<span class="cat-sidebar-ad-badge">Ad</span>
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
 * Normalize a post into a simple array for templates/JS bridges.
 *
 * @param WP_Post|int $post Post object or ID.
 * @return array<string, mixed>|null
 */
function altijara_normalize_post( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}

	$permalink = get_permalink( $post );

	return array(
		'id'       => (int) $post->ID,
		'title'    => get_the_title( $post ),
		'excerpt'  => altijara_trim_at_word( $post->post_excerpt ?: $post->post_content, 180 ),
		'url'      => $permalink,
		'link'     => $permalink,
		'img'      => altijara_get_thumb_url( $post->ID ),
		'dateText' => altijara_format_date_ar( $post->ID ),
		'timeText' => altijara_format_date_ar( $post->ID ),
		'category' => altijara_get_primary_category_name( $post->ID ),
		'slug'     => $post->post_name,
	);
}
