<?php
/**
 * Featured video for posts — URL or self-hosted file; featured image is the poster/fallback.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Meta: external or direct video URL. */
const ALTIJARA_FEATURED_VIDEO_URL = '_altijara_featured_video_url';

/** Meta: media library attachment ID (self-hosted). */
const ALTIJARA_FEATURED_VIDEO_ID = '_altijara_featured_video_id';

/** Legacy JNews single-post video URL meta (textarea id jnews_single_post-video). */
const ALTIJARA_JNEWS_VIDEO_META = 'jnews_single_post-video';

/** Option flag: one-time JNews → Altijara featured video copy completed. */
const ALTIJARA_JNEWS_VIDEO_MIGRATED = 'altijara_jnews_video_migrated';

/**
 * Extract a usable video URL from raw JNews meta (plain URL or iframe/HTML).
 *
 * @param string $raw Raw meta value.
 * @return string Sanitized URL or empty.
 */
function altijara_parse_video_url_from_raw( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return '';
	}

	// iframe / embed HTML — pull first src.
	if ( false !== stripos( $raw, '<iframe' ) || false !== stripos( $raw, '<embed' ) || false !== stripos( $raw, 'src=' ) ) {
		if ( preg_match( '/\bsrc=["\']([^"\']+)["\']/i', $raw, $m ) ) {
			$raw = html_entity_decode( $m[1], ENT_QUOTES );
		}
	}

	// Bare URL possibly wrapped in whitespace or shortcode leftovers.
	if ( preg_match( '~https?://[^\s<>"\']+~i', $raw, $m ) ) {
		$raw = $m[0];
	}

	$url = esc_url_raw( trim( $raw ) );
	return $url ? $url : '';
}

/**
 * Read JNews featured video URL for a post (runtime fallback / migrate source).
 *
 * @param int $post_id Post ID.
 * @return string Sanitized URL or empty.
 */
function altijara_get_jnews_video_url( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	$raw = get_post_meta( $post_id, ALTIJARA_JNEWS_VIDEO_META, true );
	return altijara_parse_video_url_from_raw( is_string( $raw ) ? $raw : '' );
}

/**
 * Resolve a media-library video attachment ID from a local uploads URL.
 *
 * @param string $url Video URL.
 * @return int Attachment ID or 0.
 */
function altijara_resolve_video_attachment_id( $url ) {
	$url = esc_url_raw( trim( (string) $url ) );
	if ( ! $url || ! function_exists( 'attachment_url_to_postid' ) ) {
		return 0;
	}
	$att_id = absint( attachment_url_to_postid( $url ) );
	if ( ! $att_id ) {
		// Retry without query string / size suffixes when WP cannot match.
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( $path && false !== strpos( $path, '/uploads/' ) ) {
			$home = home_url( $path );
			$att_id = absint( attachment_url_to_postid( $home ) );
		}
	}
	if ( ! $att_id ) {
		return 0;
	}
	$mime = get_post_mime_type( $att_id );
	if ( $mime && 0 !== strpos( (string) $mime, 'video/' ) ) {
		return 0;
	}
	return $att_id;
}

/**
 * One-time copy: jnews_single_post-video → Altijara featured video meta.
 *
 * Skips posts that already have Altijara video meta. Does not delete JNews meta.
 *
 * @param bool $force Re-run even if the migrated option flag is set.
 * @return array{migrated:int,skipped:int,flag:bool}
 */
function altijara_migrate_jnews_featured_videos( $force = false ) {
	$empty = array(
		'migrated' => 0,
		'skipped'  => 0,
		'flag'     => (bool) get_option( ALTIJARA_JNEWS_VIDEO_MIGRATED, false ),
	);
	if ( ! $force && $empty['flag'] ) {
		return $empty;
	}

	global $wpdb;
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_id, meta_value FROM {$wpdb->postmeta}
			WHERE meta_key = %s AND meta_value <> '' AND meta_value IS NOT NULL",
			ALTIJARA_JNEWS_VIDEO_META
		),
		ARRAY_A
	);

	$migrated = 0;
	$skipped  = 0;

	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			$post_id = absint( $row['post_id'] ?? 0 );
			if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
				++$skipped;
				continue;
			}
			$existing_url = trim( (string) get_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_URL, true ) );
			$existing_id  = absint( get_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_ID, true ) );
			if ( $existing_url || $existing_id ) {
				++$skipped;
				continue;
			}
			$url = altijara_parse_video_url_from_raw( (string) ( $row['meta_value'] ?? '' ) );
			if ( '' === $url ) {
				++$skipped;
				continue;
			}
			update_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_URL, $url );
			$att_id = altijara_resolve_video_attachment_id( $url );
			if ( $att_id ) {
				update_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_ID, $att_id );
			}
			++$migrated;
		}
	}

	update_option( ALTIJARA_JNEWS_VIDEO_MIGRATED, true, false );

	return array(
		'migrated' => $migrated,
		'skipped'  => $skipped,
		'flag'     => true,
	);
}

