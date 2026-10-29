<?php
/**
 * Homepage sections — admin choices
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hierarchical category rows for admin pickers (main ? children).
 *
 * @return array<int, array{id:string,label:string,name:string,slug:string,depth:int,parent:int,count:int}>
 */
function altijara_get_category_choice_rows() {
	$terms = get_categories(
		array(
			'hide_empty'   => false,
			'orderby'      => 'name',
			'order'        => 'ASC',
			'hierarchical' => true,
		)
	);
	$rows = array(
		array(
			'id'     => '',
			'label'  => '— ' . __( 'None', 'altijara' ) . ' —',
			'name'   => '',
			'slug'   => '',
			'depth'  => 0,
			'parent' => 0,
			'count'  => -1,
		),
	);
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return $rows;
	}

	$by_parent = array();
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$parent = (int) $term->parent;
		if ( ! isset( $by_parent[ $parent ] ) ) {
			$by_parent[ $parent ] = array();
		}
		$by_parent[ $parent ][] = $term;
	}

	$walk = static function ( $parent_id, $depth ) use ( &$walk, &$rows, $by_parent ) {
		if ( empty( $by_parent[ $parent_id ] ) || ! is_array( $by_parent[ $parent_id ] ) ) {
			return;
		}
		foreach ( $by_parent[ $parent_id ] as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}
			$tid    = (int) $term->term_id;
			$name   = (string) $term->name;
			$slug   = (string) $term->slug;
			$indent = $depth > 0 ? str_repeat( '— ', $depth ) : '';
			$kind   = ( $depth > 0 )
				? __( 'Subcategory', 'altijara' )
				: ( ! empty( $by_parent[ $tid ] ) ? __( 'Main category', 'altijara' ) : __( 'Category', 'altijara' ) );
			$label  = $indent . $name;
			if ( $slug ) {
				$label .= ' (' . $slug . ')';
			}
			$rows[] = array(
				'id'     => (string) $tid,
				'label'  => $label,
				'name'   => $name,
				'slug'   => $slug,
				'depth'  => (int) $depth,
				'parent' => (int) $parent_id,
				'count'  => (int) $term->count,
				'kind'   => $kind,
			);
			$walk( $tid, $depth + 1 );
		}
	};
	$walk( 0, 0 );

	return $rows;
}

/**
 * Categories for admin dropdown — live WP categories by term ID only.
 *
 * @return array<string, string>
 */
function altijara_get_category_choices() {
	$choices = array();
	foreach ( altijara_get_category_choice_rows() as $row ) {
		if ( ! is_array( $row ) || ! array_key_exists( 'id', $row ) ) {
			continue;
		}
		$choices[ (string) $row['id'] ] = isset( $row['label'] ) ? (string) $row['label'] : (string) $row['id'];
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
	$none    = json_decode( '"\u2014 \u0628\u062f\u0648\u0646 \u2014"' );
	$choices = array( 0 => $none );
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
 * Published-post counts for the tag dropdown (0 means the tag is empty).
 *
 * @return array<int, int>
 */
function altijara_get_tag_choice_counts() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
			'orderby'    => 'name',
			'number'     => 200,
		)
	);
	$counts = array();
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return $counts;
	}
	foreach ( $terms as $term ) {
		if ( $term instanceof WP_Term ) {
			$counts[ (int) $term->term_id ] = (int) $term->count;
		}
	}
	return $counts;
}

/**
 * Published-post counts for the category dropdown (0 means the category is empty).
 *
 * @return array<int, int>
 */
function altijara_get_category_choice_counts() {
	$terms = get_categories(
		array(
			'hide_empty' => false,
			'orderby'    => 'name',
		)
	);
	$counts = array();
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return $counts;
	}
	foreach ( $terms as $term ) {
		if ( $term instanceof WP_Term ) {
			$counts[ (int) $term->term_id ] = (int) $term->count;
		}
	}
	return $counts;
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
