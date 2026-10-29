<?php
/**
 * Issue CPT meta box + single → flipbook redirect.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical issue meta field map (logical key => storage key + UI).
 *
 * @return array<string, array<string, mixed>>
 */
function altijara_issue_meta_fields() {
	return array(
		'number'    => array(
			'meta_key'    => '_altijara_issue_number',
			'label'       => __( 'Issue number', 'altijara' ),
			'type'        => 'text',
			'placeholder' => '730',
		),
		'flipbook'  => array(
			'meta_key'    => '_altijara_flipbook_url',
			'label'       => __( 'Flipbook URL', 'altijara' ),
			'type'        => 'url',
			'placeholder' => 'https://online.fliphtml5.com/…/#p=1',
			'description' => __( 'Full FlipHTML5 (or other) URL for the digital issue. Prefer a complete https:// link.', 'altijara' ),
		),
		'pdf'       => array(
			'meta_key'    => '_altijara_pdf_url',
			'label'       => __( 'PDF file', 'altijara' ),
			'type'        => 'pdf',
			'placeholder' => 'https://…/issue.pdf',
			'description' => __( 'Upload or pick a PDF from the media library, or paste a URL. When set, the magazine hero shows a Download PDF button.', 'altijara' ),
		),
		'pages'     => array(
			'meta_key'    => '_altijara_pages',
			'label'       => __( 'Pages', 'altijara' ),
			'type'        => 'number',
			'placeholder' => '94',
		),
		'files'     => array(
			'meta_key'    => '_altijara_files',
			'label'       => __( 'Files count', 'altijara' ),
			'type'        => 'text',
			'placeholder' => '12',
		),
		'talks'     => array(
			'meta_key'    => '_altijara_interviews',
			'label'       => __( 'Interviews count', 'altijara' ),
			'type'        => 'text',
			'placeholder' => '5',
		),
		'access'    => array(
			'meta_key'    => '_altijara_access_level',
			'label'       => __( 'Access level', 'altijara' ),
			'type'        => 'select',
			'options'     => array(
				'free'        => __( 'Free (everyone)', 'altijara' ),
				'members'     => __( 'Members (any subscriber)', 'altijara' ),
				'subscribers' => __( 'Paid subscribers', 'altijara' ),
			),
			'description' => __( 'Controls who can open the flipbook / download the PDF.', 'altijara' ),
		),
	);
}

/**
 * Issue access level (free|members|subscribers).
 *
 * @param int $post_id Issue ID.
 * @return string
 */
function altijara_get_issue_access_level( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$level   = sanitize_key( (string) get_post_meta( $post_id, '_altijara_access_level', true ) );
	if ( ! in_array( $level, array( 'free', 'members', 'subscribers' ), true ) ) {
		return 'free';
	}
	return $level;
}

/**
 * Whether current user may view/download this issue.
 *
 * @param int $post_id Issue ID.
 * @return bool
 */
function altijara_user_can_access_issue( $post_id = 0 ) {
	$level = altijara_get_issue_access_level( $post_id );
	if ( function_exists( 'altijara_user_can_access_level' ) ) {
		return altijara_user_can_access_level( $level );
	}
	return 'free' === $level || is_user_logged_in();
}

/**
 * Normalize a flipbook path/URL (absolute preferred; relative paths use FlipHTML5 host).
 *
 * @param string $raw Raw meta value.
 * @return string Empty if none.
 */
function altijara_normalize_flipbook_url( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return '';
	}
	if ( preg_match( '#^https?://#i', $raw ) ) {
		return esc_url_raw( $raw );
	}
	return esc_url_raw( 'https://online.fliphtml5.com/' . ltrim( $raw, '/' ) );
}

/**
 * Resolve flipbook URL for an issue.
 *
 * @param int $post_id Issue ID.
 * @return string Empty if none.
 */
function altijara_resolve_issue_flipbook_url( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return '';
	}
	// Read raw meta (avoid recursion if get_issue_meta normalizes).
	$keys = array( '_altijara_flipbook_url', 'flipbook_url', 'flipbook' );
	$raw  = '';
	foreach ( $keys as $meta_key ) {
		$val = get_post_meta( $post_id, $meta_key, true );
		if ( '' !== $val && null !== $val && is_scalar( $val ) ) {
			$raw = (string) $val;
			break;
		}
	}
	return altijara_normalize_flipbook_url( $raw );
}

/**
 * Public front-end URL for an issue (never the WP singular permalink).
 *
 * Prefers Flipbook when set; otherwise the issues archive.
 *
 * @param int $post_id Issue ID.
 * @return string
 */
function altijara_get_issue_public_url( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		$archive = get_post_type_archive_link( 'issue' );
		return $archive ? $archive : home_url( '/issues/' );
	}
	$flipbook = altijara_resolve_issue_flipbook_url( $post_id );
	if ( $flipbook && preg_match( '#^https?://#i', $flipbook ) ) {
		return $flipbook;
	}
	$archive = get_post_type_archive_link( 'issue' );
	return $archive ? $archive : home_url( '/issues/' );
}

