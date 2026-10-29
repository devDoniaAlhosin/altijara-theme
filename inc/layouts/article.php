<?php
/**
 * Single-article sidebar layout.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preset matching the current article sidebar: latest, ad, magazine, most read.
 *
 * @return array{visible:bool,sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_layout_preset_article_sidebar() {
	$settings = function_exists( 'altijara_get_article_settings' ) ? altijara_get_article_settings() : array();
	$latest   = function_exists( 'altijara_category_archive_widget_section' )
		? altijara_category_archive_widget_section( 'article_sidebar_latest', __( 'أحدث الأخبار', 'altijara' ) )
		: array();
	$ad       = function_exists( 'altijara_category_archive_widget_section' )
		? altijara_category_archive_widget_section( 'cat_sidebar_ad', __( 'Advertisement', 'altijara' ) )
		: array();
	$magazine = function_exists( 'altijara_category_archive_widget_section' )
		? altijara_category_archive_widget_section( 'cat_sidebar_magazine', __( 'من إصدارات المجلة', 'altijara' ) )
		: array();
	$most     = function_exists( 'altijara_category_archive_widget_section' )
		? altijara_category_archive_widget_section( 'article_sidebar_most_read', __( 'الأكثر قراءة', 'altijara' ) )
		: array();

	$latest_cfg = isset( $settings['latest'] ) && is_array( $settings['latest'] ) ? $settings['latest'] : array();
	$most_cfg   = isset( $settings['most_read'] ) && is_array( $settings['most_read'] ) ? $settings['most_read'] : array();
	if ( ! empty( $latest_cfg['count'] ) ) {
		$latest['count'] = max( 1, (int) $latest_cfg['count'] );
	}
	if ( ! empty( $most_cfg['count'] ) ) {
		$most['count'] = max( 1, (int) $most_cfg['count'] );
	}
	if ( 'selected' === ( $latest_cfg['source'] ?? '' ) && ! empty( $latest_cfg['category_ids'] ) && 1 === count( (array) $latest_cfg['category_ids'] ) ) {
		$latest['category'] = (string) absint( $latest_cfg['category_ids'][0] );
	}
	if ( 'selected' === ( $most_cfg['source'] ?? '' ) && ! empty( $most_cfg['category_ids'] ) && 1 === count( (array) $most_cfg['category_ids'] ) ) {
		$most['category'] = (string) absint( $most_cfg['category_ids'][0] );
	}

	$sections = array(
		'article_side_latest'   => $latest,
		'article_side_ad'       => $ad,
		'article_side_magazine' => $magazine,
		'article_side_most'     => $most,
	);

	return array(
		'visible'  => true,
		'sections' => $sections,
		'order'    => array_keys( $sections ),
	);
}

/**
 * Saved article sidebar, or the preset.
 *
 * @return array{visible:bool,sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_get_article_sidebar_layout() {
	$raw = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'article_sidebar_layout', null ) : null;
	if ( function_exists( 'altijara_layout_pack_is_stored' ) && altijara_layout_pack_is_stored( $raw ) ) {
		if ( ! isset( $raw['visible'] ) ) {
			$raw['visible'] = true;
		}
		if ( empty( $raw['order'] ) || ! is_array( $raw['order'] ) ) {
			$raw['order'] = is_array( $raw['sections'] ) ? array_keys( $raw['sections'] ) : array();
		}
		if ( ! is_array( $raw['sections'] ) ) {
			$raw['sections'] = array();
		}
		return $raw;
	}
	return altijara_layout_preset_article_sidebar();
}

/**
 * Post tags bar at the end of the article body.
 *
 * @param int $post_id Current article.
 */
