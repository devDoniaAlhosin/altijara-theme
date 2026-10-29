<?php
/**
 * Category archive layout builder (term meta).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALTIJARA_TERM_LAYOUT_KEY', '_altijara_archive_layout' );

/**
 * Get category layout pack (saved or preset).
 *
 * @param int  $term_id Term ID.
 * @param bool $fallback_preset When empty, return preset.
 * @return array{sections:array,order:string[]}
 */
function altijara_get_category_layout( $term_id, $fallback_preset = true ) {
	$term_id = absint( $term_id );
	$raw     = $term_id ? get_term_meta( $term_id, ALTIJARA_TERM_LAYOUT_KEY, true ) : null;
	if ( function_exists( 'altijara_layout_pack_is_stored' ) && altijara_layout_pack_is_stored( $raw ) ) {
		return altijara_normalize_layout_pack( $raw );
	}
	if ( $fallback_preset ) {
		return altijara_layout_preset_category( $term_id );
	}
	return array(
		'sections' => array(),
		'order'    => array(),
	);
}

/**
 * Whether category has a custom saved layout (including an empty module list).
 *
 * @param int $term_id Term ID.
 */
function altijara_category_has_custom_layout( $term_id ) {
	$raw = get_term_meta( absint( $term_id ), ALTIJARA_TERM_LAYOUT_KEY, true );
	return function_exists( 'altijara_layout_pack_is_stored' )
		? altijara_layout_pack_is_stored( $raw )
		: ( is_array( $raw ) && ! empty( $raw['sections'] ) );
}

/**
 * Save category layout.
 *
 * @param int                  $term_id Term ID.
 * @param array<string, mixed> $pack    Layout pack.
 */
function altijara_save_category_layout( $term_id, $pack ) {
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return;
	}
	$pack = altijara_normalize_layout_pack( $pack );
	update_term_meta( $term_id, ALTIJARA_TERM_LAYOUT_KEY, $pack );
}

/**
 * Category edit form: layout builder.
 *
 * @param WP_Term $term Term.
 */
