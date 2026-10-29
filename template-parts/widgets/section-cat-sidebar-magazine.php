<?php
/**
 * Lower sidebar widget: magazine cover slider.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config   = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$count    = isset( $config['count'] ) ? max( 1, min( 20, (int) $config['count'] ) ) : 8;
$band_min = isset( $config['band_min'] ) ? max( 0, min( 9999, (int) $config['band_min'] ) ) : 0;
$band_max = isset( $config['band_max'] ) ? max( 0, min( 9999, (int) $config['band_max'] ) ) : 0;
$order    = ( isset( $config['order'] ) && 'ASC' === strtoupper( (string) $config['order'] ) ) ? 'ASC' : 'DESC';
$title    = isset( $config['label'] ) ? (string) $config['label'] : '';
$items    = function_exists( 'altijara_get_magazine_slider_items' )
	? altijara_get_magazine_slider_items( $count, $band_min, $band_max, $order )
	: array();

get_template_part(
	'template-parts/widgets/magazine-issues',
	'slider',
	array(
		'items' => $items,
		'title' => $title,
	)
);
