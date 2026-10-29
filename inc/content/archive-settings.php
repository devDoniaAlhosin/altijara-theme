<?php
/**
 * Category archive layout settings and sanitize.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed archive layout keys.
 *
 * @return string[]
 */
function altijara_archive_layout_choices() {
	return array(
		'posts'  => __( 'Posts archive', 'altijara' ),
		'issues' => __( 'Magazine issues', 'altijara' ),
	);
}

/**
 * Sanitize archive_layouts map (term_id => layout).
 *
 * @param mixed $raw Raw input.
 * @return array<string, string>
 */
function altijara_sanitize_archive_layouts( $raw ) {
	$out     = array();
	$allowed = array_keys( altijara_archive_layout_choices() );
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $term_id => $layout ) {
		$tid = absint( $term_id );
		if ( ! $tid ) {
			continue;
		}
		$layout = sanitize_key( $layout );
		if ( ! in_array( $layout, $allowed, true ) ) {
			$layout = 'posts';
		}
		$out[ (string) $tid ] = $layout;
	}
	return $out;
}

/**
 * Sanitize magazine-layout category IDs (multi-select).
 *
 * @param mixed $raw Raw POST.
 * @return int[]
 */
function altijara_sanitize_archive_magazine_ids( $raw ) {
	return altijara_sanitize_archive_full_hero( $raw );
}

/**
 * Default posts-archive count controls.
 *
 * @return array{sidebar_count:int,featured_rows:int,news_size:int}
 */
function altijara_default_archive_counts() {
	return array(
		'sidebar_count' => 3,
		'featured_rows' => 2,
		'news_size'     => 10,
	);
}

/**
 * Sanitize posts-archive counts.
 *
 * @param mixed $raw Raw input.
 * @return array{sidebar_count:int,featured_rows:int,news_size:int}
 */
function altijara_sanitize_archive_counts( $raw ) {
	$d = altijara_default_archive_counts();
	if ( ! is_array( $raw ) ) {
		return $d;
	}
	$sidebar = isset( $raw['sidebar_count'] ) ? absint( $raw['sidebar_count'] ) : $d['sidebar_count'];
	$rows    = isset( $raw['featured_rows'] ) ? absint( $raw['featured_rows'] ) : $d['featured_rows'];
	$news    = isset( $raw['news_size'] ) ? absint( $raw['news_size'] ) : $d['news_size'];
	$sidebar = max( 0, min( 30, $sidebar ) );
	if ( ! in_array( $rows, array( 1, 2, 3 ), true ) ) {
		$rows = 2;
	}
	$news = max( 1, min( 50, $news ) );
	return array(
		'sidebar_count' => $sidebar,
		'featured_rows' => $rows,
		'news_size'     => $news,
	);
}

/**
 * Get posts-archive count settings.
 *
 * @return array{sidebar_count:int,featured_rows:int,news_size:int,featured_count:int,spotlight:int}
 */
function altijara_get_archive_counts() {
	$counts = altijara_sanitize_archive_counts( altijara_get_option( 'archive_counts', array() ) );
	$counts['featured_count'] = (int) $counts['featured_rows'] * 4;
	return $counts;
}

/**
 * Whether category archives also list posts filed only in child categories.
 *
 * Missing value keeps the previous behavior (children included).
 *
 * @return bool
 */
function altijara_archive_include_children() {
	$value = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'archive_include_children', true ) : true;
	return ! empty( $value ) && '0' !== (string) $value;
}

/**
 * Build archive counts from hero / featured / news module fields.
 *
 * @param array<string, mixed> $pack Layout pack.
 * @return array{sidebar_count:int,featured_rows:int,news_size:int}
 */