/**
 * Register post meta for REST / block editor visibility.
 */
function altijara_register_featured_video_meta() {
	$args = array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => static function () {
			return current_user_can( 'edit_posts' );
		},
		'sanitize_callback' => 'esc_url_raw',
	);
	register_post_meta( 'post', ALTIJARA_FEATURED_VIDEO_URL, $args );

	register_post_meta(
		'post',
		ALTIJARA_FEATURED_VIDEO_ID,
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
			'sanitize_callback' => 'absint',
		)
	);
}
add_action( 'init', 'altijara_register_featured_video_meta' );

/**
 * Whether the post has a usable featured video.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function altijara_has_featured_video( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return false;
	}
	$data = altijara_get_featured_video( $post_id );
	return ! empty( $data['src'] );
}

/**
 * Resolve featured video for a post.
 *
 * @param int $post_id Post ID.
 * @return array{src:string,type:string,provider:string,attachment_id:int,poster:string}
 *         type: file|embed|empty; provider: self|youtube|vimeo|other
 */
function altijara_get_featured_video( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$empty   = array(
		'src'           => '',
		'type'          => 'empty',
		'provider'      => '',
		'attachment_id' => 0,
		'poster'        => '',
	);
	if ( ! $post_id ) {
		return $empty;
	}

	$att_id = absint( get_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_ID, true ) );
	$url    = trim( (string) get_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_URL, true ) );

	// Runtime fallback: JNews video meta when Altijara fields are empty (pre-migrate / unmigrated rows).
	if ( ! $att_id && '' === $url ) {
		$url = altijara_get_jnews_video_url( $post_id );
	}

	$poster = '';
	if ( has_post_thumbnail( $post_id ) ) {
		$poster = (string) ( get_the_post_thumbnail_url( $post_id, 'altijara-hero' ) ?: get_the_post_thumbnail_url( $post_id, 'full' ) );
	} else {
		// Prefer provider poster before theme logo; do not call get_thumb_url (it may call back here).
		$provider_thumb = altijara_get_video_provider_thumb_url( $post_id );
		if ( $provider_thumb ) {
			$poster = $provider_thumb;
		} elseif ( function_exists( 'altijara_fallback_thumb_url' ) ) {
			$poster = (string) altijara_fallback_thumb_url();
		}
	}

	if ( $att_id ) {
		$file = wp_get_attachment_url( $att_id );
		$mime = get_post_mime_type( $att_id );
		if ( $file && ( ! $mime || 0 === strpos( (string) $mime, 'video/' ) ) ) {
			return array(
				'src'           => $file,
				'type'          => 'file',
				'provider'      => 'self',
				'attachment_id' => $att_id,
				'poster'        => $poster,
			);
		}
	}

	if ( '' === $url ) {
		$empty['poster'] = $poster;
		return $empty;
	}

	$provider = altijara_detect_video_provider( $url );
	if ( in_array( $provider, array( 'youtube', 'vimeo' ), true ) ) {
		return array(
			'src'           => $url,
			'type'          => 'embed',
			'provider'      => $provider,
			'attachment_id' => 0,
			'poster'        => $poster,
		);
	}

	// Direct file URL (mp4/webm/ogg) or unknown URL treated as file/oEmbed.
	$path = wp_parse_url( $url, PHP_URL_PATH );
	$ext  = $path ? strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) : '';
	if ( in_array( $ext, array( 'mp4', 'webm', 'ogg', 'ogv', 'm4v' ), true ) ) {
		return array(
			'src'           => esc_url_raw( $url ),
			'type'          => 'file',
			'provider'      => 'self',
			'attachment_id' => 0,
			'poster'        => $poster,
		);
	}

	return array(
		'src'           => esc_url_raw( $url ),
		'type'          => 'embed',
		'provider'      => 'other',
		'attachment_id' => 0,
		'poster'        => $poster,
	);
}

/**
 * Detect common video hosts from a URL.
 *
 * @param string $url URL.
 * @return string youtube|vimeo|other
 */
