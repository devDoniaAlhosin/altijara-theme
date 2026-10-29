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
 * Preferred left/right order for paired split_sector sections.
 *
 * Keys are "id|pair" (order-independent lookup also checks the reverse).
 *
 * @return array<string, array{0:string,1:string}>
 */
function altijara_split_sector_display_order_map() {
	$map = array(
		'tourism|health'  => array( 'health', 'tourism' ),
		'energy|business' => array( 'business', 'energy' ),
	);

	/**
	 * Filter split-sector display order overrides.
	 *
	 * @param array<string, array{0:string,1:string}> $map Map of "a|b" => array( left, right ).
	 */
	return apply_filters( 'altijara_split_sector_display_order_map', $map );
}

/**
 * Resolve left/right section ids for a split_sector pair.
 *
 * @param string $id   First section id.
 * @param string $pair Paired section id.
 * @return array{0:string,1:string} Left, right.
 */
function altijara_resolve_split_sector_sides( $id, $pair ) {
	$id   = sanitize_key( (string) $id );
	$pair = sanitize_key( (string) $pair );
	$map  = altijara_split_sector_display_order_map();
	$key  = $id . '|' . $pair;
	$rev  = $pair . '|' . $id;
	if ( isset( $map[ $key ] ) && is_array( $map[ $key ] ) && 2 === count( $map[ $key ] ) ) {
		return array( sanitize_key( $map[ $key ][0] ), sanitize_key( $map[ $key ][1] ) );
	}
	if ( isset( $map[ $rev ] ) && is_array( $map[ $rev ] ) && 2 === count( $map[ $rev ] ) ) {
		return array( sanitize_key( $map[ $rev ][0] ), sanitize_key( $map[ $rev ][1] ) );
	}
	return array( $id, $pair );
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
			if ( ! altijara_section_occupies_layout( $id ) && ! altijara_section_occupies_layout( $pair ) ) {
				continue;
			}
			list( $left, $right ) = altijara_resolve_split_sector_sides( $id, $pair );
			$split_class = 'home-split-grid';
			if ( ! altijara_section_occupies_layout( $left ) || ! altijara_section_occupies_layout( $right ) ) {
				$split_class .= ' home-split-grid--single';
			}
			echo '<section class="home-split-sectors-section"><div class="' . esc_attr( $split_class ) . '">';
			if ( altijara_section_occupies_layout( $left ) ) {
				altijara_render_home_section( $left );
			}
			if ( altijara_section_occupies_layout( $right ) ) {
				altijara_render_home_section( $right );
			}
			echo '</div></section>';
			continue;
		}

		$variant  = $cfg['variant'] ?? 'default';
		$pair_cfg = $pair ? altijara_get_section_config( $pair ) : array();
		// Pair article_grid + most_read even if cars_row was lost in storage (normalize repairs it).
		$grid_most_pair = $pair && in_array( $pair, $ids, true )
			&& 'article_grid' === $widget
			&& 'most_read' === ( $pair_cfg['widget'] ?? '' );
		if ( $grid_most_pair || ( 'article_grid' === $widget && 'cars_row' === $variant && $pair && in_array( $pair, $ids, true ) ) ) {
			$skip[ $id ]   = true;
			$skip[ $pair ] = true;
			$most_id   = ( 'most_read' === ( $pair_cfg['widget'] ?? '' ) ) ? $pair : $id;
			$tech_id   = ( $most_id === $id ) ? $pair : $id;
			$tech_cfg  = altijara_get_section_config( $tech_id );
			$show_most = altijara_section_occupies_layout( $most_id );
			$show_tech = altijara_section_occupies_layout( $tech_id );
			if ( ! $show_most && ! $show_tech ) {
				continue;
			}
			$tech_label = isset( $tech_cfg['label'] ) ? (string) $tech_cfg['label'] : '';
			if ( $show_tech && '' !== $tech_label ) {
				echo '<div class="section-bar-wrapper" data-section-bar="' . esc_attr( $tech_id ) . '"><div class="section-bar-badge-right"><span class="pill-badge">' . esc_html( $tech_label ) . '</span></div></div>';
			}
			$cars_class = 'cars-grid' . ( ( $show_most && $show_tech ) ? '' : ' cars-grid--single' );
			echo '<section class="cars-section"><div class="' . esc_attr( $cars_class ) . '">';
			if ( $show_most ) {
				altijara_render_home_section( $most_id );
			}
			if ( $show_tech ) {
				altijara_render_home_section( $tech_id );
			}
			echo '</div></section>';
			if ( $show_tech ) {
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

		if ( 'most_read' === $widget && $pair && in_array( $pair, $ids, true ) && ! isset( $skip[ $pair ] ) ) {
			if ( empty( $pair_cfg ) ) {
				$pair_cfg = altijara_get_section_config( $pair );
			}
			if ( 'article_grid' === ( $pair_cfg['widget'] ?? '' ) ) {
				$skip[ $id ]   = true;
				$skip[ $pair ] = true;
				$most_id  = $id;
				$tech_id  = $pair;
				$show_most = altijara_section_occupies_layout( $most_id );
				$show_tech = altijara_section_occupies_layout( $tech_id );
				if ( ! $show_most && ! $show_tech ) {
					continue;
				}
				$pair_label = isset( $pair_cfg['label'] ) ? (string) $pair_cfg['label'] : '';
				if ( $show_tech && '' !== $pair_label ) {
					echo '<div class="section-bar-wrapper" data-section-bar="' . esc_attr( $pair ) . '"><div class="section-bar-badge-right"><span class="pill-badge">' . esc_html( $pair_label ) . '</span></div></div>';
				}
				$cars_class = 'cars-grid' . ( ( $show_most && $show_tech ) ? '' : ' cars-grid--single' );
				echo '<section class="cars-section"><div class="' . esc_attr( $cars_class ) . '">';
				if ( $show_most ) {
					altijara_render_home_section( $most_id );
				}
				if ( $show_tech ) {
					altijara_render_home_section( $tech_id );
				}
				echo '</div></section>';
				if ( $show_tech ) {
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

		if ( function_exists( 'altijara_render_spaced_widget' ) ) {
			altijara_render_spaced_widget( $id );
		} else {
			altijara_render_home_section( $id );
		}
	}
}

/**
 * Render the standard homepage hero.
 * Visible hero columns stay in the grid; empty ones show the shared empty message.
 */
function altijara_render_home_hero_grid() {
	$shown = static function ( $key ) {
		return function_exists( 'altijara_section_is_visible' ) && altijara_section_is_visible( $key );
	};
	$show_chamber     = $shown( 'hero_chamber' );
	$show_magazine    = $shown( 'hero_magazine' );
	$show_interviews  = $shown( 'hero_interviews' );
	$show_personality = $shown( 'hero_personality' );
	$show_logistics   = $shown( 'hero_logistics' );
	// Personality text is the labeled half of the cover composite ("شخصية العدد").
	// Hiding سلايدر الغلاف must not leave that half behind on its own.
	if ( ! $show_magazine ) {
		$show_personality = false;
	}
	$show_center      = $show_magazine || $show_interviews;
	$show_lead        = $show_personality || $show_logistics;

	if ( function_exists( 'altijara_echo_empty_tag_warnings' ) ) {
		altijara_echo_empty_tag_warnings();
	}

	if ( ! $show_chamber && ! $show_center && ! $show_lead ) {
		return;
	}

	$grid_classes = array( 'hero-grid' );
	if ( ! $show_chamber ) {
		$grid_classes[] = 'hero-grid--no-chamber';
	}
	if ( ! $show_center ) {
		$grid_classes[] = 'hero-grid--no-center';
	}
	if ( ! $show_lead ) {
		$grid_classes[] = 'hero-grid--no-lead';
	}
	if ( ! $show_magazine ) {
		$grid_classes[] = 'hero-grid--no-magazine';
	}
	if ( ! $show_personality ) {
		$grid_classes[] = 'hero-grid--no-personality';
	}
	// Prototype join only when both personality + magazine are visible.
	if ( $show_personality && $show_magazine ) {
		$grid_classes[] = 'hero-grid--joined';
	}
	// Bottom widgets fill the neighbor's row. The widget above stays on row 1.
	$span_logistics   = $show_logistics && ! $show_interviews && $show_magazine;
	$span_interviews  = $show_interviews && ! $show_logistics && $show_personality;
	$span_bottom      = $span_logistics || $span_interviews;
	$show_center_col  = $span_bottom ? $show_magazine : $show_center;
	$show_lead_col    = $span_bottom ? $show_personality : $show_lead;
	if ( $span_bottom ) {
		$grid_classes[] = 'hero-grid--span-bottom';
	}
	if ( ! $show_interviews && ! $show_logistics ) {
		$grid_classes[] = 'hero-grid--no-bottom';
	}
	$col_count = (int) $show_chamber + (int) $show_center_col + (int) $show_lead_col;
	if ( $span_bottom ) {
		$col_count = max( $col_count, 2 );
	}
	if ( 1 === $col_count ) {
		$grid_classes[] = 'hero-grid--solo';
	}
	$center_single = ! $span_bottom && ( ! $show_magazine || ! $show_interviews );
	$lead_single   = ! $span_bottom && ( ! $show_personality || ! $show_logistics );
	?>
	<section class="hero-section">
		<div class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>" data-cols="<?php echo esc_attr( (string) max( 1, $col_count ) ); ?>">
			<?php if ( $show_chamber ) : ?>
				<?php altijara_render_home_section( 'hero_chamber' ); ?>
			<?php endif; ?>
			<?php if ( $show_center_col ) : ?>
				<div class="hero-col-center<?php echo $center_single ? ' hero-col--single' : ''; ?>">
					<?php
					if ( $show_magazine ) {
						altijara_render_home_section( 'hero_magazine' );
					}
					if ( $show_interviews && ! $span_bottom ) {
						altijara_render_home_section( 'hero_interviews' );
					}
					?>
				</div>
			<?php endif; ?>
			<?php if ( $show_lead_col ) : ?>
				<div class="hero-col-lead<?php echo $lead_single ? ' hero-col--single' : ''; ?>">
					<?php
					if ( $show_personality ) {
						altijara_render_home_section( 'hero_personality' );
					}
					if ( $show_logistics && ! $span_bottom ) {
						altijara_render_home_section( 'hero_logistics' );
					}
					?>
				</div>
			<?php endif; ?>
			<?php if ( $span_logistics || $span_interviews ) : ?>
				<div class="hero-bottom-span">
					<?php altijara_render_home_section( $span_logistics ? 'hero_logistics' : 'hero_interviews' ); ?>
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