function altijara_archive_counts_from_layout_pack( $pack ) {
	$counts = altijara_sanitize_archive_counts( altijara_get_option( 'archive_counts', array() ) );
	if ( ! is_array( $pack ) || empty( $pack['sections'] ) || ! is_array( $pack['sections'] ) ) {
		return $counts;
	}
	foreach ( $pack['sections'] as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$widget = isset( $row['widget'] ) ? (string) $row['widget'] : '';
		$n      = isset( $row['count'] ) ? (int) $row['count'] : 0;
		if ( 'cat_archive_hero' === $widget ) {
			$counts['sidebar_count'] = $n;
		} elseif ( 'cat_archive_featured' === $widget ) {
			$rows = (int) round( $n / 4 );
			$counts['featured_rows'] = in_array( $rows, array( 1, 2, 3 ), true ) ? $rows : 2;
		} elseif ( 'cat_archive_news' === $widget ) {
			$counts['news_size'] = $n;
		}
	}
	return altijara_sanitize_archive_counts( $counts );
}

/**
 * Apply saved archive counts onto module cards for the editor.
 *
 * @param array<string, mixed> $pack Layout pack.
 * @return array<string, mixed>
 */
function altijara_apply_archive_counts_to_layout_pack( $pack ) {
	if ( ! is_array( $pack ) || empty( $pack['sections'] ) || ! is_array( $pack['sections'] ) ) {
		return is_array( $pack ) ? $pack : array( 'sections' => array(), 'order' => array() );
	}
	$counts = altijara_get_archive_counts();
	foreach ( $pack['sections'] as $id => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$widget = isset( $row['widget'] ) ? (string) $row['widget'] : '';
		if ( 'cat_archive_hero' === $widget ) {
			$pack['sections'][ $id ]['count'] = (int) $counts['sidebar_count'];
		} elseif ( 'cat_archive_featured' === $widget ) {
			$pack['sections'][ $id ]['count'] = (int) $counts['featured_count'];
		} elseif ( 'cat_archive_news' === $widget ) {
			$pack['sections'][ $id ]['count'] = (int) $counts['news_size'];
		}
	}
	return $pack;
}

/**
 * Trim hand-picked posts on category modules to the archive counts.
 *
 * @param array<string, mixed> $pack   Layout pack.
 * @param array<string, mixed> $counts Sanitized archive counts.
 * @return array<string, mixed>
 */
function altijara_cap_archive_module_picks( $pack, $counts ) {
	if ( ! is_array( $pack ) || empty( $pack['sections'] ) || ! is_array( $pack['sections'] ) ) {
		return $pack;
	}
	if ( ! is_array( $counts ) ) {
		$counts = array();
	}
	$rows = isset( $counts['featured_rows'] ) ? (int) $counts['featured_rows'] : 2;
	if ( ! in_array( $rows, array( 1, 2, 3 ), true ) ) {
		$rows = 2;
	}
	$featured = isset( $counts['featured_count'] ) ? max( 1, (int) $counts['featured_count'] ) : ( $rows * 4 );
	$news     = isset( $counts['news_size'] ) ? max( 1, (int) $counts['news_size'] ) : 10;
	$hero     = 1 + ( isset( $counts['sidebar_count'] ) ? max( 0, (int) $counts['sidebar_count'] ) : 0 );
	foreach ( $pack['sections'] as $id => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$widget = isset( $row['widget'] ) ? (string) $row['widget'] : '';
		$max    = 0;
		if ( 'cat_archive_featured' === $widget ) {
			$max = $featured;
		} elseif ( 'cat_archive_news' === $widget ) {
			$max = $news;
		} elseif ( 'cat_archive_hero' === $widget ) {
			$max = max( 1, $hero );
		}
		if ( $max < 1 ) {
			continue;
		}
		if ( ! empty( $row['manual_posts'] ) && is_array( $row['manual_posts'] ) ) {
			$pack['sections'][ $id ]['manual_posts'] = array_slice( array_values( $row['manual_posts'] ), 0, $max );
		}
		if ( ! empty( $row['issue_ids'] ) && is_array( $row['issue_ids'] ) ) {
			$pack['sections'][ $id ]['issue_ids'] = array_slice( array_values( $row['issue_ids'] ), 0, $max );
		}
	}
	return $pack;
}