function altijara_detect_video_provider( $url ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	$host = $host ? strtolower( (string) $host ) : '';
	if ( $host && ( false !== strpos( $host, 'youtube.com' ) || false !== strpos( $host, 'youtu.be' ) || false !== strpos( $host, 'youtube-nocookie.com' ) ) ) {
		return 'youtube';
	}
	if ( $host && false !== strpos( $host, 'vimeo.com' ) ) {
		return 'vimeo';
	}
	return 'other';
}

/**
 * Player URL for YouTube or Vimeo, with autoplay when the center button is used.
 *
 * @param string $url      Video URL.
 * @param bool   $autoplay Whether to start playback.
 * @return string
 */
function altijara_featured_video_player_src( $url, $autoplay = false ) {
	$provider = altijara_detect_video_provider( $url );
	if ( 'youtube' === $provider ) {
		$id = altijara_youtube_id_from_url( $url );
		if ( $id ) {
			$src = 'https://www.youtube.com/embed/' . rawurlencode( $id ) . '?rel=0&modestbranding=1';
			if ( $autoplay ) {
				$src .= '&autoplay=1';
			}
			return $src;
		}
	}
	if ( 'vimeo' === $provider ) {
		$id = altijara_vimeo_id_from_url( $url );
		if ( $id ) {
			$src = 'https://player.vimeo.com/video/' . rawurlencode( (string) $id );
			if ( $autoplay ) {
				$src .= '?autoplay=1';
			}
			return $src;
		}
	}
	return '';
}

/**
 * Centered play control for the featured video.
 */
function altijara_render_featured_play_button() {
	?>
	<button type="button" class="article-featured-play" aria-label="<?php esc_attr_e( 'تشغيل الفيديو', 'altijara' ); ?>">
		<svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
	</button>
	<?php
}

/**
 * Build embed HTML for YouTube / Vimeo / oEmbed.
 *
 * @param string $url    Video URL.
 * @param string $poster Poster image URL (unused for iframe; kept for API symmetry).
 * @return string
 */
function altijara_featured_video_embed_html( $url, $poster = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$url = esc_url_raw( $url );
	if ( ! $url ) {
		return '';
	}

	$provider = altijara_detect_video_provider( $url );
	if ( 'youtube' === $provider ) {
		$id = altijara_youtube_id_from_url( $url );
		if ( $id ) {
			$src = 'https://www.youtube.com/embed/' . rawurlencode( $id ) . '?rel=0&modestbranding=1';
			return sprintf(
				'<div class="article-featured-video-embed"><iframe src="%s" title="%s" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe></div>',
				esc_url( $src ),
				esc_attr__( 'Featured video', 'altijara' )
			);
		}
	}
	if ( 'vimeo' === $provider ) {
		$id = altijara_vimeo_id_from_url( $url );
		if ( $id ) {
			$src = 'https://player.vimeo.com/video/' . rawurlencode( (string) $id );
			return sprintf(
				'<div class="article-featured-video-embed"><iframe src="%s" title="%s" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe></div>',
				esc_url( $src ),
				esc_attr__( 'Featured video', 'altijara' )
			);
		}
	}

	if ( function_exists( 'wp_oembed_get' ) ) {
		$html = wp_oembed_get( $url, array( 'width' => 1200 ) );
		if ( $html ) {
			return '<div class="article-featured-video-embed">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oEmbed HTML
		}
	}

	return '';
}

/**
 * @param string $url YouTube URL.
 * @return string
 */
