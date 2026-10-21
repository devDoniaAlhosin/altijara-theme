<?php
/**
 * Widget: issues carousel band (filters by band_min / band_max when set).
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$config = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$sid    = isset( $args['section_key'] ) ? (string) $args['section_key'] : 'issuesCarousel';
$title  = ! empty( $config['label'] ) ? (string) $config['label'] : __( 'أعداد المجلة', 'altijara' );
$min    = isset( $config['band_min'] ) ? (int) $config['band_min'] : 0;
$max    = isset( $config['band_max'] ) ? (int) $config['band_max'] : 0;

// Band filters need a larger pool than a single query page.
if ( ( $min > 0 || $max > 0 ) && function_exists( 'altijara_get_issue_posts' ) ) {
	$posts = altijara_get_issue_posts( 80, 0 );
}

$items = array();
foreach ( $posts as $post ) {
	if ( ! $post instanceof WP_Post ) {
		continue;
	}
	$num = function_exists( 'altijara_issue_number_int' ) ? altijara_issue_number_int( $post ) : 0;
	if ( $min > 0 && $num > 0 && $num < $min ) {
		continue;
	}
	if ( $max > 0 && $num > 0 && $num > $max ) {
		continue;
	}
	// When both bounds set and number missing, skip.
	if ( ( $min > 0 || $max > 0 ) && $num < 1 ) {
		continue;
	}
	$items[] = $post;
}

if ( empty( $items ) ) {
	return;
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