/**
 * Lower-column widgets, in the order the category template prints today.
 *
 * @return string[]
 */
function altijara_archive_sidebar_widgets() {
	return array( 'ad', 'magazine', 'most_read' );
}

/**
 * Default lower column: visible, ad then magazine slider then most read.
 *
 * @return array{visible:bool,order:string[]}
 */
function altijara_default_archive_sidebar() {
	return array(
		'visible' => true,
		'order'   => altijara_archive_sidebar_widgets(),
	);
}

/**
 * Sanitize the category lower-column setting.
 *
 * Accepts a saved pack (`visible` + `order`) or a form payload (`visible` + `positions`).
 * Missing widgets are appended in the default order. The key is omitted from defaults
 * so a missing saved branch is not merged into a partial list.
 *
 * @param mixed $raw Raw option or POST.
 * @return array{visible:bool,order:string[]}
 */
function altijara_sanitize_archive_sidebar( $raw ) {
	$allowed = altijara_archive_sidebar_widgets();
	if ( ! is_array( $raw ) ) {
		return altijara_default_archive_sidebar();
	}

	$positions = array();
	if ( isset( $raw['positions'] ) && is_array( $raw['positions'] ) ) {
		foreach ( $raw['positions'] as $key => $pos ) {
			$key = sanitize_key( (string) $key );
			if ( in_array( $key, $allowed, true ) ) {
				$positions[ $key ] = absint( $pos );
			}
		}
	} elseif ( isset( $raw['order'] ) && is_array( $raw['order'] ) ) {
		$n = 1;
		foreach ( $raw['order'] as $key ) {
			$key = sanitize_key( (string) $key );
			if ( in_array( $key, $allowed, true ) && ! isset( $positions[ $key ] ) ) {
				$positions[ $key ] = $n;
				$n++;
			}
		}
	}

	$max = 0;
	foreach ( $positions as $pos ) {
		$max = max( $max, (int) $pos );
	}
	foreach ( $allowed as $key ) {
		if ( ! isset( $positions[ $key ] ) || (int) $positions[ $key ] < 1 ) {
			$max++;
			$positions[ $key ] = $max;
		}
	}

	$ranked = array();
	foreach ( $allowed as $index => $key ) {
		$ranked[] = array(
			'key' => $key,
			'pos' => (int) $positions[ $key ],
			'tie' => $index,
		);
	}
	usort(
		$ranked,
		static function ( $a, $b ) {
			if ( $a['pos'] === $b['pos'] ) {
				return $a['tie'] - $b['tie'];
			}
			return $a['pos'] - $b['pos'];
		}
	);

	$order = array();
	foreach ( $ranked as $row ) {
		$order[] = $row['key'];
	}

	return array(
		'visible' => ! empty( $raw['visible'] ),
		'order'   => $order,
	);
}

/**
 * Category lower column. A missing saved branch keeps today's column.
 *
 * @return array{visible:bool,order:string[]}
 */
function altijara_get_archive_sidebar() {
	$raw = get_option( defined( 'ALTIJARA_OPTION_KEY' ) ? ALTIJARA_OPTION_KEY : 'altijara_theme_settings', array() );
	if ( is_array( $raw ) && array_key_exists( 'archive_sidebar', $raw ) ) {
		return altijara_sanitize_archive_sidebar( $raw['archive_sidebar'] );
	}
	return altijara_default_archive_sidebar();
}

if ( ! defined( 'ALTIJARA_TERM_SIDEBAR_KEY' ) ) {
	define( 'ALTIJARA_TERM_SIDEBAR_KEY', '_altijara_category_sidebar' );
}

/**
 * Saved lower-column override for one category, or null when it follows the site setting.
 *
 * @param int $term_id Category term ID.
 * @return array{visible:bool,order:string[]}|null
 */
