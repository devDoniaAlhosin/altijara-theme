<?php
/**
 * Home 2 — page-meta driven homepage sections.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front page ID when a static page is set.
 *
 * @return int
 */
function altijara_get_static_front_page_id() {
	if ( 'page' !== get_option( 'show_on_front' ) ) {
		return 0;
	}
	return absint( get_option( 'page_on_front' ) );
}

/**
 * Whether the site front uses the Home 2 template.
 *
 * @return bool
 */
function altijara_front_uses_home2() {
	$pid = altijara_get_static_front_page_id();
	if ( ! $pid ) {
		return false;
	}
	return ( 'home-2.php' === (string) get_page_template_slug( $pid ) );
}

/**
 * Default section rows for Home 2 page meta (from theme section defaults).
 *
 * @return array<string, array<string, mixed>>
 */
function altijara_home_meta_default_sections() {
	if ( ! function_exists( 'altijara_default_sections' ) ) {
		return array();
	}
	$out = array();
	foreach ( altijara_default_sections() as $id => $cfg ) {
		$cat_key = isset( $cfg['category'] ) ? (string) $cfg['category'] : '';
		$cat_id  = 0;
		if ( $cat_key && function_exists( 'altijara_resolve_category_id' ) ) {
			$cat_id = (int) altijara_resolve_category_id( $cat_key );
		}
		$mode = 'category';
		if ( ! empty( $cfg['manual_posts'] ) && is_array( $cfg['manual_posts'] ) ) {
			$mode = 'specific';
		}
		$manual = '';
		if ( ! empty( $cfg['manual_posts'] ) && is_array( $cfg['manual_posts'] ) ) {
			$manual = implode( ', ', array_filter( array_map( 'absint', $cfg['manual_posts'] ) ) );
		}
		$orderby = isset( $cfg['orderby'] ) ? (string) $cfg['orderby'] : 'date';
		if ( ! in_array( $orderby, array( 'date', 'modified', 'title', 'rand', 'comment_count' ), true ) ) {
			$orderby = 'date';
		}
		$order = ( isset( $cfg['order'] ) && 'ASC' === strtoupper( (string) $cfg['order'] ) ) ? 'ASC' : 'DESC';
		$out[ $id ] = array(
			'visible'      => ! empty( $cfg['visible'] ),
			'label'        => isset( $cfg['label'] ) ? (string) $cfg['label'] : $id,
			'count'        => isset( $cfg['count'] ) ? max( 1, (int) $cfg['count'] ) : 4,
			'source_mode'  => $mode,
			'category_id'  => $cat_id ? (string) $cat_id : '',
			'manual_posts' => $manual,
			'orderby'      => $orderby,
			'order'        => $order,
		);
	}
	return $out;
}

/**
 * Sanitize Home 2 sections from POST.
 *
 * @param mixed $raw Raw sections array.
 * @return array<string, array<string, mixed>>
 */
function altijara_sanitize_home_meta_sections( $raw ) {
	$defaults = altijara_home_meta_default_sections();
	$out      = array();
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}
	$ids = array_keys( $defaults );
	foreach ( $ids as $id ) {
		$row = isset( $raw[ $id ] ) && is_array( $raw[ $id ] ) ? $raw[ $id ] : array();
		$def = $defaults[ $id ];
		$mode = isset( $row['source_mode'] ) ? sanitize_key( $row['source_mode'] ) : $def['source_mode'];
		// Legacy "random" mode → category + orderby rand.
		if ( 'random' === $mode ) {
			$mode = 'category';
			$row['orderby'] = 'rand';
		}
		if ( ! in_array( $mode, array( 'category', 'specific' ), true ) ) {
			$mode = 'category';
		}
		$manual_raw = isset( $row['manual_posts'] ) ? (string) $row['manual_posts'] : '';
		$manual_ids = array_values(
			array_unique(
				array_filter(
					array_map(
						'absint',
						preg_split( '/[\s,]+/', $manual_raw ) ?: array()
					)
				)
			)
		);
		$orderby = isset( $row['orderby'] ) ? sanitize_key( $row['orderby'] ) : ( $def['orderby'] ?? 'date' );
		if ( ! in_array( $orderby, array( 'date', 'modified', 'title', 'rand', 'comment_count' ), true ) ) {
			$orderby = 'date';
		}
		$order = ( isset( $row['order'] ) && 'ASC' === strtoupper( (string) $row['order'] ) ) ? 'ASC' : 'DESC';
		$out[ $id ] = array(
			'visible'      => ! empty( $row['visible'] ),
			'label'        => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : $def['label'],
			'count'        => isset( $row['count'] ) ? max( 1, min( 24, absint( $row['count'] ) ) ) : (int) $def['count'],
			'source_mode'  => $mode,
			'category_id'  => isset( $row['category_id'] ) ? (string) absint( $row['category_id'] ) : '',
			'manual_posts' => implode( ', ', $manual_ids ),
			'orderby'      => $orderby,
			'order'        => $order,
		);
		// Preserve checked visible when checkbox omitted → unchecked.
		if ( ! array_key_exists( 'visible', $row ) && array_key_exists( 'sections_submitted', $raw ) ) {
			$out[ $id ]['visible'] = false;
		}
	}
	return $out;
}

