<?php
/**
 * Widget: issues carousel band (filters by band_min / band_max when set).
 *
 * Trusts query-engine posts (already number-sorted + offset for lead/carousels).
 * Number bands are re-checked only as a safety net; do not re-fetch by date.
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$sid    = isset( $args['section_key'] ) ? (string) $args['section_key'] : 'issuesCarousel';
$min    = isset( $config['band_min'] ) ? (int) $config['band_min'] : 0;
$max    = isset( $config['band_max'] ) ? (int) $config['band_max'] : 0;
$picked = ! empty( $config['issue_ids'] ) && is_array( $config['issue_ids'] );
$count  = isset( $config['count'] ) ? max( 1, (int) $config['count'] ) : 0;
$order  = ( isset( $config['order'] ) && 'ASC' === strtoupper( (string) $config['order'] ) ) ? 'ASC' : 'DESC';

$apply_band = ! $picked && ( $min > 0 || $max > 0 );

// Keep number order even if a caller passed an unsorted list.
if ( ! $picked && function_exists( 'altijara_sort_issues_by_number' ) ) {
	$posts = altijara_sort_issues_by_number( $posts, $order );
}

$items = array();
foreach ( $posts as $post ) {
	if ( ! $post instanceof WP_Post ) {
		continue;
	}
	if ( $apply_band ) {
		$num = function_exists( 'altijara_issue_number_int' ) ? altijara_issue_number_int( $post ) : 0;
		if ( $num < 1 ) {
			continue;
		}
		if ( $min > 0 && $num < $min ) {
			continue;
		}
		if ( $max > 0 && $num > $max ) {
			continue;
		}
	}
	$items[] = $post;
}

if ( $count > 0 && count( $items ) > $count ) {
	$items = array_slice( $items, 0, $count );
}

if ( empty( $items ) ) {
	return;
}

$manual = function_exists( 'altijara_section_heading' )
	? altijara_section_heading( $config, '' )
	: ( isset( $config['label'] ) ? (string) $config['label'] : '' );
$manual = trim( (string) $manual );
if ( '' !== $manual ) {
	$title = $manual;
} elseif ( function_exists( 'altijara_issues_band_auto_title' ) ) {
	$title = altijara_issues_band_auto_title( $items, $order );
} else {
	$title = __( 'أعداد المجلة', 'altijara' );
}

get_template_part(
	'template-parts/archive/issues-carousel',
	null,
	array(
		'id'    => $sid . 'Track',
		'sec'   => $sid,
		'title' => $title,
		'items' => $items,
	)
);
