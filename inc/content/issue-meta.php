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
			'description' => __( 'Visitors opening this issue are sent here. Relative paths use the Magazine flip base from theme settings.', 'altijara' ),
		),
		'pdf'       => array(
			'meta_key'    => '_altijara_pdf_url',
			'label'       => __( 'PDF URL', 'altijara' ),
			'type'        => 'url',
			'placeholder' => 'https://…/issue.pdf',
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
		'tag'       => array(
			'meta_key'    => '_altijara_issue_tag',
			'label'       => __( 'Tag / topic', 'altijara' ),
			'type'        => 'text',
			'placeholder' => __( 'ملف التعليم والاستثمار', 'altijara' ),
		),
		'tag_color' => array(
			'meta_key'    => '_altijara_issue_tag_color',
			'label'       => __( 'Tag color', 'altijara' ),
			'type'        => 'text',
			'placeholder' => '#e11d48',
			'description' => __( 'CSS color or named class (e.g. blue, teal).', 'altijara' ),
		),
	);
}

/**
 * Normalize a flipbook path/URL (absolute, or flip_base + relative).
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
	$base = '';
	if ( function_exists( 'altijara_get_option' ) ) {
		$mag  = altijara_get_option( 'magazine', array() );
		$base = isset( $mag['flip_base'] ) ? (string) $mag['flip_base'] : '';
	}
	if ( ! $base ) {
		$base = 'https://online.fliphtml5.com/';
	}
	return esc_url_raw( trailingslashit( $base ) . ltrim( $raw, '/' ) );
}

/**
 * Resolve flipbook URL for an issue (absolute, or flip_base + relative).
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
		if ( ( '' === $value || null === $value ) && function_exists( 'altijara_get_issue_meta' ) ) {
			$value = altijara_get_issue_meta( $post->ID, $key );
		}
		$value = is_scalar( $value ) ? (string) $value : '';
		$id    = 'altijara_issue_' . $key;
		$type  = isset( $field['type'] ) ? $field['type'] : 'text';
		echo '<tr>';
		echo '<th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th>';
		echo '<td>';
		printf(
			'<input type="%1$s" class="%2$s" id="%3$s" name="altijara_issue[%4$s]" value="%5$s" placeholder="%6$s" %7$s>',
			esc_attr( $type ),
			'url' === $type ? 'large-text' : ( 'number' === $type ? 'small-text' : 'regular-text' ),
			esc_attr( $id ),
			esc_attr( $key ),
			esc_attr( $value ),
			esc_attr( $field['placeholder'] ?? '' ),
			'number' === $type ? 'min="0" step="1"' : ''
		);
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
		if ( 'url' === ( $field['type'] ?? '' ) ) {
			// Allow relative flip paths; sanitize absolute URLs.
			if ( $val && preg_match( '#^https?://#i', $val ) ) {
				$val = esc_url_raw( $val );
			} else {
				$val = sanitize_text_field( $val );
			}
		} elseif ( 'number' === ( $field['type'] ?? '' ) ) {
			$val = '' === $val ? '' : (string) absint( $val );
		} else {
			$val = sanitize_text_field( $val );
		}
		if ( '' === $val ) {
			delete_post_meta( $post_id, $field['meta_key'] );
		} else {
			update_post_meta( $post_id, $field['meta_key'], $val );
		}
	}
}
add_action( 'save_post_issue', 'altijara_save_issue_meta' );

/**
 * Redirect single issue views to the flipbook magazine link.
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
	$url = altijara_resolve_issue_flipbook_url( (int) get_queried_object_id() );
	if ( ! $url || ! preg_match( '#^https?://#i', $url ) ) {
		return;
	}
	// External FlipHTML5 hosts — wp_safe_redirect would block them.
	nocache_headers();
	wp_redirect( $url, 302 ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
	exit;
}
add_action( 'template_redirect', 'altijara_redirect_issue_to_flipbook', 5 );