function altijara_get_category_sidebar_override( $term_id ) {
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return null;
	}
	$raw = get_term_meta( $term_id, ALTIJARA_TERM_SIDEBAR_KEY, true );
	if ( ! is_array( $raw ) ) {
		return null;
	}
	if ( ! array_key_exists( 'visible', $raw ) && ! array_key_exists( 'order', $raw ) ) {
		return null;
	}
	return altijara_sanitize_archive_sidebar( $raw );
}

/**
 * Lower column for one category. Term meta wins; otherwise the site column is used.
 *
 * @param int $term_id Category term ID.
 * @return array{visible:bool,order:string[]}
 */
function altijara_get_category_archive_sidebar( $term_id ) {
	$override = altijara_get_category_sidebar_override( $term_id );
	if ( is_array( $override ) ) {
		return $override;
	}
	return altijara_get_archive_sidebar();
}

/**
 * Print the category lower column in the saved order.
 *
 * @param WP_Post[]                               $most_read Most-read posts for this category.
 * @param array{visible:bool,order:string[]}|null $sidebar   Resolved column. Null uses the site setting.
 * @param array<string, mixed>                    $heading   Optional. `title` replaces the most-read heading.
 */
function altijara_render_category_lower_column( $most_read, $sidebar = null, $heading = array() ) {
	if ( ! is_array( $sidebar ) ) {
		$sidebar = altijara_get_archive_sidebar();
	} else {
		$sidebar = altijara_sanitize_archive_sidebar( $sidebar );
	}
	if ( empty( $sidebar['visible'] ) ) {
		return;
	}
	$most_read = is_array( $most_read ) ? $most_read : array();
	?>
	<aside class="cat-opinion-col">
		<?php
		foreach ( $sidebar['order'] as $widget ) {
			if ( 'ad' === $widget && function_exists( 'altijara_render_sidebar_ad' ) ) {
				// Use the Advertisement widget creative from Lower sidebar settings (not the theme default).
				$ad_cfg = function_exists( 'altijara_get_sidebar_ad_widget_config' )
					? altijara_get_sidebar_ad_widget_config()
					: array();
				altijara_render_sidebar_ad( $ad_cfg );
			} elseif ( 'magazine' === $widget ) {
				get_template_part(
					'template-parts/widgets/magazine-issues',
					'slider',
					array(
						'items' => function_exists( 'altijara_get_magazine_slider_items' )
							? altijara_get_magazine_slider_items( 8 )
							: array(),
					)
				);
			} elseif ( 'most_read' === $widget ) {
				?>
				<div class="cat-most-read-widget">
					<div class="cat-section-bar cat-mr-bar">
						<h3 class="cat-section-title"><?php
						if ( is_array( $heading ) && isset( $heading['title'] ) && '' !== (string) $heading['title'] ) {
							echo esc_html( (string) $heading['title'] );
						} else {
							esc_html_e( 'الأكثر قراءة', 'altijara' );
						}
						?></h3>
					</div>
					<?php
					$most_read_posts = array();
					foreach ( $most_read as $item ) {
						if ( $item instanceof WP_Post ) {
							$most_read_posts[] = $item;
						}
					}
					?>
					<?php if ( $most_read_posts ) : ?>
					<ol class="cat-mr-list" id="catMrList">
						<?php foreach ( $most_read_posts as $idx => $item ) : ?>
							<li class="cat-mr-item">
								<span class="cat-mr-num"><?php echo esc_html( (string) ( $idx + 1 ) ); ?></span>
								<div class="cat-mr-content">
									<h4 class="cat-mr-headline">
										<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
									</h4>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>
					<?php else : ?>
					<article class="cat-hero-card cat-mr-empty">
						<div class="cat-hero-info">
							<h2 class="cat-hero-headline"><?php esc_html_e( 'لا مقالات في هذا القسم بعد', 'altijara' ); ?></h2>
						</div>
					</article>
					<?php endif; ?>
				</div>
				<?php
			}
		}
		?>
	</aside>
	<?php
}

