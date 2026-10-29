<?php
/**
 * Widget Renderer — SSR load of registered templates.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a section should render on the front.
 *
 * @param string $section_key Section ID.
 */
function altijara_section_is_visible( $section_key ) {
	$cfg = altijara_get_section_config( $section_key );
	return ! empty( $cfg['visible'] );
}

function altijara_section_tag_is_empty( $section_key ) {
	$cfg     = altijara_get_section_config( $section_key );
	$tag_ids = function_exists( 'altijara_section_tag_ids' ) ? altijara_section_tag_ids( $cfg ) : array_filter( array( absint( $cfg['tag'] ?? 0 ) ) );
	if ( ! $tag_ids || empty( $cfg['visible'] ) ) {
		return false;
	}
	// tag__in is OR: the column only stays empty when every selected tag has no posts.
	foreach ( $tag_ids as $tag ) {
		$term = get_term( (int) $tag, 'post_tag' );
		if ( $term && ! is_wp_error( $term ) && (int) $term->count >= 1 ) {
			return false;
		}
	}
	return true;
}

/**
 * Whether the section's chosen category has no posts. The default (no category) does not.
 *
 * @param string $section_key Section ID.
 * @return bool
 */
function altijara_section_category_is_empty( $section_key ) {
	$cfg = altijara_get_section_config( $section_key );
	$raw = isset( $cfg['category'] ) ? trim( (string) $cfg['category'] ) : '';
	if ( '' === $raw || empty( $cfg['visible'] ) ) {
		return false;
	}
	$term_id = is_numeric( $raw ) ? (int) $raw : 0;
	if ( $term_id < 1 && function_exists( 'altijara_resolve_category_id' ) ) {
		$term_id = (int) altijara_resolve_category_id( $raw );
	}
	if ( $term_id < 1 ) {
		return false;
	}
	$term = get_term( $term_id, 'category' );
	if ( ! $term || is_wp_error( $term ) ) {
		return true;
	}
	return (int) $term->count < 1;
}

/**
 * Whether a visible section should take a layout slot.
 * Empty sections stay visible and show the shared empty message.
 *
 * @param string $section_key Section ID.
 * @return bool
 */
function altijara_section_occupies_layout( $section_key ) {
	return altijara_section_is_visible( $section_key );
}

/**
 * Theme-option defaults for empty Home widget copy.
 *
 * @return array{message:string,fallback:string}
 */
function altijara_empty_section_message_defaults() {
	$defs = function_exists( 'altijara_default_options' ) ? altijara_default_options() : array();
	$adv  = isset( $defs['advanced'] ) && is_array( $defs['advanced'] ) ? $defs['advanced'] : array();
	return array(
		'message'  => isset( $adv['empty_section_message'] ) ? (string) $adv['empty_section_message'] : '«{name}» — لا مقالات في هذا التصنيف بعد',
		'fallback' => isset( $adv['empty_section_fallback'] ) ? (string) $adv['empty_section_fallback'] : 'لا توجد مقالات حالياً',
	);
}

/**
 * Shared front empty copy used across Home widgets / hero columns.
 *
 * Uses Theme Options → Tools templates. Placeholders: {label} (section title), {name} (category/tag).
 *
 * @param string $section_key Optional section ID.
 * @return string
 */
function altijara_section_empty_message( $section_key = '' ) {
	$defaults = altijara_empty_section_message_defaults();
	$adv      = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'advanced', array() ) : array();
	$adv      = is_array( $adv ) ? $adv : array();
	$tpl      = isset( $adv['empty_section_message'] ) ? trim( (string) $adv['empty_section_message'] ) : '';
	$fallback = isset( $adv['empty_section_fallback'] ) ? trim( (string) $adv['empty_section_fallback'] ) : '';
	// Migrate sites still on the old {label} default without requiring a Tools re-save.
	if ( '«{label}» — لا مقالات في هذا التصنيف بعد' === $tpl ) {
		$tpl = $defaults['message'];
	}
	if ( '' === $tpl ) {
		$tpl = $defaults['message'];
	}
	if ( '' === $fallback ) {
		$fallback = $defaults['fallback'];
	}

	$section_key = sanitize_key( (string) $section_key );
	if ( '' === $section_key || ! function_exists( 'altijara_get_section_config' ) ) {
		return $fallback;
	}

	$cfg   = altijara_get_section_config( $section_key );
	$label = ( isset( $cfg['label'] ) && '' !== trim( (string) $cfg['label'] ) ) ? trim( (string) $cfg['label'] ) : $section_key;
	$name  = '';

	$tag_ids = function_exists( 'altijara_section_tag_ids' ) ? altijara_section_tag_ids( $cfg ) : array_filter( array( absint( $cfg['tag'] ?? 0 ) ) );
	if ( ! empty( $tag_ids ) ) {
		$names = array();
		foreach ( $tag_ids as $tid ) {
			$term    = get_term( (int) $tid, 'post_tag' );
			$names[] = ( $term && ! is_wp_error( $term ) ) ? (string) $term->name : (string) (int) $tid;
		}
		$name = implode( ', ', $names );
	} else {
		$raw = isset( $cfg['category'] ) ? trim( (string) $cfg['category'] ) : '';
		if ( '' !== $raw ) {
			$term_id = is_numeric( $raw ) ? (int) $raw : ( function_exists( 'altijara_resolve_category_id' ) ? (int) altijara_resolve_category_id( $raw ) : 0 );
			$term    = $term_id ? get_term( $term_id, 'category' ) : null;
			$name    = ( $term && ! is_wp_error( $term ) ) ? (string) $term->name : $raw;
		}
	}

	if ( '' === $name ) {
		$name = $label;
	}

	if ( '' === $label && '' === $name ) {
		return $fallback;
	}

	return str_replace(
		array( '{label}', '{name}' ),
		array( $label, $name ),
		$tpl
	);
}

