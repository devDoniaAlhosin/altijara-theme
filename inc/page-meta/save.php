<?php
/**
 * Save page meta on page post save.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize and save page meta.
 *
 * @param int $post_id Post ID.
 */
function altijara_save_page_meta( $post_id ) {
	if ( ! isset( $_POST['altijara_page_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_page_meta_nonce'] ) ), 'altijara_save_page_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	if ( 'page' !== get_post_type( $post_id ) ) {
		return;
	}

	$schema = isset( $_POST['altijara_page_schema'] ) ? sanitize_key( wp_unslash( $_POST['altijara_page_schema'] ) ) : altijara_get_page_schema_key( $post_id );
	if ( ! $schema || ! isset( $_POST['altijara_page'] ) || ! is_array( $_POST['altijara_page'] ) ) {
		return;
	}

	$raw    = wp_unslash( $_POST['altijara_page'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$clean  = altijara_sanitize_page_meta( $schema, $raw );
	update_post_meta( $post_id, ALTIJARA_PAGE_META_KEY, $clean );
}
add_action( 'save_post_page', 'altijara_save_page_meta' );