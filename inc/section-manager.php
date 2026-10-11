<?php
/**
 * Homepage Section Manager — fixed slots + widget instances.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical homepage section IDs (12 matrix + most_read companion for Tech).
 *
 * Most Read sits beside Technology in the prototype (cars-section). It is a
 * separate configurable instance of the most_read widget, not a redesign.
 *
 * @return array<string, array<string, mixed>>
 */
function altijara_default_sections() {
	return array(
		'hero_chamber'     => array(
			'widget'       => 'chamber_column',
			'layout'       => 'chamber_column',
			'label'        => 'هيرو — أخبار الغرفة',
			'visible'      => true,
			'category'     => 'chamber',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 7,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'hero_magazine'    => array(
			'widget'       => 'magazine_slider',
			'layout'       => 'magazine_slider',
			'label'        => 'هيرو — غلاف المجلة',
			'visible'      => true,
			'category'     => 'personality',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 4,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'issues',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'hero_interviews'  => array(
			'widget'       => 'article_list',
			'layout'       => 'interviews_column',
			'label'        => 'هيرو — لقاءات',
			'visible'      => true,
			'category'     => 'interviews',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 3,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'hero_personality' => array(
			'widget'       => 'hero_featured',
			'layout'       => 'personality_card',
			'label'        => 'هيرو — شخصية الغلاف',
			'visible'      => true,
			'category'     => 'personality',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 1,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'hero_logistics'   => array(
			'widget'       => 'lead_stack',
			'layout'       => 'logistics_stack',
			'label'        => 'هيرو — لوجستيات',
			'visible'      => true,
			'category'     => 'logistics',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 3,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'followups'        => array(
			'widget'       => 'article_grid',
			'layout'       => 'news_grid',
			'label'        => 'متابعات',
			'visible'      => true,
			'category'     => 'followups',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 4,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'business'         => array(
			'widget'       => 'split_sector',
			'layout'       => 'split_sector',
			'label'        => 'مال وأعمال',
			'visible'      => true,
			'category'     => 'business',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 3,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'energy'           => array(
			'widget'       => 'split_sector',
			'layout'       => 'split_sector',
			'label'        => 'الطاقة',
			'visible'      => true,
			'category'     => 'energy',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 3,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'automotive'       => array(
			'widget'       => 'article_grid',
			'layout'       => 'news_grid',
			'label'        => 'سيارات',
			'visible'      => true,
			'category'     => 'automotive',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 4,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'tech'             => array(
			'widget'       => 'article_grid',
			'layout'       => 'news_grid',
			'label'        => 'تقنية',
			'visible'      => true,
			'category'     => 'tech',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 3,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'cars_row',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'most_read'        => array(
			'widget'       => 'most_read',
			'layout'       => 'most_read',
			'label'        => 'الأكثر قراءة في قطاع الأعمال',
			'visible'      => true,
			'category'     => '',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 5,
			'offset'       => 0,
			'orderby'      => 'comment_count',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'month',
		),
		'health'           => array(
			'widget'       => 'split_sector',
			'layout'       => 'split_sector',
			'label'        => 'صحة',
			'visible'      => true,
			'category'     => 'health',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 3,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
		'tourism'          => array(
			'widget'       => 'split_sector',
			'layout'       => 'split_sector',
			'label'        => 'سياحة وترفيه',
			'visible'      => true,
			'category'     => 'tourism',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 3,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		),
	);
}

/**
 * Normalize a section row: migrate layout → widget, fill defaults.
 *
 * @param string               $id   Section ID.
 * @param array<string, mixed> $row  Saved or default row.
 * @param array<string, mixed> $def  Defaults for ID.
 * @return array<string, mixed>
 */
function altijara_normalize_section_config( $id, $row, $def = array() ) {
	if ( ! is_array( $row ) ) {
		$row = array();
	}
	if ( ! is_array( $def ) ) {
		$def = array();
	}
	$merged = array_merge( $def, $row );

	if ( empty( $merged['widget'] ) ) {
		$merged['widget'] = altijara_layout_to_widget( $merged['layout'] ?? 'news_grid' );
	} else {
		$merged['widget'] = sanitize_key( $merged['widget'] );
		if ( ! altijara_get_widget_definition( $merged['widget'] ) ) {
			$merged['widget'] = altijara_layout_to_widget( $merged['layout'] ?? 'news_grid' );
		}
	}

	// Keep legacy layout in sync for older callers / saves.
	$merged['layout'] = altijara_widget_to_layout( $merged['widget'] );

	if ( ! empty( $merged['manual_posts'] ) && ! is_array( $merged['manual_posts'] ) ) {
		$merged['manual_posts'] = array_values(
			array_filter( array_map( 'absint', preg_split( '/[\s,]+/', (string) $merged['manual_posts'] ) ?: array() ) )
		);
	}
	if ( empty( $merged['manual_posts'] ) || ! is_array( $merged['manual_posts'] ) ) {
		$merged['manual_posts'] = array();
	}

	if ( ! empty( $merged['issue_ids'] ) && ! is_array( $merged['issue_ids'] ) ) {
		$merged['issue_ids'] = array_values(
			array_filter( array_map( 'absint', preg_split( '/[\s,]+/', (string) $merged['issue_ids'] ) ?: array() ) )
		);
	}
	if ( empty( $merged['issue_ids'] ) || ! is_array( $merged['issue_ids'] ) ) {
		$merged['issue_ids'] = array();
	}

	if ( empty( $merged['variant'] ) ) {
		$merged['variant'] = ( 'tech' === $id ) ? 'cars_row' : 'default';
	}

	if ( empty( $merged['view_period'] ) ) {
		$merged['view_period'] = 'all';
	}
	if ( empty( $merged['source'] ) ) {
		$merged['source'] = ( 'magazine_slider' === $merged['widget'] ) ? 'issues' : 'posts';
	}

	return $merged;
}

/**
 * @return array<string, array<string, mixed>>
 */
function altijara_get_sections() {
	$saved = altijara_get_option( 'sections', array() );
	$out   = altijara_default_sections();
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	foreach ( $out as $id => $defaults ) {
		$row        = isset( $saved[ $id ] ) && is_array( $saved[ $id ] ) ? $saved[ $id ] : array();
		$out[ $id ] = altijara_normalize_section_config( $id, $row, $defaults );
	}
	return $out;
}

/**
 * @param string $section_key Section ID.
 * @return array<string, mixed>
 */
function altijara_get_section_config( $section_key ) {
	$sections = altijara_get_sections();
	$key      = sanitize_key( $section_key );
	if ( isset( $sections[ $key ] ) ) {
		return $sections[ $key ];
	}
	return altijara_normalize_section_config(
		$key,
		array(),
		array(
			'widget'       => 'article_grid',
			'layout'       => 'news_grid',
			'label'        => $key,
			'visible'      => true,
			'category'     => $key,
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 6,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		)
	);
}

/**
 * Sanitize posted section settings (widget-aware).
 *
 * @param array<string, mixed> $raw Raw POST sections.
 * @return array<string, array<string, mixed>>
 */
function altijara_sanitize_sections_input( $raw ) {
	$defaults = altijara_default_sections();
	$widgets  = array_keys( altijara_get_widget_registry() );
	$out      = array();

	foreach ( $defaults as $id => $def ) {
		$row = isset( $raw[ $id ] ) && is_array( $raw[ $id ] ) ? $raw[ $id ] : array();

		$widget = isset( $row['widget'] ) ? sanitize_key( $row['widget'] ) : ( $def['widget'] ?? '' );
		if ( ! in_array( $widget, $widgets, true ) ) {
			$widget = ! empty( $row['layout'] ) ? altijara_layout_to_widget( $row['layout'] ) : $def['widget'];
		}
		if ( ! in_array( $widget, $widgets, true ) ) {
			$widget = $def['widget'];
		}

		$manual_raw = isset( $row['manual_posts'] ) ? (string) $row['manual_posts'] : '';
		$manual_ids = array_values(
			array_filter(
				array_map(
					'absint',
					preg_split( '/[\s,]+/', $manual_raw ) ?: array()
				)
			)
		);

		$issue_raw = isset( $row['issue_ids'] ) ? (string) $row['issue_ids'] : '';
		$issue_ids = array_values(
			array_filter(
				array_map(
					'absint',
					preg_split( '/[\s,]+/', $issue_raw ) ?: array()
				)
			)
		);

		$orderby = isset( $row['orderby'] ) ? sanitize_key( $row['orderby'] ) : 'date';
		if ( ! in_array( $orderby, array( 'date', 'modified', 'title', 'rand', 'menu_order', 'comment_count' ), true ) ) {
			$orderby = 'date';
		}

		$variant = isset( $row['variant'] ) ? sanitize_key( $row['variant'] ) : ( $def['variant'] ?? 'default' );
		$wdef    = altijara_get_widget_definition( $widget );
		if ( $wdef && ! empty( $wdef['variants'] ) && ! isset( $wdef['variants'][ $variant ] ) ) {
			$variant = 'default';
		}

		$view_period = isset( $row['view_period'] ) ? sanitize_key( $row['view_period'] ) : 'all';
		if ( ! in_array( $view_period, array( 'day', 'week', 'month', 'year', 'all' ), true ) ) {
			$view_period = 'all';
		}

		$source = isset( $row['source'] ) ? sanitize_key( $row['source'] ) : ( $def['source'] ?? 'posts' );
		if ( ! in_array( $source, array( 'issues', 'posts' ), true ) ) {
			$source = 'posts';
		}

		$out[ $id ] = array(
			'widget'       => $widget,
			'layout'       => altijara_widget_to_layout( $widget ),
			'label'        => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : $def['label'],
			'visible'      => ! empty( $row['visible'] ),
			'category'     => isset( $row['category'] ) ? sanitize_text_field( $row['category'] ) : $def['category'],
			'tag'          => isset( $row['tag'] ) ? absint( $row['tag'] ) : 0,
			'manual_posts' => $manual_ids,
			'count'        => isset( $row['count'] ) ? max( 1, min( 20, (int) $row['count'] ) ) : (int) $def['count'],
			'offset'       => isset( $row['offset'] ) ? max( 0, min( 100, (int) $row['offset'] ) ) : 0,
			'orderby'      => $orderby,
			'order'        => ( isset( $row['order'] ) && 'ASC' === strtoupper( (string) $row['order'] ) ) ? 'ASC' : 'DESC',
			'variant'      => $variant,
			'source'       => $source,
			'issue_ids'    => $issue_ids,
			'view_period'  => $view_period,
		);
	}

	return $out;
}

/**
 * Categories for admin dropdown.
 *
 * @return array<string, string>
 */
function altijara_get_category_choices() {
	$terms   = get_categories(
		array(
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	$choices = array( '' => '— بدون —' );
	$map     = altijara_get_option( 'category_map', array() );
	if ( is_array( $map ) ) {
		foreach ( $map as $key => $slug ) {
			$choices[ (string) $key ] = sprintf( 'مفتاح: %s → %s', $key, $slug );
		}
	}
	foreach ( $terms as $term ) {
		$choices[ $term->slug ]             = $term->name . ' (slug: ' . $term->slug . ')';
		$choices[ (string) $term->term_id ] = $term->name . ' (ID: ' . $term->term_id . ')';
	}
	return $choices;
}

/**
 * Tags for admin dropdown.
 *
 * @return array<int|string, string>
 */
function altijara_get_tag_choices() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
			'orderby'    => 'name',
			'number'     => 200,
		)
	);
	$choices = array( 0 => '— بدون —' );
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return $choices;
	}
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$choices[ (int) $term->term_id ] = $term->name . ' (ID: ' . $term->term_id . ')';
	}
	return $choices;
}

/**
 * Resolve titles for stored post IDs (admin chips).
 *
 * @param int[] $ids Post IDs.
 * @return array<int, string>
 */
function altijara_get_post_titles_map( $ids ) {
	$map = array();
	foreach ( array_filter( array_map( 'absint', (array) $ids ) ) as $pid ) {
		$title = get_the_title( $pid );
		$map[ $pid ] = $title ? $title : '#' . $pid;
	}
	return $map;
}

/**
 * Render one section card in admin (widget-specific fields).
 *
 * @param string               $id      Section ID.
 * @param array<string, mixed> $section Config.
 */
function altijara_render_section_admin_card( $id, $section ) {
	$widget  = ! empty( $section['widget'] ) ? $section['widget'] : altijara_layout_to_widget( $section['layout'] ?? '' );
	$wdef    = altijara_get_widget_definition( $widget );
	$choices = altijara_get_category_choices();
	$tags    = altijara_get_tag_choices();
	$manual  = ! empty( $section['manual_posts'] ) ? implode( ',', array_map( 'absint', (array) $section['manual_posts'] ) ) : '';
	$issues  = ! empty( $section['issue_ids'] ) ? implode( ',', array_map( 'absint', (array) $section['issue_ids'] ) ) : '';
	$field   = 'altijara_sections[' . esc_attr( $id ) . ']';
	$titles  = altijara_get_post_titles_map( $section['manual_posts'] ?? array() );
	$issue_t = altijara_get_post_titles_map( $section['issue_ids'] ?? array() );
	?>
	<details class="altijara-section-card" data-widget="<?php echo esc_attr( $widget ); ?>" <?php echo ! empty( $section['visible'] ) ? 'open' : ''; ?>>
		<summary>
			<strong><?php echo esc_html( $section['label'] ); ?></strong>
			<code><?php echo esc_html( $id ); ?></code>
			<span class="altijara-widget-badge"><?php echo esc_html( $wdef ? $wdef['label'] : $widget ); ?></span>
			<span class="altijara-section-status"><?php echo ! empty( $section['visible'] ) ? 'ظاهر' : 'مخفي'; ?></span>
		</summary>
		<div class="altijara-section-body">
			<input type="hidden" name="<?php echo esc_attr( $field ); ?>[widget]" value="<?php echo esc_attr( $widget ); ?>">

			<p>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $field ); ?>[visible]" value="1" <?php checked( ! empty( $section['visible'] ) ); ?>>
					<?php esc_html_e( 'إظهار القسم', 'altijara' ); ?>
				</label>
			</p>

			<?php if ( altijara_widget_supports( $widget, 'label' ) ) : ?>
				<p>
					<label><?php esc_html_e( 'العنوان (عربي)', 'altijara' ); ?></label><br>
					<input type="text" class="regular-text" name="<?php echo esc_attr( $field ); ?>[label]" value="<?php echo esc_attr( $section['label'] ); ?>">
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'category' ) ) : ?>
				<p>
					<label><?php esc_html_e( 'التصنيف', 'altijara' ); ?></label><br>
					<select name="<?php echo esc_attr( $field ); ?>[category]" class="regular-text">
						<?php foreach ( $choices as $val => $lab ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( (string) $section['category'], (string) $val ); ?>>
								<?php echo esc_html( $lab ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'tag' ) ) : ?>
				<p>
					<label><?php esc_html_e( 'وسم', 'altijara' ); ?></label><br>
					<select name="<?php echo esc_attr( $field ); ?>[tag]" class="regular-text">
						<?php foreach ( $tags as $val => $lab ) : ?>
							<option value="<?php echo esc_attr( (string) $val ); ?>" <?php selected( (int) $section['tag'], (int) $val ); ?>>
								<?php echo esc_html( $lab ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'source' ) ) : ?>
				<p>
					<label><?php esc_html_e( 'مصدر الشرائح', 'altijara' ); ?></label><br>
					<select name="<?php echo esc_attr( $field ); ?>[source]">
						<option value="issues" <?php selected( $section['source'] ?? '', 'issues' ); ?>><?php esc_html_e( 'أعداد المجلة (issue)', 'altijara' ); ?></option>
						<option value="posts" <?php selected( $section['source'] ?? '', 'posts' ); ?>><?php esc_html_e( 'مقالات', 'altijara' ); ?></option>
					</select>
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'issue_ids' ) ) : ?>
				<div class="altijara-post-picker" data-post-type="issue" data-field="<?php echo esc_attr( $field ); ?>[issue_ids]">
					<label><?php esc_html_e( 'أعداد محددة (بحث)', 'altijara' ); ?></label>
					<input type="hidden" class="altijara-picker-ids" name="<?php echo esc_attr( $field ); ?>[issue_ids]" value="<?php echo esc_attr( $issues ); ?>">
					<input type="search" class="altijara-picker-search regular-text" placeholder="<?php esc_attr_e( 'ابحث عن عدد…', 'altijara' ); ?>" autocomplete="off">
					<ul class="altijara-picker-results" hidden></ul>
					<ul class="altijara-picker-selected">
						<?php foreach ( $issue_t as $pid => $title ) : ?>
							<li data-id="<?php echo esc_attr( (string) $pid ); ?>">
								<span><?php echo esc_html( $title ); ?></span>
								<button type="button" class="button-link-delete altijara-picker-remove">&times;</button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'manual_posts' ) ) : ?>
				<div class="altijara-post-picker" data-post-type="<?php echo 'magazine_slider' === $widget ? 'any' : 'post'; ?>" data-field="<?php echo esc_attr( $field ); ?>[manual_posts]">
					<label><?php esc_html_e( 'مقالات يدوية (بحث AJAX)', 'altijara' ); ?></label>
					<input type="hidden" class="altijara-picker-ids" name="<?php echo esc_attr( $field ); ?>[manual_posts]" value="<?php echo esc_attr( $manual ); ?>">
					<input type="search" class="altijara-picker-search regular-text" placeholder="<?php esc_attr_e( 'ابحث عن مقال بالعنوان…', 'altijara' ); ?>" autocomplete="off">
					<ul class="altijara-picker-results" hidden></ul>
					<ul class="altijara-picker-selected">
						<?php foreach ( $titles as $pid => $title ) : ?>
							<li data-id="<?php echo esc_attr( (string) $pid ); ?>">
								<span><?php echo esc_html( $title ); ?></span>
								<button type="button" class="button-link-delete altijara-picker-remove">&times;</button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<p style="display:flex;gap:16px;flex-wrap:wrap;">
				<?php if ( altijara_widget_supports( $widget, 'count' ) ) : ?>
					<label><?php esc_html_e( 'العدد', 'altijara' ); ?>
						<input type="number" min="1" max="20" name="<?php echo esc_attr( $field ); ?>[count]" value="<?php echo esc_attr( (string) (int) $section['count'] ); ?>" style="width:70px">
					</label>
				<?php endif; ?>
				<?php if ( altijara_widget_supports( $widget, 'offset' ) ) : ?>
					<label><?php esc_html_e( 'الإزاحة', 'altijara' ); ?>
						<input type="number" min="0" max="100" name="<?php echo esc_attr( $field ); ?>[offset]" value="<?php echo esc_attr( (string) (int) $section['offset'] ); ?>" style="width:70px">
					</label>
				<?php endif; ?>
				<?php if ( altijara_widget_supports( $widget, 'orderby' ) ) : ?>
					<label><?php esc_html_e( 'الترتيب حسب', 'altijara' ); ?>
						<select name="<?php echo esc_attr( $field ); ?>[orderby]">
							<?php
							$order_opts = array(
								'date'          => 'التاريخ',
								'modified'      => 'التعديل',
								'title'         => 'العنوان',
								'rand'          => 'عشوائي',
								'comment_count' => 'التعليقات',
							);
							foreach ( $order_opts as $k => $lab ) :
								?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $section['orderby'], $k ); ?>><?php echo esc_html( $lab ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label><?php esc_html_e( 'الاتجاه', 'altijara' ); ?>
						<select name="<?php echo esc_attr( $field ); ?>[order]">
							<option value="DESC" <?php selected( $section['order'], 'DESC' ); ?>>DESC</option>
							<option value="ASC" <?php selected( $section['order'], 'ASC' ); ?>>ASC</option>
						</select>
					</label>
				<?php endif; ?>
			</p>

			<?php if ( altijara_widget_supports( $widget, 'view_period' ) ) : ?>
				<p>
					<label><?php esc_html_e( 'فترة الأكثر قراءة', 'altijara' ); ?></label><br>
					<select name="<?php echo esc_attr( $field ); ?>[view_period]">
						<?php
						foreach ( array(
							'day'   => 'آخر 24 ساعة',
							'week'  => 'آخر أسبوع',
							'month' => 'آخر 30 يوم',
							'year'  => 'آخر سنة',
							'all'   => 'كل الأوقات',
						) as $k => $lab ) :
							?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $section['view_period'] ?? 'all', $k ); ?>><?php echo esc_html( $lab ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'variant' ) && $wdef && ! empty( $wdef['variants'] ) ) : ?>
				<p>
					<label><?php esc_html_e( 'نمط التخطيط', 'altijara' ); ?></label><br>
					<select name="<?php echo esc_attr( $field ); ?>[variant]">
						<?php foreach ( $wdef['variants'] as $vk => $vdef ) : ?>
							<option value="<?php echo esc_attr( $vk ); ?>" <?php selected( $section['variant'] ?? 'default', $vk ); ?>>
								<?php echo esc_html( $vdef['label'] ?? $vk ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'cache' ) ) : ?>
				<p class="description"><?php esc_html_e( 'حفظ الإعدادات يفرّغ كاش الأقسام. استخدم زر «تفريغ كاش الأقسام» بالأسفل عند الحاجة.', 'altijara' ); ?></p>
			<?php endif; ?>
		</div>
	</details>
	<?php
}