/**
 * Convert Home 2 page meta sections into theme section configs.
 *
 * @param int $page_id Front page ID.
 * @return array<string, array<string, mixed>>|null
 */
function altijara_sections_from_home2_page( $page_id = 0 ) {
	$page_id = $page_id ? absint( $page_id ) : altijara_get_static_front_page_id();
	if ( ! $page_id || 'home-2.php' !== (string) get_page_template_slug( $page_id ) ) {
		return null;
	}
	if ( ! function_exists( 'altijara_get_home2_layout' ) ) {
		return null;
	}
	$pack = altijara_get_home2_layout( $page_id );
	return ! empty( $pack['sections'] ) ? $pack['sections'] : null;
}

/**
 * Shared homepage markup (hero + body).
 */
function altijara_render_home2_markup() {
	$pack     = function_exists( 'altijara_get_home2_layout' ) ? altijara_get_home2_layout() : array( 'sections' => array(), 'order' => array() );
	$sections = isset( $pack['sections'] ) ? $pack['sections'] : array();
	$order    = isset( $pack['order'] ) ? $pack['order'] : array();
	?>
	<main class="container" data-altijara-home-ssr="1" data-altijara-home="2">
		<?php
		if ( function_exists( 'altijara_render_layout_home_hero' ) ) {
			altijara_render_layout_home_hero( $sections );
		}
		if ( function_exists( 'altijara_with_layout_sections' ) && function_exists( 'altijara_render_layout_body_ids' ) ) {
			altijara_with_layout_sections(
				$sections,
				static function () use ( $order ) {
					altijara_render_layout_body_ids( $order );
				}
			);
		} elseif ( function_exists( 'altijara_render_home_body_sections' ) ) {
			altijara_render_home_body_sections();
		}
		?>
	</main>
	<?php
}

/**
 * Ensure Home 2 page exists and is set as the static front page.
 *
 * @return array{page_id:int,created:bool,set_front:bool}
 */
function altijara_ensure_home2_front_page() {
	$report = array(
		'page_id'   => 0,
		'created'   => false,
		'set_front' => false,
	);

	$existing = absint( altijara_get_option( 'home2_page_id', 0 ) );
	$page     = $existing ? get_post( $existing ) : null;
	if ( ! ( $page instanceof WP_Post ) || 'trash' === $page->post_status ) {
		$page = get_page_by_path( 'home-2' );
	}
	if ( ! ( $page instanceof WP_Post ) || 'trash' === $page->post_status ) {
		// Prefer an existing Home 2 template page.
		$templated = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 1,
				'meta_key'       => '_wp_page_template',
				'meta_value'     => 'home-2.php',
			)
		);
		if ( ! empty( $templated[0] ) && $templated[0] instanceof WP_Post ) {
			$page = $templated[0];
		}
	}

	if ( ! ( $page instanceof WP_Post ) ) {
		$page_id = wp_insert_post(
			array(
				'post_title'   => 'الرئيسية',
				'post_name'    => 'home-2',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			),
			true
		);
		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return $report;
		}
		$page              = get_post( (int) $page_id );
		$report['created'] = true;
	}

	if ( ! ( $page instanceof WP_Post ) ) {
		return $report;
	}

	$report['page_id'] = (int) $page->ID;
	update_post_meta( $page->ID, '_wp_page_template', 'home-2.php' );
	altijara_update_options( array( 'home2_page_id' => (int) $page->ID ) );

	// Seed page meta once if empty.
	$saved = get_post_meta( $page->ID, ALTIJARA_PAGE_META_KEY, true );
	if ( ! is_array( $saved ) || ( empty( $saved['layout'] ) && empty( $saved['sections'] ) ) ) {
		update_post_meta(
			$page->ID,
			ALTIJARA_PAGE_META_KEY,
			array(
				'layout' => function_exists( 'altijara_layout_preset_home' )
					? altijara_layout_preset_home()
					: array( 'sections' => array(), 'order' => array() ),
			)
		);
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $page->ID );
	$report['set_front'] = true;

	return $report;
}
