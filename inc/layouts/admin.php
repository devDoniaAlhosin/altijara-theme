<?php
/**
 * Reusable layout builder admin UI.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Layout silhouette key for a widget (+ variant / paired flag).
 *
 * @param string $widget  Widget id.
 * @param string $variant Variant.
 * @param string $paired  Non-empty when paired sector row.
 * @return string
 */
function altijara_builder_shape_key( $widget, $variant = 'default', $paired = '' ) {
	$widget  = sanitize_key( $widget );
	$variant = sanitize_key( $variant );
	$paired  = is_string( $paired ) ? $paired : ( $paired ? '1' : '' );
	if ( 'article_grid' === $widget && 'cars_row' === $variant && ( 'tech_most_read' === $paired || '1' === $paired ) ) {
		return 'card-row-pair';
	}
	if ( 'article_grid' === $widget && 'cars_row' === $variant ) {
		return 'card-row';
	}
	if ( 'split_sector' === $widget && $paired ) {
		return 'split-pair';
	}
	$map = array(
		'article_grid'    => 'grid-4',
		'split_sector'    => 'sector',
		'article_list'    => 'list',
		'lead_stack'      => 'lead-stack',
		'most_read'       => 'ranked',
		'chamber_column'  => 'chamber',
		'hero_featured'   => 'hero-text',
		'magazine_slider' => 'cover',
		'issues_lead'          => 'issue-lead',
		'issues_carousel'      => 'carousel',
		'cat_archive_hero'     => 'chamber',
		'cat_archive_featured' => 'grid-4',
		'cat_archive_news'     => 'list',
		'cat_archive_lower'    => 'ranked',
		'cat_sidebar_ad'       => 'block',
		'cat_sidebar_magazine' => 'cover',
		'cat_sidebar_most_read'=> 'ranked',
		'article_sidebar_latest'    => 'list',
		'article_sidebar_most_read' => 'ranked',
	);
	return isset( $map[ $widget ] ) ? $map[ $widget ] : 'block';
}

/**
 * Print mini layout shape markup (admin picker / cards).
 *
 * @param string $shape Shape key from altijara_builder_shape_key().
 * @param string $size  lg|sm.
 */
function altijara_render_builder_shape( $shape, $size = 'lg' ) {
	$shape = sanitize_key( $shape );
	$size  = ( 'sm' === $size ) ? 'sm' : 'lg';
	// Always 6 slots; unused ones are hidden per shape in CSS / JS.
	echo '<span class="altijara-el-shape altijara-el-shape--' . esc_attr( $size ) . ' altijara-el-shape--' . esc_attr( $shape ) . '" aria-hidden="true">';
	for ( $i = 1; $i <= 6; $i++ ) {
		echo '<i class="altijara-el-shape__c altijara-el-shape__c--' . esc_attr( (string) $i ) . '"></i>';
	}
	echo '</span>';
}

/**
 * Render one palette element button with its layout shape.
 *
 * @param array<string, mixed> $el Palette item.
 */
function altijara_render_builder_palette_button( array $el ) {
	$widget  = isset( $el['widget'] ) ? (string) $el['widget'] : '';
	$variant = isset( $el['variant'] ) ? (string) $el['variant'] : 'default';
	$paired  = isset( $el['paired'] ) ? (string) $el['paired'] : '';
	$label   = isset( $el['label'] ) ? (string) $el['label'] : $widget;
	$desc    = isset( $el['desc'] ) ? (string) $el['desc'] : '';
	$shape   = altijara_builder_shape_key( $widget, $variant, $paired );
	?>
	<button type="button" class="altijara-builder-element"
		data-widget="<?php echo esc_attr( $widget ); ?>"
		data-variant="<?php echo esc_attr( $variant ); ?>"
		data-label="<?php echo esc_attr( $label ); ?>"
		data-paired="<?php echo esc_attr( $paired ); ?>"
		data-shape="<?php echo esc_attr( $shape ); ?>">
		<?php altijara_render_builder_shape( $shape, 'lg' ); ?>
		<span class="altijara-builder-element__copy">
			<strong><?php echo esc_html( $label ); ?></strong>
			<span><?php echo esc_html( $desc ); ?></span>
		</span>
	</button>
	<?php
}

