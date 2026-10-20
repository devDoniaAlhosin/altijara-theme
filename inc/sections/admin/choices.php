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
 * Categories for admin dropdown — live WP categories by term ID only.
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
	$choices = array( '' => '— ' . __( 'None', 'altijara' ) . ' —' );
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return $choices;
	}
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$choices[ (string) (int) $term->term_id ] = $term->name . ' (' . $term->slug . ')';
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