/**
 * Point issue permalinks at Flipbook or the archive (no public single pages).
 *
 * @param string  $post_link The post's permalink.
 * @param WP_Post $post      Post object.
 * @return string
 */
function altijara_issue_post_type_link( $post_link, $post ) {
	if ( ! ( $post instanceof WP_Post ) || 'issue' !== $post->post_type ) {
		return $post_link;
	}
	return altijara_get_issue_public_url( (int) $post->ID );
}
add_filter( 'post_type_link', 'altijara_issue_post_type_link', 10, 2 );

/**
 * Normalize a PDF meta value (URL, relative path, or attachment ID).
 *
 * @param string|int $raw Raw meta value.
 * @return string Absolute URL or empty.
 */
function altijara_normalize_pdf_url( $raw ) {
	$raw = is_scalar( $raw ) ? trim( (string) $raw ) : '';
	if ( '' === $raw ) {
		return '';
	}
	if ( ctype_digit( $raw ) ) {
		$url = wp_get_attachment_url( (int) $raw );
		return $url ? esc_url_raw( $url ) : '';
	}
	if ( preg_match( '#^https?://#i', $raw ) ) {
		return esc_url_raw( $raw );
	}
	// Relative uploads path → site URL.
	if ( 0 === strpos( $raw, '/' ) ) {
		return esc_url_raw( home_url( $raw ) );
	}
	$uploads = wp_get_upload_dir();
	if ( ! empty( $uploads['baseurl'] ) ) {
		return esc_url_raw( trailingslashit( $uploads['baseurl'] ) . ltrim( $raw, '/' ) );
	}
	return esc_url_raw( home_url( '/' . ltrim( $raw, '/' ) ) );
}

/**
 * Resolve PDF download URL for an issue (media library, URL, or import).
 *
 * @param int $post_id Issue ID.
 * @return string Empty if none.
 */
function altijara_resolve_issue_pdf_url( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return '';
	}
	$keys = array( '_altijara_pdf_url', '_altijara_pdf_id', 'pdf_url', 'pdf', 'pdfLink' );
	foreach ( $keys as $meta_key ) {
		$val = get_post_meta( $post_id, $meta_key, true );
		if ( '' === $val || null === $val ) {
			continue;
		}
		$url = altijara_normalize_pdf_url( $val );
		if ( $url ) {
			return $url;
		}
	}
	return '';
}

/**
 * Enqueue media picker assets on issue edit screens.
 *
 * @param string $hook Current admin hook.
 */
function altijara_admin_enqueue_issue_meta( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'issue' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'altijara-issue-meta',
		altijara_asset( 'assets/js/admin/issue-meta.js' ),
		array( 'jquery' ),
		defined( 'ALTIJARA_THEME_VERSION' ) ? ALTIJARA_THEME_VERSION : '1.0',
		true
	);
}
add_action( 'admin_enqueue_scripts', 'altijara_admin_enqueue_issue_meta' );

/**
 * Register issue meta box on the post edit screen.
 */
