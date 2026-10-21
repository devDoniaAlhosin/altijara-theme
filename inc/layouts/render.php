<?php
/**
 * Render layout rows (body pairing logic shared with homepage).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render ordered body sections from a layout pack (uses section override).
 *
 * @param array{sections?:array,order?:array} $pack Layout pack.
 */
function altijara_render_layout_rows( $pack ) {
	$pack     = altijara_normalize_layout_pack( $pack );
	$sections = $pack['sections'];
	$order    = $pack['order'];
	if ( empty( $sections ) || empty( $order ) ) {
		return;
	}

	altijara_with_layout_sections(
		$sections,
		static function () use ( $order ) {
			altijara_render_layout_body_ids( $order );
		}
	);
}

/**
 * Body render loop for an explicit ID list (pairing-aware).
 *
 * @param string[] $ids Section IDs in order.
 */
function altijara_render_layout_body_ids( array $ids ) {
	$ids  = array_values( array_filter( array_map( 'sanitize_key', $ids ) ) );
	$skip = array();

	foreach ( $ids as $id ) {
		if ( isset( $skip[ $id ] ) ) {
			continue;
		}
		$cfg = altijara_get_section_config( $id );
		// Hero slots belong in the hero grid only — never re-render as body modules.
		if ( function_exists( 'altijara_layout_section_is_hero' ) && altijara_layout_section_is_hero( $id, $cfg ) ) {
			continue;
		}
		if ( ! empty( $cfg['zone'] ) && 'hero' === $cfg['zone'] ) {
			continue;
		}
		$widget = $cfg['widget'] ?? '';
		$pair   = ! empty( $cfg['pair_with'] ) ? sanitize_key( $cfg['pair_with'] ) : '';

		if ( 'split_sector' === $widget && $pair && in_array( $pair, $ids, true ) && ! isset( $skip[ $pair ] ) ) {
			$skip[ $id ]   = true;
			$skip[ $pair ] = true;
			if ( ! altijara_section_is_visible( $id ) && ! altijara_section_is_visible( $pair ) ) {
				continue;
			}
			$left  = $id;
			$right = $pair;
			if ( 'tourism' === $id && 'health' === $pair ) {
				$left  = 'health';
				$right = 'tourism';
			} elseif ( 'energy' === $id && 'business' === $pair ) {
				$left  = 'business';
				$right = 'energy';
			}
			echo '<section class="home-split-sectors-section"><div class="home-split-grid">';
			altijara_render_home_section( $left );
			altijara_render_home_section( $right );
			echo '</div></section>';
			continue;
		}

		$variant = $cfg['variant'] ?? 'default';
		if ( 'article_grid' === $widget && 'cars_row' === $variant && $pair && in_array( $pair, $ids, true ) ) {
			$skip[ $id ]   = true;
			$skip[ $pair ] = true;
			$show_self     = altijara_section_is_visible( $id );
			$show_pair     = altijara_section_is_visible( $pair );
			if ( ! $show_self && ! $show_pair ) {
				continue;
			}
			$pair_cfg = altijara_get_section_config( $pair );
			$most_id  = ( 'most_read' === ( $pair_cfg['widget'] ?? '' ) ) ? $pair : $id;
			$tech_id  = ( $most_id === $id ) ? $pair : $id;
			$tech_cfg = altijara_get_section_config( $tech_id );
			if ( altijara_section_is_visible( $tech_id ) ) {
				echo '<div class="section-bar-wrapper" data-section-bar="' . esc_attr( $tech_id ) . '"><div class="section-bar-badge-right"><span class="pill-badge">' . esc_html( $tech_cfg['label'] ) . '</span></div></div>';
			}
			echo '<section class="cars-section"><div class="cars-grid">';
			altijara_render_home_section( $most_id );
			altijara_render_home_section( $tech_id );
			echo '</div></section>';
			continue;
		}

		if ( 'most_read' === $widget && $pair && in_array( $pair, $ids, true ) && ! isset( $skip[ $pair ] ) ) {
			$pair_cfg = altijara_get_section_config( $pair );
			if ( 'article_grid' === ( $pair_cfg['widget'] ?? '' ) && 'cars_row' === ( $pair_cfg['variant'] ?? '' ) ) {
				$skip[ $id ]   = true;
				$skip[ $pair ] = true;
				if ( ! altijara_section_is_visible( $id ) && ! altijara_section_is_visible( $pair ) ) {
					continue;
				}
				if ( altijara_section_is_visible( $pair ) ) {
					echo '<div class="section-bar-wrapper" data-section-bar="' . esc_attr( $pair ) . '"><div class="section-bar-badge-right"><span class="pill-badge">' . esc_html( $pair_cfg['label'] ) . '</span></div></div>';
				}
				echo '<section class="cars-section"><div class="cars-grid">';
				altijara_render_home_section( $id );
				altijara_render_home_section( $pair );
				echo '</div></section>';
				continue;
			}
		}

		altijara_render_home_section( $id );
	}
}