function altijara_category_layout_edit_form( $term ) {
	if ( ! ( $term instanceof WP_Term ) || 'category' !== $term->taxonomy ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$term_id      = (int) $term->term_id;
	$pack         = altijara_get_category_layout( $term_id, true );
	$uses_modules = altijara_category_has_custom_layout( $term_id );
	wp_nonce_field( 'altijara_save_category_layout', 'altijara_category_layout_nonce' );
	?>
	<tr class="form-field altijara-term-layout-row">
		<th scope="row"><label for="altijara_category_layout_use"><?php esc_html_e( 'Archive layout (modules)', 'altijara' ); ?></label></th>
		<td>
			<p class="description" style="margin-top:0;">
				<?php esc_html_e( 'This category keeps the category archive template until you check “Use the module layout” and save. A layout already saved here keeps showing on the site. Unchecking the box leaves that saved layout in place. Reset removes it.', 'altijara' ); ?>
			</p>
			<label for="altijara_category_layout_use" style="display:block;margin-bottom:8px;">
				<input type="checkbox" name="altijara_category_layout_use" id="altijara_category_layout_use" value="1" <?php checked( $uses_modules ); ?>>
				<?php esc_html_e( 'Use the module layout', 'altijara' ); ?>
			</label>
			<label style="display:inline-block;margin-bottom:12px;">
				<input type="checkbox" name="altijara_category_layout_reset" value="1">
				<?php esc_html_e( 'Reset layout to theme default', 'altijara' ); ?>
			</label>
			<?php
			altijara_render_layout_builder(
				array(
					'builder_id'   => 'cat_layout_' . $term_id,
					'field_prefix' => 'altijara_cat_layout_sections',
					'order_field'  => 'altijara_cat_layout_order',
					'sections'     => $pack['sections'],
					'order'        => $pack['order'],
					'surface'      => 'category',
					'lock_hero'    => false,
				)
			);
			?>
		</td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'altijara_category_layout_edit_form', 20 );

/**
 * Save category layout from term edit.
 *
 * @param int $term_id Term ID.
 */
function altijara_category_layout_save( $term_id ) {
	$term_id = absint( $term_id );
	if ( ! $term_id || ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	if ( empty( $_POST['altijara_category_layout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_category_layout_nonce'] ) ), 'altijara_save_category_layout' ) ) {
		return;
	}
	if ( ! empty( $_POST['altijara_category_layout_reset'] ) ) {
		delete_term_meta( $term_id, ALTIJARA_TERM_LAYOUT_KEY );
		return;
	}
	// Opt-in only. An unchecked box leaves any saved pack untouched.
	if ( empty( $_POST['altijara_category_layout_use'] ) ) {
		return;
	}
	// Missing sections = every module was removed; still persist an empty pack.
	$sections = isset( $_POST['altijara_cat_layout_sections'] ) && is_array( $_POST['altijara_cat_layout_sections'] )
		? wp_unslash( $_POST['altijara_cat_layout_sections'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: array();
	$order    = isset( $_POST['altijara_cat_layout_order'] ) && is_array( $_POST['altijara_cat_layout_order'] )
		? wp_unslash( $_POST['altijara_cat_layout_order'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: array();
	$pack = altijara_sanitize_layout( $sections, $order, array( 'lock_hero' => false ) );
	if ( function_exists( 'altijara_cap_archive_module_picks' ) && function_exists( 'altijara_get_archive_counts' ) ) {
		$pack = altijara_cap_archive_module_picks( $pack, altijara_get_archive_counts() );
	}
	altijara_save_category_layout( $term_id, $pack );
}
add_action( 'edited_category', 'altijara_category_layout_save', 20 );

/**
 * Category edit form: lower-column override.
 *
 * Stored in its own term meta. It does not read or write the module layout.
 *
 * @param WP_Term $term Term.
 */
function altijara_category_sidebar_edit_form( $term ) {
	if ( ! ( $term instanceof WP_Term ) || 'category' !== $term->taxonomy ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	if ( ! function_exists( 'altijara_render_archive_sidebar_inputs' ) || ! function_exists( 'altijara_get_category_archive_sidebar' ) ) {
		return;
	}
	$term_id  = (int) $term->term_id;
	$override = function_exists( 'altijara_get_category_sidebar_override' ) ? altijara_get_category_sidebar_override( $term_id ) : null;
	$sidebar  = is_array( $override ) ? $override : altijara_get_category_archive_sidebar( $term_id );
	wp_nonce_field( 'altijara_save_category_sidebar', 'altijara_category_sidebar_nonce' );
	?>
	<tr class="form-field altijara-term-sidebar-row">
		<th scope="row"><label for="altijara_category_sidebar_use"><?php esc_html_e( 'Lower column', 'altijara' ); ?></label></th>
		<td>
			<p class="description" style="margin-top:0;">
				<?php esc_html_e( 'This category uses the lower column from Category Archives until you check “Override the lower column” and save. Unchecking the box leaves a saved override in place. “Use the site lower column” removes it. A module layout saved above replaces this template.', 'altijara' ); ?>
			</p>
			<label for="altijara_category_sidebar_use" style="display:block;margin-bottom:8px;">
				<input type="checkbox" name="altijara_category_sidebar_use" id="altijara_category_sidebar_use" value="1" <?php checked( is_array( $override ) ); ?>>
				<?php esc_html_e( 'Override the lower column for this category', 'altijara' ); ?>
			</label>
			<label style="display:inline-block;margin-bottom:12px;">
				<input type="checkbox" name="altijara_category_sidebar_reset" value="1">
				<?php esc_html_e( 'Use the site lower column', 'altijara' ); ?>
			</label>
			<?php altijara_render_archive_sidebar_inputs( $sidebar, 'altijara_category_sidebar', 'altijara_category_sidebar' ); ?>
		</td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'altijara_category_sidebar_edit_form', 21 );

/**
 * Save a category lower-column override. Does not touch the module layout meta.
 *
 * @param int $term_id Term ID.
 */
function altijara_category_sidebar_save( $term_id ) {
	$term_id = absint( $term_id );
	if ( ! $term_id || ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	if ( empty( $_POST['altijara_category_sidebar_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_category_sidebar_nonce'] ) ), 'altijara_save_category_sidebar' ) ) {
		return;
	}
	if ( ! defined( 'ALTIJARA_TERM_SIDEBAR_KEY' ) ) {
		return;
	}
	if ( ! empty( $_POST['altijara_category_sidebar_reset'] ) ) {
		delete_term_meta( $term_id, ALTIJARA_TERM_SIDEBAR_KEY );
		return;
	}
	if ( empty( $_POST['altijara_category_sidebar_use'] ) ) {
		return;
	}
	$raw = array();
	if ( isset( $_POST['altijara_category_sidebar'] ) && is_array( $_POST['altijara_category_sidebar'] ) ) {
		$raw = wp_unslash( $_POST['altijara_category_sidebar'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}
	if ( ! function_exists( 'altijara_sanitize_archive_sidebar' ) ) {
		return;
	}
	update_term_meta( $term_id, ALTIJARA_TERM_SIDEBAR_KEY, altijara_sanitize_archive_sidebar( $raw ) );
}
add_action( 'edited_category', 'altijara_category_sidebar_save', 21 );

/**
 * Enqueue builder assets on category edit screen.
 *
 * @param string $hook Hook.
 */
function altijara_category_layout_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'term.php', 'edit-tags.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'category' !== $screen->taxonomy ) {
		return;
	}
	if ( function_exists( 'altijara_enqueue_section_manager_assets' ) ) {
		altijara_enqueue_section_manager_assets();
	}
}
add_action( 'admin_enqueue_scripts', 'altijara_category_layout_admin_assets', 30 );

/**
 * Site-wide category archive layout saved from Category Archives.
 *
 * Never configured / reset → optional preset. An explicit empty save stays empty.
 *
 * @param bool $fallback_preset Use the editor preset when nothing is saved.
 * @return array{sections:array,order:string[]}
 */
function altijara_get_category_archives_layout( $fallback_preset = true ) {
	$raw = altijara_get_option( 'category_archive_layout', null );
	if ( function_exists( 'altijara_layout_pack_is_stored' ) && altijara_layout_pack_is_stored( $raw ) ) {
		return altijara_normalize_layout_pack( $raw );
	}
	if ( $fallback_preset && function_exists( 'altijara_layout_preset_category_archive' ) ) {
		return altijara_layout_preset_category_archive();
	}
	return array(
		'sections' => array(),
		'order'    => array(),
	);
}

/**
 * Full-width hero for this category (Category Archives checklist only).
 *
 * @param int $term_id Category term ID.
 * @return bool
 */
function altijara_category_archive_is_full_hero( $term_id ) {
	$term_id = absint( $term_id );
	return (bool) ( $term_id && function_exists( 'altijara_category_uses_full_hero' ) && altijara_category_uses_full_hero( $term_id ) );
}

/**
 * Whether a module picks its own posts instead of the current category stream.
 *
 * @param array<string, mixed> $section Section config.
 * @return bool
 */
function altijara_category_archive_section_owns_posts( $section ) {
	if ( ! empty( $section['manual_posts'] ) && is_array( $section['manual_posts'] ) ) {
		return true;
	}
	if ( function_exists( 'altijara_section_category_ids' ) && ! empty( altijara_section_category_ids( is_array( $section ) ? $section : array() ) ) ) {
		return true;
	}
	return isset( $section['category'] ) && '' !== (string) $section['category'] && '0' !== (string) $section['category'];
}

/**
 * Saved modules for one category, with counts, full-hero, and stream offsets applied.
 *
 * Walks modules in order by widget type so hero / featured / news share one category
 * cursor (re-added builder IDs included). Modules that own their own posts stay independent.
 *
 * @param int $term_id Category term ID.
 * @return array{sections:array,order:string[]}
 */
function altijara_category_archive_pack_for_term( $term_id ) {
	$term_id = absint( $term_id );
	$pack    = altijara_get_category_archives_layout( true );
	if ( empty( $pack['sections'] ) || ! is_array( $pack['sections'] ) ) {
		return $pack;
	}

	$stream_widgets = array( 'cat_archive_hero', 'cat_archive_featured', 'cat_archive_news' );

	// Hero / featured / news always use the category being viewed (any section ID).
	foreach ( $pack['sections'] as $sid => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$widget = isset( $row['widget'] ) ? (string) $row['widget'] : '';
		if ( ! in_array( $widget, $stream_widgets, true ) ) {
			continue;
		}
		$pack['sections'][ $sid ]['category']        = '';
		$pack['sections'][ $sid ]['categories']      = array();
		$pack['sections'][ $sid ]['category_filter'] = 'include';
		$pack['sections'][ $sid ]['manual_posts']    = array();
	}

	$counts = function_exists( 'altijara_get_archive_counts' )
		? altijara_get_archive_counts()
		: array(
			'sidebar_count'  => 3,
			'featured_count' => 8,
			'news_size'      => 10,
		);
	$full    = altijara_category_archive_is_full_hero( $term_id );
	$sidebar = $full ? 0 : (int) $counts['sidebar_count'];
	$hero_n  = max( 1, 1 + $sidebar );
	$feat_n  = max( 1, (int) $counts['featured_count'] );
	$news_n  = max( 1, (int) $counts['news_size'] );
	$paged   = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$cursor  = 0;

	$order = ( isset( $pack['order'] ) && is_array( $pack['order'] ) ) ? array_values( $pack['order'] ) : array();
	$seen  = array();
	$ids   = array();
	foreach ( $order as $sid ) {
		$sid = sanitize_key( (string) $sid );
		if ( ! $sid || ! isset( $pack['sections'][ $sid ] ) || ! is_array( $pack['sections'][ $sid ] ) ) {
			continue;
		}
		$ids[]        = $sid;
		$seen[ $sid ] = true;
	}
	foreach ( $pack['sections'] as $sid => $row ) {
		$sid = sanitize_key( (string) $sid );
		if ( ! $sid || isset( $seen[ $sid ] ) || ! is_array( $row ) ) {
			continue;
		}
		$ids[] = $sid;
	}

	foreach ( $ids as $sid ) {
		$row    = $pack['sections'][ $sid ];
		$widget = isset( $row['widget'] ) ? (string) $row['widget'] : '';
		$visible = ! isset( $row['visible'] ) || $row['visible'];

		if ( 'cat_archive_hero' === $widget ) {
			$pack['sections'][ $sid ]['count'] = $hero_n;
			if ( $full ) {
				$pack['sections'][ $sid ]['variant'] = 'full';
			}
			if ( $visible ) {
				$cursor += $hero_n;
			}
			continue;
		}

		if ( 'cat_archive_featured' === $widget ) {
			$pack['sections'][ $sid ]['count'] = $feat_n;
			if ( ! altijara_category_archive_section_owns_posts( $pack['sections'][ $sid ] ) ) {
				$pack['sections'][ $sid ]['offset'] = $cursor;
			}
			if ( $visible ) {
				$cursor += $feat_n;
			}
			continue;
		}

		if ( 'cat_archive_news' === $widget ) {
			$news          = $pack['sections'][ $sid ];
			$news['count'] = $news_n;
			if ( ! empty( $news['manual_posts'] ) && is_array( $news['manual_posts'] ) ) {
				$news['offset']      = ( $paged - 1 ) * $news_n;
				$news['list_offset'] = 0;
			} elseif ( altijara_category_archive_section_owns_posts( $news ) ) {
				$base                = isset( $news['offset'] ) ? (int) $news['offset'] : 0;
				$news['list_offset'] = max( 0, $base );
				$news['offset']      = $base + ( ( $paged - 1 ) * $news_n );
			} else {
				$news['list_offset'] = $cursor;
				$news['offset']      = $cursor + ( ( $paged - 1 ) * $news_n );
			}
			$pack['sections'][ $sid ] = $news;
			continue;
		}

		if ( 'cat_archive_lower' === $widget ) {
			$override     = ( $term_id && function_exists( 'altijara_get_category_sidebar_override' ) )
				? altijara_get_category_sidebar_override( $term_id )
				: null;
			$saved        = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'category_archive_layout', null ) : null;
			$layout_saved = function_exists( 'altijara_layout_pack_is_stored' )
				? altijara_layout_pack_is_stored( $saved )
				: ( is_array( $saved ) && ! empty( $saved['sections'] ) );
			$column       = null;
			if ( is_array( $override ) ) {
				$column = $override;
			} elseif ( ! $layout_saved && function_exists( 'altijara_get_archive_sidebar' ) ) {
				$column = altijara_get_archive_sidebar();
			}
			if ( is_array( $column ) ) {
				$pack['sections'][ $sid ]['visible'] = ! empty( $column['visible'] );
				if ( ! empty( $column['order'] ) && is_array( $column['order'] ) ) {
					$pack['sections'][ $sid ]['sidebar_order'] = implode( ',', $column['order'] );
				}
			}
		}
	}

	return $pack;
}

/**
 * Render category archive modules, keeping news and the lower column side by side.
 *
 * @param array{sections?:array,order?:array} $pack Layout pack.
 */
function altijara_render_category_archive_modules( $pack ) {
	if ( function_exists( 'altijara_normalize_layout_pack' ) ) {
		$pack = altijara_normalize_layout_pack( $pack );
	}
	$sections = ( isset( $pack['sections'] ) && is_array( $pack['sections'] ) ) ? $pack['sections'] : array();
	$order    = ( isset( $pack['order'] ) && is_array( $pack['order'] ) ) ? array_values( $pack['order'] ) : array();
	if ( ! $sections || ! $order || ! function_exists( 'altijara_with_layout_sections' ) ) {
		return;
	}

	$ctx      = function_exists( 'altijara_get_layout_context' ) ? altijara_get_layout_context() : array();
	$term_id  = ! empty( $ctx['category'] ) ? absint( $ctx['category'] ) : 0;
	$sidebar  = function_exists( 'altijara_category_sidebar_layout_for_term' )
		? altijara_category_sidebar_layout_for_term( $term_id )
		: array();
	$side_on  = function_exists( 'altijara_category_sidebar_has_widgets' ) && altijara_category_sidebar_has_widgets( $sidebar );

	altijara_with_layout_sections(
		$sections,
		static function () use ( $order, $sidebar, $side_on ) {
			$sidebar_drawn = false;
			foreach ( $order as $id ) {
				$id = sanitize_key( (string) $id );
				if ( ! $id ) {
					continue;
				}
				$cfg    = function_exists( 'altijara_get_section_config' ) ? altijara_get_section_config( $id ) : array();
				$widget = isset( $cfg['widget'] ) ? (string) $cfg['widget'] : '';
				if ( 'cat_archive_lower' === $widget ) {
					continue;
				}
				if ( 'cat_archive_news' === $widget ) {
					$news_on = function_exists( 'altijara_section_is_visible' ) && altijara_section_is_visible( $id );
					if ( ! $news_on && ( $sidebar_drawn || ! $side_on ) ) {
						continue;
					}
					echo '<section class="cat-editorial-lower-grid' . ( ( $side_on && ! $sidebar_drawn ) ? '' : ' is-sidebar-hidden' ) . '">';
					if ( $news_on ) {
						altijara_render_widget_instance( $id );
					}
					if ( $side_on && ! $sidebar_drawn ) {
						altijara_render_category_sidebar_column( $sidebar );
						$sidebar_drawn = true;
					}
					echo '</section>';
					continue;
				}
				if ( function_exists( 'altijara_render_spaced_widget' ) ) {
					altijara_render_spaced_widget( $id );
				} elseif ( function_exists( 'altijara_render_widget_instance' ) ) {
					altijara_render_widget_instance( $id );
				}
			}
			if ( $side_on && ! $sidebar_drawn ) {
				altijara_render_category_sidebar_column( $sidebar );
			}
		}
	);
}

/**
 * Saved lower sidebar, or the preset when nothing has been saved.
 *
 * @param bool $fallback_preset Use the preset when empty.
 * @return array{visible:bool,sections:array,order:string[]}
 */
function altijara_get_category_sidebar_layout( $fallback_preset = true ) {
	$raw = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'archive_sidebar_layout', null ) : null;
	if ( function_exists( 'altijara_layout_pack_is_stored' ) && altijara_layout_pack_is_stored( $raw ) && function_exists( 'altijara_normalize_layout_pack' ) ) {
		$pack            = altijara_normalize_layout_pack( $raw );
		$pack['visible'] = ! empty( $raw['visible'] );
		return $pack;
	}
	if ( $fallback_preset && function_exists( 'altijara_layout_preset_category_sidebar' ) ) {
		return altijara_layout_preset_category_sidebar();
	}
	return array(
		'visible'  => true,
		'sections' => array(),
		'order'    => array(),
	);
}

/**
 * Sidebar for one category. A term override can hide the column or reorder the original widgets.
 *
 * @param int $term_id Category term ID.
 * @return array{visible:bool,sections:array,order:string[]}
 */
function altijara_category_sidebar_layout_for_term( $term_id ) {
	$pack = altijara_get_category_sidebar_layout( true );
	// Keep most-read category / manual picks. Empty category still inherits the
	// viewed archive term via altijara_resolve_section_category_id() + layout context.
	if ( ! empty( $pack['sections'] ) && is_array( $pack['sections'] ) ) {
		foreach ( $pack['sections'] as $sid => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$widget = isset( $row['widget'] ) ? (string) $row['widget'] : '';
			if ( 'cat_archive_lower' !== $widget ) {
				continue;
			}
			$pack['sections'][ $sid ]['category']     = '';
			$pack['sections'][ $sid ]['manual_posts'] = array();
		}
	}
	$term_id = absint( $term_id );
	if ( ! $term_id || ! function_exists( 'altijara_get_category_sidebar_override' ) ) {
		return $pack;
	}
	$override = altijara_get_category_sidebar_override( $term_id );
	if ( ! is_array( $override ) ) {
		return $pack;
	}
	$pack['visible'] = ! empty( $override['visible'] );
	$map = array(
		'ad'        => 'cat_sidebar_ad',
		'magazine'  => 'cat_sidebar_magazine',
		'most_read' => 'cat_sidebar_most_read',
	);
	$buckets = array();
	$rest    = array();
	$order   = isset( $pack['order'] ) && is_array( $pack['order'] ) ? $pack['order'] : array();
	foreach ( $order as $id ) {
		$id     = sanitize_key( (string) $id );
		$widget = isset( $pack['sections'][ $id ]['widget'] ) ? (string) $pack['sections'][ $id ]['widget'] : '';
		$bucket = '';
		foreach ( $map as $key => $type ) {
			if ( $widget === $type && ! isset( $buckets[ $key ] ) ) {
				$bucket = $key;
				break;
			}
		}
		if ( '' !== $bucket ) {
			$buckets[ $bucket ] = $id;
		} else {
			$rest[] = $id;
		}
	}
	$next = array();
	if ( ! empty( $override['order'] ) && is_array( $override['order'] ) ) {
		foreach ( $override['order'] as $key ) {
			$key = sanitize_key( (string) $key );
			if ( isset( $buckets[ $key ] ) ) {
				$next[] = $buckets[ $key ];
				unset( $buckets[ $key ] );
			}
		}
	}
	foreach ( $buckets as $id ) {
		$next[] = $id;
	}
	$pack['order'] = array_merge( $next, $rest );
	return $pack;
}

/**
 * Whether the lower sidebar should print.
 *
 * @param array<string, mixed> $pack Sidebar layout.
 * @return bool
 */
function altijara_category_sidebar_has_widgets( $pack ) {
	if ( empty( $pack['visible'] ) || empty( $pack['sections'] ) || empty( $pack['order'] ) || ! is_array( $pack['order'] ) ) {
		return false;
	}
	foreach ( $pack['order'] as $id ) {
		$id = sanitize_key( (string) $id );
		if ( $id && ! empty( $pack['sections'][ $id ]['visible'] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Print the lower sidebar widgets.
 *
 * @param array<string, mixed> $pack Sidebar layout.
 */
function altijara_render_category_sidebar_column( $pack ) {
	$sections = ( isset( $pack['sections'] ) && is_array( $pack['sections'] ) ) ? $pack['sections'] : array();
	$order    = ( isset( $pack['order'] ) && is_array( $pack['order'] ) ) ? $pack['order'] : array();
	if ( ! $sections || ! $order || ! function_exists( 'altijara_with_layout_sections' ) ) {
		return;
	}
	echo '<aside class="cat-opinion-col">';
	altijara_with_layout_sections(
		$sections,
		static function () use ( $order ) {
			foreach ( $order as $id ) {
				$id = sanitize_key( (string) $id );
				if ( $id && function_exists( 'altijara_render_widget_instance' ) ) {
					altijara_render_widget_instance( $id );
				}
			}
		}
	);
	echo '</aside>';
}

/**
 * Print the site-wide category archive and footer.
 *
 * @param int $term_id Category term ID.
 * @return bool True when the module page was printed.
 */
function altijara_render_category_archive_page( $term_id ) {
	if ( ! function_exists( 'altijara_with_layout_context' ) ) {
		return false;
	}
	// Preset when never configured; explicit empty pack stays empty (no classic markup path).
	$pack     = altijara_category_archive_pack_for_term( $term_id );
	$term     = get_queried_object();
	$cat_name = ( $term && ! empty( $term->name ) ) ? $term->name : '';
	?>
<main class="container category-main-wrapper" data-altijara-layout="archive-modules">
	<?php if ( $cat_name ) : ?>
		<h1 class="screen-reader-text"><?php echo esc_html( $cat_name ); ?></h1>
	<?php endif; ?>
	<?php
	altijara_with_layout_context(
		array(
			'category' => absint( $term_id ),
			'surface'  => 'category_archive',
		),
		static function () use ( $pack ) {
			altijara_render_category_archive_modules( $pack );
		}
	);
	?>
</main>
	<?php
	get_footer();
	return true;
}