function altijara_register_issue_meta_box() {
	add_meta_box(
		'altijara_issue_details',
		__( 'Issue details (magazine)', 'altijara' ),
		'altijara_render_issue_meta_box',
		'issue',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'altijara_register_issue_meta_box' );

/**
 * Render issue meta box fields.
 *
 * @param WP_Post $post Post.
 */
function altijara_render_issue_meta_box( $post ) {
	wp_nonce_field( 'altijara_save_issue_meta', 'altijara_issue_meta_nonce' );
	$fields = altijara_issue_meta_fields();
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( $fields as $key => $field ) {
		$meta_key = $field['meta_key'];
		$value    = get_post_meta( $post->ID, $meta_key, true );
		if ( ( '' === $value || null === $value ) && 'pdf' === $key ) {
			$value = altijara_resolve_issue_pdf_url( $post->ID );
		} elseif ( ( '' === $value || null === $value ) && function_exists( 'altijara_get_issue_meta' ) ) {
			$value = altijara_get_issue_meta( $post->ID, $key );
		}
		$value = is_scalar( $value ) ? (string) $value : '';
		$id    = 'altijara_issue_' . $key;
		$type  = isset( $field['type'] ) ? $field['type'] : 'text';
		echo '<tr>';
		echo '<th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th>';
		echo '<td>';
		if ( 'pdf' === $type ) {
			$filename = $value ? wp_basename( preg_replace( '/\?.*$/', '', $value ) ) : '';
			echo '<div class="altijara-media-field altijara-pdf-field" data-media-type="application/pdf">';
			printf(
				'<input type="url" class="large-text altijara-media-url" id="%1$s" name="altijara_issue[%2$s]" value="%3$s" placeholder="%4$s">',
				esc_attr( $id ),
				esc_attr( $key ),
				esc_attr( $value ),
				esc_attr( $field['placeholder'] ?? '' )
			);
			echo '<div class="altijara-media-buttons" style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">';
			echo '<button type="button" class="button altijara-media-upload altijara-pdf-upload">' . esc_html__( 'Choose PDF from library', 'altijara' ) . '</button>';
			echo '<button type="button" class="button altijara-media-clear">' . esc_html__( 'Clear', 'altijara' ) . '</button>';
			if ( $filename ) {
				echo '<span class="description altijara-pdf-filename" style="margin:0;">' . esc_html( $filename ) . '</span>';
			} else {
				echo '<span class="description altijara-pdf-filename" style="margin:0;"></span>';
			}
			echo '</div>';
			echo '</div>';
		} elseif ( 'select' === $type && ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
			if ( '' === $value ) {
				$value = 'free';
			}
			echo '<select id="' . esc_attr( $id ) . '" name="altijara_issue[' . esc_attr( $key ) . ']">';
			foreach ( $field['options'] as $opt_val => $opt_label ) {
				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $opt_val ),
					selected( $value, $opt_val, false ),
					esc_html( $opt_label )
				);
			}
			echo '</select>';
		} else {
			printf(
				'<input type="%1$s" class="%2$s" id="%3$s" name="altijara_issue[%4$s]" value="%5$s" placeholder="%6$s" %7$s>',
				esc_attr( in_array( $type, array( 'url', 'number', 'text' ), true ) ? $type : 'text' ),
				'url' === $type ? 'large-text' : ( 'number' === $type ? 'small-text' : 'regular-text' ),
				esc_attr( $id ),
				esc_attr( $key ),
				esc_attr( $value ),
				esc_attr( $field['placeholder'] ?? '' ),
				'number' === $type ? 'min="0" step="1"' : ''
			);
		}
		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	$resolved = altijara_resolve_issue_flipbook_url( $post->ID );
	if ( $resolved ) {
		echo '<p class="description"><strong>' . esc_html__( 'Resolved flipbook:', 'altijara' ) . '</strong> ';
		printf( '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></p>', esc_url( $resolved ), esc_html( $resolved ) );
	}
	$pdf_resolved = altijara_resolve_issue_pdf_url( $post->ID );
	if ( $pdf_resolved ) {
		echo '<p class="description"><strong>' . esc_html__( 'Resolved PDF:', 'altijara' ) . '</strong> ';
		printf( '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></p>', esc_url( $pdf_resolved ), esc_html( $pdf_resolved ) );
	}
}

/**
 * Save issue meta from the edit screen.
 *
 * @param int $post_id Post ID.
 */
function altijara_save_issue_meta( $post_id ) {
	if ( ! isset( $_POST['altijara_issue_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_issue_meta_nonce'] ) ), 'altijara_save_issue_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'issue' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( empty( $_POST['altijara_issue'] ) || ! is_array( $_POST['altijara_issue'] ) ) {
		return;
	}
	$raw    = wp_unslash( $_POST['altijara_issue'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$fields = altijara_issue_meta_fields();
	foreach ( $fields as $key => $field ) {
		if ( ! array_key_exists( $key, $raw ) ) {
			continue;
		}
		$val = is_string( $raw[ $key ] ) ? trim( $raw[ $key ] ) : '';
		$ftype = $field['type'] ?? '';
		if ( 'select' === $ftype && ! empty( $field['options'] ) ) {
			$val = sanitize_key( $val );
			if ( ! isset( $field['options'][ $val ] ) ) {
				$val = 'free';
			}
		} elseif ( 'pdf' === $ftype ) {
			$val = $val ? altijara_normalize_pdf_url( $val ) : '';
		} elseif ( 'url' === $ftype ) {
			// Allow relative flip paths; sanitize absolute URLs.
			if ( $val && preg_match( '#^https?://#i', $val ) ) {
				$val = esc_url_raw( $val );
			} else {
				$val = sanitize_text_field( $val );
			}
		} elseif ( 'number' === $ftype ) {
			$val = '' === $val ? '' : (string) absint( $val );
		} else {
			$val = sanitize_text_field( $val );
		}
		if ( '' === $val ) {
			delete_post_meta( $post_id, $field['meta_key'] );
			if ( 'pdf' === $key ) {
				delete_post_meta( $post_id, '_altijara_pdf_id' );
			}
		} else {
			update_post_meta( $post_id, $field['meta_key'], $val );
		}
	}
}
add_action( 'save_post_issue', 'altijara_save_issue_meta' );

/**
 * Redirect single issue views to Flipbook, or the issues archive when none is set.
 * Editors can still preview the WP single template via ?preview=true.
 */
function altijara_redirect_issue_to_flipbook() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}
	if ( ! is_singular( 'issue' ) ) {
		return;
	}
	// Let editors preview the WP single template.
	if ( is_preview() || isset( $_GET['preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$url = altijara_get_issue_public_url( (int) get_queried_object_id() );
	if ( ! $url ) {
		return;
	}
	nocache_headers();
	// External FlipHTML5 hosts — wp_safe_redirect would block them.
	if ( preg_match( '#^https?://#i', $url ) && false === strpos( $url, home_url() ) ) {
		wp_redirect( $url, 302 ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
	} else {
		wp_safe_redirect( $url, 302 );
	}
	exit;
}
add_action( 'template_redirect', 'altijara_redirect_issue_to_flipbook', 5 );