function altijara_render_article_tags( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : (int) get_the_ID();
	if ( ! $post_id ) {
		return;
	}
	$tags = get_the_tags( $post_id );
	if ( empty( $tags ) || is_wp_error( $tags ) ) {
		return;
	}
	?>
	<div class="article-tags-bar" aria-label="<?php esc_attr_e( 'الوسوم', 'altijara' ); ?>">
		<span class="tags-label"><?php esc_html_e( 'الوسوم:', 'altijara' ); ?></span>
		<?php foreach ( $tags as $tag ) : ?>
			<?php if ( ! $tag instanceof WP_Term ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<a class="article-tag-pill" href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" rel="tag"><?php echo esc_html( $tag->name ); ?></a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Print the single-article sidebar from the Article screen layout.
 *
 * @param int $post_id Current article.
 */
function altijara_render_article_sidebar( $post_id ) {
	$pack = altijara_get_article_sidebar_layout();
	if ( empty( $pack['visible'] ) || empty( $pack['sections'] ) || empty( $pack['order'] ) ) {
		return;
	}
	if ( ! function_exists( 'altijara_with_layout_context' ) || ! function_exists( 'altijara_with_layout_sections' ) ) {
		return;
	}
	$post_id = absint( $post_id );
	$cat     = function_exists( 'altijara_article_preferred_category_id' )
		? altijara_article_preferred_category_id( $post_id, 'latest' )
		: 0;
	if ( ! $cat ) {
		$cats = $post_id ? wp_get_post_categories( $post_id ) : array();
		$cat  = ! empty( $cats ) ? (int) $cats[0] : 0;
	}
	echo '<aside class="article-sidebar">';
	altijara_with_layout_context(
		array(
			'category'     => $cat,
			'surface'      => 'article',
			'exclude_post' => $post_id,
		),
		static function () use ( $pack ) {
			altijara_with_layout_sections(
				$pack['sections'],
				static function () use ( $pack ) {
					foreach ( $pack['order'] as $id ) {
						$id = sanitize_key( (string) $id );
						if ( $id && function_exists( 'altijara_render_widget_instance' ) ) {
							altijara_render_widget_instance( $id );
						}
					}
				}
			);
		}
	);
	echo '</aside>';
}

/**
 * Default modules under the article. One related block, matching the current page.
 *
 * @return array{visible:bool,sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_layout_preset_article_body() {
	$settings = function_exists( 'altijara_get_article_settings' ) ? altijara_get_article_settings() : array();
	$related  = function_exists( 'altijara_category_archive_widget_section' )
		? altijara_category_archive_widget_section( 'article_related', __( 'اقرأ ايضاً', 'altijara' ) )
		: array(
			'widget'  => 'article_related',
			'layout'  => 'article_related',
			'label'   => 'اقرأ ايضاً',
			'visible' => true,
			'count'   => 2,
		);
	$related['label']   = 'اقرأ ايضاً';
	$related['count']   = isset( $settings['related_count'] ) ? max( 1, min( 12, (int) $settings['related_count'] ) ) : 2;
	$related['visible'] = ! isset( $settings['show_related'] ) || ! empty( $settings['show_related'] );

	return array(
		'visible'  => true,
		'sections' => array(
			'article_body_related' => $related,
		),
		'order'    => array( 'article_body_related' ),
	);
}

/**
 * Saved modules under the article, or the preset.
 *
 * @return array{visible:bool,sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_get_article_body_layout() {
	$raw = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'article_body_layout', null ) : null;
	if ( function_exists( 'altijara_layout_pack_is_stored' ) && altijara_layout_pack_is_stored( $raw ) ) {
		if ( ! isset( $raw['visible'] ) ) {
			$raw['visible'] = true;
		}
		if ( empty( $raw['order'] ) || ! is_array( $raw['order'] ) ) {
			$raw['order'] = is_array( $raw['sections'] ) ? array_keys( $raw['sections'] ) : array();
		}
		if ( ! is_array( $raw['sections'] ) ) {
			$raw['sections'] = array();
		}
		return $raw;
	}
	return altijara_layout_preset_article_body();
}

/**
 * Modules under the article (related posts, and anything added in Article modules).
 *
 * @param int $post_id Current article.
 */
function altijara_render_article_modules( $post_id ) {
	$pack = altijara_get_article_body_layout();
	if ( empty( $pack['visible'] ) || empty( $pack['sections'] ) || empty( $pack['order'] ) ) {
		return;
	}
	if ( ! function_exists( 'altijara_with_layout_context' ) || ! function_exists( 'altijara_with_layout_sections' ) ) {
		return;
	}
	$post_id = absint( $post_id );
	$cat     = function_exists( 'altijara_article_preferred_category_id' )
		? altijara_article_preferred_category_id( $post_id, 'related' )
		: 0;
	if ( ! $cat ) {
		$cats = $post_id ? wp_get_post_categories( $post_id ) : array();
		$cat  = ! empty( $cats ) ? (int) $cats[0] : 0;
	}
	echo '<div class="article-modules">';
	altijara_with_layout_context(
		array(
			'category'     => $cat,
			'surface'      => 'article',
			'exclude_post' => $post_id,
		),
		static function () use ( $pack ) {
			altijara_with_layout_sections(
				$pack['sections'],
				static function () use ( $pack ) {
					foreach ( $pack['order'] as $id ) {
						$id = sanitize_key( (string) $id );
						if ( ! $id ) {
							continue;
						}
						if ( function_exists( 'altijara_render_spaced_widget' ) ) {
							altijara_render_spaced_widget( $id );
						} elseif ( function_exists( 'altijara_render_widget_instance' ) ) {
							altijara_render_widget_instance( $id );
						}
					}
				}
			);
		}
	);
	echo '</div>';
}
