<?php
/**
 * Homepage sections — front body layout
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function altijara_render_home_body_sections() {
	$ids  = altijara_get_body_section_ids();
	$skip = array();

	foreach ( $ids as $index => $id ) {
		if ( isset( $skip[ $id ] ) ) {
			continue;
		}
		$cfg    = altijara_get_section_config( $id );
		$widget = $cfg['widget'] ?? '';
		$pair   = ! empty( $cfg['pair_with'] ) ? sanitize_key( $cfg['pair_with'] ) : '';

		// Paired split sectors.
		if ( 'split_sector' === $widget && $pair && in_array( $pair, $ids, true ) && ! isset( $skip[ $pair ] ) ) {
			$skip[ $id ]   = true;
			$skip[ $pair ] = true;
			if ( ! altijara_section_is_visible( $id ) && ! altijara_section_is_visible( $pair ) ) {
				continue;
			}
			// Prefer canonical RTL order: health (right) | tourism (left).
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

		// Tech cars_row + most_read companion.
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
			if ( altijara_section_is_visible( $tech_id ) ) {
				echo '<div class="section-footer-more">';
				get_template_part(
					'template-parts/home/section-more',
					null,
					array(
						'url' => altijara_get_section_archive_url( ! empty( $tech_cfg['category'] ) ? $tech_cfg['category'] : 'tech' ),
					)
				);
				echo '</div>';
			}
			continue;
		}

		// Also handle most_read that pairs with tech if tech comes first in loop already skipped.
		if ( 'most_read' === $widget && $pair && in_array( $pair, $ids, true ) && ! isset( $skip[ $pair ] ) ) {
			$pair_cfg = altijara_get_section_config( $pair );
			if ( 'article_grid' === ( $pair_cfg['widget'] ?? '' ) && 'cars_row' === ( $pair_cfg['variant'] ?? '' ) ) {
				$skip[ $id ]   = true;
				$skip[ $pair ] = true;
				$show_self     = altijara_section_is_visible( $id );
				$show_pair     = altijara_section_is_visible( $pair );
				if ( ! $show_self && ! $show_pair ) {
					continue;
				}
				if ( $show_pair ) {
					echo '<div class="section-bar-wrapper" data-section-bar="' . esc_attr( $pair ) . '"><div class="section-bar-badge-right"><span class="pill-badge">' . esc_html( $pair_cfg['label'] ) . '</span></div></div>';
				}
				echo '<section class="cars-section"><div class="cars-grid">';
				altijara_render_home_section( $id );
				altijara_render_home_section( $pair );
				echo '</div></section>';
				if ( $show_pair ) {
					echo '<div class="section-footer-more">';
					get_template_part(
						'template-parts/home/section-more',
						null,
						array(
							'url' => altijara_get_section_archive_url( ! empty( $pair_cfg['category'] ) ? $pair_cfg['category'] : 'tech' ),
						)
					);
					echo '</div>';
				}
				continue;
			}
		}

		altijara_render_home_section( $id );
	}
}