/**
 * Render the standard homepage hero (skips empty columns).
 */
function altijara_render_home_hero_grid() {
	$show_chamber     = function_exists( 'altijara_section_is_visible' ) && altijara_section_is_visible( 'hero_chamber' );
	$show_magazine    = function_exists( 'altijara_section_is_visible' ) && altijara_section_is_visible( 'hero_magazine' );
	$show_interviews  = function_exists( 'altijara_section_is_visible' ) && altijara_section_is_visible( 'hero_interviews' );
	$show_personality = function_exists( 'altijara_section_is_visible' ) && altijara_section_is_visible( 'hero_personality' );
	$show_logistics   = function_exists( 'altijara_section_is_visible' ) && altijara_section_is_visible( 'hero_logistics' );
	$show_center      = $show_magazine || $show_interviews;
	$show_lead        = $show_personality || $show_logistics;

	if ( ! $show_chamber && ! $show_center && ! $show_lead ) {
		return;
	}
	?>
	<section class="hero-section">
		<div class="hero-grid">
			<?php if ( $show_chamber ) : ?>
				<?php altijara_render_home_section( 'hero_chamber' ); ?>
			<?php endif; ?>
			<?php if ( $show_center ) : ?>
				<div class="hero-col-center">
					<?php
					altijara_render_home_section( 'hero_magazine' );
					altijara_render_home_section( 'hero_interviews' );
					?>
				</div>
			<?php endif; ?>
			<?php if ( $show_lead ) : ?>
				<div class="hero-col-lead">
					<?php
					altijara_render_home_section( 'hero_personality' );
					altijara_render_home_section( 'hero_logistics' );
					?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Home hero grid from layout (uses known hero IDs when present).
 *
 * @param array<string, array<string, mixed>> $sections Sections map.
 */
function altijara_render_layout_home_hero( array $sections ) {
	$hero_ids = function_exists( 'altijara_hero_section_ids' ) ? altijara_hero_section_ids() : array();
	$has_std  = true;
	foreach ( $hero_ids as $hid ) {
		if ( empty( $sections[ $hid ] ) ) {
			$has_std = false;
			break;
		}
	}

	altijara_with_layout_sections(
		$sections,
		static function () use ( $has_std, $hero_ids, $sections ) {
			if ( $has_std ) {
				altijara_render_home_hero_grid();
				return;
			}
			// Fallback: render any hero-zone rows as a flex row (siblings expand).
			$hero_order = array();
			foreach ( $sections as $id => $row ) {
				if ( ! empty( $row['zone'] ) && 'hero' === $row['zone'] ) {
					$hero_order[] = $id;
				} elseif ( in_array( $id, $hero_ids, true ) ) {
					$hero_order[] = $id;
				}
			}
			$hero_order = array_values(
				array_filter(
					$hero_order,
					static function ( $hid ) {
						return function_exists( 'altijara_section_is_visible' ) && altijara_section_is_visible( $hid );
					}
				)
			);
			if ( $hero_order ) {
				echo '<section class="hero-section"><div class="hero-grid hero-grid--stack">';
				foreach ( $hero_order as $hid ) {
					echo '<div class="hero-grid-item">';
					altijara_render_home_section( $hid );
					echo '</div>';
				}
				echo '</div></section>';
			}
		}
	);
}