/**
 * Stored magazine-assignment category IDs.
 *
 * Read-only. Category URLs no longer use this list. Legacy archive_layouts is
 * read only when archive_magazine_ids was never saved.
 *
 * @return int[]
 */
function altijara_get_archive_magazine_ids() {
	$raw = get_option( defined( 'ALTIJARA_OPTION_KEY' ) ? ALTIJARA_OPTION_KEY : 'altijara_theme_settings', array() );
	if ( is_array( $raw ) && array_key_exists( 'archive_magazine_ids', $raw ) && is_array( $raw['archive_magazine_ids'] ) ) {
		return array_values( array_unique( array_filter( array_map( 'absint', $raw['archive_magazine_ids'] ) ) ) );
	}
	// Legacy: term_id => issues in archive_layouts.
	$map = altijara_get_option( 'archive_layouts', array() );
	$ids = array();
	if ( is_array( $map ) ) {
		foreach ( $map as $tid => $layout ) {
			if ( 'issues' === $layout ) {
				$tid = absint( $tid );
				if ( $tid ) {
					$ids[] = $tid;
				}
			}
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Build archive_layouts map from magazine ID list (BC for older callers).
 *
 * @param int[] $magazine_ids Term IDs.
 * @return array<string, string>
 */
function altijara_archive_layouts_from_magazine_ids( $magazine_ids ) {
	$out = array();
	foreach ( (array) $magazine_ids as $tid ) {
		$tid = absint( $tid );
		if ( $tid ) {
			$out[ (string) $tid ] = 'issues';
		}
	}
	return $out;
}

/**
 * Category URLs always use the posts archive.
 *
 * Stored magazine-assignment IDs are not applied.
 *
 * @param int $term_id Category term ID. Unused; kept for existing callers.
 * @return string
 */
function altijara_get_category_archive_layout( $term_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return 'posts';
}

/**
 * Sanitize list of category term IDs that use full-width hero (no side recommended column).
 *
 * @param mixed $raw Raw POST (array of term IDs).
 * @return int[]
 */
function altijara_sanitize_archive_full_hero( $raw ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $tid ) {
		$tid = absint( $tid );
		if ( $tid ) {
			$out[] = $tid;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Category term IDs using hero-only top layout.
 * Until saved from Archives tab, falls back to legacy keys: automotive, interviews, tourism, exhibitions.
 *
 * @return int[]
 */
function altijara_get_full_hero_category_ids() {
	$raw = get_option( defined( 'ALTIJARA_OPTION_KEY' ) ? ALTIJARA_OPTION_KEY : 'altijara_theme_settings', array() );
	if ( is_array( $raw ) && array_key_exists( 'archive_full_hero', $raw ) && is_array( $raw['archive_full_hero'] ) ) {
		// Explicit save (including empty) wins over legacy defaults.
		return array_values( array_unique( array_filter( array_map( 'absint', $raw['archive_full_hero'] ) ) ) );
	}

	$legacy_keys = array( 'automotive', 'interviews', 'tourism', 'exhibitions' );
	$ids         = array();
	foreach ( $legacy_keys as $key ) {
		$id = function_exists( 'altijara_resolve_category_id' ) ? altijara_resolve_category_id( $key ) : 0;
		if ( $id ) {
			$ids[] = (int) $id;
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Whether a category archive should show a single full hero (no side list).
 *
 * @param int $term_id Category term ID.
 * @return bool
 */
function altijara_category_uses_full_hero( $term_id ) {
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return false;
	}
	return in_array( $term_id, altijara_get_full_hero_category_ids(), true );
}

/**
 * Whether the current request is the magazine issues archive.
 *
 * Category URLs are not included, even when older settings assigned them here.
 *
 * @return bool
 */
function altijara_is_issues_archive_view() {
	return is_post_type_archive( 'issue' ) || is_page_template( 'page-issues.php' );
}