function altijara_youtube_id_from_url( $url ) {
	if ( preg_match( '~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{6,})~', $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * @param string $url Vimeo URL.
 * @return string
 */
function altijara_vimeo_id_from_url( $url ) {
	if ( preg_match( '~vimeo\.com/(?:video/)?(\d+)~', $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * Remote poster URL for YouTube/Vimeo featured video when no WP featured image is set.
 *
 * Reads featured video URL meta only (no recursion through get_thumb_url).
 *
 * @param int $post_id Post ID.
 * @return string Empty when not applicable.
 */
function altijara_get_video_provider_thumb_url( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	// Self-hosted library video has no remote provider thumb.
	if ( absint( get_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_ID, true ) ) ) {
		return '';
	}
	$url = trim( (string) get_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_URL, true ) );
	if ( '' === $url ) {
		$url = altijara_get_jnews_video_url( $post_id );
	}
	if ( '' === $url ) {
		return '';
	}
	$provider = altijara_detect_video_provider( $url );
	if ( 'youtube' === $provider ) {
		$id = altijara_youtube_id_from_url( $url );
		if ( $id ) {
			return 'https://i.ytimg.com/vi/' . rawurlencode( $id ) . '/hqdefault.jpg';
		}
	}
	if ( 'vimeo' === $provider ) {
		$id = altijara_vimeo_id_from_url( $url );
		if ( $id ) {
			return 'https://vumbnail.com/' . rawurlencode( (string) $id ) . '.jpg';
		}
	}
	return '';
}

/**
 * Render featured media: video (with poster fallback image) or featured image.
 *
 * @param int $post_id Post ID.
 */
function altijara_render_featured_media( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	$video  = altijara_get_featured_video( $post_id );
	$poster = $video['poster'];
	$title  = get_the_title( $post_id );

	if ( 'file' === $video['type'] && $video['src'] ) {
		echo '<div class="article-featured-video-wrap">';
		printf(
			'<video class="article-featured-video" playsinline webkit-playsinline preload="metadata" src="%s"%s></video>',
			esc_url( $video['src'] ),
			$title ? ' title="' . esc_attr( $title ) . '"' : ''
		);
		if ( $poster ) {
			printf(
				'<img class="article-featured-video-poster" src="%s" alt="%s">',
				esc_url( $poster ),
				esc_attr( $title )
			);
		}
		altijara_render_featured_play_button();
		echo '</div>';
		return;
	}

	if ( 'embed' === $video['type'] && $video['src'] ) {
		$player = altijara_featured_video_player_src( $video['src'], true );
		if ( ! $player ) {
			$html = altijara_featured_video_embed_html( $video['src'], $poster );
			if ( $html && preg_match( '/src=["\']([^"\']+)["\']/', $html, $match ) ) {
				$player = html_entity_decode( $match[1], ENT_QUOTES );
			}
		}
		if ( $player ) {
			echo '<div class="article-featured-video-embed" data-embed-src="' . esc_url( $player ) . '" data-embed-title="' . esc_attr( $title ? $title : __( 'Featured video', 'altijara' ) ) . '">';
			if ( $poster ) {
				printf(
					'<img class="article-featured-video-poster" src="%s" alt="%s">',
					esc_url( $poster ),
					esc_attr( $title )
				);
			}
			echo '<div class="article-featured-video-embed__frame"></div>';
			altijara_render_featured_play_button();
			echo '</div>';
			return;
		}
	}

	if ( $poster || has_post_thumbnail( $post_id ) ) {
		$src = $poster ? $poster : (string) ( get_the_post_thumbnail_url( $post_id, 'altijara-hero' ) ?: get_the_post_thumbnail_url( $post_id, 'full' ) );
		if ( $src ) {
			printf(
				'<img src="%s" alt="%s" class="article-featured-img">',
				esc_url( $src ),
				esc_attr( $title )
			);
		}
	}
}

/**
 * Play badge markup for card thumbnails.
 *
 * @param int $post_id Post ID.
 */
function altijara_render_video_play_badge( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id || ! altijara_has_featured_video( $post_id ) ) {
		return;
	}
	?>
	<span class="cat-video-play-badge" aria-hidden="true" title="<?php esc_attr_e( 'Video', 'altijara' ); ?>">
		<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" focusable="false"><path d="M8 5v14l11-7z"/></svg>
	</span>
	<?php
}

/**
 * Register side metabox (alongside Featured Image).
 */
function altijara_register_featured_video_meta_box() {
	add_meta_box(
		'altijara_featured_video',
		__( 'Featured Video', 'altijara' ),
		'altijara_render_featured_video_meta_box',
		'post',
		'side',
		'low'
	);
}
add_action( 'add_meta_boxes', 'altijara_register_featured_video_meta_box' );

/**
 * Metabox UI.
 *
 * @param WP_Post $post Post.
 */
function altijara_render_featured_video_meta_box( $post ) {
	wp_nonce_field( 'altijara_save_featured_video', 'altijara_featured_video_nonce' );
	$att_id = absint( get_post_meta( $post->ID, ALTIJARA_FEATURED_VIDEO_ID, true ) );
	$url    = (string) get_post_meta( $post->ID, ALTIJARA_FEATURED_VIDEO_URL, true );
	$jnews  = '';
	$from_jnews = false;
	if ( ! $att_id && '' === trim( $url ) ) {
		$jnews = altijara_get_jnews_video_url( $post->ID );
		if ( $jnews ) {
			$url        = $jnews;
			$from_jnews = true;
		}
	}
	$att_url = $att_id ? (string) wp_get_attachment_url( $att_id ) : '';
	$label   = $att_id ? get_the_title( $att_id ) : '';
	if ( ! $label && $att_url ) {
		$label = wp_basename( $att_url );
	}
	?>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'Optional. Featured Image is used as the video poster / fallback when no video plays.', 'altijara' ); ?>
	</p>
	<?php if ( $from_jnews ) : ?>
		<p class="description" style="color:#2271b1;">
			<?php esc_html_e( 'Inherited from JNews video meta. Save the post to store it as an Altijara featured video.', 'altijara' ); ?>
		</p>
	<?php endif; ?>

	<p>
		<strong><?php esc_html_e( 'Self-hosted video', 'altijara' ); ?></strong>
	</p>
	<div class="altijara-featured-video-field" data-media-type="video">
		<input type="hidden" name="altijara_featured_video_id" id="altijara_featured_video_id" value="<?php echo esc_attr( (string) $att_id ); ?>">
		<p class="altijara-featured-video-file-label" style="margin:0 0 8px;word-break:break-all;">
			<?php echo $label ? esc_html( $label ) : '<span class="description">' . esc_html__( 'No file selected', 'altijara' ) . '</span>'; ?>
		</p>
		<p style="display:flex;gap:6px;flex-wrap:wrap;margin:0 0 12px;">
			<button type="button" class="button altijara-featured-video-upload"><?php esc_html_e( 'Choose from library', 'altijara' ); ?></button>
			<button type="button" class="button altijara-featured-video-clear-file"><?php esc_html_e( 'Clear file', 'altijara' ); ?></button>
		</p>
	</div>

	<p>
		<strong><?php esc_html_e( 'Or video link', 'altijara' ); ?></strong>
	</p>
	<p>
		<label class="screen-reader-text" for="altijara_featured_video_url"><?php esc_html_e( 'Video URL', 'altijara' ); ?></label>
		<input type="url" class="widefat" name="altijara_featured_video_url" id="altijara_featured_video_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://youtube.com/… or https://…/video.mp4">
	</p>
	<p class="description">
		<?php esc_html_e( 'YouTube, Vimeo, or a direct .mp4 / .webm URL. Self-hosted file above takes priority when set.', 'altijara' ); ?>
	</p>
	<?php
	$resolved = altijara_get_featured_video( $post->ID );
	if ( ! empty( $resolved['src'] ) ) {
		echo '<p class="description"><strong>' . esc_html__( 'Active:', 'altijara' ) . '</strong> ';
		echo esc_html( $resolved['provider'] ? $resolved['provider'] . ' · ' : '' );
		echo esc_html( $resolved['type'] );
		echo '</p>';
	}
}

/**
 * Save featured video meta.
 *
 * @param int $post_id Post ID.
 */
function altijara_save_featured_video_meta( $post_id ) {
	if ( ! isset( $_POST['altijara_featured_video_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_featured_video_nonce'] ) ), 'altijara_save_featured_video' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'post' !== get_post_type( $post_id ) ) {
		return;
	}

	$att_id = isset( $_POST['altijara_featured_video_id'] ) ? absint( wp_unslash( $_POST['altijara_featured_video_id'] ) ) : 0;
	$url    = isset( $_POST['altijara_featured_video_url'] ) ? esc_url_raw( trim( (string) wp_unslash( $_POST['altijara_featured_video_url'] ) ) ) : '';

	if ( $att_id ) {
		$mime = get_post_mime_type( $att_id );
		if ( $mime && 0 !== strpos( (string) $mime, 'video/' ) ) {
			$att_id = 0;
		}
	}

	if ( $att_id ) {
		update_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_ID, $att_id );
	} else {
		delete_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_ID );
	}

	if ( $url ) {
		update_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_URL, $url );
	} else {
		delete_post_meta( $post_id, ALTIJARA_FEATURED_VIDEO_URL );
	}
}
add_action( 'save_post_post', 'altijara_save_featured_video_meta' );

/**
 * Admin script for video media picker.
 *
 * @param string $hook Hook.
 */
function altijara_admin_enqueue_featured_video( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'altijara-featured-video',
		altijara_asset( 'assets/js/admin/featured-video.js' ),
		array( 'jquery' ),
		defined( 'ALTIJARA_THEME_VERSION' ) ? ALTIJARA_THEME_VERSION : '1',
		true
	);
}
add_action( 'admin_enqueue_scripts', 'altijara_admin_enqueue_featured_video' );