/**
 * Print the shared empty-state block inside a Home widget shell.
 *
 * @param string $extra_class Optional extra class names.
 * @param string $section_key Optional section ID for a reason message.
 */
function altijara_render_section_empty( $extra_class = '', $section_key = '' ) {
	$class = trim( 'altijara-section-empty ' . (string) $extra_class );
	echo '<div class="' . esc_attr( $class ) . '" role="status">';
	echo '<p>' . esc_html( altijara_section_empty_message( $section_key ) ) . '</p>';
	echo '</div>';
}

/**
 * Editor notice when a chosen tag has no posts. Printed once per page.
 */
function altijara_echo_empty_tag_warnings() {
	if ( ! empty( $GLOBALS['altijara_empty_tag_warnings_printed'] ) ) {
		return;
	}
	$GLOBALS['altijara_empty_tag_warnings_printed'] = true;
	if ( ! is_user_logged_in() || ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	if ( ! function_exists( 'altijara_get_sections' ) ) {
		return;
	}
	$notes = array();
	foreach ( altijara_get_sections() as $id => $cfg ) {
		if ( altijara_section_tag_is_empty( $id ) ) {
			$tag_ids  = function_exists( 'altijara_section_tag_ids' ) ? altijara_section_tag_ids( $cfg ) : array_filter( array( absint( $cfg['tag'] ?? 0 ) ) );
			$names    = array();
			foreach ( $tag_ids as $tid ) {
				$term    = get_term( (int) $tid, 'post_tag' );
				$names[] = ( $term && ! is_wp_error( $term ) ) ? $term->name : (string) (int) $tid;
			}
			$tag_name = implode( ', ', $names );
			$title    = ( isset( $cfg['label'] ) && '' !== (string) $cfg['label'] ) ? (string) $cfg['label'] : (string) $id;
			$notes[]  = sprintf(
				/* translators: 1: section title, 2: tag name */
				__( '«%1$s» — الوسم «%2$s» لا يحتوي على مقالات.', 'altijara' ),
				$title,
				$tag_name
			);
		}
		if ( altijara_section_category_is_empty( $id ) ) {
			$raw      = isset( $cfg['category'] ) ? (string) $cfg['category'] : '';
			$term_id  = is_numeric( $raw ) ? (int) $raw : ( function_exists( 'altijara_resolve_category_id' ) ? (int) altijara_resolve_category_id( $raw ) : 0 );
			$term     = $term_id ? get_term( $term_id, 'category' ) : null;
			$cat_name = ( $term && ! is_wp_error( $term ) ) ? $term->name : $raw;
			$title    = ( isset( $cfg['label'] ) && '' !== (string) $cfg['label'] ) ? (string) $cfg['label'] : (string) $id;
			$notes[]  = sprintf(
				/* translators: 1: section title, 2: category name */
				__( '«%1$s» — التصنيف «%2$s» لا يحتوي على مقالات.', 'altijara' ),
				$title,
				$cat_name
			);
		}
	}
	if ( ! $notes ) {
		return;
	}
	echo '<div class="altijara-empty-tag-warnings" role="status">';
	foreach ( $notes as $note ) {
		echo '<p>' . esc_html( $note ) . '</p>';
	}
	echo '</div>';
}

/**
 * Render a homepage section instance via its widget type.
 *
 * @param string $section_key Section ID.
 */
function altijara_render_home_section( $section_key ) {
	altijara_render_widget_instance( $section_key );
}

/**
 * Render one stacked module with the standard section gap.
 *
 * Widgets that already draw their own bar or margin keep that spacing.
 * Anything else picks up the same top gap and divider as the homepage sections.
 *
 * @param string $section_key Section / instance ID.
 */
function altijara_render_spaced_widget( $section_key ) {
	if ( ! function_exists( 'altijara_render_widget_instance' ) ) {
		return;
	}
	ob_start();
	altijara_render_widget_instance( $section_key );
	$html = ob_get_clean();
	if ( ! is_string( $html ) || '' === trim( $html ) ) {
		return;
	}
	echo '<div class="altijara-block">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- widget template HTML
}

/**
 * @param string $section_key Section / instance ID.
 */
function altijara_render_widget_instance( $section_key ) {
	if ( ! altijara_section_is_visible( $section_key ) ) {
		return;
	}

	$cfg      = altijara_get_section_config( $section_key );
	$posts    = altijara_get_section_posts( $section_key );
	$is_empty = ( ! empty( $cfg['tag'] ) && altijara_section_tag_is_empty( $section_key ) ) || altijara_section_category_is_empty( $section_key );
	if ( $is_empty ) {
		$posts = array();
	}
	$path = altijara_widget_template_path( $cfg );

	get_template_part(
		$path,
		null,
		array(
			'section_key' => $section_key,
			'config'      => $cfg,
			'posts'       => $posts,
			'widget'      => ! empty( $cfg['widget'] ) ? $cfg['widget'] : altijara_layout_to_widget( $cfg['layout'] ?? '' ),
		)
	);
}