/**
 * Render layout builder.
 *
 * @param array<string, mixed> $args {
 *   @type string $builder_id   Unique DOM id prefix.
 *   @type string $field_prefix POST field for sections map.
 *   @type string $order_field  POST field for order array name (without []).
 *   @type array  $sections     Sections map.
 *   @type array  $order        Body order.
 *   @type string $surface      home|category|issues.
 *   @type bool   $lock_hero    Keep hero zone.
 *   @type bool   $show_reset   Show reset hint.
 * }
 */
function altijara_render_layout_builder( array $args ) {
	$builder_id   = isset( $args['builder_id'] ) ? sanitize_key( $args['builder_id'] ) : 'altijara_layout';
	$field_prefix = isset( $args['field_prefix'] ) ? (string) $args['field_prefix'] : 'altijara_sections';
	$order_field  = isset( $args['order_field'] ) ? (string) $args['order_field'] : 'altijara_sections_order';
	$sections     = isset( $args['sections'] ) && is_array( $args['sections'] ) ? $args['sections'] : array();
	$order        = isset( $args['order'] ) && is_array( $args['order'] ) ? $args['order'] : array_keys( $sections );
	$surface      = isset( $args['surface'] ) ? sanitize_key( $args['surface'] ) : 'home';
	$lock_hero    = ! empty( $args['lock_hero'] );
	$zone_title   = isset( $args['zone_title'] ) ? (string) $args['zone_title'] : __( 'Modules', 'altijara' );
	$zone_desc    = isset( $args['zone_desc'] ) ? (string) $args['zone_desc'] : __( 'Add, reorder, or remove widgets. Control category, posts, and counts per row.', 'altijara' );
	$palette      = function_exists( 'altijara_layout_palette_for' ) ? altijara_layout_palette_for( $surface ) : array( 'cards' => array(), 'blocks' => array() );
	$list_id      = $builder_id . '_body';
	$modal_id     = $builder_id . '_modal';
	$tpl_id       = $builder_id . '_tpl';

	$hero_ids = $lock_hero && function_exists( 'altijara_hero_section_ids' ) ? altijara_hero_section_ids() : array();
	$body_ids = array();
	foreach ( $order as $oid ) {
		$oid = sanitize_key( $oid );
		if ( ! $oid || in_array( $oid, $hero_ids, true ) ) {
			continue;
		}
		if ( isset( $sections[ $oid ] ) ) {
			$body_ids[] = $oid;
		}
	}
	foreach ( array_keys( $sections ) as $sid ) {
		$sid = sanitize_key( $sid );
		if ( in_array( $sid, $hero_ids, true ) || in_array( $sid, $body_ids, true ) ) {
			continue;
		}
		$body_ids[] = $sid;
	}

	$card_args = array(
		'field_prefix' => $field_prefix,
		'order_field'  => $order_field,
		'surface'      => $surface,
	);
	?>
	<div class="altijara-home-builder altijara-layout-builder"
		data-builder-id="<?php echo esc_attr( $builder_id ); ?>"
		data-field-prefix="<?php echo esc_attr( $field_prefix ); ?>"
		data-order-field="<?php echo esc_attr( $order_field ); ?>"
		data-list-id="<?php echo esc_attr( $list_id ); ?>"
		data-modal-id="<?php echo esc_attr( $modal_id ); ?>"
		data-tpl-id="<?php echo esc_attr( $tpl_id ); ?>">

		<?php if ( $lock_hero && $hero_ids ) : ?>
			<div class="altijara-builder-zone altijara-builder-zone--hero">
				<div class="altijara-builder-zone__head">
					<div class="altijara-builder-zone__head-text">
						<strong><?php esc_html_e( 'Hero grid', 'altijara' ); ?></strong>
						<span class="description"><?php esc_html_e( 'Edit sources and titles. Hero structure stays fixed.', 'altijara' ); ?></span>
					</div>
				</div>
				<div class="altijara-builder-hero-slots">
					<?php foreach ( $hero_ids as $hid ) : ?>
						<?php if ( isset( $sections[ $hid ] ) ) : ?>
							<?php
							altijara_render_section_admin_card(
								$hid,
								$sections[ $hid ],
								array_merge( $card_args, array( 'is_hero' => true ) )
							);
							?>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="altijara-builder-zone altijara-builder-zone--body">
			<div class="altijara-builder-zone__head">
				<div class="altijara-builder-zone__head-text">
					<strong><?php echo esc_html( $zone_title ); ?></strong>
					<span class="description"><?php echo esc_html( $zone_desc ); ?></span>
				</div>
				<button type="button" class="button button-primary altijara-builder-add-open">
					<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Add element', 'altijara' ); ?></span>
				</button>
			</div>
			<div class="altijara-sections-list altijara-body-sortable" id="<?php echo esc_attr( $list_id ); ?>">
				<?php foreach ( $body_ids as $bid ) : ?>
					<?php
					altijara_render_section_admin_card(
						$bid,
						$sections[ $bid ],
						array_merge( $card_args, array( 'is_hero' => false ) )
					);
					?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="altijara-builder-modal" id="<?php echo esc_attr( $modal_id ); ?>" hidden>
		<div class="altijara-builder-modal__backdrop" data-close="1"></div>
		<div class="altijara-builder-modal__dialog" role="dialog" aria-modal="true">
			<div class="altijara-builder-modal__header">
				<strong><?php esc_html_e( 'Add element', 'altijara' ); ?></strong>
				<button type="button" class="altijara-builder-modal-close" data-close="1" aria-label="<?php esc_attr_e( 'Close', 'altijara' ); ?>">
					<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
				</button>
			</div>
			<div class="altijara-builder-tabs">
				<?php if ( ! empty( $palette['cards'] ) ) : ?>
					<button type="button" class="altijara-builder-tab is-active" data-tab="cards"><?php esc_html_e( 'Cards', 'altijara' ); ?></button>
				<?php endif; ?>
				<button type="button" class="altijara-builder-tab <?php echo empty( $palette['cards'] ) ? 'is-active' : ''; ?>" data-tab="blocks"><?php esc_html_e( 'Blocks', 'altijara' ); ?></button>
			</div>
			<?php if ( ! empty( $palette['cards'] ) ) : ?>
				<div class="altijara-builder-panel is-active" data-panel="cards">
					<?php foreach ( $palette['cards'] as $el ) : ?>
						<?php altijara_render_builder_palette_button( $el ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="altijara-builder-panel <?php echo empty( $palette['cards'] ) ? 'is-active' : ''; ?>" data-panel="blocks" <?php echo empty( $palette['cards'] ) ? '' : 'hidden'; ?>>
				<?php foreach ( $palette['blocks'] as $el ) : ?>
					<?php altijara_render_builder_palette_button( $el ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<template id="<?php echo esc_attr( $tpl_id ); ?>">
		<?php
		altijara_render_section_admin_card(
			'__ID__',
			array(
				'zone'         => 'body',
				'widget'       => 'article_grid',
				'layout'       => 'news_grid',
				'label'        => '__LABEL__',
				'visible'      => true,
				'category'     => '',
				'tag'          => 0,
				'manual_posts' => array(),
				'issue_ids'    => array(),
				'count'        => 4,
				'offset'       => 0,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'variant'      => 'default',
				'source'       => 'posts',
				'view_period'  => 'all',
				'pair_with'    => '',
				'band_min'     => 0,
				'band_max'     => 0,
			),
			array_merge( $card_args, array( 'is_hero' => false ) )
		);
		?>
	</template>
	<?php
	if ( in_array( $surface, array( 'category_archive', 'category_sidebar', 'article_sidebar', 'article_body', 'search_sidebar', 'tag_sidebar' ), true ) && function_exists( 'altijara_category_archive_widget_section' ) ) {
		$seen = array();
		foreach ( array( 'cards', 'blocks' ) as $group ) {
			if ( empty( $palette[ $group ] ) || ! is_array( $palette[ $group ] ) ) {
				continue;
			}
			foreach ( $palette[ $group ] as $el ) {
				$wid = isset( $el['widget'] ) ? sanitize_key( (string) $el['widget'] ) : '';
				if ( ! $wid || isset( $seen[ $wid ] ) ) {
					continue;
				}
				$seen[ $wid ] = true;
				$stub_label   = isset( $el['label'] ) ? (string) $el['label'] : $wid;
				echo '<template id="' . esc_attr( $tpl_id . '_' . $wid ) . '">';
				altijara_render_section_admin_card(
					'__ID__',
					altijara_category_archive_widget_section( $wid, $stub_label ),
					array_merge( $card_args, array( 'is_hero' => false ) )
				);
				echo '</template>';
			}
		}
	}
	?>
	<?php
}
