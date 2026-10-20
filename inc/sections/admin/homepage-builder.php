<?php
/**
 * Homepage sections — homepage builder UI
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function altijara_render_homepage_builder() {
	$sections = altijara_get_sections();
	$palette  = altijara_section_element_palette();
	?>
	<div class="altijara-home-builder">
		<div class="altijara-builder-zone altijara-builder-zone--hero">
			<div class="altijara-builder-zone__head">
				<strong><?php esc_html_e( 'Hero grid', 'altijara' ); ?></strong>
				<span class="description"><?php esc_html_e( 'Fixed layout ? edit sources and titles; cannot remove or reorder out of hero.', 'altijara' ); ?></span>
			</div>
			<div class="altijara-builder-hero-slots">
				<?php foreach ( altijara_hero_section_ids() as $hid ) : ?>
					<?php if ( isset( $sections[ $hid ] ) ) : ?>
						<?php altijara_render_section_admin_card( $hid, $sections[ $hid ], array( 'is_hero' => true ) ); ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="altijara-builder-zone altijara-builder-zone--body">
			<div class="altijara-builder-zone__head">
				<strong><?php esc_html_e( 'Body sections', 'altijara' ); ?></strong>
				<span class="description"><?php esc_html_e( 'Drag to reorder. Add Cards or Blocks like a page builder.', 'altijara' ); ?></span>
				<button type="button" class="button button-primary altijara-builder-add-open"><?php esc_html_e( 'Add element', 'altijara' ); ?></button>
			</div>
			<div class="altijara-sections-list altijara-body-sortable" id="altijara-body-sections">
				<?php foreach ( altijara_get_body_section_ids() as $bid ) : ?>
					<?php if ( isset( $sections[ $bid ] ) ) : ?>
						<?php altijara_render_section_admin_card( $bid, $sections[ $bid ], array( 'is_hero' => false ) ); ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="altijara-builder-modal" id="altijara-builder-modal" hidden>
		<div class="altijara-builder-modal__backdrop" data-close="1"></div>
		<div class="altijara-builder-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="altijara-builder-modal-title">
			<div class="altijara-builder-modal__header">
				<strong id="altijara-builder-modal-title"><?php esc_html_e( 'Add element', 'altijara' ); ?></strong>
				<button type="button" class="button-link altijara-builder-modal-close" data-close="1">&times;</button>
			</div>
			<div class="altijara-builder-tabs">
				<button type="button" class="altijara-builder-tab is-active" data-tab="cards"><?php esc_html_e( 'Cards', 'altijara' ); ?></button>
				<button type="button" class="altijara-builder-tab" data-tab="blocks"><?php esc_html_e( 'Blocks', 'altijara' ); ?></button>
			</div>
			<div class="altijara-builder-panel is-active" data-panel="cards">
				<?php foreach ( $palette['cards'] as $el ) : ?>
					<button type="button" class="altijara-builder-element"
						data-widget="<?php echo esc_attr( $el['widget'] ); ?>"
						data-variant="<?php echo esc_attr( $el['variant'] ); ?>"
						data-label="<?php echo esc_attr( $el['label'] ); ?>"
						data-paired="">
						<strong><?php echo esc_html( $el['label'] ); ?></strong>
						<span><?php echo esc_html( $el['desc'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="altijara-builder-panel" data-panel="blocks" hidden>
				<?php foreach ( $palette['blocks'] as $el ) : ?>
					<button type="button" class="altijara-builder-element"
						data-widget="<?php echo esc_attr( $el['widget'] ); ?>"
						data-variant="<?php echo esc_attr( $el['variant'] ); ?>"
						data-label="<?php echo esc_attr( $el['label'] ); ?>"
						data-paired="<?php echo esc_attr( $el['paired'] ?? '' ); ?>">
						<strong><?php echo esc_html( $el['label'] ); ?></strong>
						<span><?php echo esc_html( $el['desc'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<template id="altijara-section-card-template">
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
			),
			array( 'is_hero' => false )
		);
		?>
	</template>
	<?php
}
